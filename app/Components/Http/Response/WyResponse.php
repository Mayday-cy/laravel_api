<?php

namespace App\Components\Http\Response;

use App\Components\Http\Request\WyRequest;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use \App\Contracts\Http\Response;

/**
 * Class WyResponse
 *
 * @package App\Library\Http
 */
class WyResponse implements Response, Arrayable, \ArrayAccess
{

	/**
	 * @var \Psr\Http\Message\ResponseInterface
	 */
	protected $response;

    /**
     * @var WyRequest
     */
	protected $wyRequest;
	/**
	 * @var array 请求响应内容
	 */
	protected $contents;
	/**
	 * @var int 格式化时间时间
	 */
	protected $formatTime;


	/**
	 * WyResponse constructor.
	 *
	 * @param WyRequest         $request
	 * @param ResponseInterface $response
	 */
	public function __construct(WyRequest $request, ResponseInterface $response)
	{
		$this->wyRequest = $request;
		$this->response = $response;
		$this->formatContents();
	}

	protected function formatContents()
	{
		$formatTime = microtime(1);
		$this->contents = (array)json_decode($this->response->getBody(), true);
		$this->formatTime = round(microtime(1) - $formatTime, 4);
	}


	/**
	 * 创建http响应
	 *
	 * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
	 */
	public function toResponse()
	{
		return response()->json($this->toArray());
	}

	public function toArray()
	{
		$data = $this->contents;
		$stat = $this->wyRequest->getStat();
		//格式化数据时间
		$data['bhc_info']['format_time'] = $this->formatTime;
		//建立连接所消耗的时间
		$data['bhc_info']['connect_time'] = $stat->gethandlerStats()['connect_time'];
		//数据传输所消耗的时间
		$data['bhc_info']['down_time'] = $stat->gethandlerStats()['starttransfer_time'];
		//下载数据量的大小
		$data['bhc_info']['down_size'] = $stat->gethandlerStats()['size_download'];
		//平均下载速度
		$data['bhc_info']['down_speed'] = $stat->gethandlerStats()['speed_download'];
		//总的消耗时间
		$data['bhc_info']['total_time'] = $stat->gethandlerStats()['total_time'];
		//请求方法
		$data['bhc_info']['method'] = $this->wyRequest->getUrl();
		//请求参数
		$data['bhc_info']['args'] = $this->wyRequest->getArgs();
		//请求配置
		$data['bhc_info']['config'] = $this->wyRequest->getConfig();
		//请求头信息
		$data['bhc_info']['headers'] = data_get($this->wyRequest->getOptions(), 'headers', []);
		//REST请求地址
		$data['bhc_info']['request_uri'] = app('request')->path();
		//REST请求参数
		$data['bhc_info']['request_args'] = app('request')->all();

		$this->logger($data);
		// 开启 debug 输出统计数据
		if (!$this->wyRequest->getConfig()['debug']) {
			unset($data['bhc_info']);
		}

		return $data;
	}

	/**
	 * 把响应的值转换为数组
	 *
	 * @return array
	 */
	public function getContent()
	{
		return $this->toArray();
	}


	/**
	 * @param $header
	 *
	 * @return string
	 */
	public function header($header)
	{
		return $this->response->getHeaderLine($header);
	}


	/**
	 * 返回所有的header
	 *
	 * @return array
	 */
	public function headers()
	{
		return array_map(function ($item) {
			return $item[0];
		}, $this->response->getHeaders());
	}


	/**
	 * 获取响应的状态码
	 *
	 * @return int
	 */
	public function status()
	{
		return $this->response->getStatusCode();
	}


	/**
	 * 获取cookies
	 *
	 * @return CookieJar
	 */
	public function getCookies()
	{
		return $this->wyRequest->getCookies();
	}


	/**
	 * 获取指定的cookie
	 *
	 * @param string $name
	 *
	 * @return \GuzzleHttp\Cookie\SetCookie
	 */
	public function getCookie($name)
	{
		return $this->getCookies()->getCookieByName($name) ?? new SetCookie();
	}

	/**
	 *  记录调用日志
	 *
	 * @param array $data
	 * @throws \Illuminate\Contracts\Container\BindingResolutionException
	 */
	protected function logger($data)
	{
		// 防止返回大内容写入文件
//		unset($data['data']);
		Log::getLogger(
			Str::lower(basename(str_replace('\\', '/', get_class($this->wyRequest)))) . '.client.execute.info'
		)->info($this->wyRequest->getUrl(), $data);
	}

	/**
	 * @inheritdoc
	 *
	 * @param mixed $key
	 *
	 * @return bool
	 */
	public function offsetExists($key)
	{
		return array_key_exists($key, $this->contents);
	}

	/**
	 * @inheritdoc
	 *
	 * @param mixed $key
	 *
	 * @return mixed|null
	 */
	public function offsetGet($key)
	{
		return $this->contents[$key] ?? null;
	}

	/**
	 * @inheritdoc
	 *
	 * @param mixed $key
	 * @param mixed $value
	 *
	 */
	public function offsetSet($key, $value)
	{
		if (is_null($key)) {
			$this->contents[] = $value;
		} else {
			$this->contents[$key] = $value;
		}
	}

	/**
	 * @inheritdoc
	 *
	 * @param mixed $key
	 *
	 */
	public function offsetUnset($key)
	{
		unset($this->contents[$key]);
	}

	public function __call($method, $args)
	{
		return $this->response->{$method}(...$args);
	}
}
