<?php
/**
 * Created by PhpStorm.
 * User: HanYaMin
 * Date: 2017/11/28
 * Time: 16:00
 */
class Mem{

    public $con;

    public function __construct()
    {
        $this->con=new Memcache();
        $this->con->connect('localhost',11211);
    }
    
    public function getKey($operate,$table_name,$where=1,$field='*'){
        if(is_array($where)){
            $where=implode("_",$where);
        }
        if(is_array($field)){
            $field=implode("_",$field);
        }
        return md5($operate.$table_name.$where.$field);
    }
    
    public function getCache($key){
        return $this->con->get($key);
    }

    //set 如果key已经存在,则更新value值
    public function setCache($key,$value,$compress=0,$expire=0){
        return $this->con->set($key,$value,$compress,$expire);
    }

    //add 如果key已经存在，则不会更新value值
    public function addCache($key,$var,$compress=0,$expire=0){
        return $this->con->add($key,$var,$compress,$expire);
    }

    //替换已经存在的value值，如果key不存在，则替换失败
    public function replaceCache($key,$var,$compress=0,$expire=0){
        return $this->con->replace($key,$var,$compress,$expire);
    }

    //删除已存在的key
    public function deleteCache($key,$timeout=0){
        return $this->con->delete($key,$timeout);
    }

    //对已经存在的key键的数字值进行自增操作
    public function incrementCache($key,$value=1){
        return $this->con->increment($key,$value);
    }

    //对已经存在的key键的数字值进行自减操作
    public function decrementCache($key,$value=1){
        return $this->con->decrement($key,$value);
    }

    //清理缓存
    public function flushCache(){
        return $this->con->flush();
    }

}