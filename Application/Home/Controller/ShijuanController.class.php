<?php
namespace Home\Controller;
use Home\Controller\GlobalController;
class ShijuanController extends GlobalController {
	var $parent_id;
	/**
	 * 初始化
	 */
	function _initialize()
	{
		parent::_initialize();
		$this->checkLogin();
		$Tiku = A('Tiku');
		$tiku_cart = $Tiku->_getTikuCart();
		$this->assign('tiku_cart',$tiku_cart);
		$this->assign('tikus_in_cart',json_encode($_SESSION['cart']));
	}
	
    public function index(){
    	if(empty($_SESSION['cart'])){
    		redirect('/');
    	}
    	$shijuan_type = !empty($_SESSION['shijuan']['shijuan_banshi'])?$_SESSION['shijuan']['shijuan_banshi']:1;//默认的试卷类型：随堂练习
    	$shijuantypeModel = M('shijuan_banshi');
		$shijuantype_data = $shijuantypeModel->where("id=$shijuan_type")->find();
    	$courseModel = M('tiku_course');
    	$course_data = $courseModel->where("id=".$_SESSION['course_id'])->find();
		$grade = $course_data['course_type']==1?'高中':'初中';
    	$_SESSION['shijuan']['title'] = $grade.$course_data['course_name'].$shijuantype_data['name'].'-'.date('Ymd');
		foreach ($_SESSION['cart'] as $key => $val) {
			if(!in_array($val['type_name'],$arr)){
				$arr[] = $val['type_name'];
			}
		
		}
		
		foreach($arr as $k=>$v){
			$count = 0;
			foreach($_SESSION['cart'] as $key=>$val){
				if(empty($new_arr[$k]['childs'])) $new_arr[$k]['childs']=array();
				if($v==$val['type_name']){
					$new_arr[$k]['type_name'] = $val['type_name'];
					$new_arr[$k]['childs'] = array_merge($new_arr[$k]['childs'],array($val['id']));
				}
				
			}
		}
		
		//var_dump($new_arr);
		//区分第一卷和第二卷
		foreach($new_arr as $k=>$v){
			if($v['type_name']=='单选题' || $v['type_name']=='多选题'){
				$data[1][] = $new_arr[$k];
			}else{
				$data[2][] = $new_arr[$k];
			}
		}
		foreach($data as $key=>$val){
			$oc = array(1=>'一',2=>'二');
			$_SESSION['shijuan'][$key]['t_title'] = '';//第N卷标题
			if($key==1){
				$_SESSION['shijuan'][$key]['t_title'] = '第I卷（选择题）';//第1卷标题
			}else{
				$_SESSION['shijuan'][$key]['t_title'] = '第II卷（非选择题）';//第2卷标题
			}
			$_SESSION['shijuan'][$key]['note'] = '';//第N卷注释
			
			$count = 0;
			$shiti_count_per_juan = 0;
			foreach($val as $k=>$v){
				$count ++;
				$shiti_count = count($v['childs']);
				$_SESSION['shijuan'][$key]['shiti'][$count]['t_title'] = $v['type_name'].'(共'.$shiti_count.'小题)';
				$_SESSION['shijuan'][$key]['shiti'][$count]['childs'] = $v['childs'];
				$shiti_count_per_juan += $shiti_count;
			}
			$_SESSION['shijuan'][$key]['note'] = '本试卷第'.$oc[$key].'部分共有'.$shiti_count_per_juan.'道试题。';//第N卷注释
		}
		$oa = array(1=>'一',2=>'二',3=>'三',4=>'四',5=>'五',6=>'六',7=>'七');
		$last = 0;
		$o = 1;
		if($_SESSION['shijuan'][1]){
			$first_juan['t_title'] = $_SESSION['shijuan'][1]['t_title'];
			$first_juan['note'] = $_SESSION['shijuan'][1]['note'];
			foreach($_SESSION['shijuan'][1]['shiti'] as $k=>$v){
				$first_juan['shiti'][$k]['t_title'] = $v['t_title'];
				$first_juan['shiti'][$k]['childs'] = $this->_getTikuInfo($v['childs'],$o);
				$first_juan['shiti'][$k]['order_char']  = $oa[$k];
				$last = $k;
				
			}
		}
		if($_SESSION['shijuan'][2]){
			$second_juan['t_title'] = $_SESSION['shijuan'][2]['t_title'];
			$second_juan['note'] = $_SESSION['shijuan'][2]['note'];
			foreach($_SESSION['shijuan'][2]['shiti'] as $k=>$v){
				$second_juan['shiti'][$k]['t_title'] = $v['t_title'];
				$second_juan['shiti'][$k]['childs'] = $this->_getTikuInfo($v['childs'],$o);
				$second_juan['shiti'][$k]['order_char'] = $oa[$k+$last];
			}
		}
		//var_dump($second_juan);
		$shijuan['title'] = $_SESSION['shijuan']['title'];
		$this->assign('first_juan',$first_juan);
		$this->assign('shijuan_title',$_SESSION['shijuan']['title']);
		$this->assign('shijuan_subtitle',$_SESSION['shijuan']['subtitle']);
		$this->assign('second_juan',$second_juan);
		$this->assign('shijuan',$shijuan);
        $this->display();
	}
	/**
	 * 生成word文件
	 */
	public function createToWord(){
		// if(empty($_SESSION['shijuan'])){
    		// redirect('/');
    	// }
		Vendor('PhpWord.src.PhpWord.Autoloader');
		\PhpOffice\PhpWord\Autoloader::register();
		
		// Creating the new document...
		$phpWord = new \PhpOffice\PhpWord\PhpWord();
		// Every element you want to append to the word document is placed in a section. So you need a section:
		$section = $phpWord->addSection();
		//$section->getStyle()->setPageNumberingStart(1);
		$footer = $section->addFooter();
		//$footer->addText('第1页');
		$footer->addPreserveText('第{PAGE}页(共{NUMPAGES}页).');
		//$header = $section->addHeader();
		//$header->addText('头部');
		// You can directly style your text by giving the addText function an array:
		$section->addText($_SESSION['shijuan']['title'], array( 'size'=>'15','bold'=>true),array('align' => 'center'));
		$section->addText('满分：', array( 'size'=>'13'),array('align' => 'center'));
		$section->addText('班级：_________  姓名：_________  考号：_________', array( 'size'=>'13'),array('align' => 'center'));
		$section->addTextBreak();//换行
		$oa = array(1=>'一',2=>'二',3=>'三',4=>'四',5=>'五',6=>'六',7=>'七');
		$last = 0;
		$o = 1;
		if($_SESSION['shijuan'][1]){
			$option_index = array(0=>'A',1=>'B',2=>'C',3=>'D',4=>'E');
			$section->addText($_SESSION['shijuan'][1]['t_title'],array('size'=>13,'bold'=>true),array('align' => 'center'));
			$section->addText($_SESSION['shijuan'][1]['note'],array('size'=>13));
			foreach($_SESSION['shijuan'][1]['shiti'] as $k=>$v){
				$childs = $this->_getTikuInfo($v['childs'],$o);
				$last = $k;
				$section->addText($oa[$k].'、'.$v['t_title'],array('size'=>13,'bold'=>true));
				foreach($childs as $key=>$val){
					$textrun = $section->createTextRun(array('widowControl'=>'true'));
					$question = trim(strip_tags(htmlspecialchars_decode($val['content']),'<img>'));
					$question = preg_replace('/(&nbsp;)*/','',$question);
					$text_arr = preg_split('/<img[\s|\S]+>/U',$question);
					preg_match_all('/src="[\s|\S]+"/U',$question,$matchs);
					//var_dump($matchs);exit;
					if($matchs){
						$img_arr = preg_replace('/(src="\/)|"/U','',$matchs[0]);
						$i=0;
						$text_count = count($text_arr);
						$img_count = count($img_arr);
						$textrun->addText($val['order_char'].'.',array('size'=>13));
						while($i<$text_count){
							//echo $text_arr[$i];exit;
							$textrun->addText($text_arr[$i],array('size'=>13));
							if($i==$img_count) break;
							$textrun->addImage($img_arr[$i],array('marginTop'=>100));
							$i++;
						}
						
					}else{
						$section->addText($val['order_char'].'.'.$question,array('size'=>13));
					}
					if($val['options']){
						$options = json_decode($val['options']);
						
						$table = $section->addTable('myTable');
						$table->addRow();
						foreach($options as $d=>$c){
							$cell = $table->addCell(2500);
							$textrun2 = $cell->addTextRun();
							$option = trim($c);
							$option = preg_replace('/(&nbsp;)*/','',$option);
							$text_arr = preg_split('/<img[\s|\S]+>/U',$option);
							preg_match_all('/src="[\s|\S]+"/U',$option,$matchs);
							//var_dump($matchs);exit;
							if($matchs){
								$img_arr = preg_replace('/(src="\/)|"/U','',$matchs[0]);
								$i=0;
								$text_count = count($text_arr);
								$img_count = count($img_arr);
								while($i<$text_count){
									//echo $text_arr[$i];exit;
									$textrun2->addText($option_index[$d].'.'.$text_arr[$i],array('size'=>13));
									if($i==$img_count) break;
									$textrun2->addImage($img_arr[$i],array('marginTop'=>100));
									$i++;
								}
							}else{
								$textrun2->addText($option_index[$d].'.'.$option,array('size'=>13));
							}
					}
					//break;
					
					}
				}
				//$textrun->addImage('Public/tikupics/20151103/08/42/563803062838d1446511366.gif');
			}
		}
		$section->addTextBreak();
		if($_SESSION['shijuan'][2]){
			$section->addText($_SESSION['shijuan'][2]['t_title'],array('size'=>13,'bold'=>true),array('align' => 'center'));
			$section->addText($_SESSION['shijuan'][2]['note'],array('size'=>13));
			foreach($_SESSION['shijuan'][2]['shiti'] as $k=>$v){
				$childs = $this->_getTikuInfo($v['childs'],$o);
				$last = $k;
				$section->addText($oa[$k].'、'.$v['t_title'],array('size'=>13,'bold'=>true));
				//break;
				foreach($childs as $key=>$val){
					$question = trim(strip_tags(htmlspecialchars_decode($val['content']),'<img><p><br />'));
					$question = preg_replace('/(&nbsp;)*/','',$question);
					$question_arr = preg_split('/<p[\s|\S]*>|<br \/>/U',$question);
					//var_dump($question_arr);exit;
					foreach($question_arr as $kk=>$vv){
						//echo $vv;
						$vv =strip_tags($vv,'<img>');
						$order_char = '';
						if($kk==0) $order_char = $val['order_char'].'.';
						$textrun_name = 'textrun_'.$kk;
						$$textrun_name = $section->createTextRun();
						$text_arr = preg_split('/<img[\s|\S]+>/U',$vv);
						$text_arr = preg_replace('/\n/','',$text_arr);
						preg_match_all('/src="[\s|\S]+"/U',$vv,$matchs);
						//var_dump($matchs);exit;
						if(!empty($matchs[0])){
							$img_arr = preg_replace('/(src="\/)|"/U','',$matchs[0]);
							$i=0;
							$text_count = count($text_arr);
							$img_count = count($img_arr);
							$$textrun_name->addText($order_char,array('size'=>13));
							while($i<$text_count){
								//echo $text_arr[$i];exit;
								$$textrun_name->addText($text_arr[$i],array('size'=>13));
								if($i==$img_count) break;
								//echo $img_arr[$i];exit;
								$$textrun_name->addImage($img_arr[$i]);
								$i++;
							}
							
						}else{
							//echo $vv;
							$section->addText($order_char.$vv,array('size'=>13));
						}
						//break;
					}
					if(strpos($v['t_title'],'解答题')!==false){
						$section->addTextBreak(10);
					}
					
				}
			}
		}
		//exit;
		// At least write the document to webspace:
		//$PHPWord_IOFactory = new \Vendor\PHPWord\PHPWord_IOFactory();
		$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
		//$objWriter->save('helloWorld.docx');
		
		//$objWriter->save(Yii::app()->params['exportToDir'].$filename.".docx");
        header("Content-Description: File Transfer");
        header('Content-Disposition: attachment; filename="'.$_SESSION['shijuan']['title'].'.docx"');
        //header("Content-Type: application/docx");
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Transfer-Encoding: binary');
        header("Cache-Control: public");
        header('Expires: 0');
        $objWriter->save("php://output");
	}
	public function ajaxSave(){
		$Model = M('user_shijuan');
		if(isset($_SESSION['shijuan']['id'])){//更新数据库
			$data['id'] = $_SESSION['shijuan']['id'];
			$data['update_time'] = time();
			$data['content'] = json_encode($_SESSION['shijuan']);
			if($Model->data($data)->save()){
				$this->ajaxReturn(array('status'=>'success','action'=>'update'));
			}else{
				$this->ajaxReturn(array('status'=>'error','action'=>'update'));
			}
		}else{//添加到数据库
			$data['title'] = $_SESSION['shijuan']['title'];
			$data['user_id'] = $_SESSION['user_id'];
			$data['create_time'] = $data['update_time'] = time();
			$data['content'] = json_encode($_SESSION['shijuan']);
			if($id = $Model->add($data)){
				$_SESSION['shijuan']['id'] = $id;
				$this->ajaxReturn(array('status'=>'success','action'=>'add'));
			}else{
				$this->ajaxReturn(array('status'=>'error','action'=>'add'));
			}
		}
		
	}
	public function ajaxDownload(){
		if(isset($_SESSION['shijuan']['id'])){
			$this->ajaxReturn(array('status'=>'success'));
		}else{
			$this->ajaxSave();
		}
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
	public function deleteShijuan(){
		unset($_SESSION['shijuan']);
	}
	
}