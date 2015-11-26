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
		$this->checkLogin();
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
		//if($banshi_id) $_SESSION['shijuan']['shijuan_banshi'] = $banshi_id;
		$_SESSION['shijuan']['shijuan_banshi'] = 3;
		redirect('/tiku/c'.$course_id.'/');
	}
}
?>