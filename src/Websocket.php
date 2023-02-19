<?php
namespace usualtool\Swoole;
class Websocket{
    private $server = null;
    public function __construct($host,$port,$type='only'){
        $this->host = $host;
        $this->port = $port;
        $this->type = $type;
        $this->server = new \Swoole\Websocket\Server($this->host,$this->port);
    }
    public function Run(){
        $this->server->on("open", [$this, "OnOpen"]);
        $this->server->on("message", [$this, "OnMessage"]);
        $this->server->on("request", [$this, "OnRequest"]);
        $this->server->on("close", [$this, "OnClose"]);
        $this->server->start();
    }
    public function OnOpen($server,$request){
        //$server->push($request->fd,"hello,The ".$request->fd.".");
    }
    public function OnMessage($server,$frame){
        //对单连接
        if(empty($this->type) || $this->type=="only"){
            $server->push($frame->fd,$frame->data);
        //对多连接
        }elseif($this->type=="more"){
            //数据格式{"type":"speak","msg":{"name":"昵称","speak":"内容"}}
            $data=json_decode($frame->data, true);
            if($data["type"]=="login"){
                $send_msg = "进入了房间。";
            }elseif($data["type"]=="logout"){
                $send_msg = "退出了房间。";
            }else{
                $send_msg = ":".$data["msg"]["speak"];
            }
            foreach($server->connections as $fd){
                $name=$data['msg']['name'];
                $server->push($fd,$name.$send_msg);
            }
        }
    }
    public function OnRequest($request,$response){
        foreach ($this->server->connections as $fd) {
            if($this->server->isEstablished($fd)){
                $this->server->push($fd,$request->get['message']);
            }
        }
    }
    public function OnClose($server, $fd){
        //echo "The ".$fd." closed.\r\n";
    }
}
