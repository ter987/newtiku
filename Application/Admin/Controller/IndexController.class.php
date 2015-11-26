<?php
namespace Admin\Controller;
use Admin\Controller\GlobalController;
class IndexController extends GlobalController {
	/**
	 * 初始化
	 */
	function _initialize()
	{
		$course_data = parent::getCourse();
		$this->assign('course_data',$course_data);
	}
    public function index(){
        $this->display();
	}
	public function welcome(){
		$this->display();
	}
}