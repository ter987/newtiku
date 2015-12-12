<?php
namespace Home\Controller;
use Home\Controller\GlobalController;
class CepingController extends GlobalController {
	/**
	 * 初始化
	 */
	function _initialize()
	{
		parent::_initialize();
		$course_data = parent::getCourse();
		$this->assign('course_data',$course_data);
	}
	
    public function index(){
    	unset($_SESSION['shijuan']);
		unset($_SESSION['cart']);
		if(!empty($_POST['course_select'])){
			$_SESSION['course_id'] = I('post.course_select');
		}else{
			if(empty($_SESSION['course_id'])){
				$first = current($this->course_data);
				$_SESSION['course_id'] = $first['id'];
			}
		}
		$courseModel = M('tiku_course');
		$course_data  = $courseModel->where("id=".$_SESSION['course_id'])->find();
		$grade = $course_data['course_type']==1?'高中':'初中';
		$title = $grade.$course_data['course_name'].'在线测评('.date('Ymd').')';
		$this->assign('title',$title);
		$this->assign('current_course',$_SESSION['course_id']);
        $this->display();
	}
	public function ajaxCheckStudent(){
		$student = I('post.param');
		$userModel = M('user');
		$student_data = $userModel->where("email='".$student."' OR telphone='$student'")->find();
		if(!$student_data){
			$this->ajaxReturn(array('status'=>'n','info'=>'该学生不存在'));
		}elseif($student_data['type']!=1){
			$this->ajaxReturn(array('status'=>'n','info'=>'该账号不是学生'));
		}else{
			$this->ajaxReturn(array('status'=>'y','info'=>'验证成功'));
		}
		
	}
	public function xuanti(){
		if(empty($_POST)) redirect('/ceping/');
		$student = I('post.student');
		$shiti_num = I('post.shiti_num');
		$ceshi_score = I('post.ceshi_score');
		$ceshi_time = I('post.ceshi_time');
		$ceshi_title = I('post.title');
		$userModel = M('user');
		$student_data = $userModel->where("email='".$student."' OR telphone='$student'")->find();
		$_SESSION['ceping']['student'] = $student_data['id'];
		$_SESSION['ceping']['title'] = $ceshi_title;
		$_SESSION['ceping']['score'] = $ceshi_score;
		$_SESSION['ceping']['time'] = $ceshi_time;
		$_SESSION['ceping']['shiti_num'] = $shiti_num;
		unset($_SESSION['cart']);
		unset($_SESSION['shijuan']);
		redirect('/tiku/');
	}
	public function exam(){
		if(empty($_SESSION['cart'])){
    		redirect('/tiku/');
    	}
		$Model = M('tiku');
		foreach($_SESSION['cart'] as $k=>$val){
			$ids .= $k.',';
		}
		$ids = trim($ids,',');
		$data = $Model->field("tiku.id,tiku.content,tiku.options,tiku.difficulty_id")
			->where("tiku.id IN($ids)")->select();
		$this->assign('tiku_data',$data);
		$this->display();
	}
	public function start(){
		$cepingModel = M('ceping');
		$cpjoinModel = M('ceping_join');
		$cpextendModel = M('ceping_extend');
		$cepingModel->startTrans();
		$cp_data['title'] = $_SESSION['ceping']['title'];
		$cp_data['teacher'] = $_SESSION['user_id'];
		$cp_data['shiti_num'] = $_SESSION['ceping']['shiti_num'];
		$cp_data['limit_time'] = $_SESSION['ceping']['time'];
		$cp_data['score'] = $_SESSION['ceping']['score'];
		$cp_data['join_num'] = 1;
		$cp_data['unjoined_num'] = 1;
		$cp_data['create_time'] = time();
		$cp_id = $cepingModel->add($cp_data);
		$cpjoin_data['student'] = $_SESSION['ceping']['student'];
		$cpjoin_data['ceping_id'] = $cp_id;
		$result = $cpjoinModel->add($cpjoin_data);
		foreach($_SESSION['cart'] as $key=>$val){
			$cpextend_data['tiku_id'] = $key;
			$cpextend_data['ceping_id'] = $cp_id;
			$_result = $cpextendModel->add($cpextend_data);
			if(!$_result) break; 
		}
		if($cp_id && $result && $_result){
			$cepingModel->commit();
			unset($_SESSION['ceping']);
			$this->ajaxReturn(array('status'=>'success'));
		}else{
			$cepingModel->rollback();
			$this->ajaxReturn(array('status'=>'error'));
		}
		
	}
}
?>