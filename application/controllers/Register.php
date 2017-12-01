<?php
/**
 * Created by PhpStorm.
 * User: HanYaMin
 * Date: 2017/11/22
 * Time: 15:02
 */
class RegisterController extends Yaf_Controller_Abstract{
    public function init(){    //类似构造函数
        $this->_session = Yaf_Session::getInstance();
        $this->_session->start();
        if(!$this->getRequest()->isXmlHttpRequest()){
            Yaf_Dispatcher::getInstance()->enableView();
        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        }
    }
    public function indexAction(){
        $file=$this->getRequest()->getFiles('image');
        if($file){
            if ((($file['type'] == 'image/gif')
                    || ($file['type'] == 'image/jpeg')
                    || ($file['type'] == 'image/pjpeg'))
                && ($file["size"] < 20000))
            {
                $oldName=$file['name'];
                $tmp=explode('.',$oldName);
                $newName=$this->getUniId().'.'.$tmp[1];
                move_uploaded_file($file['tmp_name'], '../public/img/'.$newName);
                $this->_session->set('picture',$newName);
                echo '上传成功';
            }
        }
    }

    public function registerAction(){
        if($this->getRequest()->isXmlHttpRequest()){
            $data=[
                'user_name'=>$this->getRequest()->getPost('user_name'),
                'pass_word'=>md5($this->getRequest()->getPost('password')),
                'user_real_name'=>$this->getRequest()->getPost('user_real_name'),
                'city'=>$this->getRequest()->getPost('city'),
                'school'=>$this->getRequest()->getPost('school'),
                'creat_time'=>date('Y-m-d H:i:s',time()),
                'picture'=>$this->_session->get('picture'),
            ];
            $user=new UserModel();
            $result=$user->register($data);
            echo json_encode($result);
            exit();
        }
    }

    function getUniId(){
        $str=time();
        return  $str.str_pad(rand(0,10000),4,0);
    }
}