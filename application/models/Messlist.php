<?php
/**
 * Created by PhpStorm.
 * User: HanYaMin
 * Date: 2017/11/22
 * Time: 16:29
 */
class MesslistModel extends Model {

    var $mc;
    var $rowsPerPage=3;

    public function __construct()
    {
        parent::__construct();
        $this->setEntity('table_message');
        $this->mc=new Mem();
    }

    public function getList($curPage){
        $pages=$this->mc->getCache('pages');
        if(!$pages){
            $where=array('audit'=>'0');
            $rows=count($this->getData($where));
            $pages=ceil($rows/$this->rowsPerPage);
            $this->mc->addCache('pages',$pages);
        }
        if(isset($_POST['curPage'])){
            $curPage=$_POST['curPage'];
        }else{
            $curPage=1;
        }
        $this->mc->setCache('curPage',$curPage);
        if($curPage>$pages){
            return false;
        }else if($curPage<=0){
            return false;
        }else {
            $messids=$this->mc->getCache('curPage'.$curPage);
            if(!$messids){
                $sql = "select mess_id from table_message where audit=0 order by creat_time DESC limit " . ($curPage - 1) * $this->rowsPerPage . "," . $this->rowsPerPage;
                $messids=array_values(Db::getDb()->query($sql)->fetchAll());
                $this->mc->addCache('curPage'.$curPage,$messids);
            }
            $data = array();
            foreach ($messids as $mess_id) {
                $mess_id=$mess_id['mess_id'];
                $row=$this->mc->getCache($mess_id);    //留言列表以其id作为key
                if(!$row){
                    $sqlB = "select mess_id,user_name,user_id,mess_content,creat_time from table_message where mess_id={$mess_id}  limit 1";
                    $row=Db::getDb()->query($sqlB)->fetch();
                    $this->mc->addCache($mess_id,$row);
                }
                $dat['mess_id'] = $row['mess_id'];
                $dat['user_name'] = $row['user_name'];
                $dat['user_id'] = $row['user_id'];
                $dat['mess_content'] = $row['mess_content'];
                $dat['creat_time'] = $row['creat_time'];
                $picture=$this->mc->getCache($row['user_id'].'picture');
                if(!$picture){
                    $sqlC = "select picture from table_user_info where user_id={$row['user_id']}  limit 1";
                    $picture=Db::getDb()->query($sqlC)->fetch();
                    $picture=$picture['picture'];
                    $this->mc->addCache($row['user_id'].'$picture',$picture);
                }
                $dat['picture'] = $picture;

                $ap_mess_ids=$this->mc->getCache($row['mess_id'].'apids');   //留言的一级回复  key="mess_id".'apids'
                if(!$ap_mess_ids){
                    $subsql = "select ap_mess_id from table_append_mess where ap_parent_id=:ap_parent_id and pid=0";
                    $rs = Db::getDb()->prepare($subsql);
                    $rs->execute(array(':ap_parent_id' => $row['mess_id']));
                    $ap_mess_ids=array_values($rs->fetchAll());
                    $this->mc->addCache($row['mess_id'].'apids',$ap_mess_ids);
                }
                $das = array();
                foreach ($ap_mess_ids as $apid) {
                    $apid=$apid['ap_mess_id'];
                    $r=$this->mc->getCache($apid);
                    if(!$r){
                        $subsqlB = "select ap_mess_id,ap_mess_content,ap_user_id,ap_user_name,creat_time from table_append_mess where ap_mess_id=:ap_mess_id";
                        $rs = Db::getDb()->prepare($subsqlB);
                        $rs->execute(array(':ap_mess_id' => $apid));
                        $r=$rs->fetch();
                        $this->mc->addCache($apid,$r);
                    }
                    $da['ap_mess_id'] = $r['ap_mess_id'];
                    $da['ap_mess_content'] = $r['ap_mess_content'];
                    $da['ap_user_id'] = $r['ap_user_id'];
                    $da['ap_user_name'] = $r['ap_user_name'];
                    $da['creat_time'] = $r['creat_time'];

                    $pid = $r['ap_mess_id'];
                    $parentId = $row['mess_id'];
                    $da['children'] = $this->loop($parentId, $pid);
                    $das[] = $da;
                }
                $dat['children'] = $das;
                $data[] = $dat;
            }
            return $data;
        }
    }
    protected function loop($parentId,$pid){
        $rsrsids=$this->mc->getCache($pid.'pids');   //二级回复 key=$pid.'pids'
        if(!$rsrsids){
            $subsubsql="select ap_mess_id from table_append_mess where pid=$pid";
            $rsrs = Db::getDb()->prepare($subsubsql);
            $rsrs->execute();
            $rsrsids=$rsrs->fetchAll();
            $this->mc->addCache($pid.'pids',$rsrsids);
        }
        if($rsrsids){
            $re=array();
            foreach ($rsrsids as $rsrid){
                $rsrid=$rsrid['ap_mess_id'];
                $rsr=$this->mc->getCache($rsrid);
                if(!$rsr){
                    $subsubsql="select ap_mess_id,ap_mess_content,ap_user_id,ap_user_name,creat_time from table_append_mess where ap_mess_id=$rsrid";
                    $rsrs=Db::getDb()->prepare($subsubsql);
                    $rsrs->execute();
                    $rsr=$rsrs->fetch();
                    $this->mc->addCache($rsrid,$rsr);
                }
                $r['ap_mess_id']=$rsr['ap_mess_id'];
                $r['ap_mess_content']=$rsr['ap_mess_content'];
                $r['ap_user_id']=$rsr['ap_user_id'];
                $r['ap_user_name']=$rsr['ap_user_name'];
                $r['creat_time']=$rsr['creat_time'];
                $r['children']=$this->loop($parentId,$rsr['ap_mess_id']);
                $re[]=$r;
            }
            return $re;
        }else{
            return null;
        }
    }

