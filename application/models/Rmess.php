<?php
/**
 * Created by PhpStorm.
 * User: HanYaMin
 * Date: 2017/11/23
 * Time: 17:18
 */
class RmessModel extends Model {

    var $mc;

    public function __construct()
    {
        parent::__construct();
        $this->setEntity('table_append_mess');
        $this->mc=new Mem();
    }

    public function postRmess($ap_parent_id,$ap_mess_content,$user_id,$user_name,$pid=0){
        $data=[
            'ap_mess_id'=>$this->getUniId(),
            'ap_parent_id'=>$ap_parent_id,
            'ap_mess_content'=>$ap_mess_content,
            'ap_user_id'=>$user_id,
            'ap_user_name'=>$user_name,
            'creat_time'=>date('Y-m-d H:i:s',time()),
            'operate_time'=>date('Y-m-d H:i:s',time()),
            'audit'=>0,
            'pid'=>$pid,
        ];
        $result=$this->insert($data);
        if($result){
            $this->mc->addCache($data['ap_mess_id'],$data);
            return array('success'=>true,'message'=>'回复成功');
        }else{
            return array('success'=>false,'message'=>'回复成功');
        }
    }
    public function deleteMessByParentId($parentId){
        $where=array('ap_parent_id'=>$parentId);
        $this->delete($where);
    }
    public function deleteMess($mess_id){
        $where=array('ap_mess_id'=>$mess_id);
        $result=$this->delete($where);
        if($result){
            $this->mc->deleteCache($mess_id);
            return array('success'=>true,'message'=>'删除成功');
        }else{
            return array('success'=>false,'message'=>'删除失败');
        }
    }
}