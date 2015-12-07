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
		var_dump($_POST);
	}
}
?>