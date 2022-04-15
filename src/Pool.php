<?php
namespace usualtool\Swoole;
use library\UsualToolMysql;
/**
 * 目前支持Mysql
 * $mode 默认0,0CLI模式 1客户端模式
 * $worker_num 默认5,进程数
 * $task_num 默认10,维持连接数
 * $dispatch_mode 默认2,1轮循 2固定 3抢占 4IP分配 5UID分配 7stream
 * $daemonize 默认0,守护进程 1开启 0关闭
 */
class Pool{
    protected $log_file;
    protected $max_request;
    public function __construct($host='127.0.0.1',$port='9510',$mode='0',$daemonize='0'){
        $this->host = $host;
        $this->port = $port;
        $this->mode = $mode;
        $this->worker_num = 5;
        $this->task_worker_num = 10;    
        $this->dispatch_mode = 2;
        $this->daemonize = $daemonize;
        $this->max_request = 10000;
        if($this->mode==0):
            $this->pool = new \Swoole\Server($this->host, $this->port);
            $this->pool->set(array(
                'worker_num'=>$this->worker_num,
                'task_worker_num' => $this->task_worker_num,
                'max_request' => $this->max_request,
                'daemonize' => $this->daemonize,
                'log_file' => APP_ROOT."/app/other.log",
                'dispatch_mode' => $this->dispatch_mode
            ));
        endif;
    }
    public function Run(){
        $this->pool->on('Receive', array($this, 'OnReceive'));
        $this->pool->on('Task', array($this, 'OnTask'));
        $this->pool->on('Finish', array($this, 'OnFinish'));        
        $this->pool->start();
    }
    public function OnReceive($serv, $fd, $from_id, $data){
        $result = $this->pool->taskwait($data);
        if ($result !== false) {
            $result=json_decode($result,true);
                  if ($result['status'] == 'OK') {
                $this->pool->send($fd, json_encode($result['data']) . "\n");
            } else {
                $this->pool->send($fd, $result);
            }
            return;
        } else {
            $this->pool->send($fd, "Error. Task timeout\n");
        }
    }
    public function OnTask($serv, $task_id, $from_id, $sql){
        static $link = null;
        UTKILL:
            if ($link == null) {
                $link = UsualToolMysql\UTMysql::GetMysql();
                if (!$link) {
                    $link = null;
                    $this->pool->finish(mysqli_error($link));
                    return;
                }   
            }   
        $result = $link->query($sql);
        if (!$result) {
            if(in_array(mysqli_errno($link),[2013,2006])){
                    $link = null;
                    goto UTKILL;
            }else{
                $this->pool->finish(mysqli_error($link));
                return;
            }
        }
        if(preg_match("/^select/i", $sql)){
             $data = array();
                while ($fetchResult = mysqli_fetch_assoc($result) ){
                     $data['data'][]=$fetchResult;
                }                
        }else{
            $data['data'] = $result;
        }
        $data['status']="OK";
        $this->pool->finish(json_encode($data));
    }
    public function OnFinish($serv, $task_id, $data){
        echo "done";
    }
    public function Query($sql){
        $timeout=20;
        $client=new \Swoole\Client(SWOOLE_SOCK_TCP);
        $client->connect($this->host,$this->port,$timeout) or die("connection failed");
        $client->send($sql);
        $data = $client->recv();
        $client->close();
        return json_decode($data,true);
    }
}
