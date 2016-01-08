<?php
namespace Admin\Controller;
use Admin\Controller\GlobalController;
class MemberController extends GlobalController {
	/**
	 * 初始化
	 */
	function _initialize()
	{
		parent::_initialize();
	}
    public function index(){
    	$condition = I('request.condition');
		$time_start = strtotime(I('request.time_start'));
		$time_end = strtotime(I('request.time_end'));
		
		$where = '1=1';
		if($_GET['p']){
			$page = $_GET['p'];
		}else{
			$page = 1;
		}
		$jump_url = ACTION_NAME;
		if(!empty($condition)){
			$where = "nick_name like '%$condition%' || telphone like '%$condition%' || email like '%$condition%'";
			$jump_url .= '/condition/'.$condition;
		}
		if($time_start){
			$where .= " && create_time > $time_start";
			$jump_url .= '/time_start/'.$time_start;
		}
		if($time_end){
			$where .= " && create_time < $time_end";
			$jump_url .= '/time_end/'.$time_end;
		}
		if($_GET['p']){
			$jump_url .= '/p/'.$_GET['p'];
		}	
		$_SESSION['jump_url'] = $jump_url;
		
		$Model = M('User');
		$count = $Model->where($where)->count();
		//echo $Model->getLastSql();exit;
		$Page = new \Think\Page($count,20);
		$Page->parameter['condition'] = $condition;
		$Page->parameter['time_start'] = $time_start;
		$Page->parameter['time_end'] = $time_end;
		$Page->setConfig('first','第一页');
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		$page_show = $Page->show();
		$this->assign('page_show',$page_show);
		$data = $Model
		->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
		//var_dump($tiku_data);
		$this->assign('data',$data);
		$this->assign('count',$count);
        $this->display();
	}
	public function edit(){
		if($_POST){
			$id = I('post.id');
			//$data['type'] = I('post.type');
			$Model = M('User');
			if($Model->where("id=$id")->save($data)){
				$System = A('System');
				$System->logWrite($_SESSION['admin_id'],"编辑会员成功(ID:$id)");
				$this->ajaxReturn(array('status'=>'y'));
			}else{
				$this->ajaxReturn(array('status'=>'n'));
			}
		}else{
			$id = I('get.id');
			$Model = M('User');
			$result = $Model->where("id=$id")->find();
			$this->assign('data',$result);
			$this->display();
		}
	}
	public function changePassword(){
		if($_POST){
			$id = I('post.id');
			$Model = M('User');
			$password = I('post.password');
			$data['salt'] = substr(uniqid(),2,6);
			$data['password'] = md5(md5($password.$data['salt']));
			if($Model->where("id=$id")->save($data)){
				$System = A('System');
				$System->logWrite($_SESSION['admin_id'],"修改会员密码成功(ID:$id)");
				$this->ajaxReturn(array('status'=>'y'));
			}else{
				$this->ajaxReturn(array('status'=>'n'));
			}
		}else{
			$id = I('get.id');
			$Model = M('User');
			$result = $Model->where("id=$id")->find();
			$this->assign('data',$result);
			$this->display();
		}
	}
	public function delete(){
		$id = I('get.id');
		$Model = M('User');
		$Model->startTrans();
		$result_1 = $Model->where("id=$id")->delete();
		$result_2 = true;
		if($Model->table("user_collected")->where("user_id=$id")->find()){
			$result_2 = $Model->table("user_collected")->where("user_id=$id")->delete();
		}
		$result_3 = true;
		if($Model->table("user_shijuan")->where("user_id=$id")->find()){
			$result_3 = $Model->table("user_shijuan")->where("user_id=$id")->delete();
		}
		$result_4 = true;
		if($Model->table("onlinetest")->where("user_id=$id")->find()){
			$result_4 = $Model->execute("DELETE `onlinetest`,`onlinetest_extend`,`onlinetest_note` FROM `onlinetest` LEFT JOIN `onlinetest_extend` ON `onlinetest`.`id`=onlinetest_extend.`onlinetest_id` LEFT JOIN onlinetest_note ON onlinetest.`id`=onlinetest_note.`onlinetest_id` WHERE onlinetest.user_id=$id");
		}
		$result_5 = true;
		if($Model->table("collected_tag")->where("user_id=$id")->find()){
			$result_5 = $Model->table("collected_tag")->where("user_id=$id")->delete();
		}		
		$result_7 = true;
		if($Model->table("ceping")->where("teacher=$id")->find()){
			$result_7 = $Model->execute("DELETE ceping,ceping_extend FROM ceping LEFT JOIN ceping_extend ON ceping.`id`=ceping_extend.`ceping_id` WHERE ceping.`teacher`=$id");
		}
		$result_8 = true;
		if($Model->table("ceping_join")->where("student=$id")->find()){
			$result_8 = $Model->execute("DELETE ceping_join,ceping_answer FROM ceping_join LEFT JOIN ceping_answer ON ceping_answer.`student`=ceping_join.`student` WHERE ceping_join.`student`=$id");
		}
		if($result_1 && $result_2 && $result_3 && $result_4 && $result_5 && $result_7 && $result_8){
			$Model->commit();
			$System = A('System');
			$System->logWrite($_SESSION['admin_id'],"删除会员成功(ID:$id)");
			$this->ajaxReturn(array('status'=>'y'));
		}else{
			$Model->rollback();
			$this->ajaxReturn(array('status'=>'n'));
		}
	}
	public function stop(){
		$id = I('get.id');
		$Model = M('User');
		if($Model->where("id=$id")->save(array('status'=>0))){
			$System = A('System');
			$System->logWrite($_SESSION['admin_id'],"停用会员成功(ID:$id)");
			$this->ajaxReturn(array('status'=>'success'));
		}else{
			$this->ajaxReturn(array('status'=>'error'));
		}
	}
	public function start(){
		$id = I('get.id');
		$Model = M('User');
		if($Model->where("id=$id")->save(array('status'=>1))){
			$System = A('System');
			$System->logWrite($_SESSION['admin_id'],"启用会员成功(ID:$id)");
			$this->ajaxReturn(array('status'=>'success'));
		}else{
			$this->ajaxReturn(array('status'=>'error'));
		}
	}
	public function show(){
		$id = I('get.id');
		$Model = M('User');
		$result = $Model->where("id=$id")->find();
		$this->assign('data',$result);
		$this->display();
	}
}