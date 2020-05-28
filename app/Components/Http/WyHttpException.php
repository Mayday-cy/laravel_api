<?php

namespace App\Components\Http;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Wedoctor\Convention\Domain\Results;

/**
 * Class WyHttpException 封装http请求错误返回格式
 *
 * @package App\Library\Http
 */
class WyHttpException extends HttpResponseException
{
	protected $data;
	/**
	 * WyHttpException constructor.
	 *
	 * @param        $code
	 * @param string $message
	 * @param array  $data
	 * @throws \Illuminate\Contracts\Container\BindingResolutionException
	 */
	public function __construct($code, $message = '', $data = [])
	{
		$this->data = $data;
		$return = [
			'code' => -$code,
			'msg'  => $message ?: '网络拥挤，请稍后重试',
			'data' => [
				'error' => data_get($data, 'error', '')
			],
		];
		$this->report($data);
		
		parent::__construct(
			response()->json(
				Results::error($return['msg']),
				200
			)
		);
	}
	
	/**
	 * 记录异常
	 *
	 * @param $data
	 * @throws \Illuminate\Contracts\Container\BindingResolutionException
	 */
	public function report($data)
	{
		Log::getLogger('http.client.exception')->error(static::class, $data);
	}
	
	/**
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}
}
