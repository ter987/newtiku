<?php
namespace Admin\Controller;
use Admin\Controller\GlobalController;
class TikupointController extends GlobalController {
	var $parent_id;
	var $points ;
	var $i;
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
	public function add(){
		$this->display();
	}
	public function edit(){
		if($_POST){
			$data['id'] = $_POST['id'];
			$data['parent_id'] = $_POST['parent_id'];
			$data['point_name'] = $_POST['point_name'];
			$Model = M('tiku_point');
			$result = $Model->save($data);

			if($result){
				$this->_message('success','更新成功',$_SERVER['HTTP_REFERER']);
			}else{
				$this->_message('error','更新失败',$_SERVER['HTTP_REFERER']);
			}
		}else{
			$id = $_GET['id'];
			$Model = M('tiku_point');
			$data = $Model->where("id=$id")->find();
			$this->assign('data',$data);
			$tiku = A('Tiku');
			$option = $tiku->getAllChildrenPointId(0,$data['course_id'],$data['parent_id']);
			$this->assign('option',$option);
		}
		
		$this->display();
	}
	
	/**
	 * 删除
	 */
	public function delete(){
		$id = $_GET['id'];
		$Model = M('tiku');
		$Model->startTrans();
		$result = $Model->where("id=$id")->delete();
		$pointModel = M('tiku_to_point');
		$result_2 = $pointModel->where("tiku_id=$id")->delete();
		if($result && $result_2){
			$Model->commit();
			$this->ajaxReturn(array('status'=>'success'));
		}else{
			$Model->rollback();
			$this->ajaxReturn(array('status'=>'error'));
		}
		
	}
	public function deleteAll(){
		$ids = $_GET['ids'];
		$Model = M('tiku');
		$Model->startTrans();
		$result = $Model->where("id IN ($ids)")->delete();
		$pointModel = M('tiku_to_point');
		$result_2 = $pointModel->where("tiku_id IN ($ids)")->delete();
		if($result && $result_2){
			$Model->commit();
			$this->_message('success','删除成功！',$_SESSION['jump_url']);
		}else{
			$Model->rollback();
			$this->_message('error','删除失败！',$_SESSION['jump_url']);
		}
	}
	public function shenheAll(){
		$ids = $_GET['ids'];
		$Model = M('tiku');
		$result = $Model->where("id IN ($ids)")->data(array('status'=>1))->save();
		if($result){
			$this->_message('success','审核成功！',$_SESSION['jump_url']);
		}
	}
	/**
	 * ajax获取知识点
	 */
	public function ajaxGetPointsByCourseId(){
		$course_id = $_GET['course_id'];
		$Model = M('tiku_point');
		$child_data = $Model->where("course_id=$course_id")->select();
		if(!$child_data){
			$this->ajaxReturn(array('status'=>'error'));
		}
		$data = $this->getTree($child_data,0);
		$this->ajaxReturn(array('data'=>$data,'status'=>'success'));
		
	}
	public function selectCourse(){
		$this->display();
	}
	
}