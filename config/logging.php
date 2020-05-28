<?php

use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'daily'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        'single' => [
            'driver' => 'single',
            'path'   => env('LOG_CHANNELS_SINGLE_PATH', storage_path('logs')),
            'level'  => env('LOG_CHANNELS_SINGLE_LEVEL', 'debug'),
        ],
        'daily'  => [
            'driver' => 'daily',
            'path'   => env('LOG_CHANNELS_DAILY_PATH', storage_path('logs')),
            'level'  => env('LOG_CHANNELS_DAILY_LEVEL', 'debug'),
            'days'   => 0,
        ],
    ],

    // 是否开启日志记录uuid
    'enable_log_uuid' => true

];
