<?php

namespace App\Exceptions;

use Illuminate\Support\Facades\Log;
use RuntimeException;

class ApiException extends RuntimeException
{
    /**
     * 自定义数据
     *
     * @var array
     */
    protected $data = [];

    /**
     * ApiException constructor.
     *
     * @param string $message
     * @param int $code
     * @param array $data
     */
    public function __construct($message = '', $code = 0, $data = [])
    {
        $this->data = $data;

        parent::__construct($message, $code);
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * 记录异常
     */
    public function report()
    {
        Log::getLogger(str_replace('/', '.', app('request')->path()))->info(static::class, [
            'code' => $this->getCode(),
            'msg'  => $this->getMessage(),
            'args' => app('request')->all(),
            'data' => $this->getData()
        ]);
    }

    /**
     * 将异常渲染到 HTTP 响应中
     *
     * @param $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function render($request)
    {
        return response()->json([
            'status'  => 'error',
            'message' => $this->getMessage(),
            'code'    => $this->getCode(),
            'data'    => [],
        ]);
    }
}
