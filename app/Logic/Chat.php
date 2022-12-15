<?php

namespace App\Logic;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;

class Chat
{

    //验证连接用户token
    public static function authCheck($server, $request)
    {
        // 获取token
        $token = '';
        if (isset($request->header['sec-websocket-protocol'])) {
            $token = $request->header['sec-websocket-protocol'];
        }
        // token为空拒绝连接
        if (!empty($token)) {
            try {
                // 验证令牌是否有效
                if (JWTAuth::setToken($token)) {
                    // 获取用户信息
                    $user = JWTAuth::toUser();
                    if ($user) {
//                        self::redis_set($user->id, $request->fd);
                        $data = [
                            'type' => 'auth',
                            'code' => 20000,
                            'msg'  => $user
                        ];
                        $server->push($request->fd, '欢迎 ' . $user->name . ' 连接成功');
                        $server->push($request->fd, json_encode($data));
                    }
                    return $user;
                } else {
                    //无效的用户
                    $data = [
                        'type' => 'auth',
                        'code' => 40000,
                        'msg'  => '获取用户失败，请检查令牌是否有效！',
                    ];
                    $server->push($request->fd, json_encode($data));
                    return false;
                }
            } catch (TokenExpiredException $e) {
                $data = [
                    'type' => 'auth',
                    'code' => 40001,
                    'msg'  => $e->getMessage(),
                ];
                // 发送错误后断开连接
                $server->push($request->fd, json_encode($data));
                return false;
            }

        } else {
            return false;
        }
    }

    // 用户与fd标识绑定
    public function bind()
    {

    }

    public function redis_set($userId, $fd)
    {
        $key = 'chat_user_' . $userId;
        Redis::SET($key, $fd);
    }
}
