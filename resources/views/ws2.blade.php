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
        let token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sYXN3b29sZS50ZXN0XC9hcGlcL2xvZ2luIiwiaWF0IjoxNjcxMTgzMTI3LCJleHAiOjE2NzExODM3MjcsIm5iZiI6MTY3MTE4MzEyNywianRpIjoiaG15dmtES0tENjNSUDBXOSIsInN1YiI6MiwicHJ2IjoiMjNiZDVjODk0OWY2MDBhZGIzOWU3MDFjNDAwODcyZGI3YTU5NzZmNyJ9.LiPkGci3AYlbwK9celekC7CXiF4-z0C2ZYTE9cA2Wfk";
        // 初始化客户端套接字并建立连接
        let socket = new WebSocket("ws://laravel-s.com/ws",[token]);
        // 建立连接时触发
        socket.onopen = function(event) {
            console.log('连接开始...');
        }

        // 接收到服务器信息
        socket.onmessage = function(event) {
            let msg = event.data;
            console.log(msg);
            let node = document.createTextNode(msg);
            let div = document.createElement('div');
            div.appendChild(node);
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
                "user_id":1,
                "msg":msg,
                "type":"msg",
            }
            var json_data = JSON.stringify(data)
            socket.send(json_data);
            input.value="";
        }
    }
</script>
</body>
</html>
