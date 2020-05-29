<?php

namespace App\Components\Http;

use App\Components\Http\Request\Base;
use App\Contracts\Http\Factory;
use Illuminate\Support\Manager;

/**
 * Class HttpManager
 *
 * @package App\Library\Http
 */
class HttpManager extends Manager implements Factory
{
    /**
     * The application instance.
     *
     * @var
     */
    protected $app;

    /**
     * 选择服务
     *
     * @param null $name
     *
     * @return mixed
     */
    public function with($name = null)
    {
        return $this->driver($name);
    }

    /**
     * 创建base服务
     *
     * @return Base
     */
    public function createBaseDriver()
    {
        return new Base($this->app, $this->getConfig('base'));
    }

    /**
     *  获取默认驱动
     *
     * @return mixed
     */
    public function getDefaultDriver()
    {
        return 'base';
    }

    /**
     * 获取驱动对应的配置
     *
     * @param $name
     *
     * @return mixed
     */
    protected function getConfig($name)
    {
        return $this->app['config']['http'][$name];
    }
}
