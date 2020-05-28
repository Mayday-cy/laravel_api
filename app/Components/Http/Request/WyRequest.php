<?php

namespace App\Components\Http\Request;

use App\Components\Http\WyHttpException;
use App\Components\Http\Response\WyResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\TransferStats;
use GuzzleHttp\Cookie\CookieJar;
use App\Contracts\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Class WyRequest
 *
 * @package App\Library\Http\Request
 */
abstract class WyRequest implements Request
{

    protected $app;
    protected $options = [];
    /**
     * @var string 数据发送格式
     */
    protected $bodyFormat = 'json';
    /**
     * @var TransferStats 请求统计
     */
    protected $stat;
    /**
     * @var string 请求网关
     */
    protected $gatewayUrl;
    /**
     * @var int 重试次数
     */
    protected $retry;
    /**
     * @var int 重试间隔
     */
    protected $sleep;
    /**
     * @var array 服务配置
     */
    protected $config;
    protected $systemParams = [];
    protected $url;
    protected $args;

    public function __construct($app, array $config)
    {
        $this->app = $app;
        $this->config = $config;
        $this->gatewayUrl = $config['gateway_url'] ?? '';
        $this->retry = $config['retry'] ?? 0;
        $this->sleep = $config['sleep'] ?? 0;
        $this->options = [
            'timeout'     => $config['timeout'] ?? 3,
            'http_errors' => true,
            'on_stats'    => function (TransferStats $stats) {
                $this->stat = $stats;
            }
        ];
    }

    /**
     * get请求
     *
     * @param       $url
     * @param array $queryParams
     * @return WyResponse|null
     */
    public function get($url, $queryParams = [])
    {
        $this->url = $url;
        $this->args = $queryParams;
        $response = $this->send('GET', $url, [
            'query' => array_merge($this->systemParams, $queryParams),
        ]);

        return $response;
    }

    /**
     * post 请求
     *
     * @param       $url
     * @param array $params
     * @return WyResponse|null
     */
    public function post($url, $params = [])
    {
        $this->url = $url;
        $this->args = $params;
        $response = $this->send('POST', $url, [
            $this->bodyFormat => array_merge($this->systemParams, $params),
        ]);

        return $response;
    }

    /**
     * patch 请求
     *
     * @param string $url
     * @param array  $params
     *
     * @return mixed
     * @throws \Exception
     */
    public function patch($url, $params = [])
    {
        $this->url = $url;
        $this->args = $params;
        $response = $this->send('PATCH', $url, [
            $this->bodyFormat => array_merge($this->systemParams, $params),
        ]);

        return $response;
    }

    /**
     * put 请求
     *
     * @param string $url
     * @param array  $params
     *
     * @return mixed
     * @throws \Exception
     */
    public function put($url, $params = [])
    {
        $this->url = $url;
        $this->args = $params;
        $response = $this->send('PUT', $url, [
            $this->bodyFormat => array_merge($this->systemParams, $params),
        ]);

        return $response;
    }

    /**
     * delete 请求
     *
     * @param string $url
     * @param array  $params
     *
     * @return mixed
     * @throws \Exception
     */
    public function delete($url, $params = [])
    {
        $this->url = $url;
        $this->args = $params;
        $response = $this->send('DELETE', $url, [
            $this->bodyFormat => array_merge($this->systemParams, $params),
        ]);

        return $response;
    }

    /**
     * 发送json
     *
     * @return mixed
     */
    public function asJson()
    {
        return $this->bodyFormat('json')->contentType('application/json');
    }

    /**
     * 发送表单
     *
     * @return mixed
     */
    public function asFormParams()
    {
        return $this->bodyFormat('form_params')->contentType('application/x-www-form-urlencoded');
    }

    /**
     * @param $contentType
     * @return WyRequest
     */
    protected function contentType($contentType)
    {
        return $this->withHeaders(['Content-Type' => $contentType]);
    }

    /**
     * @param $format
     * @return $this
     */
    protected function bodyFormat($format)
    {
        $this->bodyFormat = $format;

        return $this;
    }

    /**
     * @param $options
     * @return $this
     */
    public function withOptions($options)
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    /**
     * 设置cookie值
     *
     * @param array $cookie
     * @return $this
     */
    public function withCookie(array $cookie)
    {
        $this->options = array_merge($this->options, [
            'cookies' => $cookie,
        ]);

        return $this;
    }

