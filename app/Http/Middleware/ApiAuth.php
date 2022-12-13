<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\JWTAuth;

class ApiAuth
{

    /**
     * 处理传入的请求
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // 是否在header头提供了token
        if (!$request->bearerToken()) {
            return response()->json(['code' => 401, 'msg' => '未登录', 'data' => []], 401);
        }
        try {
            // 获取token
            $token = JWTAuth::getToken();
            if(JWTAuth::setToken($token) && !JWTAuth::toUser()) {
                return response()->json([
                    'code' => 404,
                    'msg' => '此用户不存在',
                    'data' => []
                ], 404);
            }
        } catch (TokenExpiredException $e) {
            // token过期
            return response()->json([
                'code' => 400,
                'msg' => 'Error: ' . $e->getMessage(),
                'data' => []
            ]);
        } catch (TokenInvalidException $e) {
            // token失效
            return response()->json([
                'code' => 400,
                'msg' => 'Error: token 失效',
                'data' => []
            ]);
        } catch (JWTException $e) {
            // token错误
            return response()->json([
                'code' => 400,
                'msg' => 'Token Error: ' . $e->getMessage(),
                'data' => []
            ]);
        }

        return $next($request);
    }

}
