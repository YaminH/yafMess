<?php
/**
 * Created by PhpStorm.
 * User: HanYaMin
 * Date: 2017/11/27
 * Time: 13:48
 */
class MemcachedAggregator{
    var $connections;

    //构造函数
    public function __construct($servers){
        //建立和检索到所有服务器的持久连接
        //如果其中一个服务器宕机，则不会进入当前的活动列表
        $this->connections=array();
        foreach ($servers as $server){
            $con=memcache_connect($server['host'],$server['port']);
            if($con!=false){
                $this->connections[]=$con;
            }
        }
    }

    private function _getConForKey($key){
        $hashCode=0;
        for($i=0,$len=strlen($key);$i<$len;++$i){
            //ord()  返回字符的ASCII码值
            $hashCode=(int)(($hashCode*33)+ord($key[$i])) & 0x7FFFFFFF;
        }
        if(($ns=count($this->connections))>0){
            return $this->connections[$hashCode%$ns];
        }
        return false;
    }

    //debug
    public function debug($on_off){
        $result=false;
        for($i=0;$i <count($this->connections);++$i){
            if($this->connections[$i]->debug($on_off)) $result=true;
        }
        return $result;
    }

    //flush 删除存储的所有元素
    public function flush(){
        $result=false;
        for($i=0;$i<count($this->connections);++$i){
            if($this->connections[$i]->flush()) $result=true;
        }
        return $result;
    }

    //getStats() 获取服务器统计信息
    public function getStats(){

    }

    //getVersion() 返回服务器版本信息
    public function getVersion(){

    }

    public function get($key){
        if(is_array($key)){
            $dest=array();
            foreach ($key as $subkey){
                $val=get($key);
                if(!($val === false)) $dest[$subkey]=$val;
            }
            return $dest;
        }else{
            return $this->_getConForKey($key)->get($key);
        }
    }

    //$compress
    //$expire 过期时间
    public function set($key,$value,$compress=0,$expire=0){
        return $this->_getConForKey($key)->set($key,$value,$compress,$expire);
    }

    public function add($key,$var,$compress=0,$expire=0){
        return $this->_getConForKey($key)->add($key,$var,$compress,$expire);
    }

    public function replace($key,$var,$compress=0,$compress=0){
        return $this->_getConForKey($key)->replace($key,$var,$compress,$expire);
    }

    public function delete($key,$timeout=0){
        return $this->_getConForKey($key)->delete($key,$timeout);
    }

    public function increment($key,$value=1){
        return $this->_getConForKey($key)->increment($key,$value);
    }

    public function decrement($key,$value=1){
        return $this->_getConForKey($key)->decrement($key,$value);
    }
}