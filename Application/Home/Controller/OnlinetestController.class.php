<?php
namespace Home\Controller;
use Home\Controller\GlobalController;
class OnlinetestController extends GlobalController {
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
		if($_POST['zsd_select']=='zsd' || empty($_POST['zsd_select'])){
			$this->getTopLevelPoint();
			$this->assign('zsd_select',1);
		}else{
			$version_id = I('post.zsd_select');
			$this->getBookByVersionId($version_id);
			$this->assign('zsd_select',0);
		}
		$tiku_type = $this->getTikuType($_SESSION['course_id']);
		$this->assign('tiku_type',$tiku_type);
		$this->assign('current_course',$_SESSION['course_id']);
		$version_data = $this->getVersionByCourseId();
		$this->assign('version_data',$version_data);
		$this->assign('current_zsd',$_POST['zsd_select']);
		$this->display();
	}
	public function start(){
		if(empty($_POST)) redirect('/onlinetest/');
		$zsd_select = I('post.zsd_select');
		if($zsd_select){
			$point_id = I('post.items_submit');
			$pointModel = M('tiku_point');
			foreach($point_id as $val){
				$point = $pointModel->field("id")->where("parent_id=".$val)->select();
				foreach($point as $v){
					$ids .= $v['id'].',';
				}
			}
			$ids = trim($ids,',');
			
		}else{
			$chapter_id = I('post.items_submit');
			$ids = implode(',',$chapter_id);
		}
		
		
		$shiti_num = I('post.shiti_num');
		$difficulty_id = I('post.difficulty_id');
		$Model = M('tiku');
		$pointModel = M('tiku_point');
		if($zsd_select){
			$data = $Model->field("tiku.id,tiku.content,tiku.options,tiku.difficulty_id")
			->join("tiku_type on tiku.type_id=tiku_type.id")
			->join("tiku_to_point on tiku_to_point.tiku_id=tiku.id")
			->where("tiku.difficulty_id=$difficulty_id AND tiku.type_id=1 AND tiku_to_point.point_id IN($ids)")
			->limit($shiti_num)->order("rand()")->select();
			$point_data = $pointModel->field('point_name')->where("id IN($ids)")->select();
			foreach($point_data as $val){
				$point_str .= $val['point_name'].',';
			}
		}else{
			$data = $Model->field("tiku.id,tiku.content,tiku.options,tiku.difficulty_id")
			->join("tiku_type on tiku.type_id=tiku_type.id")
			->where("tiku.difficulty_id=$difficulty_id AND tiku.type_id=1 AND tiku.chapter_id IN($ids)")
			->limit($shiti_num)->order("rand()")->select();
		}
		$otModel = M('onlinetest');
		$otModel->startTrans();
		$ot_data['user_id'] = $_SESSION['user_id'];
		$ot_data['course_id'] = $_SESSION['course_id'];
		$ot_data['title'] = '在线练习-'.date('Y-m-d');
		$ot_data['difficulty_id'] = $difficulty_id;
		$ot_data['shiti_num'] = $shiti_num;
		$ot_data['create_time'] = time();
		$ot_data['update_time'] = time();
		$ot_data['point'] = trim($point_str,',');
		$ot_id = $otModel->add($ot_data);
		//echo $ot_id;exit;
		$otextendModel = M('onlinetest_extend');
		$tiku_id_arr = array();
		foreach($data as $key=>$val){
			$otextend_data['onlinetest_id'] = $ot_id;
			$otextend_data['tiku_id'] = $val['id'];
			$otextend_data['difficulty_id'] = $val['difficulty_id'];
			$result = $otextendModel->add($otextend_data);
			if(!$result) break;
			$tiku_id_arr[$val['id']] = array('id'=>$val['id']);
		}
		if($ot_id && $result){
			$otModel->commit();
		}else{
			$otModel->rollback();
			redirect('/');
		}
		$_SESSION['onlinetest']['id'] =  $ot_id;
		$_SESSION['onlinetest']['start_time'] = time();
		$_SESSION['onlinetest']['shiti'] = $tiku_id_arr;
		redirect('/onlinetest/exam');
		
	}
	public function exam(){
		if(empty($_SESSION['onlinetest']['shiti'])){
			redirect('/onlinetest/');
		}
		$Model = M('tiku');
		foreach($_SESSION['onlinetest']['shiti'] as $k=>$val){
			$ids .= $k.',';
		}
		$ids = trim($ids,',');
		$data = $Model->field("tiku.id,tiku.content,tiku.options,tiku.difficulty_id")
			->where("tiku.id IN($ids)")->select();
		$used_seconds = time()-$_SESSION['onlinetest']['start_time'];
		$minutes = floor($used_seconds/60);
		$seconds = $used_seconds%60;
		$this->assign('minutes',$minutes);
		$this->assign('seconds',$seconds);
		$this->assign('tiku_data',$data);
		$this->display();
	}
	public function ajaxExam(){
		$tiku_id = I('get.id');
		$s_answer = I('get.s_answer');
		$_SESSION['onlinetest']['shiti'][$tiku_id]['s_answer'] = $s_answer;
		$this->ajaxReturn(array('status'=>'success'));
	}
	public function submit(){
		$otModel = M('onlinetest');
		$otextendModel = M('onlinetest_extend');
		$otnoteModel = M('onlinetest_note');
		$tikuModel = M('tiku');
		$right_num = 0;
		$otModel->startTrans();
		foreach($_SESSION['onlinetest']['shiti'] as $key=>$val){
			$tiku = $tikuModel->field('answer')->where("id=$key")->find();
			$is_right = 0;
			if($tiku['answer']==$val['s_answer']){
				$is_right = 1;
				$right_num++;
			}else{
				$note_data['tiku_id'] = $key;
				$note_data['user_id'] = $_SESSION['user_id'];
				$note_data['course_id'] = $_SESSION['course_id'];
				$note_data['create_time'] = time();
				$note_data['onlinetest_id'] = $_SESSION['onlinetest']['id'];
				$result_3 = $otnoteModel->add($note_data);
				if(!$result_3) break;
			}
			$data['is_right'] = $is_right;
			$data['s_answer'] = $val['s_answer'];
			$result = $otextendModel->where("onlinetest_id=".$_SESSION['onlinetest']['id']." AND tiku_id=$key")->save($data);
			if(!$result) break;
		}
		$ot_data['used_time'] = time()-$_SESSION['onlinetest']['start_time'];
		$ot_data['right_num'] = $right_num;
		$ot_data['update_time'] = time();
		$ot_data['submited'] = 1;
		$_result = $otModel->where("id=".$_SESSION['onlinetest']['id'])->save($ot_data);
		
		if($result && $_result){
			$otModel->commit();
			$this->ajaxReturn(array('status'=>'success'));
		}else{
			$otModel->rollback();
			$this->ajaxReturn(array('status'=>'error'));
		}
	}
}
?>