<?php

namespace App\Contracts\Logging;

interface Log
{
	/**
	 * 日志主题
	 *
	 * @param $topic
	 * @return $this
	 */
	public function getLogger($topic);
}
