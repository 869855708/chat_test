<?php

namespace App\Logic;

class Chat
{

    public static function authCheck($token, $server, $fd)
    {
        // token为空拒绝连接
        if(empty($token)) {
            // 关闭ws连接 第二个参数reset设置为true会强制关闭连接，丢弃发送队列中的数据
            $server->close($fd, true);
        }
    }

    // 用户与fd标识绑定
    public function bind()
    {

    }
}
