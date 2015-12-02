<?php
namespace Home\Controller;
use Home\Controller\GlobalController;
class MemberController extends GlobalController {
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
	public function info(){
		$Modle = M('user');
		$data = $Modle->where("id=".$_SESSION['user_id'])->find();
		$this->assign('user_info',$data);
		$this->display();
	}
	public function register(){
		if($_POST){
			$is_mail = I('post.is_mail');
			
			$type = I('post.type');
			$user = I('post.email');
			$nick_name = I('post.nick_name');
			$password = I('post.password');
			$Model = M('User');
			$this->assign('is_mail',$is_mail);
			if($is_mail){
				$email = I('post.email');
				if(!preg_match('/\S{1,30}@\w+\.\w{1,10}/i',$email)){
					$error_msg = "非法邮箱!";
					$this->assign('error_msg',$error_msg);
					$this->display();
					return false;
				}
				if($Model->where("email='".$email."'")->find()){
					$error_msg = "该邮箱已注册!";
					$this->assign('error_msg',$error_msg);
					$this->display();
					return false;
				}
				$data['email'] = $email;
			}else{
				$telphone = I('post.telphone');
				if(!preg_match('/1\d{10}/',$telphone)){
					$error_msg = "非法手机号码!";
					$this->assign('error_msg',$error_msg);
					$this->display();
					return false;
				}
				//校验短信验证码
				if(I('post.code')!=$_SESSION['code']){
					
				}
				if($Model->where("telphone=$telphone")->find()){
					$error_msg = "该手机号码已注册!";
					$this->assign('error_msg',$error_msg);
					$this->display();
					return false;
				}
				$data['telphone'] = $telphone;
			}
			
			if(!preg_match('/\w{6,16}/',$password)){
				$error_msg = "密码由6-16位字母、数字或下划线组成！";
				$this->assign('error_msg',$error_msg);
				$this->display();
				return false;
			}
			$data['create_time'] = time();
			$data['last_login'] = time();
			$data['login_ip'] = get_client_ip();
			$data['type'] = $type;
			$data['nick_name'] = $nick_name;

			$data['salt'] = substr(uniqid(),2,6);
			$data['password'] = md5(md5($password.$data['salt']));;
			//var_dump($_POST);exit;
			
			if($user_id = $Model->add($data)){
				if($is_mail){
					$Mail = A('Mail');
					$hash = $this->register_hash('encode', $user_id);
					$Mail->sendMail('active_email',$email,$_SERVER['HTTP_HOST'].'/member/activemail?hash='.$hash);
					redirect("/member/toactivity?id=$user_id");
				}else{
					$_SESSION['nick_name'] = $nick_name;
					$_SESSION['user_id'] = $user_id;
					$_SESSION['user_type'] = $type;
					redirect("/member/");
				}
				
			}
		}else{
			$this->display();
		}
		
	}
	public function myshijuan(){
		$this->display();
	}
	public function sendMailAgain(){
		$user_id = I('get.id');
		$Model = M('user');
		$data = $Model->field('email')->where("id=$user_id")->find();
		$Mail = A('Mail');
		$hash = $this->register_hash('encode', $user_id);
		$Mail->sendMail('active_email',$data['email'],$_SERVER['HTTP_HOST'].'/member/activemail?hash='.$hash);
		$this->display('toactivity');
	}
	public function toActivity(){
		$user_id = I('get.id');
		$this->assign('user_id',$user_id);
		$this->display();
	}
	public function activeMail(){
		$hash = I('get.hash');
		$user_id = $this->register_hash('decode', $hash);
		$Model = M('user');
		$data = $Model->field('id,nick_name,email_verified,type')->where("id=$user_id")->find();
		if($data['email_verified']==1){
			redirect('/');
		}
		if($user_id){
			$Model->data(array('email_verified'=>1))->where("id=$user_id")->save();
			$_SESSION['nick_name'] = $data['nick_name'];
			$_SESSION['user_id'] = $data['id'];
			$_SESSION['user_type'] = $data['type'];
			redirect("/member/");
		}else{
			redirect('/');
		}
	}
	private function register_hash($operation,$key){
		if($operation=='encode'){
			$user_id = intval($key);
			$Model = M('user');
			$data = $Model->where("id=$user_id")->find();
			$hash = base64_encode($user_id.'_'.md5(substr(md5($data['create_time']),4,6)));
			return $hash;
		}else{
			$arr = explode('_',base64_decode($key));
			if(count($arr)!=2){
				return false;
			}
			$user_id = $arr[0];
			$salt = $arr[1];
			$Model = M('user');
			$data = $Model->where("id=$user_id")->find();
			if(!$data){
				return false;
			}
			if(md5(substr(md5($data['create_time']),4,6)) != $arr[1]){
				return false;
			}
			return $user_id;
			
		}
	}
	
	public function login(){
		if($_POST){
			$error_msg = '';
			$user = I('post.username');
			if(preg_match('/1\d{10}/',$user)){
				$tel = $user;
			}elseif(preg_match('/\S+@\w+\.\w+/i',$user)){
				$email = $user;
			}else{
				$error_msg = "非法邮箱或手机号码!";
				$this->assign('error_msg',$error_msg);
				$this->display();
				return false;
			}
			$password = I('post.password');
			$Model = M('User');
			$result = $Model->where("email='$email' OR telphone='$tel'")->find();
			//echo $Model->getLastSql();exit;
			//var_dump($result);exit;
			if(!$result){
				$error_msg = "用户名不存在!";
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
			//echo $Model->getLastSql();exit;
			if($error_msg==''){
				$_SESSION['nick_name'] = $result['nick_name'];
				$_SESSION['user_id'] = $result['id'];
				$_SESSION['user_type'] = $result['type'];
				if(I('post.auto_login')){
					setcookie('user_name',$user,time()+C('COOKIE_EXPIRE'),'/');
					setcookie('password',$password,time()+C('COOKIE_EXPIRE'),'/');
				}
				redirect("/member/");
			}
			
		}else{
			$this->display();
		}
		
	}
	public function resetpass(){
		$this->display();
	}
	public function logout(){
		session_destroy();
		setcookie('user_name','',time()-3600,'/');
		setcookie('password','',time()-3600,'/');
		redirect('/');
	}
	public function ajaxCheckUser(){
		$user = trim($_POST['param']);
		$Model = M('User');
		$resutl = $Model->where("email='$user' OR telphone='$user'")->find();
		if(!$resutl){
			$this->ajaxReturn(array('status'=>'n','info'=>'该用户还未注册'));
		}else{
			$this->ajaxReturn(array('status'=>'y','info'=>'验证通过'));
		}
	}
	public function ajaxCheckEmail(){
		$email = trim($_POST['param']);
		$Model = M('User');
		$resutl = $Model->where("email='$email'")->find();
		if(!$resutl){
			$this->ajaxReturn(array('status'=>'y','info'=>'通过验证'));
		}else{
			$this->ajaxReturn(array('status'=>'n','info'=>'该邮箱已存在！'));
		}
	}
	public function ajaxCheckNickname(){
		$nickname = trim($_POST['param']);
		$Model = M('User');
		$resutl = $Model->where("nick_name='$nickname'")->find();
		if(!$resutl){
			$this->ajaxReturn(array('status'=>'y','info'=>'通过验证'));
		}else{
			$this->ajaxReturn(array('status'=>'n','info'=>'该昵称已存在！'));
		}
	}
}