<?php
/**
 * Created by PhpStorm.
 * User: HanYaMin
 * Date: 2017/11/22
 * Time: 15:52
 */
class UserModel extends Model
{
    var  $mc;
    //如果没有构造函数会自动执行父类的构造函数
    //如果有了构造函数，就会执行自己的构造函数，如果此时还想执行父类的构造函数，必须显式调用parent::__construct();
    public function __construct()
    {
        parent::__construct();
        $this->mc=new Mem();
    }

    public function login($user_name,$pass_word){
        $cache=$this->mc->getCache($user_name);
        if(!$cache){
            $this->setEntity('table_user');
            $where=array('user_name'=>$user_name);
            $field=array('user_id','user_name','pass_word');
            $data=$this->getData($where,$field);
            $tempUser=$data;
            $this->mc->addCache($user_name,$tempUser);
        }else{
            $tempUser=$cache;
        }
        if(!empty($tempUser) && $tempUser['pass_word']==$pass_word){
            return array('user_id'=>$tempUser['user_id'],'user_name'=>$user_name);
        }else{
            return false;
        }
    }

    public function register(&$data){
        $data1=[
            'user_id'=>$this->getUniId(),
            'user_real_name'=>$data['user_real_name'],
            'city'=>$data['city'],
            'school'=>$data['school'],
            'picture'=>$data['picture'],
        ];
        $data2=[
            'user_id'=>$data1['user_id'],
            'user_name'=>$data['user_name'],
            'pass_word'=>$data['pass_word'],
            'creat_time'=>$data['creat_time'],
            'audit'=>0,
        ];
        $this->setEntity('table_user_info');
        $result1=$this->insert($data1);
        $this->setEntity('table_user');
        $result2=$this->insert($data2);
        if($result1 && $result2){
            //注册成功后加入缓存
            $this->mc->addCache($data2['user_name'],$data2);
            $this->mc->addCache($data1['user_id'].'picture',$data['picture']);
            return array('success'=>true,'message'=>'注册成功');
        }else{
            return array('success'=>false,'message'=>'注册失败');
        }
    }
}