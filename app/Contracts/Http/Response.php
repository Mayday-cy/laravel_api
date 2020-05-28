<?php

namespace App\Contracts\Http;

interface Response
{


	/**
	 * @param $header
	 *
	 * @return string
	 */
	public function header($header);


	/**
	 * 返回所有的header
	 *
	 * @return array
	 */
	public function headers();


	/**
	 * 获取响应的状态码
	 *
	 * @return int
	 */
	public function status();


	/**
	 * 获取cookies
	 *
	 * @return \GuzzleHttp\Cookie\CookieJar
	 */
	public function getCookies();


	/**
	 * 获取指定的cookie
	 *
	 * @param string $name
	 *
	 * @return \GuzzleHttp\Cookie\SetCookie|null
	 */
	public function getCookie($name);

}
