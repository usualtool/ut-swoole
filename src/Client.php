<?php
namespace usualtool\Swoole;
class Client{  
    protected $client = null;
    public function __construct($host='0.0.0.0',$port='5555'){
        $this->host = $host;
        $this->port = $port;
        $this->client = new \Swoole\Client(SWOOLE_SOCK_TCP);  
    }
    public function Run(){  
        if (!$this->client->connect($this->host,$this->port)){  
            echo "Swoole Error: ".$this->client->errCode;  
        }
    }
    public function Send($data){  
        if ($this->client->isConnected()){  
            if (!is_string($data)) {  
                $data = json_encode($data);  
            }
            return $this->client->send($data);  
        } else {  
            echo " Swoole Server does not connected. ";  
        }  
    }
    public function Close(){  
        $this->client->close();  
    }  
}
