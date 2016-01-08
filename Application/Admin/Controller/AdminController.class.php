<?php
namespace Admin\Controller;
use Admin\Controller\GlobalController;
class AdminController extends GlobalController {
	/**
	 * 初始化
	 */
	function _initialize()
	{
		parent::_initialize();
		
	}
    public function index(){
		$user_name = I('post.user_name');
		$this->assign('user_name',$user_name);
		$where = '1=1';
		if($user_name){
			$where .= " && admin.user_name like '%".$user_name."%'";
		}
		$Model = M('admin');
		$data = $Model
		->field("admin.*,admin_group.title")
		->join('admin_group_access on admin_group_access.uid=admin.id')
		->join('admin_group on admin_group_access.group_id=admin_group.id')
		->where($where)->select();
		$this->assign('data',$data);
        $this->display();
	}
	public function add(){
		if($_POST){
			$data['create_time'] = time();
			$data['status'] = 1;
			$data['user_name'] = I('post.user_name');
			$data['salt'] = substr(uniqid(),2,6);
			$password = I('post.password');
			$data['password'] = md5(md5($password.$data['salt']));
			$group_id = I('post.group_id');
			$Model = M('admin');
			if($Model->where("user_name='".$data['user_name']."'")->find()){
				$this->ajaxReturn(array('status'=>'n','info'=>'该账号已存在'));
			}
			$Model->startTrans();
			$result_1 = $Model->add($data);
			$accessModel = M('admin_group_access');
			$result_2 = $accessModel->data(array('uid'=>$result_1,'group_id'=>$group_id))->add();
			if($result_1 && $result_2){
				$Model->commit();
				$System = A('System');
				$System->logWrite($_SESSION['admin_id'],"添加管理员成功(ID:$result_1)");
				$this->ajaxReturn(array('status'=>'y','info'=>'成功'));
			}else{
				$Model->rollback();
				$this->ajaxReturn(array('status'=>'n','info'=>'失败'));
			}
		}else{
			$this->getRoles();
			$this->display();
		}
	}
	public function edit(){
		if($_POST){
			$id = I('post.id');
			$Model = M('Admin');
			$result = $Model->where("id=$id")->find();
			if(!$result){
				$this->ajaxReturn(array('status'=>'n','info'=>'该账号不存在'));
			}
			$password = I('post.password');
			if(!empty($password)){
				$salt = $result['salt'];
				$data['password'] = md5(md5($password.$salt));
			}
			$data['update_time'] = time();
			$group_id = I('post.group_id');
			$Model = M('admin');
			$Model->startTrans();
			$result_1 = $Model->where("id=$id")->save($data);
			$accessModel = M('admin_group_access');
			$result_2 = $accessModel->where("uid=".$result['id'])->data(array('group_id'=>$group_id,'update_time'=>time()))->save();
			if($result_1 && $result_2){
				$Model->commit();
				$System = A('System');
				$System->logWrite($_SESSION['admin_id'],"编辑管理员成功(ID:$id)");
				$this->ajaxReturn(array('status'=>'y','info'=>'成功'));
			}else{
				$Model->rollback();
				$this->ajaxReturn(array('status'=>'n','info'=>'失败'));
			}
		}else{
			$id = I('get.id');
			$Model = M('Admin');
			$result = $Model->join('admin_group_access on admin_group_access.uid=admin.id')->where("admin.id=$id")->find();
			$this->assign('data',$result);
			$this->getRoles();
			$this->display();
		}
	}
	public function delete(){
		$id = I('get.id');
		$Model = M('admin');
		$Model->startTrans();
		$result_1 = $Model->where("id=$id")->delete();
		$accessModel = M('admin_group_access');
		$result_2 = $accessModel->where("uid=$id")->delete();
		if($result_1 && $result_2){
			$Model->commit();
			$System = A('System');
			$System->logWrite($_SESSION['admin_id'],"删除管理员成功(ID:$id)");
			$this->ajaxReturn(array('status'=>'success'));
		}else{
			$Model->rollback();
			$this->ajaxReturn(array('status'=>'error'));
		}
	}
	public function stop(){
		$id = I('get.id');
		$Model = M('admin');
		if($Model->where("id=$id")->save(array('status'=>0))){
			$System = A('System');
			$System->logWrite($_SESSION['admin_id'],"停用管理员成功(ID:$id)");
			$this->ajaxReturn(array('status'=>'success'));
		}else{
			$this->ajaxReturn(array('status'=>'error'));
		}
	}
	public function start(){
		$id = I('get.id');
		$Model = M('admin');
		if($Model->where("id=$id")->save(array('status'=>1))){
			$System = A('System');
			$System->logWrite($_SESSION['admin_id'],"启用管理员成功(ID:$id)");
			$this->ajaxReturn(array('status'=>'success'));
		}else{
			$this->ajaxReturn(array('status'=>'error'));
		}
	}
	protected function getRoles(){
		$Model = M('Admin_group');
		$result = $Model->where("status=1")->select();
		$this->assign('roles',$result);
	}
	public function welcome(){
		$this->display();
	}
	public function role(){
		$Model = M('admin_group');
		$group = $Model->select();
		$adminModel = M('admin');
		foreach($group as $key=>$val){
			$admin = $adminModel->field('admin.user_name')
			->join('admin_group_access on admin_group_access.uid=admin.id')->where("admin_group_access.group_id=".$val['id'])->select();
			$user = '';
			foreach($admin as $v){
				$user .= $v['user_name'];
				$user .= ',';
			}
			$group[$key]['user'] = trim($user,',');
		}
		//var_dump($group);
		$this->assign('group',$group);
		$this->display();
	}
	public function roleDelete(){
		$id = I('get.id');
		$Model = M('admin_group');
		if($Model->where("id=$id")->delete()){
			$System = A('System');
			$System->logWrite($_SESSION['admin_id'],"删除角色成功(ID:$id)");
			$this->ajaxReturn(array('status'=>'success'));
		}else{
			$this->ajaxReturn(array('status'=>'error'));
		}
	}
	public function rule(){
		$this->display();
	}
	public function roleEdit(){
		if($_POST){
			$data['description'] = I('post.description');
			$data['rules'] = implode(',',I('post.rules'));
			$id = I('post.id');
			$Model = M('admin_group');
			if($Model->where("id=$id")->save($data)){
				$System = A('System');
				$System->logWrite($_SESSION['admin_id'],"编辑角色成功(ID:$id)");
				$this->ajaxReturn(array('status'=>'y','info'=>'成功'));
			}else{
				$this->ajaxReturn(array('status'=>'n','info'=>'失败'));
			}
		}else{
			$id = I('get.id');
			$Model = M('admin_group');
			$result = $Model->where("id=$id")->find();
			$result['rules'] = explode(',', $result['rules']);
			$this->assign('group',$result);
			$modules = $this->getModules();
			$this->assign('modules',$modules);
			$this->display();
		}
	}
	public function roleAdd(){
		if($_POST){
			$data['status'] = 1;
			$data['title'] = I('post.title');
			$data['description'] = I('post.description');
			$data['rules'] = implode(',',I('post.rules'));
			$Model = M('admin_group');
			if($Model->where("title='".$data['title']."'")->find()){
				$this->ajaxReturn(array('status'=>'n','info'=>'该角色已存在'));
			}
			if($id = $Model->add($data)){
				$System = A('System');
				$System->logWrite($_SESSION['admin_id'],"添加角色成功(ID:$id)");
				$this->ajaxReturn(array('status'=>'y','info'=>'成功'));
			}else{
				$this->ajaxReturn(array('status'=>'n','info'=>'失败'));
			}
		}else{
			$modules = $this->getModules();
			$this->assign('modules',$modules);
			$this->display();
		}
	}
	protected function getModules(){
		$Model = M('Module');
		$modules = $Model->where("status=1")->select();
		
		$ruleModel = M('Admin_rule');
		foreach($modules as $key=>$val){
			$rule = $ruleModel->where("module_id=".$val['id'])->select();
			$modules[$key]['childs'] = $rule;
		}
		return $modules;
	}
	public function login(){
		if($_POST){
			$verify_code = I('post.verify');
			$Verify = new \Think\Verify();
			if(!$Verify->check($verify_code)){
				$error_msg = "验证码有误!";
				$this->assign('error_msg',$error_msg);
				$this->display();
				return false;
			}
			$error_msg = '';
			$user = I('post.user_name');
			$password = I('post.password');
			$Model = M('Admin');
			$result = $Model->where("user_name='".$user."'")->find();
			if(!$result){
				$error_msg = "该账号不存在!";
				$this->assign('error_msg',$error_msg);
				$this->display();
				return false;
			}
			if($result['status']==0){
				$error_msg = "该账号已停用!";
				$this->assign('error_msg',$error_msg);
				$this->display();
				return false;
			}
			if(md5(md5($password.$result['salt']))!=$result['password']){
				$error_msg = "密码不对!";
				$this->assign('error_msg',$error_msg);
				$this->display();
				return false;
			}
			

			if($error_msg==''){
				$data['last_login'] = time();
				$data['login_ip'] = get_client_ip();
				$Model->where("id=".$result['id'])->save($data);
				$_SESSION['admin_id'] = $result['id'];
				$System = A('System');
				$System->logWrite($_SESSION['admin_id'],'登录成功');
				redirect('/index.php/admin');
			}
		}else{
			$this->display();
		}
		
	}
	public function logout(){
		session_destroy();
		redirect('/index.php/admin/admin/login');
	}
	public function verifyCode(){
		//$config = array('imageH'=>'35px','imageW'=>'70px');
	 	$Verify = new \Think\Verify();
		$Verify->useCurve =false;
		$Verify->entry();
	}
	public function ajaxCheckVerifyCode(){
	 	$verify_code = I('post.param');
		$Verify = new \Think\Verify();
		//$this->ajaxReturn(array('status'=>$verify_code,'info'=>"验证码有误！"));
		if(!$Verify->check($verify_code)){
			$this->ajaxReturn(array('status'=>'n','info'=>"验证码有误！"));
		}else{
			$this->ajaxReturn(array('status'=>'y'));
		}
	}
}