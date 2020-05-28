<?php

namespace App\Providers;

use App\Components\Monolog\LogManager;
use Illuminate\Support\ServiceProvider;

class MonologServiceProvider extends ServiceProvider
{

	public function register()
	{
		$this->app->singleton('log', function () {
			return new LogManager($this->app);
		});
	}
}
