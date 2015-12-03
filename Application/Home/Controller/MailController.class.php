<?php
namespace Home\Controller;
use Home\Controller\GlobalController;
class MailController extends GlobalController {
	/**
	 * 初始化
	 */
	function _initialize()
	{
		import('Org/Util/Sendmail');
		parent::_initialize();
	}
	public function sendMail($template_code,$mailto,$url=false){
		$mail = new \Org\Util\sendmail();
		$mail->setServer(C('MAIL_SMTP'),C('MAIL_ADDRESS'),C('MAIL_PASSWORD'),C('MAIL_PORT'));
		$mail->setReceiver($mailto);
		$mail->setFrom(C('MAIL_ADDRESS'));
		$mail_template = $this->getMailTemplate($template_code);
		//var_dump($template_code);exit;
		$mail_subject = $mail_template['template_subject'];
		if($url){
			$mail_content = str_replace('{$'.$template_code.'}',$url,$mail_template['template_content']);
		}else{
			$mail_content = $mail_template['template_content'];
		}
		
		$mail->setMail($mail_subject,$mail_content);
		$result = $mail->sendMail();
	}
	protected function getMailTemplate($template_code){
		$Model = M('mail_template');
		$data = $Model->where("template_code='".$template_code."'")->find();
		if($data){
			return $data;
		}else{
			return false;
		}
	}
}
?>