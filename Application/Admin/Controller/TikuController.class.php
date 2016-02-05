<?php
namespace Admin\Controller;
use Admin\Controller\GlobalController;
class TikuController extends GlobalController {
	var $parent_id;
	var $points ;
	var $i;
	/**
	 * 初始化
	 */
	function _initialize()
	{
		parent::_initialize();
		$i = 0;
		$course_data = parent::getCourse();
		$this->getAllTypes();
		$this->assign('course_data',$course_data);
	}
	
    public function index(){
    	
		$course_id = $_REQUEST['course_id'];
		$type_id = $_REQUEST['type_id'];;//题型id
		$source_name = $_REQUEST['source_name'];
		$content = $_REQUEST['content'];
		$status = $_REQUEST['status'];
		
		$this->assign('course_id',$course_id);
		$this->assign('type_id',$type_id);
		$this->assign('source_name',$source_name);
		$this->assign('content',$content);
		$this->assign('status',$status);
		$where = '1=1';
		$jump_url = '/index.php?m=Admin&c='.CONTROLLER_NAME.'&a='.ACTION_NAME.'&';
		if($course_id){
			$where = "tiku_source.course_id=$course_id ";
			$jump_url .= 'course_id='.$course_id.'&';
		}
		if($type_id){
			$where .= " && tiku.type_id=$type_id ";
			$jump_url .= 'type_id='.$type_id.'&';
		}
		if($status){
			$where .= " && tiku.status=$status ";
			$jump_url .= 'status='.$status.'&';
		}
		if($content){
			$where .= " && tiku.content like '%".$content."%'";
			$jump_url .= 'content='.$content.'&';
		}
		if($source_name){
			$where .= " && tiku_source.source_name like '%".$source_name."%'";
			$jump_url .= 'type_id='.$type_id.'&';
		}
		if($_GET['p']){
			$jump_url .= 'p='.$_GET['p'];
		}	
		$_SESSION['jump_url'] = $jump_url;
		//获取题库数据
		$Model = M('tiku');
		$count = $Model->join("tiku_source on tiku.source_id = tiku_source.id")->where($where)->count();
		//echo $Model->getLastSql();exit;
		$Page = new \Think\Page($count,50);
		$Page->parameter['course_id'] = $course_id;
		$Page->parameter['type_id'] = $type_id;
		$Page->parameter['content'] = $content;
		$Page->parameter['source_name'] = $source_name;
		$Page->setConfig('first','第一页');
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		$page_show = $Page->show();
		$this->assign('page_show',$page_show);
		$tiku_data = $Model->field(" tiku.`id`,tiku.`content`,tiku.`clicks`,tiku.`status`,tiku.`create_time`,tiku_source.`source_name`")
		->join("left join tiku_source on tiku.`source_id`=tiku_source.id")
		->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
		//echo $Model->getLastSql();
		//var_dump($tiku_data);
		$this->assign('tiku_data',$tiku_data);
		$this->assign('count',$count);
		$this->display();
		
        
	}
	public function edit(){
		if($_POST){
			//var_dump($_POST['abc']);
			//var_dump($_POST);exit;
			$data['id'] = $_POST['id'];
			$data['difficulty_id'] = $_POST['difficulty_id'];
			$data['content'] = trim(I('post.content'));
			$data['answer'] = trim(I('post.answer'));
			$data['analysis'] = trim(I('post.analysis'));
			$data['status'] = $_POST['status'];
			$data['course_id'] = $_POST['course_id'];
			$data['type_id'] = $_POST['type_id'];
			$data['update_time'] = time();
			if($data['type_id']==1 || $data['type_id']==6){
				$data['options'] = json_encode(trim($_POST['options']));
			}
			
			$Model = M('tiku');
			$result = $Model->save($data);
			//echo $Model->getLastSql();exit;
			//var_dump($_SERVER);exit;
			if($result){
				$pointModel = M('tiku_to_point');
				$point_data['point_id'] = $_POST['point_id'];
				$pointModel->data($point_data)->where("tiku_id=".$data['id'])->save();
				$System = A('System');
				$System->logWrite($_SESSION['admin_id'],"编辑题库成功(ID:".$data['id'].")");
				$this->_message('success','更新成功',$_SESSION['jump_url'],1);exit;
			}else{
				$this->_message('error','更新失败',$_SERVER['HTTP_REFERER'],1);exit;
			}
		}else{
			$tiku_id = $_GET['id'];
			$Model = M('tiku');
			$tiku_data = $Model->field(" tiku.`id`,tiku.difficulty_id,tiku.options,tiku.content_old,tiku.type_id,tiku_to_point.point_id,province.province_name,tiku.`content`,tiku.`clicks`,tiku.`status`,tiku.`answer`,tiku.`analysis`,tiku.`create_time`,tiku_source.course_id,tiku_source.source_name,tiku_source.course_id,year,tiku_source.grade,tiku_source.source_type_id,tiku_source.id as sid,tiku_source.wen_li")
			->join("tiku_source on tiku.`source_id`=tiku_source.id")
			->join("left join province on tiku_source.province_id=province.id")
			->join("tiku_to_point on tiku_to_point.tiku_id=tiku.id")
			->where("tiku.id=$tiku_id")->find();
			//echo $Model->getLastSql();
			//var_dump($tiku_data);exit;
			$point_html = $this->getAllChildrenPointId(0,$tiku_data['course_id'],$tiku_data['point_id']);
			$this->assign('point_html',$point_html);
			$this->assign('tiku_data',$tiku_data);
		}
		$type_data = $this->getTypeByTikuId($tiku_data['course_id']);
		$this->assign('type_data',$type_data);
		$difficulty_data = $this->getTikuDifficulty();
		$this->assign('difficulty_data',$difficulty_data);
		$source_type_data = $this->getSourceType($tiku_data['course_id']);
		//var_dump($source_type_data);
		$this->assign('source_type_data',$source_type_data);
		$this->display();
	}
	public function getSourceType($course_id){
		$Model = M('source_type');
		$data = $Model->field("source_type.*")->join("tiku_course on tiku_course.course_type=source_type.course_type")->where("tiku_course.id=$course_id")->select();
		return $data;
	}
	public function getTypeByTikuId($course_id){
		$data = S('tiku_type_'.$course_id);
		if(!$data){
			$Model = M('tiku_type');
			$data = $Model->field("tiku_type.`type_name`,tiku_type.`id`")->join("course_to_type on tiku_type.id=course_to_type.type_id")->where("course_to_type.course_id=$course_id")->select();

			S('tiku_type_'.$course_id,$data,array('type'=>'file','expire'=>FILE_CACHE_TIME));
		}
		return $data;
	}
	/**
	 * 获取题型
	 * 单选题、多选题。。。
	 */
	public function getTikuType(){
		$course_id = $_GET['course_id'];
		$Model = M('tiku_type');
		if($course_id==0){
			$data = $Model->field("tiku_type.`type_name`,tiku_type.`id`")->select();
		}else{
			$data = $Model->field("tiku_type.`type_name`,tiku_type.`id`")->join("course_to_type on tiku_type.id=course_to_type.type_id")->where("course_to_type.course_id=$course_id")->select();
		}

		$this->ajaxReturn($data);
	}
	/**
	 * 获取所有题型
	 */
	public function getAllTypes(){
		$Model = M('tiku_type');
		$data = $Model->select();
		$this->assign('tiku_type',$data);
	}
	/**
	 * 获取题库难度数据
	 */
	public function getTikuDifficulty(){
		$data = S('tiku_difficulty');
		if(!$data){
			$Model = M('tiku_difficulty');
			$data = $Model->order('degreen desc')->select();
			S('tiku_difficulty',$data,array('type'=>'file','expire'=>FILE_CACHE_TIME));
		}
		return $data;
	}
	public function getPointsByCouresId(){
		$course_id = $_GET['course_id'];
		$html = $this->getAllChildrenPointId(0, $course_id, 0);
		$this->ajaxReturn($html);
	}
	/**
	 * 获取子节点ID
	 */
	public function getAllChildrenPointId($parent_id,$course_id,$current_id){
		$Model = M('tiku_point');
		$child_data = $Model->where("course_id=$course_id")->select();
		$data = $this->getTree($child_data,$parent_id);
		//var_dump($data);exit;
		$html = '';
		$select = '';
		foreach($data as $key=>$val){
			if($val['id']==$current_id) $select = 'selected';
			$html .= '<option value="'.$val['id'].'" '.$select.'>'.$val['point_name'].'</option>';
			$select = '';
			if($childs = $val['childs']){
				foreach($childs as $v){
					if($v['id']==$current_id) $select = 'selected';
					$html .= '<option value="'.$v['id'].'" '.$select.'>├'.$v['point_name'].'</option>';
					$select = '';
					if($childss = $v['childs']){
						foreach($childss as $vs){
							if($vs['id']==$current_id) $select = 'selected';
							$html .= '<option value="'.$vs['id'].'" '.$select.'>├├'.$vs['point_name'].'</option>';
							$select = '';
						}
					}
				}
			}
		}
		return $html;
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
			$System = A('System');
			$System->logWrite($_SESSION['admin_id'],"删除题库成功(ID:$result)");
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
			$System = A('System');
			$System->logWrite($_SESSION['admin_id'],"批量删除题库成功(ID:$ids)");
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
			$System = A('System');
			$System->logWrite($_SESSION['admin_id'],"批量审核题库成功(ID:$ids)");
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