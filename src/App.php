<?php
/**
 * UsualTool Framework - Swoole HTTP 适配入口
 */
if(!defined('UTF_ROOT')){
    $root=getenv('UT_ROOT') ?: '';
    if($root===''){
        $dir=__DIR__;
        for($i=0;$i<8;$i++){
            if(is_file($dir.'/.ut.config')){
                $root=$dir;
                break;
            }
            $next=dirname($dir);
            if($next===$dir){
                break;
            }
            $dir=$next;
        }
    }
    define('UTF_ROOT',$root==='' ? dirname(__DIR__,2) : $root);
}
require_once UTF_ROOT.'/autoload.php';
use usualtool\Lib\Kernel;
/**
 * 从 Swoole 请求构造 PHP 超级全局
 */
$server=array();
if(isset($request) && is_object($request)){
    foreach((array)$request->server as $key=>$val){
        $server[strtoupper($key)]=$val;
    }
    foreach((array)$request->header as $key=>$val){
        $server['HTTP_'.strtoupper(str_replace('-','_',$key))]=$val;
    }
    $server['HTTP_HOST']=$server['HTTP_HOST'] ?? ($server['SERVER_NAME'] ?? 'localhost');
    $server['REQUEST_METHOD']=$server['REQUEST_METHOD'] ?? 'GET';
    $server['REQUEST_URI']=$server['REQUEST_URI'] ?? '/';
    $server['SCRIPT_NAME']=$server['SCRIPT_NAME'] ?? '/index.php';
    $server['QUERY_STRING']=$server['QUERY_STRING'] ?? (parse_url($server['REQUEST_URI'],PHP_URL_QUERY) ?: '');
    if(strtolower($server['HTTP_X_FORWARDED_PROTO'] ?? '')==='https'){
        $server['HTTPS']='on';
    }
    $get=is_array($request->get) ? $request->get : array();
    $post=is_array($request->post) ? $request->post : array();
    $cookie=is_array($request->cookie) ? $request->cookie : array();
}else{
    $get=$_GET;
    $post=$_POST;
    $cookie=$_COOKIE;
}
/**
 * 执行请求
 */
try{
    $result=Kernel::Handle(array(
        'server'=>$server,
        'get'=>$get,
        'post'=>$post,
        'cookie'=>$cookie,
        'session'=>true
    ));
}catch(\Throwable $e){
    if(isset($response) && is_object($response)){
        $response->status(500);
        $response->end('Swoole Error:'.$e->getMessage());
        return;
    }
    echo 'Swoole Error:'.$e->getMessage();
    return;
}
/**
 * 回写响应头
 */
if(isset($response) && is_object($response) && method_exists($response,'header')){
    $cookies=array();
    foreach($result['headers'] as $header){
        $pos=strpos($header,':');
        if($pos===false){
            continue;
        }
        $name=trim(substr($header,0,$pos));
        $value=trim(substr($header,$pos+1));
        if(strcasecmp($name,'Set-Cookie')===0){
            $cookies[]=$value;
        }else{
            $response->header($name,$value);
        }
    }
    if(!empty($cookies)){
        $response->header('Set-Cookie',$cookies);
    }
    if(!empty($result['status']) && $result['status']!==200){
        $response->status($result['status']);
    }
}
echo $result['output'];
