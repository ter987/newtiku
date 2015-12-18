<?php
namespace Home\Controller;
use Home\Controller\GlobalController;
class SmartController extends GlobalController {
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
		
		
		$tixing = I('post.tixing');
		$difficulty_id = I('post.difficulty_id');
		$Model = M('tiku');
		foreach($tixing as $key=>$val){
			if($val==0) continue;
			if($zsd_select){
				$data = $Model->field("tiku.id,tiku_type.type_name")
				->join("tiku_type on tiku.type_id=tiku_type.id")
				->join("tiku_to_point on tiku_to_point.tiku_id=tiku.id")
				->where("tiku.difficulty_id=$difficulty_id AND tiku.type_id=$key AND tiku_to_point.point_id IN($ids)")
				->select();
				//echo $Model->getLastSql();exit;
				//var_dump($data);exit;
				shuffle($data);
				$data = array_slice($data,0,$val);
				//var_dump($data);exit;
			}else{
				$data = $Model->field("tiku.id,tiku_type.type_name")
				->join("tiku_type on tiku.type_id=tiku_type.id")
				->where("tiku.difficulty_id=$difficulty_id AND tiku.type_id=$key AND tiku.chapter_id IN($ids)")
				->limit($val)->order("rand()")->select();
				shuffle($data);
				$data = array_slice($data,0,$val);
			}
			//echo $Model->getLastSql();exit;
			foreach($data as $k=>$v){
				$_SESSION['cart'][$v['id']] = $v; 
			}
		}
		redirect('/shijuan/');
	}
}
?>