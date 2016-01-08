<?php
namespace Admin\Controller;
use Admin\Controller\GlobalController;
class TikusourceController extends GlobalController {
	var $parent_id;
	var $points ;
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
    	
		$course_id = $_REQUEST['course_id'];
		$source_name = $_REQUEST['source_name'];
		
		$this->assign('course_id',$course_id);
		$this->assign('source_name',$source_name);
		$where = '1=1';
		if($_GET['p']){
			$page = $_GET['p'];
		}else{
			$page = 1;
		}
		$jump_url = ACTION_NAME;
		if($course_id){
			$where = "tiku_source.course_id=$course_id ";
			$jump_url .= '/course_id/'.$course_id;
		}
		if($source_name){
			$where .= " && tiku_source.source_name like '%".$source_name."%'";
			$jump_url .= '/source_name/'.$source_name;
		}
		if($_GET['p']){
			$jump_url .= '/p/'.$_GET['p'];
		}	
		$jump_url .= '.html';
		$_SESSION['jump_url'] = $jump_url;
		
		//获取题库数据
		$Model = M('tiku_source');
		$count = $Model->where($where)->count();
		//echo $Model->getLastSql();exit;
		$Page = new \Think\Page($count,10);
		$Page->parameter['course_id'] = $course_id;
		$Page->parameter['source_name'] = $source_name;
		$Page->setConfig('first','第一页');
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		$page_show = $Page->show();
		$this->assign('page_show',$page_show);
		$tiku_data = $Model
		->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
		//var_dump($tiku_data);
		$this->assign('tiku_data',$tiku_data);
		$this->assign('count',$count);
		$this->display();
		
        
	}
	public function edit(){
		if($_POST){
			$data['id'] = $_POST['id'];
			$data['course_id'] = $_POST['course_id'];
			$data['year'] = $_POST['year'];
			$data['source_name'] = htmlspecialchars($_POST['source_name']);
			$data['province_id'] = $_POST['province_id'];
			$data['grade'] = $_POST['grade'];
			$data['source_type_id'] = $_POST['source_type_id'];
			$data['wen_li'] = $_POST['wen_li'];
			$data['update_time'] = time();
			
			$Model = M('tiku_source');
			$result = $Model->save($data);
			//echo $Model->getLastSql();exit;
			if($result){
				$System = A('System');
				$System->logWrite($_SESSION['admin_id'],"编辑试卷成功(ID:".$data['id'].")");
				$this->_message('success','更新成功',$_SESSION['jump_url'],1);
			}else{
				$this->_message('error','更新失败',$_SERVER['HTTP_REFERER'],1);
			}
		}else{
			$source_id = $_GET['id'];
			$Model = M('tiku_source');
			$tiku_data = $Model->field("tiku_source.*,tiku_course.course_name")->where("tiku_source.id=$source_id")->join("tiku_course on tiku_course.id=tiku_source.course_id")->find();
			//var_dump($tiku_data['course_id']);exit;
			
			$this->assign('tiku_data',$tiku_data);
		}
		$province_data = $this->getProvinces();
		$this->assign('province_data',$province_data);
		$source_type_data = $this->getSourceType($tiku_data['course_id']);
		//var_dump($source_type_data);
		$this->assign('source_type_data',$source_type_data);
		$this->display();
	}
	public function add(){
		if($_POST){
			
		}else{
			$province_data = $this->getProvinces();
			$this->assign('province_data',$province_data);
			$this->display();
		}
	}
	public function getSourceType($course_id){
		$Model = M('source_type');
		$data = $Model->field("source_type.*")->join("tiku_course on tiku_course.course_type=source_type.course_type")->where("tiku_course.id=$course_id")->select();
		return $data;
	}
	/**
	 * 获取地区
	 */
	public function getProvinces(){
		$Model = M('province');
		$data = $Model->select();
		return $data;
	}
	/**
	 * 删除
	 */
	public function delete(){
		$id = $_GET['id'];
		$Model = M('tiku_source');
		$Model->startTrans();
		$result = $Model->where("id=$id")->delete();
		$tikuModel = M('tiku');
		$tiku_data = $tikuModel->field('id')->where("source_id=$id")->select();
		$result_2 = true;
		$result_3 = true;
		if($tiku_data){
			foreach($tiku_data as $val){
				$new_arr[] = $val['id'];
			}
			$ids = implode(',', $new_arr);
			$result_2 = $tikuModel->where("id IN($ids)")->delete();
			$tikupintModel = M('tiku_to_point');
			$result_3 = $tikupintModel->where("tiku_id IN($ids)")->delete();
		}
		if($result && $result_2 && $result_3){
			$Model->commit();
			$System = A('System');
			$System->logWrite($_SESSION['admin_id'],"删除试卷成功(ID:$id)");
			$this->ajaxReturn(array('status'=>'success'));
		}else{
			$Model->rollback();
		}
	}
	public function deleteAll(){
		$ids = $_GET['ids'];
		$Model = M('tiku_source');
		$Model->startTrans();
		$result = $Model->where("id IN ($ids)")->delete();
		$tikuModel = M('tiku');
		$tiku_data = $tikuModel->field('id')->where("source_id IN ($ids)")->select();
		$result_2 = true;
		$result_3 = true;
		if($tiku_data){
			foreach($tiku_data as $val){
				$new_arr[] = $val['id'];
			}
			$tiku_ids = implode(',', $new_arr);
			$result_2 = $tikuModel->where("id IN($tiku_ids)")->delete();
			$tikupintModel = M('tiku_to_point');
			$result_3 = $tikupintModel->where("tiku_id IN($ids)")->delete();
		}
		if($result && $result_2 && $result_3){
			$Model->commit();
			$System = A('System');
			$System->logWrite($_SESSION['admin_id'],"批量删除试卷成功(ID:$ids)");
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
	 * 格式化参数
	 */
	public function formatParams(){
		
	}
	public function selectCourse(){
		$this->display();
	}
	
}