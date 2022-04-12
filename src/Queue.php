<?php
namespace usualtool\Swoole;
use library\UsualToolInc;
/**
 * $task_worker_num 默认4,维持连接数
 * $daemonize 默认0,守护进程 1开启 0关闭
 */
class Queue{  
    protected $server = null;
    public function __construct($host='0.0.0.0',$port='5555',$daemonize='0'){
        $this->host = $host;
        $this->port = $port;
        $this->daemonize = $daemonize;
        $this->server = new \Swoole\Server($this->host,$this->port);  
        $this->server->set(array(
            'task_worker_num' => 4,
            'daemonize' => $this->daemonize
        ));
    }  
    public function Run(){
        $this->server->on('Receive', array($this, 'OnReceive'));
        $this->server->on('Task', array($this, 'OnTask'));  
        $this->server->on('Finish', array($this, 'OnFinish'));  
        $this->server->start(); 
    }
    public function OnReceive($serv, $fd, $from_id, $data){ 
        $this->server->task($data);  
    }
    public function OnTask($serv, $task_id, $from_id, $data){  
        $array = json_decode($data, true);  
        if ($array['url']) {  
            return $this->HttpGet($array['url'], $array['param']);  
        }
    }
    public function OnFinish($serv, $task_id, $data){  
        echo "Task {$task_id} finish.\r\n";
    }
    public function HttpGet($url,$data){
        if($data){
            if(UsualToolInc\UTInc::Contain("?",$url)){
                $url.='&'.http_build_query($data);
            }else{
                $url.='?'.http_build_query($data);
            }
        }
        $obj = curl_init();
        curl_setopt($obj, CURLOPT_URL, $url);
        curl_setopt($obj, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($obj, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($obj, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($obj, CURLOPT_HEADER, 0);
        $response = curl_exec($obj);
        curl_close($obj);
        return $response;
    }
}
