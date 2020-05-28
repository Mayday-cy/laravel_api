<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * 创建人：Rex.栗田庆
 * 创建时间：2019-05-13 19:48
 * Class HTTP 服务 Facade
 * @method static \App\Components\Http\Request\WyRequest get($url, $queryParams = [])
 * @method static \App\Components\Http\Request\WyRequest with($driver)
 * @method static \App\Components\Http\Request\WyRequest post($url, $params = [])
 * @method static \App\Components\Http\Request\WyRequest patch($url, $params = [])
 * @method static \App\Components\Http\Request\WyRequest put($url, $params = [])
 * @method static \App\Components\Http\Request\WyRequest delete($url, $params = [])
 * @method static \App\Components\Http\Request\WyRequest asJson()
 * @method static \App\Components\Http\Request\WyRequest asFormParams()
 * @method static \App\Components\Http\Request\WyRequest contentType($contentType)
 * @method static \App\Components\Http\Request\WyRequest withOptions($options)
 * @method static \App\Components\Http\Request\WyRequest withCookie(array $cookie)
 * @method static \App\Components\Http\Request\WyRequest withHeaders($headers)
 * @method static \App\Components\Http\Request\WyRequest timeout($seconds)
 * @method static \App\Components\Http\Request\WyRequest retry($retry)
 * @method static \App\Components\Http\Request\WyRequest sleep($sleep)
 * @method static \App\Components\Http\Request\WyRequest send($method, $url, $options)
 * @method static array getOptions()
 * @method static \GuzzleHttp\Cookie\CookieJar getCookies()
 * @see \App\Components\Http\Request\WyRequest
 */
class Http extends Facade
{
	/**
	 * @return string
	 * @author chenpeng1@guahao.com
	 */
	protected static function getFacadeAccessor()
	{
		return 'http';
	}
}
