<?php
/**
 * Created by PhpStorm.
 * User: HanYaMin
 * Date: 2017/11/22
 * Time: 15:14
 */
class MesslistController extends Yaf_Controller_Abstract{
    private $mess;
    private $rmess;
    public function init(){    //类似构造函数
        $this->_session = Yaf_Session::getInstance();
        $this->_session->start();
        if(!$this->getRequest()->isXmlHttpRequest()){
            Yaf_Dispatcher::getInstance()->enableView();
        }else{
            //如果是Ajax请求, 关闭自动渲染, 返回Json响应
            Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        }
        $this->mess = new MesslistModel();
        $this->rmess = new RmessModel();
    }

    public function indexAction(){
        $name=$this->_session->get('user_name');
        $this->getView()->assign('user_name',$name);   //分配到phtml
        $id=$this->_session->get('user_id');
        $this->getView()->assign('user_id',$id);     //分配到phtml
        $curPage=1;
        $list=$this->mess->getList($curPage);
        $this->getView()->assign('list',$list);
        $this->getView()->assign('curPage',$curPage);
    }

    public function postAction(){
        if($this->getRequest()->isXmlHttpRequest()){
            //获取post提交的参数
            $new_mess = $this->getRequest()->getPost('new_mess');
            $user_id=$this->_session->get('user_id');
            $user_name=$this->_session->get('user_name');
            $data=$this->mess->post($user_id,$user_name,$new_mess);
            if($data['success']){
                $data['htmltxt']=$this->gethtmltxt(1);
            }
            echo json_encode($data);
            exit;
        }
    }

    private function gethtmltxt($curPage){
        $user_id=$this->_session->get('user_id');
        $list=$this->mess->getList($curPage);
        if(!$list) return false;
        $result="";
        foreach ($list as $item){
            $un=strip_tags($item['user_name']);    //strip_tags()去除字符串中所有的HTML，JavaScript和PHP标签
            $mc=strip_tags($item['mess_content']);
            $result.= "<div>
                           <div>用户名:{$un}<br/>
                           <img src='../img/{$item['picture']}' width='100' height='100' /><br/>
                           </div>
                            <div >
                                <div>发表时间:{$item['creat_time']}
                                    <button class=\"btn btn-xs\" onclick=\"inform({$item['mess_id']})\">举报</button><br/>
                                 </div>                    
                                <div>
                                <textarea class=\"form-control\" id=\"{$item['mess_id']}\" disabled='disabled' cols='80' rows='5'>{$mc}</textarea>";
            if($user_id==$item['user_id']){
                $result.=  "<div class=\"btn-group\" id='button_group'>  
                                                <a onclick=\"editMess({$item['mess_id']})\">  <span class=\"glyphicon glyphicon-pencil\"></span>  </a>
                                                <a onclick=\"deleteMess({$item['mess_id']})\" >  <span class=\"glyphicon glyphicon-trash\"></span> </a>
                                            </div>";
            }
            $result.= "</div>";
            if(isset($item['children'])){  $result.= $this->loop($user_id,$item['mess_id'],$un,$item['children']);}
            $result.= "<div style=\"padding: 0px 50px 0px;\">
                    回复：<textarea class=\"form-control\" id=\"rv{$item['mess_id']}\"  name=\"res_content\" cols='50' rows='1'></textarea>
                   <button class=\"btn btn-sm\" onclick=\"subRes({$item['mess_id']})\">提交</button>
                   </div>
                </div>
                </div><br/>";
        }
        $result.= "<br/>当前为第<input id='curPage' value='{$curPage}'></input>页";
        return $result;
    }
    private function loop($user_id,$parent_id,$un,$item){
        $result="";
        foreach ($item as $r) {
            $aun = $r['ap_user_name'];
            $amc = $r['ap_mess_content'];
            $result.= "<div style=\"padding: 0px 50px 0px;\"><div>{$aun}回复$un<a onclick=\"sedrev({$parent_id},{$r['ap_mess_id']})\"><span class=\"glyphicon glyphicon-share-alt\"></span></a>";
            if ($user_id==$r['ap_user_id']){
                $result.= "<a onclick=\"deleteRevMess({$r['ap_mess_id']})\" > <span class=\"glyphicon glyphicon-trash\"></span> </a>";
            }
            $result.= "</div><div>时间:{$r['creat_time']}</div><textarea  class=\"form-control\"  readonly='readonly' cols='80' rows='1'>{$amc}</textarea>
                            <div id='{$r['ap_mess_id']}'></div>
                        </div>";
            if(isset($r['children']))   $result.=$this->loop($user_id,$parent_id,$r['ap_user_name'],$r['children']);
        }
        return $result;
    }

