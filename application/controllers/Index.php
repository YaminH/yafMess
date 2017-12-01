<?php
/**
 * Created by PhpStorm.
 * User: HanYaMin
 * Date: 2017/11/22
 * Time: 14:01
 * 在Yaf中, 默认的模块/控制器/动作, 都是以Index命名的，也可以通过配置文件修改
 */
class IndexController extends Yaf_Controller_Abstract{
    /**
     * Controller的init方法会被自动首先地调用
     */
    public function init(){    //类似构造函数
        $this->_session = Yaf_Session::getInstance();
        $this->_session->start();
        /**
         * 如果不是Ajax请求，则开启HTML输出
         */
        if(!$this->getRequest()->isXmlHttpRequest()){
            Yaf_Dispatcher::getInstance()->enableView();
        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //如果是Ajax请求, 关闭自动渲染, 返回Json响应
            Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        }
    }

    /**
     * $this->getRequest()->getParam('name')
     * 获取当前请求中的所有路由参数, 路由参数不是指$_GET或者$_POST, 而是在路由过程中, 路由协议根据Request Uri分析出的请求参数.
     *                     ->getPost()
     * 这些参数是来自用户请求URL, 所以使用前一定要做安全化过滤.
     * 另外, 为了防止PHP抛出参数缺失的警告, 请尽量定义有默认值的参数.
     */
    public function indexAction(){}

    public function loginAction(){
        if($this->getRequest()->isXmlHttpRequest()){
            //获取post提交的参数
            $name = $this->getRequest()->getPost('user_name');
            $pwd = $this->getRequest()->getPost('pass_word');
            $user = new UserModel();
            $data=$user->login($name, $pwd);
            if(!$data){
                $result=array('success'=>false,'message'=>'用户名或密码错误');
                echo json_encode($result);
                exit;
            }
            $this->_session->set('user_id',$data['user_id']);
            $this->_session->set('user_name',$data['user_name']);
            $result=array('success'=>true,'message'=>'登录成功');
            echo json_encode($result);
            exit;
        }
    }
}