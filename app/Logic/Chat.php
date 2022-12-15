<?php

namespace App\Logic;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;

class Chat
{

    // 聊天室用户key
    const KEY = 'chat_user_';

    //验证连接用户token
    public function authCheck($server, $request)
    {
        // 在连接的自定义协议中 获取token
        $token = '';
        if (isset($request->header['sec-websocket-protocol'])) {
            $token = $request->header['sec-websocket-protocol'];
        }

        if (!empty($token)) {
            try {
                // 验证令牌是否有效
                if (JWTAuth::setToken($token)) {
                    // 获取用户信息
                    $user = JWTAuth::toUser();
                    if ($user) {
                        $this->redisSet($user->id, $request->fd);
                        $this->pushMessage($server, $request->fd, '欢迎 ' . $user->name . ' 连接成功', 'auth');
                        $this->pushMessage($server, $request->fd, $user, 'auth');
                    }
                    return $user;
                } else {
                    //无效的用户
                    $this->pushMessage($server, $request->fd, '获取用户失败，请检查令牌是否有效！', 'auth', 40000);
                    return false;
                }
            } catch (TokenExpiredException $e) {
                // 发送错误后断开连接
                $this->pushMessage($server, $request->fd, $e->getMessage(), 'auth', 40001);
                return false;
            }
        } else {
            // token为空拒绝连接
            return false;
        }
    }


    /**
     * 消息处理
     * $server  swoole服务对象
     * $frame   swoole_websocket_frame对象，包含了客户端发来的数据帧信息
     * @return void
     */
    public function messageHandling($server, $frame)
    {
        /*
         * {"user_id":1,"msg":"您好","type":"msg"}
         * user_id  接收消息的用户ID（给指定的用户发送消息）
         * msg      消息内容
         * type     消息类型
         */
        // 消息内容
        $data = json_decode($frame->data,true);
        if(!empty($data['type'])){
            switch ($data['type']) {
                case 'msg': //普通消息处理
                    $userFd = $this->redisGet(2);
                    // 用户socket Id存在表示连接中，可以发送消息
                    if($userFd){
                        $user = JWTAuth::toUser();
                        $this->pushMessage($server, $userFd, ['user'=>$user->name, 'msg'=>$data['msg']]);
                    } else {
                        // 不存在表示用户当前没有进行连接，可以将消息加入数据库操作
                    }
                    break;
                case 'tentative':// 暂定
                    break;
                default:
                    $this->pushMessage($server, $frame->fd, '服务端无法处理您发送的消息, 请确认消息数据是否有效！', 'error', 40000);
                    break;
            }
        } else {
            $this->pushMessage($server, $frame->fd, '服务端无法处理您发送的消息, 请确认消息数据是否有效！', 'error', 40000);
        }
    }

    // 用户与fd标识绑定
    public function bind()
    {

    }


    /**
     * 发送消息
     * @$server     swoole服务对象
     * @$request    swoole请求信息对象
     * @$msg        消息内容
     * @$type       消息类型
     * @$code       消息状态码
     * @return void
     */
    public function pushMessage($server, $fd, $msg, $type = 'msg', $code=20000)
    {
        $contents = [
            'type' => $type,
            'code' => $code,
            'msg'  => $msg,
        ];

        $server->push($fd, json_encode($contents, JSON_UNESCAPED_UNICODE));
    }


    // 用户ID与socketId进行逻辑关联
    public function redisSet($userId, $fd)
    {
        $key = self::KEY . $userId;
        Redis::SET($key, $fd);
    }


    // 获取用户的socket ID
    public function redisGet($userId)
    {
        $key = self::KEY . $userId;
        $value = Redis::get($key);
        return $value;
    }
}
