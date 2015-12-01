<?php
namespace Home\Controller;
use Home\Controller\GlobalController;
class HandController extends GlobalController {
	/**
	 * 初始化
	 */
	function _initialize()
	{
		parent::_initialize();
	}
	public function index(){
		$course_data = parent::getCourse();
		$this->assign('course_data',$course_data);
		unset($_SESSION['shijuan']);
		unset($_SESSION['cart']);
		$this->display();
	}
	public function start(){
		$course_id = I('post.course');
		$banshi_id = I('post.banshi');
		$_SESSION['shijuan']['shijuan_banshi'] = $banshi_id;
		$_SESSION['course_id'] = $course_id;
		redirect('/tiku/');
	}
}
?>