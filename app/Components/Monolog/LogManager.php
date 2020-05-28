<?php

namespace App\Components\Monolog;

use App\Contracts\Logging\Log;
use Throwable;
use Illuminate\Log\Logger;
use Monolog\Logger as Monolog;
use Illuminate\Log\LogManager as Manager;


/**
 * Class LogManager
 *
 * @package App\Wy\Monolog
 */
class LogManager extends Manager implements Log
{
	/**
	 * @var array
	 */
	protected $logger = [];

	/**
	 * @param null $logger
	 * @return $this|Log|mixed
	 * @throws \Illuminate\Contracts\Container\BindingResolutionException
	 */
	public function getLogger($logger = null)
	{
		if (empty($logger)) {
			return $this;
		}

		return $this->logger[$logger] ?? $this->createLogger($logger);
	}

	/**
	 * Attempt to get the log from the local cache.
	 *
	 * @param  string $name
	 * @return \Psr\Log\LoggerInterface
	 */
	protected function get($name)
	{
		try {
			return $this->logger[$this->logChannel()] ?? with($this->resolve($name), function ($logger) use ($name) {
					return $this->logger[$this->logChannel()] = $this->tap($name, new Logger($logger, $this->app['events']));
				});
		} catch (Throwable $e) {
			return tap($this->createEmergencyLogger(), function ($logger) use ($e) {
				$logger->emergency('Unable to create configured logger. Using emergency logger.', [
					'exception' => $e,
				]);
			});
		}
	}

	/**
	 * @param $name
	 * @return mixed
	 * @throws \Illuminate\Contracts\Container\BindingResolutionException
	 */
	public function createLogger($name)
	{
		$this->app->make('config')->set('app.log_channel', $name);

		return tap($this->driver(), function ($logger) {
			$this->app->make('config')->set('app.log_channel', null);
		});
	}


	/**
	 * @param array $config
	 * @return Monolog|\Psr\Log\LoggerInterface
	 * @throws \Illuminate\Contracts\Container\BindingResolutionException
	 */
	protected function createSingleDriver(array $config)
	{
		$logger = new Writer(
			new Monolog($this->logChannel())
		);
		$logger->useFiles(
			$this->getLogPath($config) . '/' . $this->logChannel() . '.log',
			$this->level($config)
		);

		return $logger->getMonolog();
	}

	/**
	 * @param array $config
	 * @return Monolog|\Psr\Log\LoggerInterface
	 * @throws \Illuminate\Contracts\Container\BindingResolutionException
	 */
	protected function createDailyDriver(array $config)
	{
		$logger = new Writer(
			new Monolog($this->logChannel())
		);
		$logger->useDateFiles(
			$this->getLogPath($config) . '/' . $this->logChannel() . '.log',
			$config['days'] ?? 0,
			$this->level($config)
		);

		return $logger->getMonolog();
	}


	/**
	 * @return bool|string
	 * @throws \Illuminate\Contracts\Container\BindingResolutionException
	 */
	public function logChannel()
	{
		if ($this->app->bound('config') &&
			$channel = $this->app->make('config')->get('app.log_channel')) {

			return $channel;
		}

		return $this->app->bound('env') ? $this->app->environment() : 'production';
	}

	/**
	 * Extract the log path from the given configuration.
	 *
	 * @param $config
	 * @return string
	 */
	protected function getLogPath($config)
	{
		return $config['path'] ?? $this->app->storagePath('logs');
	}

}
