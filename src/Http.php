<?php
namespace usualtool\Swoole;
class Http{
    protected $server = null;
    public function __construct($host,$port){
        $this->host = $host;
        $this->port = $port;
        $this->server = new \Swoole\Http\Server($this->host,$this->port);
        $this->server->set(array(
            "enable_static_handler" => true,
            "document_root" => UTF_ROOT,
            "worker_num"=>10
        ));
    }
    public function Run(){
        $this->server->on('WorkerStart', array($this, 'OnWorkerStart'));
        $this->server->on('Request', array($this, 'OnRequest'));
        $this->server->on('Close', array($this, 'OnClose'));
        $this->server->start();
    }
    public function onWorkerStart($serv,$worker_id){  
    }
    public function OnRequest(\Swoole\Http\Request $request,\Swoole\Http\Response $response){
        if($request->server){
            foreach ($request->server as $key => $val){
                $_SERVER[strtoupper($key)] = $val;
            }
        }
        if($request->header){
            foreach ($request->header as $key => $val){
                $_SERVER[strtoupper($key)] = $val;
            }
        }
        if($request->post){
            foreach ($request->post as $key => $val){
                $_POST[$key] = $val;
            }
        }
        if($request->server['request_uri'] == '/favicon.ico') {
            $response->status(404);
            $response->end();
            return;
        }
        ob_start();
        require_once 'App.php';
        $body = ob_get_contents();
        ob_end_clean();
        $response->end($body);
    }
    public function onClose(){
        echo"Close Server.\r\n";
    }
}
