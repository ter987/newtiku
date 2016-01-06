<?php
namespace Admin\Controller;
use Admin\Controller\GlobalController;
class IndexController extends GlobalController {
	/**
	 * 初始化
	 */
	function _initialize()
	{
		parent::_initialize();
	}
    public function index(){
        $this->display();
	}
	public function welcome(){
		$this->display();
	}
}