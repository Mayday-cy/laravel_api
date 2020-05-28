<?php

namespace App\Providers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Log;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        $this->listenSqlExecute();
    }

    /**
     * 监听数据库执行语句
     */
    private function listenSqlExecute()
    {
        if (config('database.enableQueryLog')) {
            Event::listen(QueryExecuted::class, function ($query) {
                Log::getLogger('system.execute.sql')->info(
                    $query->sql,
                    [
                        'bindings' => $query->bindings,
                        'driver'   => $query->connection->getConfig('driver'),
                        'host'     => $query->connection->getConfig('host'),
                        'port'     => $query->connection->getConfig('port'),
                        'database' => $query->connection->getConfig('database'),
                        'time'     => $query->time,
                    ]
                );
            });
        }
    }
}
