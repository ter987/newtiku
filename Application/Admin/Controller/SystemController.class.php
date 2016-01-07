<?php
namespace Admin\Controller;
use Admin\Controller\GlobalController;
class SystemController extends GlobalController {
	/**
	 * 初始化
	 */
	function _initialize()
	{
		parent::_initialize();
	}
    public function logs(){
        $this->display();
	}
	public function welcome(){
		$this->display();
	}
}