<?php
namespace Home\Controller;
use Home\Controller\GlobalController;
class JingpinController extends GlobalController {
	/**
	 * 初始化
	 */
	function _initialize()
	{
		parent::_initialize();
		$course_data = parent::getCourse();
		$this->assign('course_data',$course_data);
		$tikuModel = A('Tiku');
		$tiku_cart = $tikuModel->_getTikuCart();
		$this->assign('tiku_cart',$tiku_cart);
		$this->assign('tikus_in_cart',json_encode($_SESSION['cart']));
	}
	
    public function index(){
    	$params = I('get.param');
		$result1 = preg_split("/[0-9]/", $params,0,PREG_SPLIT_NO_EMPTY);
		$result2 = preg_split("/[a-z]/", $params,0,PREG_SPLIT_NO_EMPTY );
		$new_params = array_combine($result1, $result2);
		
		$course_id = $_SESSION['course_id'];
		if(!$course_id){//错误跳转
			
		}
		$feature_id = $new_params['f'];//试卷类型ID
		$year = $new_params['y'];//年份
		$province_id = $new_params['a'];//省份
		$grade = $new_params['g'];//年级
		$this->parent_id = $point_id;
		
		$this->assign('feature_id',$feature_id);//试卷类型
		$this->assign('year',$year);
		$this->assign('province_id',$province_id);
		$this->assign('grade',$grade);
		//var_dump($feature_id);exit;

		
    	//获取题库类型
		$this->assign('course_id',$course_id);
		//获取科目类型，高中或初中
		$course_type = $this->getCourseType($course_id);
		//获取试卷类型
		$source_type = $this->getSourceType($course_type);
		$this->assign('source_type',$source_type);
		//获取年级数据
		$grade_data = $this->getGrades($course_type);
		$this->assign('grade_data',$grade_data);
		//获取年份数据
		$year_data = $this->getYears($course_id);
		$this->assign('year_data',$year_data);
		//获取地区数据
		$province_data = $this->getProvinces($course_id);
		$this->assign('province_data',$province_data);
		
		$where = "tiku_source.course_id=$course_id && tiku_source.source_name<>''";
		if($feature_id){
			$where .= " && tiku_source.source_type_id=$feature_id";
		}
		if($province_id){
			$where .= " && tiku_source.province_id=$province_id";
		}
		if($grade){
			$where .= " && tiku_source.grade=$grade";
		}
		if($year){
			$where .= " && tiku_source.year=$year";
		}
		//echo $join;exit;
		$Model = M('tiku_source');
		$count = $Model->where($where)->count();
		//echo $count;
		//echo $Model->getLastSql();exit;
		$Page = new \Think\Page($count,10);
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		$Page->setConfig('first','首页');
		$Page->setConfig('last','末页');
		$page_show = $Page->_show($params);
		$this->assign('page_show',$page_show);
		$source_data = $Model->field("tiku_source.source_name,tiku_source.id,tiku_source.update_time,tiku_source.clicks")
		->where($where)->limit($Page->firstRow.','.$Page->listRows)->order("tiku_source.id DESC")->select();
		//echo $Model->getLastSql();
		//var_dump($tiku_data);
		$this->assign('source_data',$source_data);
        $this->display();
	}
	/**
	 * 试卷详情页
	 */
	public function detail(){
		$id = I('get.id');
		if(!id){//错误提示页面
			
		}
		$Modle = M('tiku_source');
		$source_data = $Modle->field("tiku_source.*,province.province_name,source_type.type_name")->join("province on tiku_source.province_id = province.id")->join("source_type on source_type.id=tiku_source.source_type_id")->where("tiku_source.id=$id")->find();
		//echo $Modle->getLastSql();
		$this->updateClicks($id);
		$this->assign('source_data',$source_data);
		$tiku_datas  = $this->getTikus($id);
		$this->display();
	}
	/**
	 * 获取试卷下的题目
	 */
	protected function getTikus($id){
		$Model = M('tiku');
		$_data = $Model->field("tiku.id,tiku_type.type_name")->join("tiku_type on tiku_type.id=tiku.type_id")->where("tiku.source_id=$id")->order("tiku_type.weight DESC")->select();
		foreach ($_data as $key => $val) {
			if(!in_array($val['type_name'],$arr)){
				$arr[] = $val['type_name'];
			}
		
		}
		//var_dump($arr);exit;
		foreach($arr as $k=>$v){
			$count = 0;
			foreach($_data as $key=>$val){
				if(empty($new_arr[$k]['childs'])) $new_arr[$k]['childs']=array();
				if($v==$val['type_name']){
					$new_arr[$k]['type_name'] = $val['type_name'];
					$new_arr[$k]['childs'] = array_merge($new_arr[$k]['childs'],array($val['id']));
				}
				
			}
		}
		
		//var_dump($new_arr);exit;
		//区分第一卷和第二卷
		foreach($new_arr as $k=>$v){
			if($v['type_name']=='单选题' || $v['type_name']=='多选题'){
				$data[1][] = $new_arr[$k];
			}else{
				$data[2][] = $new_arr[$k];
			}
		}
		//var_dump($data);exit;
		foreach($data as $key=>$val){
			$oc = array(1=>'一',2=>'二');
			$shijuan[$key]['t_title'] = '';//第N卷标题
			if($key==1){
				$shijuan[$key]['t_title'] = '第一部分 选择题';//第1卷标题
			}else{
				$shijuan[$key]['t_title'] = '第二部分 非选择题';//第2卷标题
			}
			
			$count = 0;
			$shiti_count_per_juan = 0;
			foreach($val as $k=>$v){
				$count ++;
				$shiti_count = count($v['childs']);
				$shijuan[$key]['shiti'][$count]['t_title'] = $v['type_name'];
				$shijuan[$key]['shiti'][$count]['childs'] = $v['childs'];
				$shiti_count_per_juan += $shiti_count;
			}
		}
		$oa = array(1=>'一',2=>'二',3=>'三',4=>'四',5=>'五',6=>'六',7=>'七');
		$last = 0;
		$o = 1;
		if($shijuan[1]){
			$first_juan['t_title'] = '第一部分 选择题';//第1卷标题
			$first_juan['t_title'] = $shijuan[1]['t_title'];
			$first_juan['note'] = $shijuan[1]['note'];
			foreach($shijuan[1]['shiti'] as $k=>$v){
				$first_juan['shiti'][$k]['t_title'] = $v['t_title'];
				$first_juan['shiti'][$k]['childs'] = $this->_getTikuInfo($v['childs'],$o);
				$first_juan['shiti'][$k]['order_char']  = $oa[$k];
				$last = $k;
				
			}
		}
		if($shijuan[2]){
			$second_juan['t_title'] = '第二部分 非选择题';//第2卷标题
			$second_juan['t_title'] = $shijuan[2]['t_title'];
			$second_juan['note'] = $shijuan[2]['note'];
			foreach($shijuan[2]['shiti'] as $k=>$v){
				$second_juan['shiti'][$k]['t_title'] = $v['t_title'];
				$second_juan['shiti'][$k]['childs'] = $this->_getTikuInfo($v['childs'],$o);
				$second_juan['shiti'][$k]['order_char'] = $oa[$k+$last];
			}
		}
		//var_dump($first_juan);
		$this->assign('first_juan',$first_juan);
		$this->assign('second_juan',$second_juan);
		$this->assign('shijuan',$shijuan);
	}
	protected function _getTikuInfo($id_arr,&$o){
		$Model = M('tiku');
		foreach($id_arr as $key=>$val){
			$rs = $Model->field("id,content,options,answer,analysis")->where("id=$val")->find();
			$rs['order_char'] = $o;
			$tiku[] = $rs;
			$o++;
		}
		return $tiku;
	}
	/**
	 * 更新点击次数
	 */
	protected function updateClicks($id){
		$Model = M('tiku_source');
		$Model->where("id=$id")->setInc('clicks',1);
	}
	/**
	 * 获取年级数据
	 */
	public function getGrades($course_type){
		if($course_type == 1){//高中
			return array(array('id'=>1,'name'=>'高一'),array('id'=>2,'name'=>'高二'),array('id'=>3,'name'=>'高三'));
		}elseif($course_type == 2){
			return array(array('id'=>4,'name'=>'初一'),array('id'=>5,'name'=>'初二'),array('id'=>6,'name'=>'初三'));
		}
	}
	/**
	 * 获取年份数据
	 */
	public function getYears($course_id){
		$year_data = S('source_year_'.$course_id);
		if(!$year_data){
			$Model = M('tiku_source');
			$year_data = $Model->field("distinct year")->where("course_id=$course_id")->order("year desc")->select();
			S('source_year_'.$course_id,$year_data,array('type'=>'file','expire'=>FILE_CACHE_TIME));
		}
		return $year_data;
	}
	/**
	 * 获取地区数据
	 */
	public function getProvinces($course_id){
		$province_data = S('source_province_'.$course_id);
		if(!$province_data){
			$Model = M('tiku_source');
			$province_data = $Model->field("distinct province.id,province.province_name")->join("province on tiku_source.province_id=province.id")->where("tiku_source.course_id=$course_id")->order("tiku_source.year desc")->select();
			//var_dump($province_data);exit;
			S('source_province_'.$course_id,$province_data,array('type'=>'file','expire'=>FILE_CACHE_TIME));
		}
		return $province_data;
	}
	/**
	 * 获取试卷类型
	 * 历年高考真题、名校模拟题。。。
	 */
	public function getSourceType($course_type){
		$data = S('source_type');
		if(!$data){
			$Model = M('source_type');
			$data = $Model->where("course_type=$course_type")->select();
			S('source_type',$data,array('type'=>'file','expire'=>FILE_CACHE_TIME));
		}
		return $data;
	}
	public function getCourseType($course_id){
		$course_type = S('course_type');
		if(!$data){
			$Model = M('tiku_course');
			$data = $Model->field('course_type')->where("id=$course_id")->find();
			$course_type = $data['course_type'];
			S('course_type',$course_type,array('type'=>'file','expire'=>FILE_CACHE_TIME));
		}
		return $course_type;
	}
	/**
	 * 判断是否名校模拟题
	 */
	public function isMingXiao($feature_id){
		if($feature_id==2){
			$data = array(array('type_name'=>'高考模拟','id'=>2),array('type_name'=>'月考试卷','id'=>3),array('type_name'=>'期中试卷','id'=>4),array('type_name'=>'期末试卷','id'=>5));
			
			return $data;
		}else{
			return false;
		}
	}
	/**
	 * 判断选择的课程是否数学并且选择的题目特点是否
	 * 历年高考真题或名校模拟题或原创
	 * 只有高中数学题目有分文理科
	 */
	public function isShuxue($course_id,$feature_id){
		$Model = M('tiku_course');
		$data_1 = $Model->where("id=$course_id")->find();
		
		$Model = M('tiku_feature');
		$data_2 = $Model->where("id=$feature_id")->find();
		
		if($data_1['course_name']=='数学' && $data_2['is_wenli']==1){
			return true;
		}else{
			return false;
		}
		
	}
	/**
	 * 获取子节点ID
	 */
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
	/**
	 * 格式化参数
	 */
	public function formatParams(){
		
	}
	public function selectCourse(){
		$this->display();
	}
	
}
?>