    /**
     * @param $headers
     * @return $this
     */
    public function withHeaders($headers)
    {
        if (!empty($this->options['headers'])) {
            $this->options['headers'] = array_merge($this->options['headers'], $headers);
        } else {
            $this->options = array_merge($this->options, [
                'headers' => $headers
            ]);
        }

        return $this;
    }

    /**
     * 设置服务机地址
     *
     * @param string $str
     * @return $this
     * @author zhangjf4@guahao.com
     */
    public function withPrefix($str)
    {
        return $this;
    }

    /**
     * 设置操作时间
     *
     * @param int $seconds
     * @return $this
     */
    public function timeout($seconds)
    {
        $this->options['timeout'] = $seconds;

        return $this;
    }

    /**
     * 设置尝试次数
     *
     * @param int $retry
     * @return $this
     */
    public function retry($retry)
    {
        if ($retry >= 0) {
            $this->retry = $retry;
        }

        return $this;
    }

    /**
     * 设置重试时请求间隔时间
     *
     * @param int $sleep
     * @return $this
     */
    public function sleep($sleep)
    {
        if ($sleep >= 0) {
            $this->sleep = $sleep;
        }

        return $this;
    }

    /**
     * 获取response对象
     *
     * @param $method
     * @param $url
     * @param $options
     * @return WyResponse
     * @throws GuzzleException
     */
    protected function getResponse($method, $url, $options)
    {
        return new WyResponse($this, $this->buildClient()->request($method, $url, $this->mergeOptions([
            'query' => $this->parseQueryParams($url)
        ], $options)));
    }

    /**
     * @param $method
     * @param $url
     * @param $options
     * @return WyResponse|null
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function send($method, $url, $options)
    {
        $response = null;
        beginning:
        try {
            $response = $this->getResponse($method, $url, $options);
        } catch (ConnectException $connectException) {
            $context = $connectException->getHandlerContext();

            // 超过重试次数则抛出异常
            if (!$this->retry) {
                throw new WyHttpException($context['errno'], '', [
                    'error'        => $context['error'],
                    'method'       => $method,
                    'url'          => $this->gatewayUrl . $this->getUrl(),
                    'args'         => $this->getArgs(),
                    'request_uri'  => app('request')->path(),
                    'request_args' => app('request')->all(),
                ]);
            }
            $this->retry--;
            if ($this->sleep) {
                usleep($this->sleep * 1000);
            }

            $this->logger([
                'method' => $method,
                'url'    => $this->gatewayUrl . $this->getUrl(),
                'args'   => $this->getArgs(),
                'errno'  => $context['errno'],
                'error'  => $context['error'],
                'retry'  => $this->retry,
                'sleep'  => $this->sleep
            ]);

            goto beginning;
        } catch (GuzzleException $exception) {
            throw new WyHttpException($exception->getCode(), '', [
                'error'        => $exception->getMessage(),
                'method'       => $method,
                'url'          => $this->gatewayUrl . $this->getUrl(),
                'args'         => $this->getArgs(),
                'request_uri'  => app('request')->path(),
                'request_args' => app('request')->all(),
            ]);
        }

        return $response;

    }

    /**
     * @return \GuzzleHttp\Client
     */
    protected function buildClient()
    {
        return (new Client([
            'base_uri' => $this->gatewayUrl
        ]));
    }

    /**
     * @param mixed ...$options
     * @return array
     */
    protected function mergeOptions(...$options)
    {
        $this->options = array_merge($this->options, ...$options);
        if ((data_get($this->options, 'cookies', []) instanceof CookieJar)) {
            $this->options['cookies'] = CookieJar::fromArray(data_get($this->options, 'cookies', []),
                parse_url($this->gatewayUrl)['host']);
        }

        return $this->options;
    }

    /**
     * @param $url
     * @return mixed
     */
    protected function parseQueryParams($url)
    {
        return tap([], function (&$query) use ($url) {
            parse_str(parse_url($url, PHP_URL_QUERY), $query);
        });
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * 返回cookie
     *
     * @return \GuzzleHttp\Cookie\CookieJar
     */
    public function getCookies()
    {
        return $this->options['cookies'] ?? new CookieJar();
    }

    /**
     * 获取请求统计
     *
     * @return TransferStats
     */
    public function getStat()
    {
        return $this->stat;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getArgs()
    {
        return $this->args;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function finalTransformer($obj)
    {
        return $this;
    }

    /**
     * 记录重试调用日志
     *
     * @param array $data
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function logger($data)
    {
        Log::getLogger(
            Str::lower(basename(str_replace('\\', '/', static::class))) . '.client.execute.info.retry'
        )->info($this->getUrl(), $data);
    }
}
