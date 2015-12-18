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
		$course_data = parent::getCourse();
		$this->assign('course_data',$course_data);
	}
    public function index(){
    	redirect('/member/info');
        $this->display();
	}
	public function info(){
		$Modle = M('user');
		$data = $Modle->where("id=".$_SESSION['user_id'])->find();
		$this->assign('user_info',$data);
		$menPhotos = $this->getPhotoByRand('men');
		$womenPhotos = $this->getPhotoByRand('women');
		$this->assign('menPhotos',$menPhotos);
		$this->assign('womenPhotos',$womenPhotos);
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
				if(I('post.code')!=$_SESSION['phone_vcode']){
					
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
	public function onlinetest(){
		if(empty($_GET['id'])){
			$course_id = 1;//默认物理
		}else{
			$course_id = I('get.id');
		}
		$Model = M('onlinetest');
		$count = $Model->where("user_id=".$_SESSION['user_id']." AND course_id=$course_id")->count();
		$Page = new \Think\Page($count,10);
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		$Page->setConfig('first','首页');
		$Page->setConfig('last','末页');
		$page_show = $Page->m_show();
		$Model = M('onlinetest');
		$data = $Model->field('onlinetest.*,tiku_difficulty.section')->join("tiku_difficulty on onlinetest.difficulty_id=tiku_difficulty.id")->where("user_id=".$_SESSION['user_id']." AND course_id=$course_id")->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('ot_data',$data);
		$this->assign('page_show',$page_show);
		$this->assign('course_id',$course_id);
		$this->display();
	}
	public function testReport(){
		$id = I('get.id');
		$otModel = M('onlinetest');
		$ot_data = $otModel->field('onlinetest.*,tiku_difficulty.section')->join("tiku_difficulty on onlinetest.difficulty_id=tiku_difficulty.id")->where("user_id=".$_SESSION['user_id']." AND onlinetest.id=$id")->find();
		if(!$ot_data){//404
			
		}
		$otextendModel = M('onlinetest_extend');
		$count = $otextendModel->where("onlinetest_id=$id")->count();
		$Page = new \Think\Page($count,10);
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		$Page->setConfig('first','首页');
		$Page->setConfig('last','末页');
		$page_show = $Page->m_show();
		$data = $otextendModel->field('onlinetest_extend.*,tiku_difficulty.section,tiku_point.point_name')
		->join("tiku_to_point on onlinetest_extend.tiku_id=tiku_to_point.tiku_id")
		->join("tiku_point on tiku_to_point.point_id=tiku_point.id")
		->join("tiku_difficulty on onlinetest_extend.difficulty_id=tiku_difficulty.id")->where("onlinetest_extend.onlinetest_id=$id")->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('shiti_data',$data);
		$this->assign('page_show',$page_show);
		$this->assign('ot_data',$ot_data);
		$this->display();
	}
	public function myNote(){
		if(empty($_GET['id'])){
			$course_id = 1;//默认物理
		}else{
			$course_id = I('get.id');
		}
		$Model = M('onlinetest_note');
		$count = $Model->where("user_id=".$_SESSION['user_id']." AND course_id=$course_id")->count();
		$Page = new \Think\Page($count,10);
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		$Page->setConfig('first','首页');
		$Page->setConfig('last','末页');
		$page_show = $Page->m_show();
		if($count>0){
			$data = $Model->field('onlinetest_note.*,tiku.content,tiku.options')->join("tiku on onlinetest_note.tiku_id=tiku.id")->where("onlinetest_note.user_id=".$_SESSION['user_id']." AND onlinetest_note.course_id=$course_id")->limit($Page->firstRow.','.$Page->listRows)->select();
			$this->assign('note_data',$data);
		}
		$this->assign('page_show',$page_show);
		$this->assign('course_id',$course_id);
		$this->display();
	}
	public function ajaxEditPhoto(){
		$photo = I('get.photo');
		$Model = M('user');
		if($Model->where('id='.$_SESSION['user_id'])->save(array('photo'=>$photo))){
			$this->ajaxReturn(array('status'=>'success','photo'=>$photo));
		}else{
			$this->ajaxReturn(array('status'=>'error'));
		}
	}
	protected function getPhotoByRand($gender){
		if($handle = opendir('Public/photo/'.$gender)){
			while(false !==($file=readdir($handle))){
				if ($file != "." && $file != "..") {
					$file_arr[] = $file;
				}
			}
		}
		$count = count($file_arr);
		for($i=1;$i<$count;$i++){
			$rand = mt_rand(0, $count-1);
			if(!in_array($file_arr[$rand], $new_file)){
				$new_file[] = '/Public/photo/'.$gender.'/'.$file_arr[$rand];
				if(count($new_file)==5) break;
			}
			
		}
		return $new_file;
	}
	public function myshijuan(){
		$Model = M('user_shijuan');
		$count = $Model->where("user_id=".$_SESSION['user_id'])->count();
		$Page = new \Think\Page($count,5);
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		$Page->setConfig('first','首页');
		$Page->setConfig('last','末页');
		$page_show = $Page->m_show();
		$data = $Model->where("user_id=".$_SESSION['user_id'])->limit($Page->firstRow.','.$Page->listRows)->order("id desc")->select();
		$this->assign('my_shijuan',$data);
		$this->assign('page_show',$page_show);
		$this->display();
	}
	public function delShijuan(){
		$id = I('get.id');
		$Model = M('user_shijuan');
		if($Model->where("user_id=".$_SESSION['user_id'].' AND id='.$id)->delete()){
			$this->ajaxReturn(array('status'=>'success'));
		}else{
			$this->ajaxReturn(array('status'=>'error'));
		}
	}
	public function shijuanDetail(){
		$id = I('get.id');
		$Model = M('user_shijuan');
		$data = $Model->where("user_id=".$_SESSION['user_id']." AND id=$id")->find();
		if(!$data){//404错误
			
		}
		
		unset($_SESSION['shijuan']);
		unset($_SESSION['cart']);
		
		$_SESSION['shijuan'] = json_decode($data['content'],true);
		$_SESSION['course_id'] = $data['course_id'];
		$_SESSION['cart'] = json_decode($data['cart'],true);
		redirect('/shijuan/');
	}
	public function myCollect(){
		$tag_id = I('get.tagid');
		//获取题库数据
		$Model = M('user_collected');
		if(empty($tag_id)){
			$where = "user_collected.user_id=".$_SESSION['user_id'];
			$count = $Model->where($where)->count();
			//echo $Model->getLastSql();exit;
			//echo $count;exit;
			$Page = new \Think\Page($count,5);
			$Page->setConfig('prev','上一页');
			$Page->setConfig('next','下一页');
			$Page->setConfig('first','首页');
			$Page->setConfig('last','末页');
			$page_show = $Page->m_show();
			$this->assign('page_show',$page_show);
			$tiku_data = $Model->field("tiku.`id`,tiku.options,tiku.`content`,tiku.`clicks`,tiku_source.`source_name`,tiku.difficulty_id")
			->join("tiku on user_collected.tiku_id=tiku.id")
			->join("tiku_source on tiku_source.id=tiku.source_id")
			->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
		}else{
			$result = $Model->query("SELECT COUNT(*) AS counts FROM (SELECT * FROM user_collected WHERE user_collected.`tiku_id` IN 
(SELECT collected_tag.`tiku_id` FROM collected_tag WHERE collected_tag.`user_id`=".$_SESSION['user_id']." AND collected_tag.`tag_id`=$tag_id)) AS a 
INNER JOIN tiku ON a.tiku_id=tiku.`id`");
			$count = $result[0]['counts'];
			$Page = new \Think\Page($count,5);
			$Page->setConfig('prev','上一页');
			$Page->setConfig('next','下一页');
			$Page->setConfig('first','首页');
			$Page->setConfig('last','末页');
			$page_show = $Page->m_show();
			$this->assign('page_show',$page_show);
			$tiku_data = $Model->query("SELECT tiku.* FROM (SELECT * FROM user_collected WHERE user_collected.`tiku_id` IN 
(SELECT collected_tag.`tiku_id` FROM collected_tag WHERE collected_tag.`user_id`=".$_SESSION['user_id']." AND collected_tag.`tag_id`=$tag_id)) AS a 
INNER JOIN tiku ON a.tiku_id=tiku.`id`");
		}
		
		//var_dump($tiku_data);
		//echo $Model->getLastSql();
		$this->assign('page_show',$page_show);
		$this->assign('tiku_data',$tiku_data);
		$myTags = $this->getMytags();
		$this->assign('my_tags',$myTags);
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
				redirect($_COOKIE['pre_page']);
				//redirect("/member/");
			}
			
		}else{
			setcookie('pre_page',$_SERVER['HTTP_REFERER']);
			$this->display();
		}
		
	}
	public function resetpass(){
		$this->display();
	}
	public function resetpassEmail(){
		if($_POST){
			$email = I('post.email');
			$userModel = M('user');
			$result = $userModel->where("email='".$email."'")->find();
			if(!$result){
				$error_msg = '该邮箱未注册！';
				$this->display();
				return false;
			}
			$Mail = A('Mail');
			$hash = $this->register_hash('encode', $result['id']);
			$time_encrypt = base64_encode(md5(substr(md5($result['create_time']),6,4)).'_'.time());
			$Mail->sendMail('resetpass_email',$email,$_SERVER['HTTP_HOST'].'/member/newpass?hash='.$hash.'&t='.$time_encrypt);
			$this->assign('email',$email);
		}
		$this->display();
	}
	public function resetpassPhone(){
		
		$this->display();
	}
	public function resetpassPhone_two(){
		if(!empty($_POST)){
			if($_POST['yanzhengma']!=$_SESSION['phone_vcode']){
				redirect('/');
			}
			$_SESSION['telphone'] = I('post.telphone');
			setcookie(session_name(),session_id(),time()+120);
			$this->display();
		}else{
			redirect('/');
		}
	}
	public function resetpassPhone_three(){
		if(!isset($_SESSION['telphone'])){
			redirect('/');
		}
		$userModel = M('user');
		$result = $userModel->where("telphone='".$_SESSION['telphone']."'")->find();
		if(!$result){
			redirect('/');
		}
		$new_password = I('post.new_password');
		$data['salt'] = substr(uniqid(),2,6);
		$data['password'] = md5(md5($new_password.$data['salt']));
		$userModel->where("id=".$result['id'])->save($data);
		$_SESSION['nick_name'] = $result['nick_name'];
		$_SESSION['user_id'] = $result['id'];
		$_SESSION['user_type'] = $result['type'];
		redirect('/member/resetok');
	}
	public function newpass(){
		if(!empty($_GET['hash'])){
			$hash = I('get.hash');
			$user_id = $this->register_hash('decode', $hash);
			if(!$user_id) redirect('/');
			$Model = M('user');
			$result = $Model->where("id=$user_id")->find();
			$time_str = base64_decode(I('get.t'));
			$time_arr = explode('_',$time_str);
			if($time_arr[0]!=md5(substr(md5($result['create_time']),6,4))){
				redirect('/');
			}
			if((time()-$time_arr[1])>86400){
				redirect('/');
			}
			if(!$result){
				redirect('/');
			}
			if(empty($_POST)){
				$this->display();
			}else{
				$new_password = I('post.new_password');
				$data['salt'] = substr(uniqid(),2,6);
				$data['password'] = md5(md5($new_password.$data['salt']));
				$Model->where("id=$user_id")->save($data);
				$_SESSION['nick_name'] = $result['nick_name'];
				$_SESSION['user_id'] = $result['id'];
				$_SESSION['user_type'] = $result['type'];
				redirect('/member/resetok');
			}
		}else{
			redirect('/');
		}
	}
	public function resetok(){
		
		$this->display();
	}
	public function ajaxCollect(){
		if(empty($_SESSION['user_id'])){
			$this->ajaxReturn(array('status'=>'notlogin'));
		}
		$id = I('get.id');
		$Model = M('user_collected');
		$data['user_id'] = $_SESSION['user_id'];
		$data['tiku_id'] = $id;
		$data['collected_time'] = time();
		if($result = $Model->where("user_id=".$_SESSION['user_id']." AND tiku_id=$id")->find()){
			$Model->where("id=".$result['id'])->delete();
			$Model->table('collected_tag')->where("user_id=".$_SESSION['user_id']." AND tiku_id=$id")->delete();
			$this->ajaxReturn(array('status'=>'success','action'=>'delete'));
		}else{
			$Model->add($data);
			$tag = $this->getDefaultTag($id);
			$this->ajaxReturn(array('status'=>'success','action'=>'add','tag'=>$tag,'tiku_id'=>$id));
		}
	}
	public function ajaxAddTag(){
		$tiku_id = I('get.id');
		$tagStr = I('get.tag');
		$tagArr = explode(',',$tagStr);
		$tagModel = M('tag');
		$collectedTagModel = M('collected_tag');
		foreach($tagArr as $val){
			if(!empty($val)){
				if($result = $tagModel->where("tag_name='".$val."'")->find()){
					$data['tag_id'] = $result['id'];
				}else{
					$data['tag_id'] = $tagModel->add(array('tag_name'=>$val));
				}
				$data['user_id'] = $_SESSION['user_id'];
				$data['tiku_id'] = $tiku_id;
				$collectedTagModel->add($data);
			}
		}
		$this->ajaxReturn(array('status'=>'success'));
	}
	public function studentCeping(){
		$this->display();
	}
	public function teacherCeping(){
		$this->display();
	}
	protected function getMytags(){
		$Model = M('tag');
		$data = $Model->field("DISTINCT tag.*")->join("collected_tag on collected_tag.tag_id=tag.id")
		->where("collected_tag.user_id=".$_SESSION['user_id'])->select();
		return $data;
	}
	private function getDefaultTag($tiku_id){
		$Model = M('tiku');
		$data = $Model->join("tiku_source ON tiku.`source_id`=tiku_source.`id`")
		->join("tiku_course ON tiku_source.`course_id`=tiku_course.`id`")
		->join("tiku_type ON tiku.`type_id`=tiku_type.`id`")
		->where("tiku.id=$tiku_id")->find();
		if($data['course_type']==1){
			return '高中'.$data['course_name'].$data['type_name'];
		}else{
			return '初中'.$data['course_name'].$data['type_name'];
		}
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
	public function ajaxCheckPassword(){
		$password = I('post.param');;
		$Model = M('User');
		$result = $Model->field('password,salt')->where("id=".$_SESSION['user_id'])->find();
		if(md5(md5($password.$result['salt']))!=$result['password']){
			$this->ajaxReturn(array('status'=>'n','info'=>'旧密码输入有误！'));
		}else{
			$this->ajaxReturn(array('status'=>'y','info'=>'验证通过'));
		}
	}
	public function ajaxResetPassword(){
		$password = I('get.password');
		$new_password = I('get.new_password');
		$check_new_password = I('get.check_new_password');
		if(empty($new_password) || empty($password) || empty($check_new_password)){
			$this->ajaxReturn(array('status'=>'error','info'=>'密码不能为空！'));
		}
		if($new_password != $check_new_password){
			$this->ajaxReturn(array('status'=>'error','info'=>'两次输入的密码不一样！'));
		}
		$Model = M('User');
		$result = $Model->field('password,salt')->where("id=".$_SESSION['user_id'])->find();
		if(md5(md5($password.$result['salt']))!=$result['password']){
			$this->ajaxReturn(array('status'=>'error','info'=>'旧密码输入有误！'));
		}else{
			$Model->where("id=".$_SESSION['user_id'])->save(array('password'=>md5(md5($new_password.$result['salt']))));
			$this->ajaxReturn(array('status'=>'success'));
		}
	}
	public function ajaxCheckEmail(){
		$email = trim($_POST['param']);
		$Model = M('User');
		$resutl = $Model->where("email='$email'")->find();
		if(!$resutl){
			$this->ajaxReturn(array('status'=>'y','info'=>'通过验证'));
		}else{
			$this->ajaxReturn(array('status'=>'n','info'=>'该邮箱已注册！'));
		}
	}
	public function ajaxCheckTelphone(){
		$tel = I('post.param');
		$Model = M('User');
		$resutl = $Model->where("telphone='$tel'")->find();
		if(!$resutl){
			$this->ajaxReturn(array('status'=>'y','info'=>'通过验证'));
		}else{
			$this->ajaxReturn(array('status'=>'n','info'=>'该手机已注册！'));
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