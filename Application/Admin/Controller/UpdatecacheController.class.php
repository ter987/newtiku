<?php
namespace Admin\Controller;
use Admin\Controller\GlobalController;
class UpdatecacheController extends GlobalController {
	
    public function index(){
    	//$this->cecheTiku();
    	$dir = './Application/Runtime/Temp/';
		$handle = opendir($dir);
		while (false !== ($file = readdir($handle))) {
		    if($file!="." && $file!="..") {
		      $fullpath=$dir.$file;
		      unlink($fullpath);
		    }
		 }
		$this->cecheTiku();
        $this->display();
	}
	public function cecheTiku(){
		$courseModel = M('tiku_course');
		$typeModel = M('tiku_type');
		$course_data = $courseModel->select();
		foreach($course_data as $val){
			$pointModel = M('tiku_point');
			$point_data = $pointModel->where("course_id=".$val['id'])->select();
			$point_data = array_merge($point_data,array(array('id'=>0)));
			if(!$point_data) continue;
			$type_data = $typeModel->join("course_to_type on tiku_type.id=course_to_type.type_id")->where("course_to_type.course_id=".$val['id'])->select();
			$type_data = array_merge($type_data,array(array('id'=>0)));
			foreach($type_data as $tv){
				for($d=0;$d<6;$d++){
					for($f=0;$f<7;$f++){
						foreach($point_data as $v){
							$where = "tiku_source.course_id=".$val['id'];
							$join ="tiku_source on tiku_source.id=tiku.source_id";
							if($tv['id']){
								$where .= " && tiku.type_id=".$tv['id'].' ';
							}
							if($d){
								$where .= " && tiku.difficulty_id=$d ";
							}
							if($f){
								$where .= " && tiku_source.source_type_id=$f";
							}
							
							if($v['id']){
								$_points = $v[id].$this->getAllChildrenPointId($v['id']);
								$join2= "tiku_to_point ON tiku_to_point.`tiku_id`=tiku.`id`";
								$where .= " && tiku_to_point.point_id IN ($_points)";
							}
							//echo $join;exit;
							$Model = M('tiku');
							//获取年份数据 地区数据
							$year_data = $Model->field("distinct tiku_source.year")->join($join)->join($join2)->where($where)->order("tiku_source.year desc")->select();
							if($year_data){
								S('tiku_year_'.$where,$year_data,array('type'=>'file','expire'=>C('FILE_CACHE_TIME')));
							}
							
							$province_data = $Model->field("distinct province.id,province.province_name")->join($join)->join($join2)->join("province on tiku_source.province_id=province.id")->where($where)->order("tiku_source.year desc")->select();
							if($province_data){
								S('province_data_'.$where,$province_data,array('type'=>'file','expire'=>C('FILE_CACHE_TIME')));
							}
							
							//获取题库数据
							$result = $Model->field("COUNT(*) AS tp_count")->join($join)->join($join2)->where($where)->find();
							$count = $result['tp_count'];
							if($count){
								S('tiku_count_'.$where,$count,array('type'=>'file','expire'=>C('FILE_CACHE_TIME')));
							}
						}
					}
				}
			}
		}
	}
	
	public function getAllChildrenPointId($parent_point_id){
		$Model = M('tiku_point');
		$child_data = $Model->where("parent_id=$parent_point_id")->select();
		if($child_data){//如果存在子节点
			foreach($child_data as $val){
				$GLOBALS['str'] .= ','.$val['id'];
				$this->getAllChildrenPointId($val['id']);
			}
			
		}
		return $this->parent_id.$GLOBALS['str'];
	}
	
}