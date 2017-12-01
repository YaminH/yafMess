<?php
/**
 * Created by PhpStorm.
 * User: HanYaMin
 * Date: 2017/11/22
 * Time: 15:59
 */
class Model
{
    protected $entity_name;
    protected $db;


    /**
     * Model constructor.
     */
    public function __construct()
    {
        $this->db=Db::getDb();
    }

    protected function getEntiy(){
        return $this->entity_name;
    }

    protected function setEntity($entity_name){
        $this->entity_name=$entity_name;
    }

    //select
    protected function getData($where=1,$field='*'){
        if(is_array($field)){
            $field=implode(',',$field);
        }
        $sql='select '.$field.' from '.$this->entity_name.' where ';
        if(is_array($where)){
            $key=array_keys($where);
            $keys=implode('=?,',$key);
            $sql.=$keys.'=?';
        }else{
            $sql.='1';
        }
        $sth=$this->db->prepare($sql);
        if(is_array($where)){
            $sth->execute(array_values($where));
        }else{
            $sth->execute();
        }
        $data=$sth->fetchAll();
         return $data;
    }

    //insert
    protected function insert($value){
        $field=implode(',',array_keys($value));
        $val=array_fill(0,count($value),'?');
        $sql='insert into '.$this->entity_name.' ('.$field.') values ( '.implode(',',$val).' )';
        $sth=$this->db->prepare($sql);
        $result=$sth->execute(array_values($value));
        return $result;
    }

    //delete
    protected function delete($where=1){
        $sql='delete from '.$this->entity_name.' where ';
        $newwhere=1;
        if(is_array($where)){
            foreach ($where as $key=>$value){
                $newwhere.=' and '.$key.'='.$value;
            }
        }
        $sql.=$newwhere;
        return $this->db->exec($sql);
    }

    //update
    protected function update($sets='',$where=1){
        $sql='update '.$this->entity_name.' set ';
        if(is_array($sets)){
            $key=array_keys($sets);
            $keys=implode("=?,",$key);
        }
        $sql.=$keys.'=? where ';
        $newwhere=1;
        if(is_array($where)){
            foreach ($where as $key=>$value){
                $newwhere.=' and '.$key.'='.$value;
            }
        }
        $sql.=$newwhere;
        $sth=$this->db->prepare($sql);
        $result=$sth->execute(array_values($sets));
        return $result;
    }

//    protected function getCache($cacheKey){
//        $key=md5($cacheKey);
//        return $this->mc->get($key);
//    }
//
//    protected function addCache($cacheKey,$value){
//        $key=md5($cacheKey);
//        //add($key,$val,$flag,$expire)
//        return $this->mc->add($key,$value,false,5);
//    }
//
//    public function deleteCache($cachekey){
//        $key=md5($cachekey);
//        return $this->mc->delete($key);
//    }
//
//    public function listCacheData(){
//        var_dump($this->mc->getStats());
//    }


    public  function getUniId(){
        $str=time();
        return  $str.str_pad(rand(0,10000),4,0);
    }
}