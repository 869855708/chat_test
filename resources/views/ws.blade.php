<!DOCTYPE html>
<html>
<head>
    <title></title>
</head>
<body>
<input id="input" style="width: 100%;" >
<script>
    window.onload = function() {
        let nick = prompt('Enter your nickname');
        let input = document.getElementById('input');
        input.focus();
        let token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sYXJhdmVsLXMuY29tXC9hcGlcL2xvZ2luIiwiaWF0IjoxNjcxMTg2NTczLCJleHAiOjE2NzExODgzNzMsIm5iZiI6MTY3MTE4NjU3MywianRpIjoiYlBaT21wbzdxcUEzTkNoMiIsInN1YiI6MSwicHJ2IjoiMjNiZDVjODk0OWY2MDBhZGIzOWU3MDFjNDAwODcyZGI3YTU5NzZmNyJ9.o-fMbNwMFjJcBUF6ryPVXZvmgCVZNEXg4QSyF3IU8so";
        // 初始化客户端套接字并建立连接
        // let socket = new WebSocket("ws://laravel-s.com/ws",[token]);
        let socket = new WebSocket("ws://today.ouwentao.com/ws",[token]);

        console.log(socket.readyState);
        // 建立连接时触发
        socket.onopen = function(event) {
            console.log('连接开始...');
        }

        // 接收到服务器信息
        socket.onmessage = function(event) {
            let msg = event.data;
            let data = JSON.parse(msg);
            let node
            let div = document.createElement('div');
            if(data.code == 20000) {
                switch(data.type){
                    case 'msg':// 正常消息
                        var content = data.msg.username + '对您说:' + data.msg.contents
                        node = document.createTextNode(content);
                        div.appendChild(node);
                        break;
                    case 'auth':// 服务端验证消息
                        console.log(data.msg)
                        break;
                    default:
                        console.log('未知内容')
                        console.log(msg)
                        console.log(data)
                        break;
                }
            } else {
                console.log(msg);
            }

            document.body.insertBefore(div,input);
            input.scrollIntoView();
        }

        // 关闭连接时触发
        socket.onclose = function(event) {
            console.log('连接关闭...');
        }

        input.onchange = function() {
            // let msg = nick + ": " + input.value;
            let msg = input.value;
            var data = {
                "user_id":2,
                "msg":msg,
                "type":"msg",
            }
            var json_data = JSON.stringify(data)

            socket.send(json_data);
            var content = '你:' + msg;
            let node = document.createTextNode(content);
            let div = document.createElement('div');
            div.style.textAlign='right';
            div.appendChild(node);
            document.body.insertBefore(div,input);
            input.scrollIntoView();

            input.value="";
        }
    }
</script>
</body>
</html>
