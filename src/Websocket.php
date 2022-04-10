<?php
namespace usualtool\Swoole;
class Websocket{
    private $server = null;
    public function __construct($host,$port){
        $this->host = $host;
        $this->port = $port;
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
        $server->push($request->fd,"hello,The ".$request->fd.".");
    }
    public function OnMessage($server,$frame){
        $server->push($frame->fd,$frame->data);
    }
    public function OnRequest($request,$response){
        foreach ($this->server->connections as $fd) {
            if($this->server->isEstablished($fd)){
                $this->server->push($fd,$request->get['message']);
            }
        }
    }
    public function OnClose($server, $fd){
        echo "The ".$fd." closed.\r\n";
    }
}
