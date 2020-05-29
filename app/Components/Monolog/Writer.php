<?php

namespace App\Components\Monolog;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use App\Components\Monolog\Processor\ChannelProcessor;
use App\Components\Monolog\Processor\HostProcessor;
use App\Components\Monolog\Processor\UidProcessor;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Handler\HandlerInterface;

class Writer
{
	/**
	 * The Monolog logger instance.
	 *
	 * @var \Monolog\Logger
	 */
	protected $monolog;

	/**
	 * The Log levels.
	 *
	 * @var array
	 */
	protected $levels = [
		'debug'     => Logger::DEBUG,
		'info'      => Logger::INFO,
		'notice'    => Logger::NOTICE,
		'warning'   => Logger::WARNING,
		'error'     => Logger::ERROR,
		'critical'  => Logger::CRITICAL,
		'alert'     => Logger::ALERT,
		'emergency' => Logger::EMERGENCY,
	];

	/**
	 * Create a new log writer instance.
	 *
	 * @param  \Monolog\Logger $monolog
	 * @return void
	 */
	public function __construct(Logger $monolog)
	{
		$this->monolog = $monolog;
	}

	/**
	 * Register a file log handler.
	 *
	 * @param        $path
	 * @param   int  $level
	 * @throws \Exception
	 */
	public function useFiles($path, $level = 100)
	{
		$handler = new StreamHandler($path, $level);
		$this->setFormatter($handler);
	}

	/**
	 * 使用dateFile 形式来记录日志
	 *
	 * @param        $path
	 * @param int    $days
	 * @param int    $level
	 */
	public function useDateFiles($path, $days = 0, $level = 100)
	{
		$handler = new RotatingFileHandler($path, $days, $level);
		$handler->setFilenameFormat('{date}/{filename}', 'Y-m-d');
		$this->setFormatter($handler);
	}

	/**
	 * @param HandlerInterface $handler
	 */
	protected function setFormatter(HandlerInterface $handler)
	{
		$handler->setFormatter($this->getLineFormatter());
		$this->monolog->pushHandler($handler);
		// 增加当前脚本的文件名和类名等信息
		$this->monolog->pushProcessor(new IntrospectionProcessor(Logger::DEBUG, array('Illuminate\\')));
		// 把机器名称添加到日志中
		$this->monolog->pushProcessor(new HostProcessor());
		// 把频道名添加到日志中
//		$this->monolog->pushProcessor(new ChannelProcessor($this->monolog->getName()));
		// 把请求Uid添加到日志中
		if (config('logging.enable_log_uuid')) {
			$this->monolog->pushProcessor(new UidProcessor(24));
		}
	}

	/**
	 * Get a default Monolog formatter instance.
	 *
	 * @return \Monolog\Formatter\LineFormatter
	 */
	protected function getLineFormatter()
	{
		return new LineFormatter("%datetime% [%level_name%] : %extra.host_name%  %extra.file%:%extra.line% %extra.uid% %message% %context% \n" . PHP_EOL, 'Y-m-d H:i:s,u', true, true);
	}

	/**
	 * @return Logger
	 */
	public function getMonolog()
	{
		return $this->monolog;
	}
}
