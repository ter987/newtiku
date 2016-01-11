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
    public function readLog(){
    	$content = I('request.content');
		$time_start = strtotime(I('request.time_start'));
		$time_end = strtotime(I('request.time_end'));
		
		$where = '1=1';
		if(!empty($content)){
			$where = "system_log.content like '%$content%'";
		}
		if($time_start){
			$where .= " && system_log.update_time > $time_start";
		}
		if($time_end){
			$where .= " && system_log.update_time < $time_end";
		}
		
		$Model = M('system_log');
		$count = $Model->where($where)->count();
		//echo $Model->getLastSql();exit;
		$Page = new \Think\Page($count,20);
		$Page->parameter['content'] = $content;
		$Page->parameter['time_start'] = $time_start;
		$Page->parameter['time_end'] = $time_end;
		$Page->setConfig('first','第一页');
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		$page_show = $Page->show();
		$this->assign('page_show',$page_show);
		$data = $Model->field("system_log.*,admin.user_name")->join("admin on admin.id=system_log.admin_id")
		->where($where)->limit($Page->firstRow.','.$Page->listRows)->order("system_log.id desc")->select();
		//var_dump($tiku_data);
		$this->assign('data',$data);
		$this->assign('count',$count);
        $this->display();
	}
	public function report(){
		$Model = M('admin');
		$data = $Model->field("id,user_name")->where("id <> 2")->select();
		$systemModel = M('system_log');
		foreach($data as $key=>$val){
			$data[$key]['yestoday_count'] = $systemModel->where("admin_id=".$val['id']." AND content LIKE '编辑题库%' AND FROM_UNIXTIME(update_time,'%Y-%m-%d')=DATE_ADD(CURDATE(),INTERVAL '-1' DAY)")->count();
			$data[$key]['day_count'] = $systemModel->where("admin_id=".$val['id']." AND content LIKE '编辑题库%' AND FROM_UNIXTIME(update_time,'%Y-%m-%d')=CURDATE()")->count();
			$data[$key]['month_count'] = $systemModel->where("admin_id=".$val['id']." AND content LIKE '编辑题库%' AND MONTHNAME(FROM_UNIXTIME(update_time))=MONTHNAME(NOW())")->count();
			$data[$key]['week_count'] = $systemModel->where("admin_id=".$val['id']." AND content LIKE '编辑题库%' AND WEEK(FROM_UNIXTIME(update_time))=WEEK(NOW())")->count();
			$data[$key]['lastweek_count'] = $systemModel->where("admin_id=".$val['id']." AND content LIKE '编辑题库%' AND WEEK(FROM_UNIXTIME(update_time))=WEEK(NOW())-1")->count();
		}
		$this->assign('data',$data);
        $this->display();
	}
	public function logWrite($admin_id,$content){
		$Model = M('system_log');
		$data['admin_id'] = $admin_id;
		$data['content'] = $content;
		$data['client_ip'] = get_client_ip();
		$data['update_time'] = time();
		$Model->add($data);
	}
	public function deleteLog(){
		$id = I('get.id');
		$Model = M('system_log');
		$result_1 = $Model->where("id=$id")->delete();
		if($result_1){
			$this->ajaxReturn(array('status'=>'y'));
		}else{
			$this->ajaxReturn(array('status'=>'n'));
		}
	}
	public function deleteAllLog(){
		$ids = $_GET['ids'];
		$Model = M('system_log');
		$result = $Model->where("id IN ($ids)")->delete();
		if($result){
			$this->_message('success','删除成功！');
		}else{
			$this->_message('error','删除失败！');
		}
	}
}