<?php
namespace Home\Controller;
use Home\Controller\GlobalController;
class IndexController extends GlobalController {
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
        $this->display();
	}
}