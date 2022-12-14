<?php

namespace App\Logic;

use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;

class Chat
{

    //验证连接用户token
    public static function authCheck($server, $request)
    {
        // 获取token
        $token = '';
        if(isset($request->header['sec-websocket-protocol'])){
            $token = $request->header['sec-websocket-protocol'];
        }
        // token为空拒绝连接
        if(!empty($token)) {
            try {
                // 验证令牌是否有效
                if(JWTAuth::setToken($token)) {
                    // 获取用户信息
                    $user = JWTAuth::toUser();
                    if($user){
                        $data = [
                            'type'=>'auth',
                            'code'=>20000,
                            'msg'=>$user
                        ];
                        $server->push($request->fd, '欢迎 '. $user->name . ' 连接成功');
                        $server->push($request->fd, json_encode($data));
                    }
                    return true;
                } else {
                    //无效的用户
                    $data = [
                        'type' => 'auth',
                        'code' => 40000,
                        'msg'  => '获取用户失败，请检查令牌是否有效！',
                    ];
                    $server->push($request->fd, json_encode($data));
//                    $server->close($request->fd, true);
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
//                $server->close($request->fd, true);
                return false;
            }

        } else {
            // 没有令牌关闭ws连接 第二个参数reset设置为true会强制关闭连接，丢弃发送队列中的数据
//            $server->close($request->fd, true);
            return false;
        }
    }

    // 用户与fd标识绑定
    public function bind()
    {

    }
}