    public function post($user_id, $user_name, $new_mess){
        $data=[
            'mess_id'=>$this->getUniId(),
            'mess_content'=>$new_mess,
            'user_id'=>$user_id,
            'user_name'=>$user_name,
            'creat_time'=>date('Y-m-d H:i:s',time()),
            'operate_time'=>date('Y-m-d H:i:s',time()),
            'audit'=>0,
        ];
        $result=$this->insert($data);
        if($result){
            $this->mc->addCache($data['mess_id'],$data);
            $curPage=$this->mc->getCache('curPage');
            $pages=$this->mc->getCache('pages');
            if($curPage && $pages){
                for($i=$curPage;$i<$pages;$i++){
                    $this->mc->deleteCache('curPage'.$i);
                }
            }
            return array('success'=>true,'message'=>'发表成功');
        }else{
            return array('success'=>false,'message'=>'发表失败');
        }
    }
    public function deleteMess($mess_id){
        $where=array('mess_id'=>$mess_id);
        $result=$this->delete($where);
        $reply=new RmessModel();
        $reply->deleteMessByParentId($mess_id);
        if($result){
            $this->mc->deleteCache($mess_id);
            $curPage=$this->mc->getCache('curPage');
            $pages=$this->mc->getCache('pages');
            if($curPage && $pages){
                for($i=$curPage;$i<$pages;$i++){
                    $this->mc->deleteCache('curPage'.$i);
                }
            }
            return array('success'=>true,'message'=>'删除成功');
        } else {
            return array('success'=>false,'message'=>'删除失败');
        }
    }
    public function editSaveContent($mess_id,$mess_content){
        $sets=array('mess_content'=>$mess_content);
        $where=array('mess_id'=>$mess_id);
        $result=$this->update($sets,$where);
        if($result){
            if($data=$this->mc->getCache($mess_id)){
                $data['mess_content']=$mess_content;
                $this->mc->replaceCache($mess_id,$data);
            }
            return array('success'=>true,'message'=>'更改成功');
        }else{
            return array('success'=>false,'message'=>'更新失败');
        }
    }
    public function informMess($mess_id){
        $sets=array('audit'=>1);
        $where=array('mess_id'=>$mess_id);
        $result=$this->update($sets,$where);
        if($result){
            if($data=$this->mc->getCache($mess_id)){
                $data['audit']=1;
                $this->mc->replaceCache($mess_id,$data);
            }
            return array('success'=>true,'message'=>'举报成功');
        }else{
            return array('success'=>false,'message'=>'举报失败');
        }
    }

}