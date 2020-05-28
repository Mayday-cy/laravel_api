<?php

namespace App\Components\Monolog\Processor;

/**
 * Class ChannelProcessor
 *
 * @package App\Wy\Monolog\Processor
 */
class ChannelProcessor
{
	protected $name;

	/**
	 * ChannelProcessor constructor.
	 *
	 * @param string $name
	 */
	public function __construct($name = 'local')
	{
		$this->name = $name;
	}

	/**
	 * 获取频道名称
	 *
	 * @param array $record
	 *
	 * @return array
	 */
	public function __invoke(array $record)
	{
		$record['extra']['channel_name'] = $this->name;

		return $record;
	}
}
