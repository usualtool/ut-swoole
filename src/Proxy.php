<?php
namespace usualtool\Swoole;
use Swoole\Http\Request;
use Swoole\Http\Response;
class Proxy{
    protected $server = null;
    public function __construct($host,$port,$sock){
        $this->host = $host;
        $this->port = $port;
        $this->sock = $sock;
        $this->server = new \Swoole\Http\Server($this->host,$this->port,SWOOLE_BASE);
        $this->server->set(array(
            \Swoole\Constant::OPTION_WORKER_NUM => swoole_cpu_num() * 2,
            \Swoole\Constant::OPTION_HTTP_PARSE_COOKIE => false,
            \Swoole\Constant::OPTION_HTTP_PARSE_POST => false,
            \Swoole\Constant::OPTION_DOCUMENT_ROOT => UTF_ROOT,
            \Swoole\Constant::OPTION_ENABLE_STATIC_HANDLER => true,
            \Swoole\Constant::OPTION_STATIC_HANDLER_LOCATIONS => ['/']
        ));
    }
    public function Run(){
        $proxy = new \Swoole\Coroutine\FastCGI\Proxy("unix:/tmp/".$this->sock.".sock",UTF_ROOT);
        $this->server->on(
            'request',
            function (Request $request, Response $response) use ($proxy) {
                $proxy->pass($request, $response);
            }
        );
        $this->server->start();
    }
}
