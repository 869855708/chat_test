<?php

namespace App\Http\Middleware;

use Closure;

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

        if (!$request->header('Authorization')) {
            return response()->json(['msg' => '未登录'], 401);
        }

        return $next($request);
    }

}
