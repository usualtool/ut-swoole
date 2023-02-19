<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Swoole WebSocket Chat</title>
</head>
<body>
    <h4>Swoole WebSocket Chat Demo</h4>
    <p>开启websocket服务：php usualtool swoole websocket 0.0.0.0 端口 more</p>
    <p>
        昵称：<input type="text" id="name" style="width:10%" value="游客<?php echo rand(1000,9999);?>"/>
    </p>
    <p>
        内容：<input type="text" id="content" style="width:20%">
        <button onclick="speak_to_all()">发送</button>
    </p>
    <br/><br/>
    <textarea id="message" style="overflow-x:hidden" rows="10" cols="50"></textarea>
    <p id="output"></p>
</body>
<script language="javascript" type="text/javascript">
    var wsUri ="ws://127.0.0.1:3003/";
    var output;
    function init() {
        output = document.getElementById("output");
        getWebSocket();
    }
    function getWebSocket() {
        websocket = new WebSocket(wsUri);
        websocket.onopen = function(evt) {
            onOpen(evt)
        };
        websocket.onclose = function(evt) {
            onClose(evt)
        };
        websocket.onmessage = function(evt) {
            onMessage(evt)
        };
        websocket.onerror = function(evt) {
            onError(evt)
        };
    }
    function get_speak_msg(){
        var name=document.getElementById("name").value;
        var speak=document.getElementById("content").value;
        var json_msg='{"name":"'+name+'","speak":\"'+speak+'"}';
        return json_msg;
    }
    function pack_msg(type,msg){
        return '{"type":"'+type+'","msg":'+msg+'}';
    }
    function onOpen(evt) {
        append_speak("已经连接服务器。");
        speak_msg=get_speak_msg();
        send_msg=pack_msg("login",speak_msg);
        doSend(send_msg);
    }
    function onClose(evt) {
        append_speak("俺老孙去也！");
    }
    function onMessage(evt) {
        append_speak(evt.data);
    }
    function onError(evt) {
        alert(evt.data);
    }
    function doSend(message) {
        websocket.send(message);
    }
    function append_speak(new_msg){
        document.getElementById("message").value=document.getElementById("message").value+new_msg+"\n";
        document.getElementById('message').scrollTop = document.getElementById('message').scrollHeight;
    }
    function speak_to_all(){
        send_msg=pack_msg("speak",get_speak_msg());
        if(document.getElementById("content").value==""){
            return;
        }
        console.log(send_msg);
        doSend(send_msg);
        document.getElementById("content").value="";
    }
    init();
</script>
</html>