    public function pageAction(){
        if($this->getRequest()->isXmlHttpRequest()){
            $curPage=$this->getRequest()->getPost('curPage');
            $result=$this->gethtmltxt($curPage);
            if(!$result){
                $data=array('success'=>false,'message'=>'页码越界');
            }else{
                $data=array('success'=>true,'message'=>'页码正常');
                $data['htmltxt']=$result;
            }
            echo json_encode($data);
            exit;
        }
    }

    public function deleteMessAction(){
        if($this->getRequest()->isXmlHttpRequest()){
            $mess_id = $this->getRequest()->getPost('mess_id');
            $data=$this->mess->deleteMess($mess_id);
            if($data){
                $data['htmltxt']=$this->gethtmltxt(1);
            }
            echo json_encode($data);
            exit;
        }
    }

    public function saveMessAction(){
        if($this->getRequest()->isXmlHttpRequest()){
            $mess_id = $this->getRequest()->getPost('mess_id');
            $mess_content=$this->getRequest()->getPost('mess_content');
            $result=$this->mess->editSaveContent($mess_id,$mess_content);
            if ($result['success']) {
                $result['htmltxt']=$mess_content;
            }
            echo json_encode($result);
            exit;
        }
    }

    public function informAction(){
        if($this->getRequest()->isXmlHttpRequest()){
            $mess_id = $this->getRequest()->getPost('mess_id');
            $data=$this->mess->informMess($mess_id);
            if ($data['success']) {
                $data['htmltxt']=$this->gethtmltxt(1);
            }
            echo json_encode($data);
            exit;
        }
    }

    public function subResAction(){
        if($this->getRequest()->isXmlHttpRequest()){
            $ap_parent_id = $this->getRequest()->getPost('mess_id');
            $ap_mess_content=$this->getRequest()->getPost('ap_mess_content');
            $user_id=$this->_session->get('user_id');
            $user_name=$this->_session->get('user_name');
            $data=$this->rmess->postRmess($ap_parent_id,$ap_mess_content,$user_id,$user_name);
            if ($data['success']) {
                $data['htmltxt']=$this->gethtmltxt(1);
            }
            echo json_encode($data);
            exit;
        }
    }

    public function sedrevsaveAction(){
        if($this->getRequest()->isXmlHttpRequest()){
            $ap_parent_id = $this->getRequest()->getPost('parent_id');
            $ap_mess_content=$this->getRequest()->getPost('ap_mess_content');
            $pid=$this->getRequest()->getPost('mess_id');
            $user_id=$this->_session->get('user_id');
            $user_name=$this->_session->get('user_name');
            $data=$this->rmess->postRmess($ap_parent_id,$ap_mess_content,$user_id,$user_name,$pid);
            if ($data['success']) {
                $data['htmltxt']=$this->gethtmltxt(1);
            }
            echo json_encode($data);
            exit;
        }
    }

    public function deleteRevAction(){
        if($this->getRequest()->isXmlHttpRequest()){
            $mess_id=$this->getRequest()->getPost('mess_id');
            $data=$this->rmess->deleteMess($mess_id);
            if($data['success']){
                $data['htmltxt']=$this->gethtmltxt(1);
            }
            echo json_encode($data);
            exit;
        }
    }

}