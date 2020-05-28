<?php
return [
	'base' => [
		'gateway_url' => env('BASE_HTTP_GATEWAY_URL', 'https://user.sihehos.com'),
		'debug'       => env('BASE_HTTP_DEBUG', false),
		'timeout'     => env('BASE_HTTP_TIMEOUT', 5), // 全局超时时长
		'retry'       => env('BASE_HTTP_RETRY', 0), // 全局重试次数
		'sleep'       => env('BASE_HTTP_SLEEP', 0), // 睡眠时间,只有开启重试后会启用
	]
];
