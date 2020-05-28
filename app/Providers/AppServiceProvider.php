<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // 开发环境或者测试环境下 注册开发所需要的服务提供者
        if (in_array($this->app->environment(), ['local', 'dev', 'development'])) {
            array_map([$this->app, 'register'], config('app.devProviders'));
        }
    }
}
