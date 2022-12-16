<?php

namespace App\Services;

use App\Logic\Chat;
use Hhxsv5\LaravelS\Swoole\WebSocketHandlerInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

class WebSocketService implements WebSocketHandlerInterface
{

    // 声明没有参数的构造函数
    public function __construct()
    {
    }

    // 客户端连接成功回调
    public function onOpen(Server $server, Request $request)
    {
        // 在触发onOpen事件之前，建立WebSocket的HTTP请求已经经过了Laravel的路由，
        // 所以Laravel的Request、Auth等信息是可读的，Session是可读写的，但仅限在onOpen事件中。
        // \Log::info('New WebSocket connection', [$request->fd, request()->all(), session()->getId(), session('xxx'), session(['yyy' => time()])]);
        // 此处抛出的异常会被上层捕获并记录到Swoole日志，开发者需要手动try/catch
        Log::info('WebSocket 建立连接');
        Log::info($request->fd . '进入房间');
//        $conn_list = $server->getClientList(0, 10);
//        Log::info('当前在线人数', $conn_list);

        $chat = new Chat();
        $user = $chat->authCheck($server, $request);
        if($user !== false){
            // 将用户与当前连接的socket id绑定
            $server->bind($request->fd, $user->id);
            Log::debug('绑定的用户：', $server->getClientInfo($request->fd));
        } else {
            // 没有令牌或者令牌异常 关闭ws连接 第二个参数reset设置为true会强制关闭连接，丢弃发送队列中的数据
            $server->close($request->fd, true);
        }
        //$server->push($request->fd, '欢迎来到LaravelS');
    }



    // 接收客户端发送的消息回调
    public function onMessage(Server $server, Frame $frame)
    {
        try {
            Log::info('收到 message', [$frame->fd, $frame->data, $frame->opcode, $frame->finish]);
            // 此处抛出的异常会被上层捕获并记录到Swoole日志，开发者需要手动try/catch
//            $server->push($frame->fd, date('Y-m-d H:i:s'));
            (new Chat())->messageHandling($server, $frame);
        }catch (\Throwable $e){
            Log::error('Error：'.$e->getMessage(), [
                'ErrorMsg'=>$e->getMessage(),
                'ErrorLine'=>$e->getLine(),
                'ErrorFile'=>$e->getFile(),
            ]);
        }

    }



    // 关闭连接回调
    public function onClose(Server $server, $fd, $reactorId)
    {
        Log::info('连接关闭, '.$fd . '离开房间');
        // 获取bind关联用户的连接信息
        $info = $server->getClientInfo($fd);
        Log::debug('连接信息：', $info);
        // 连接关闭时，删除redis中的记录 判断一下uid是否存在，因为如果没有通过token验证是没有进行bind操作所以不会存在uid
        if($info !== false && isset($info['uid'])){
            $user_key = Chat::KEY . $info['uid'];
            if(Redis::exists($user_key)){
                Redis::del($user_key);
            }
        }
    }
}
