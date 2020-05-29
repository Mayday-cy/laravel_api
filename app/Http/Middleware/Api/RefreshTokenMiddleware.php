<?php

namespace App\Http\Middleware\Api;

use Auth;
use Closure;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

// 注意，我们要继承的是 jwt 的 BaseMiddleware
class RefreshTokenMiddleware extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     * @throws \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     *
     * @throws TokenInvalidException
     */
    public function handle($request, Closure $next)
    {
        // 检查此次请求中是否带有 token，如果没有则抛出异常。
        $this->checkForToken($request);
        //1. 格式通过，验证是否是专属于这个的token

        //获取当前守护的名称
        $present_guard = Auth::getDefaultDriver();

        //获取当前token
        $token = Auth::getToken();

        //即使过期了，也能获取到token里的 载荷 信息。
        $payload = Auth::manager()->getJWTProvider()->decode($token->get());
        //如果不包含guard字段或者guard所对应的值与当前的guard守护值不相同
        //证明是不属于当前guard守护的token
        if (empty($payload['guard']) || $payload['guard'] != $present_guard) {
            throw new TokenInvalidException();
        }
        //使用 try 包裹，以捕捉 token 过期所抛出的 TokenExpiredException  异常
        //2. 此时进入的都是属于当前guard守护的token
        try {

            // 检测用户的登录状态，如果正常则通过
            if ($this->auth->parseToken()->authenticate()) {
                $user = Auth::user();
                if ($user->status == 1) {
                    return response()->json([
                        'message' => '该账号已被封禁，解封请联系客服!(ID:' . $user->uid . ')',
                        'code'    => 200
                    ], 200, ['Access-Control-Allow-Origin' => '*']);
                }
                return $next($request);
            }
            throw new UnauthorizedHttpException('jwt-auth', '未登录');
        } catch (TokenExpiredException $exception) {
            try {
                // 刷新用户的 token
                $token = $this->auth->refresh();
                // 使用一次性登录以保证此次请求的成功
                Auth::onceUsingId($this->auth->manager()->getPayloadFactory()->buildClaimsCollection()->toPlainArray()['sub']);

                //刷新了token，将token存入数据库
                $user = Auth::user();
                $user->last_token = $token;
                $user->save();
//                SaveLastTokenJob::dispatch($user, $token);

            } catch (JWTException $exception) {
                throw new UnauthorizedHttpException('jwt-auth', $exception->getMessage());
            }
        }

        // 在响应头中返回新的 token
        return $this->setAuthenticationHeader($next($request), $token);
    }
}
