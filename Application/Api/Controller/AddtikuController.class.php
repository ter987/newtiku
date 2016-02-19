<?php
namespace Api\Controller;
use Think\Controller;
class AddtikuController extends Controller {
	var $dir_path;
	var $date;
	var $course_id;
	var $cookies;
	/**
	 * 初始化
	 */
	function _initialize()
	{   //'disciplineCode'=>'2','disciplineId'=>'21','disciplineType'=>'2','flag'=>'3'
		$this->dir_path = 'Public/tikupics/';
		$this->date = date('Ymd');
		$this->course_id = 8;//数学3   物理1  化学2 历史4 语文5 生物6 地理7 英语8
		$this->source_name_default = '高中英语（未知）';
		$this->cookies = 'jsessionid=DFAF12C0C277CFA4FEF001EE3550B42A';
		$this->disciplineCode = 03;//物理4  数学2 化学5  历史8 语文1 生物6 地理9 英语03
		$this->disciplineId = 22;//物理23  数学21  化学24  历史27 语文20 生物25 地理28  英语22
		$this->disciplineType =2;
		//历史'13652,13653'  语文 '13635,1232453,13640,13641,13636,13642,3933440,2400602,13639,13637'
		//生物 '13629,2400600,2400601'   地理  '13654,13656'   英语  '18170,13611,18174,13616,13613,13614,18176,18171,13617'
		//物理 '13618,11112810,13622,13623,13621,11112811'  化学  '13625,13626,16300,13628' 数学 '13646,13647,13648'
		$this->queTypeIds = '18170,13611,18174,13616,13613,13614,18176,18171,13617';
		$this->flag = 3;
		$this->rows = 200;
		
	}
	/*
	 * 过滤解析字段非法字符
	 */ 
	public function filterAnalysisChar(){
		$tikuModel = M('tiku');
		$max = $tikuModel->field("MAX(id) as id")->find();
		//echo $max['id'];exit;
		for($i=1;$i<=$max['id'];$i++){
			$result = $tikuModel->where("id=$i")->find();
			if($result){
				$analysis = preg_replace('/．/','.',$result['analysis']);
				$tikuModel->where("id=$i")->save(array('analysis'=>$analysis));
			}
		}
		echo 'Filter Success!';
	}
	/*
	 * 过滤答案中的非法字符
	 */ 
	public function filterAnswerChar(){
		$tikuModel = M('tiku');
		$max = $tikuModel->field("MAX(id) as id")->find();
		//echo $max['id'];exit;
		for($i=1;$i<=$max['id'];$i++){
			$result = $tikuModel->where("id=$i AND type_id=1")->find();
			if($result){
				preg_match_all('/A|B|C|D/', $result['answer'],$match);
				if(!empty($match[0])){
					if(count($match[0]==1)){
						$tikuModel->where("id=$i")->save(array('answer'=>$match[0][0]));
					}
				}
			}
		}
		echo 'Filter Answer Success!';
	}
	function flash(){
		ob_start();
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_REFERER, "http://gdyja.shxbe.com");
		curl_setopt($ch, CURLOPT_URL, "http://gdyja.shxbe.com/resroot/topicres/GD000000073/GD000000001/GD000000011/GD000000226/GD000001664/course01/media/course/t01/1/index.swf");
		curl_setopt($ch,  CURLOPT_RETURNTRANSFER, true);
		$data = curl_exec($ch);
		curl_close($ch);
		$handle = fopen('in.swf', 'w+');
		fwrite($handle, $data);
	}
	public function pipei_tiku(){
		$Model = M('tiku');
		for($id=250000;$id<=974901;$id++){//974901
			//$id = 258910;
			$data = array();
			$result = $Model->field('content_old,type_id,answer,analysis')->where("id=$id AND status=0")->find();
			if(!$result) continue;
			$analysis = $result['analysis'];
			$answer = $result['answer'];
			$type_id = $result['type_id'];
			//var_dump($result);
			//$content = strip_tags($result['content_old'],'remove','<div>');
			$content = preg_replace('/<div[\s|\S]*>/U','',$result['content_old']);
			$content = preg_replace('/<\/div>/U','',$content);
			$content = preg_replace('/<!--[\s|\S]+-->/U','',$content);
			$content = preg_replace('/\s{2,}/',' ',$content);
			//var_dump($content);
			$content = preg_replace('/_{4,}/','<span style="text-decoration: underline;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>',$content);
			if(strpos(substr($content,0,10),'<p')!==false){
				$content= preg_replace('/<p[\s|\S]*>/U','',$content,1);
				$content = preg_replace('/<\/p>/','',$content,1);
			}
			//echo $content;exit;
			if($type_id==1){
				$a=$b=$c=$d='';
				$spider_error = 0;
				$option_arr = array();
				$str = strip_tags($content,'<img><sup><sub><strong><em><p>');
				//$str= preg_replace('/\s*/','',$str);
				//echo $content;exit;
				$str = preg_replace('/\n/','',$str);
				$result = preg_match('/A(．|\.|、){1}[^\n]+B(．|\.|、)/',$str,$match);
		
				if(!$result){ if(preg_match('/A(．|\.|、){1}[\s|\S]+\n/U',$str,$match)){}else{preg_match('/（A）[\s|\S]+（B）/U',$str,$match);$match[0] = preg_replace('/（A）|（B）/','',$match[0]);}}
				//var_dump($match);exit;
				
				$a = trim(preg_replace('/A．|A\.|A、|B．|B\.|B、|&nbsp;/','',$match[0]));
				$a = strip_tags($a,'<img><sup><sub><strong><em>');
				//echo $a;exit;
				$result = preg_match('/B(．|\.|、){1}[^\n]+C(．|\.|、)/',$str,$match);
				if(!$result){ if(preg_match('/B(．|\.|、){1}[\s|\S]+\n/U',$str,$match)){}else{preg_match('/（B）[\s|\S]+（C）/U',$str,$match);$match[0] = preg_replace('/（B）|（C）/','',$match[0]);}}
				//var_dump($match);exit;
				
				$b = trim(preg_replace('/B．|B\.|B、|C．|C\.|C、|&nbsp;/','',$match[0]));
				$b = strip_tags($b,'<img><sup><sub><strong><em>');
				$result = preg_match('/C(．|\.|、){1}[^\n]+D(．|\.|、)/',$str,$match);
				if(!$result){ if(preg_match('/C(．|\.|、){1}[\s|\S]+\n/U',$str,$match)){}else{preg_match('/（C）[\s|\S]+（D）/U',$str,$match);$match[0] = preg_replace('/（C）|（D）/','',$match[0]);}}
				
				$c = trim(preg_replace('/C．|C\.|C、|D．|D\.|D、|&nbsp;/','',$match[0]));
				$c = strip_tags($c,'<img><sup><sub><strong><em>');
				$result = preg_match('/(&nbsp;)*\s*D(．|\.|、){1}[\s|\S]+(<\/p>){0,1}/',$str,$match);
				if(!$result){ if(preg_match('/D(．|\.|、){1}[\s|\S]+(<\/p>){0,1}/',$str,$match)){}else{preg_match('/（D）[\s|\S]+(<\/p>){0,1}/',$str,$match);$match[0] = preg_replace('/（D）/','',$match[0]);}}
				//exit($str);
				//var_dump($match);exit;
				$d = trim(preg_replace('/D．|D\.|D、|&nbsp;/i','',$match[0]));
				$d = strip_tags($d,'<img><sup><sub><strong><em>');
				$option_arr = array(0=>$a,1=>$b,2=>$c,3=>$d);
				if(!empty($a) || !empty($b) || !empty($c) || !empty($d)){
					$options = json_encode($option_arr);
				}
				
				if(empty($a) || empty($b) || empty($c) || empty($d)){
				 	$spider_error = 1;
				}
				
				preg_match_all('/<p[\s|\S]*<\/p>/U',$content,$matchs);
				$count = count($matchs[0]);
				
				if($count){
					$i = 0;
					while($i<$count){
						if(preg_match('/[ABCD](．|\.|、){1}/',strip_tags($matchs[0][$i]),$m) || preg_match('/（[ABCD]）/',strip_tags($matchs[0][$i]),$m)){
							$content = str_replace($matchs[0][$i],'',$content);
						}else{
							$spider_error = 0;
						}
						$i++;
					}
				}else{
					$spider_error = 1;
				}
				preg_match_all('/<p class=MsoNormal[\s|\S]*<\/p>/U',$content,$matchs);
				foreach($matchs[0] as $key=>$val){
					$result = preg_replace('/(&nbsp;)| /','',strip_tags($val,'<img>'));
					if($result ==''){
						$content = str_replace($matchs[0][$key],'',$content);
					}
				}
			}
			//过滤答案
			if($type_id==1){
				$answer = preg_replace('/(&nbsp;)|(&amp;)|(nbsp;)|(&lt;p&gt;)|(&lt;\/p&gt;)|\s{2,}| /','',$answer);
			}else{
				$answer = htmlspecialchars_decode($answer);
				$answer = preg_replace('/<div[\s|\S]*>/U','',$answer);
				$answer = preg_replace('/<\/div>/U','',$answer);
				$answer = preg_replace('/\s{2,}/',' ',$answer);
				if(strpos(substr($answer,0,10),'<p')!==false){
					$answer= preg_replace('/<p[\s|\S]*>/U','',$answer,1);
					$answer = preg_replace('/<\/p>/','',$answer,1);
				}
				$answer = htmlspecialchars($answer);
			}
			//过滤解析
			$analysis = htmlspecialchars_decode($analysis);
			
			$analysis = preg_replace('/<div[\s|\S]*>/U','',$analysis);
			$analysis = preg_replace('/<\/div>/U','',$analysis);
			
			$analysis = preg_replace('/\s{2,}/',' ',$analysis);
			if(strpos(substr($analysis,0,10),'<p')!==false){
				$analysis= preg_replace('/<p[\s|\S]*>/U','',$analysis,1);
				$analysis = preg_replace('/<\/p>/','',$analysis,1);
			}
			
			$analysis = htmlspecialchars(trim($analysis));
			$content = htmlspecialchars(trim($content));
			
			$answer = trim($answer);
			$data['content'] = $content;
			$data['answer'] = $answer;
			$data['analysis'] = $analysis;
			$data['spider_error'] = $spider_error;
			$data['options'] = $options;
			$Model->where("id=$id")->save($data);
			unset($data);
			unset($result);
			usleep(1000);
		}
		echo 'Success';
	}
	/**
	 * 采集题库
	 * 采集源：http://www.jtyhjy.com/sts/
	 * $queTypeIds  $type_id   $is_xuanzheti  这三个变量每次采集时要正确设置
	 */
	public function spider_tiku(){
		$pointModel = M('tiku_point');
		$tikuModel = M('tiku');
		$point_data = $pointModel->field("knowledgeId,id")->where("course_id=$this->course_id  AND level=3")->select();
		//var_dump($point_data);exit;
		foreach($point_data as $pv){
			$queTypeIds = 13613;//采集源题型ID
			$point_id = $pv['knowledgeid'];
			$type_id = 27;//本地题型ID
			$is_xuanzheti = false;//如果是选择题，设置为true
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie:$this->cookies"));
			curl_setopt($ch, CURLOPT_URL, "http://www.jtyhjy.com/sts/question_findQuestionPage.action");
			curl_setopt($ch, CURLOPT_POSTFIELDS, array('difficults'=>'1,2,3,4,5','disciplineCode'=>$this->disciplineCode,'disciplineId'=>$this->disciplineId,'disciplineType'=>$this->disciplineType,'flag'=>$this->flag,'knowledgeIds'=>$point_id,'knowledgeLevel'=>'3','page'=>'1','queTypeIds'=>$queTypeIds,'rows'=>'10'));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$data = curl_exec($ch);
			curl_close($ch);
			$data = json_decode($data,true);
			$total = $data['data']['questionList']['total'];
			$page_num = ceil($total/10);
			//var_dump($data);exit;
			$sourceModel = M('tiku_source');
			$provinceModel = M('province');
			$page = 1;
			while($page<=$page_num){
				$tikus = array();
				$tiku = array();
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie:$this->cookies"));
				curl_setopt($ch, CURLOPT_URL, "http://www.jtyhjy.com/sts/question_findQuestionPage.action");
				curl_setopt($ch, CURLOPT_POSTFIELDS, array('difficults'=>'1,2,3,4,5','disciplineCode'=>$this->disciplineCode,'disciplineId'=>$this->disciplineId,'disciplineType'=>$this->disciplineType,'flag'=>$this->flag,'knowledgeIds'=>$point_id,'knowledgeLevel'=>'3','page'=>$page,'queTypeIds'=>$queTypeIds,'rows'=>'10'));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
				$data = curl_exec($ch);
				curl_close($ch);
				$data = json_decode($data,true);
				$tikus = $data['data']['questionList']['rows'];
				foreach($tikus as $val){
					$tiku['type_id'] = $type_id;
					$spider_error = false;
					$tiku['difficulty_id'] = $val['difficult'];
					$source_name = empty($val['queSource'])?$this->source_name_default:trim($val['queSource']);
					$result = $sourceModel->where("source_name='$source_name' AND course_id=$this->course_id")->find();
					if(!$result){
						$pattern_1 = "/河北|山西|辽宁|吉林|黑龙江|江苏|浙江|安徽|福建|江西|山东|河南|湖北|湖南|广东|海南|四川|贵州|云南|陕西|甘肃|青海|北京|天津|上海|重庆|广西|内蒙古|西藏|宁夏|新疆|新课标全国卷|新课标全国卷Ⅰ|新课标全国卷Ⅱ|大纲全国卷|大纲全国卷II/";
						$pattern_2 = "/\d{4}/";
						$pattern_3 = "/高一|高二|高三/";
						$pattern_4 = "/文科|理科/";
						$pattern_5 = "/期中|期末|月考|模拟|单元|高考真题/";
						$province_id = 32;
						preg_match($pattern_1, $source_name,$m_1);
						if($m_1){
							$province_name = $m_1[0];
							$result_2 = $provinceModel->where("province_name='$province_name'")->find();
							if(!$result_2){
								$province_id = $provinceModel->data(array('province_name'=>$province_name))->add();
							}else{
								$province_id = $result_2['id'];
							}
						}
						$source_data['province_id'] = $province_id;
						preg_match($pattern_2, $source_name,$m_2);
						if($m_2){
							$source_data['year'] = $m_2[0];
						}
						$grade = 0;
						preg_match($pattern_3, $source_name,$m_3);
						if($m_3){
							if($m_3[0]=='高一') $grade=1;
							if($m_3[0]=='高二') $grade=2;
							if($m_3[0]=='高三') $grade=3;
						}
						$source_data['grade'] = $grade;
						$wen_li = 0;
						if($this->course_id==3){
							preg_match($pattern_4, $source_name,$m_4);
							if($m_4){
								
								if($m_4[0]=='理科') $wen_li=1;
								if($m_4[0]=='文科') $wen_li=2;
							}
						}
						$source_data['wen_li'] = $wen_li;
						//试卷类型，1表示高考真题，2表示名校模拟，3表示月考试卷，4表示期中试卷，5表示期末试卷,6表示单元测试（默认）
						$source_type_id = 6;
						preg_match($pattern_5, $source_name,$m_5);
						if($m_5){
							if($m_5[0]=='期中') $source_type_id=4;
							if($m_5[0]=='期末') $source_type_id=5;
							if($m_5[0]=='月考') $source_type_id=3;
							if($m_5[0]=='模拟') $source_type_id=2;
							if($m_5[0]=='高考真题') $source_type_id=1;
							if($m_5[0]=='单元') $source_type_id=6;
						}
						$source_data['source_type_id'] = $source_type_id;
						$source_data['source_name'] = $source_name;
						$source_data['course_id'] = $this->course_id;
						$source_data['update_time'] = time();
						$source_id = $sourceModel->data($source_data)->add();
						$tiku['source_id'] = $source_id;
					}else{
						$tiku['source_id'] = $result['id'];
					}
					//判断试题是否已经存在
					$rs = $tikuModel->where("spider_code=".$val['questionId'])->find();
					if($rs) continue;
					//过滤答案
					$answer = $val['answerHtmlText'];
					preg_match_all('/src=["|\'][\s|\S]+["|\']/U',$answer,$a_m);
					//var_dump($a_m);exit;
					if($a_m){
						$imgs = $a_m[0];
						$imgs = preg_replace('/src=[\'|"]{1}/','',$imgs);
						$imgs = preg_replace('/[\'|"]/','',$imgs);
						//var_dump($imgs);exit;
						foreach($imgs as $vl){//下载文件
							$new_file = $this->downFile($vl);
							$answer = str_replace($vl,$new_file,$answer);
						}
					}
					if($is_xuanzheti){
						$answer = trim(strip_tags($answer));
					}
					//$answer = $val['answerHtmlText'];
					//过滤题目
					$content = $val['bodyHtmlText'];
					preg_match_all('/src="\S+"/i',$content,$a_m);
					if($a_m){
						$imgs = $a_m[0];
						$imgs = preg_replace('/src=[\'|"]{1}/','',$imgs);
						$imgs = preg_replace('/[\'|"]/','',$imgs);
						//var_dump($imgs);exit;
						foreach($imgs as $vl){//下载文件
							$new_file = $this->downFile($vl);
							$content = str_replace($vl,$new_file,$content);
						}
					}
					$tiku['content_old'] = $content;
					$content = preg_replace('/<!--[\s|\S]+-->/U','',$content);
					$content = preg_replace('/\s{2,}/',' ',$content);
					if($is_xuanzheti){//如果是选择题，从题目中过滤出选项
						$a=$b=$c=$d='';
						$option_arr = array();
						$str = strip_tags($content,'<img>');
						//$str= preg_replace('/\s*/','',$str);
						//echo $str;exit;
						$str = preg_replace('/\n/','',$str);
						$result = preg_match('/A(．|\.){1}[^\n]+B(．|\.)/',$str,$match);
				
						if(!$result){ if(preg_match('/A(．|\.){1}[\s|\S]+\n/U',$str,$match)){}else{preg_match('/（A）[\s|\S]+（B）/U',$str,$match);$match[0] = preg_replace('/（A）|（B）/','',$match[0]);}}
						//var_dump($match);exit;
						$a = trim(preg_replace('/A．|A\.|B．|B\.|&nbsp;/','',$match[0]));
						//echo $a;exit;
						$result = preg_match('/B(．|\.){1}[^\n]+C(．|\.)/',$str,$match);
						if(!$result){ if(preg_match('/B(．|\.){1}[\s|\S]+\n/U',$str,$match)){}else{preg_match('/（B）[\s|\S]+（C）/U',$str,$match);$match[0] = preg_replace('/（B）|（C）/','',$match[0]);}}
						//var_dump($match);exit;
						$b = trim(preg_replace('/B．|B\.|C．|C\.|&nbsp;/i','',$match[0]));
						$result = preg_match('/C(．|\.){1}[^\n]+D(．|\.)/',$str,$match);
						if(!$result){ if(preg_match('/C(．|\.){1}[\s|\S]+\n/U',$str,$match)){}else{preg_match('/（C）[\s|\S]+（D）/U',$str,$match);$match[0] = preg_replace('/（C）|（D）/','',$match[0]);}}
						$c = trim(preg_replace('/C．|C\.|D．|D\.|&nbsp;/i','',$match[0]));
						$result = preg_match('/&nbsp;\s*D(．|\.){1}[\s|\S]+/i',$str,$match);
						if(!$result){ if(preg_match('/D(．|\.){1}[\s|\S]+/i',$str,$match)){}else{preg_match('/（D）[\s|\S]+/',$str,$match);$match[0] = preg_replace('/（D）/','',$match[0]);}}
						$d = trim(preg_replace('/D．|D\.|&nbsp;/i','',$match[0]));
						$option_arr = array(0=>$a,1=>$b,2=>$c,3=>$d);
						if(!empty($a) || !empty($b) || !empty($c) || !empty($d)){
							$tiku['options'] = json_encode($option_arr);
						}
						
						if(empty($a) || empty($b) || empty($c) || empty($d)){
						 $spider_error = true;
						}
						preg_match_all('/<p[\s|\S]*<\/p>/U',$content,$matchs);
						$count = count($matchs[0]);
						if($count){
							$i = $count-1;
							while($i>0){
								if(preg_match('/[ABCD](．|\.){1}/',strip_tags($matchs[0][$i]),$m) || preg_match('/（[ABCD]）/',strip_tags($matchs[0][$i]),$m)){
									$content = str_replace($matchs[0][$i],'',$content);
								}else{
									$spider_error = true;
								}
								$i--;
							}
						}else{
							$spider_error = true;
						}
					}
					// else{
// 						
						// preg_match('/mso-spacerun:yes[\'|"]>(&nbsp;){3,}[\s|\S]*<\/span>/U',$content,$match);
						// $count = preg_match_all('/(&nbsp;)/', $match[0],$m);
						// $underline = '';
						// for($i=0;$i<$count;$i++){
							// $underline .= '_';
						// }
						// //echo $count;exit;
						// $content = preg_replace('/[\'|"]mso-spacerun:yes[\'|"]>(&nbsp;){3,}[\s|\S]*<\/span>/','"mso-spacerun:yes">'.$underline.'</span>',$content);
					// }
					
					//$content = strip_tags(trim($content),"<p><a><span><img><table><tr><td><th><li><o:p>");
					$content= preg_replace('/<p[\s|\S]*>/U','',$content,1);
					$content = preg_replace('/<\/p>/','',$content,1);
					$content = trim($content);
					//过滤解析
					$analysis = trim($val['analysisHtmlText']);
					preg_match_all('/src=["|\'][\s|\S]+["|\']/U',$analysis,$a_m);
					if($a_m){
						$imgs = $a_m[0];
						$imgs = preg_replace('/src=[\'|"]{1}/','',$imgs);
						$imgs = preg_replace('/[\'|"]/','',$imgs);
						//var_dump($imgs);exit;
						foreach($imgs as $vl){//下载文件
							$new_file = $this->downFile($vl);
							$analysis = str_replace($vl,$new_file,$analysis);
						}
					}
					
					$tiku['spider_code'] = $val['questionId'];
					$tiku['answer'] = trim(htmlspecialchars($answer));
					$tiku['content'] = htmlspecialchars($content);
					$tiku['analysis'] = htmlspecialchars($analysis);
					$tiku['update_time'] = time();
					$tiku['create_time'] = time();
					$tiku['spider_error'] = $spider_error;

					$tiku_id = $tikuModel->add($tiku);
					$_Model = M('tiku_to_point');
					$_Model->data(array('tiku_id'=>$tiku_id,'point_id'=>$pv['id']))->add();
					unset($tiku);
				}
				unset($tikus);
				unset($content);
				unset($imgs);
				unset($match);
				$page++;
			}
		}
		unset($point_data);
		echo 'Spider Success!';
		//$this->checkChapter();
	}
	public function checkChapter(){
		$tikuModel = M('tiku');
		$matchingModel = M('matching');
		$tikutochapterModel = M("tiku_to_chapter");
		$max = $tikuModel->field("MAX(id) as id")->find();
		//echo $max['id'];exit;
		for($i=$max['id'];$i>0;$i--){
			$result = $tikuModel->where("id=$i")->find();
			if($result){
				$_result = $matchingModel->where("spider_code=".$result['spider_code'])->select();
				//echo $matchingModel->getLastSql();exit;
				foreach($_result as $val){
					if(!$matchingModel->table("tiku_to_chapter")->where("tiku_id=".$result['id']." AND chapter_id=".$val['chapter_id'])->find()){
						//echo $matchingModel->getLastSql();exit;
						$data['chapter_id'] = $val['chapter_id'];
						$data['tiku_id'] = $result['id'];
						$tikutochapterModel->add($data);
					}
				}
			}
		}
		echo 'Check Chapter Success!';
	}
	/**
	 * 采集知识点
	 * 采集源：http://www.jtyhjy.com/sts/
	 * 数学 ：'disciplineCode'=>'2','disciplineId'=>'21','disciplineType'=>'2'
	 * 物理：'disciplineCode'=>'4','disciplineId'=>'23','disciplineType'=>'2'
	 */
	public function spider_point(){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie:$this->cookies"));
		curl_setopt($ch, CURLOPT_URL, "http://www.jtyhjy.com/sts/initPage_initQuestionPageForKnowledge.action");
		curl_setopt($ch, CURLOPT_POSTFIELDS, array('disciplineCode'=>$this->disciplineCode,'disciplineId'=>$this->disciplineId,'disciplineType'=>$this->disciplineType));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$data = curl_exec($ch);
		$data = json_decode($data,true);
		$potin_arr = $data['data']['knowledgeList'];
		//var_dump($potin_arr);exit;
		$Model = M('tiku_point');
		foreach($potin_arr as $val){
			$p_point['diKnowledgeId'] = trim($val['diKnowledgeId']);
			$p_point['knowledgeId'] = trim($val['knowledgeId']);
			$p_point['level'] = trim($val['level']);
			$p_point['point_name'] = trim($val['name']);
			$p_point['parent_id'] = 0;
			$p_point['course_id'] = $this->course_id;
			$result = $Model->where("point_name='".$p_point['point_name']."' AND course_id=$this->course_id AND knowledgeId=".$p_point['knowledgeId'])->find();
			if(!$result){
				$point_id = $Model->add($p_point);
			}else{
				$point_id = $result['id'];
			}
			foreach($val['knowledgeList'] as $v){
				$c_point['diKnowledgeId'] = $v['diKnowledgeId'];
				$c_point['knowledgeId'] = $v['knowledgeId'];
				$c_point['level'] = $v['level'];
				$c_point['point_name'] = htmlspecialchars($v['name']);
				$c_point['parent_id'] = $point_id;
				$c_point['course_id'] = $this->course_id;
				$_result = $Model->where("point_name='".$c_point['point_name']."' AND parent_id=$point_id")->find();
				if(!$_result){
					$_point_id = $Model->add($c_point);
				}
				
			}
		}
		
		echo 'Spider Sucess!';
	}
	/**
	 * 根据二级节点获取三级节点
	 * 数学： 'disciplineCode'=>'2','disciplineId'=>'21'
	 * 物理 ： 'disciplineCode'=>'04','disciplineId'=>'23'
	 */
	public function spider_children_point(){
		$Model = M('tiku_point');
		$result = $Model->where("level=2 AND course_id=$this->course_id")->select();
		if($result){
			foreach($result as $val){
				//var_dump($val);exit;
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie:$this->cookies"));
				curl_setopt($ch, CURLOPT_URL, "http://www.jtyhjy.com/sts/knowledge_findKnowledgeByParentId.action");
				curl_setopt($ch, CURLOPT_POSTFIELDS, array('disciplineCode'=>$this->disciplineCode,'disciplineId'=>$this->disciplineId,'parentId'=>$val['knowledgeid']));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$data = curl_exec($ch);
				$data = json_decode($data,true);
				//var_dump($val['knowledgeid']);exit;
				curl_close($ch);
				$point_arr = $data['data'];
				foreach($point_arr as $v){
				$c_point['knowledgeId'] = $v['knowledgeId'];
				$c_point['level'] = $v['level'];
				$c_point['point_name'] = htmlspecialchars($v['name']);
				$c_point['parent_id'] = $val['id'];
				$c_point['course_id'] = $this->course_id;
				$_result = $Model->where('point_name="'.$c_point['point_name'].'" AND parent_id='.$val['id'])->find();
				//var_dump($_result);exit;
				if(!$_result){
					$point_id = $Model->add($c_point);
					//echo $Model->getLastSql();exit;
				}
				
			}
			}
		}
		echo 'Spider Success!';
	}
	/**
	 * 根据章节id采集题目编号
	 */
	public function spider_code(){
		$chapterModel = M('chapter');
		$matchingModel = M('matching');
		$chapter_data = $chapterModel->field("chapter.*")->join("books ON chapter.`book_id`=books.`id`")->join("version ON version.`id`=books.`version_id`")->where("version.`course_id`=$this->course_id AND chapter.`parent_id`<>0")->select();
		//echo $chapterModel->getLastSql();exit;
		//var_dump($chapter_data);exit;
		foreach($chapter_data as $v){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie:$this->cookies"));
			curl_setopt($ch, CURLOPT_URL, "http://www.jtyhjy.com/sts/question_findQuestionPage.action");
			curl_setopt($ch, CURLOPT_POSTFIELDS, array('difficults'=>'1,2,3,4,5','disciplineCode'=>$this->disciplineCode,'disciplineId'=>$this->disciplineId,'disciplineType'=>$this->disciplineType,'flag'=>$this->flag,'paragradphIds'=>$v['spider_id'],'page'=>1,'queTypeIds'=>$this->queTypeIds,'rows'=>'10'));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$data = curl_exec($ch);
			$data = json_decode($data,true);
			$total = $data['data']['questionList']['total'];
			$page_num = ceil($total/$this->rows);
			$page=1;
			while($page<=$page_num){
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie:$this->cookies"));
				curl_setopt($ch, CURLOPT_URL, "http://www.jtyhjy.com/sts/question_findQuestionPage.action");
				curl_setopt($ch, CURLOPT_POSTFIELDS, array('difficults'=>'1,2,3,4,5','disciplineCode'=>$this->disciplineCode,'disciplineId'=>$this->disciplineId,'disciplineType'=>$this->disciplineType,'flag'=>$this->flag,'paragradphIds'=>$v['spider_id'],'page'=>$page,'queTypeIds'=>$this->queTypeIds,'rows'=>$this->rows));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$data = curl_exec($ch);
				$data = json_decode($data,true);
				$tikus = $data['data']['questionList']['rows'];
				foreach($tikus  as $val){
					$result = $matchingModel->where("spider_code=".$val['questionId']." AND chapter_id=".$v['id'])->find();
					if(!$result){
						$matching_data['spider_code'] = $val['questionId'];
						$matching_data['chapter_id'] = $v['id'];
						$matchingModel->data($matching_data)->add();
					}
				}
				$page++;
				unset($data);
				unset($tikus);
			}
		}
		echo 'Spider Sucess!';
		//$this->checkChapter();
	}
	
	/**
	 * 采集章节
	 * 采集源：http://www.jtyhjy.com/sts/
	 */
	public function spider_chapter(){
		$versionModel = M('version');
		$version_data = $versionModel->where("course_id=".$this->course_id)->select();
		foreach($version_data as $ver){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie:$this->cookies"));
			curl_setopt($ch, CURLOPT_URL, "http://www.jtyhjy.com/sts/version_changeVersionForChapter.action");
			curl_setopt($ch, CURLOPT_POSTFIELDS, array('disciplineCode'=>$this->disciplineCode,'disciplineId'=>$this->disciplineId,'disciplineType'=>$this->disciplineType,'versionId'=>$ver['spider_id']));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$data = curl_exec($ch);
			$data = json_decode($data,true);
			$book_arr = $data['data']['bookList'];
			//var_dump($book_arr);exit;
			$bookModel = M('books');
			$chapterModel = M('chapter');
			foreach($book_arr as $val){
				$book_data['book_name'] = trim($val['bookName']);
				$book_data['spider_id'] = trim($val['bookId']);
				$book_data['version_id'] = $ver['id'];
				$result = $bookModel->where("spider_id=".$val['bookId'])->find();
				if(!$result){
					$book_id = $bookModel->add($book_data);
					$book_spider_id = $val['bookId'];
				}else{
					$book_id = $result['book_id'];
					$book_spider_id = $result['spider_id'];
				}
				//通过书本ID获取章节
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie:$this->cookies"));
				curl_setopt($ch, CURLOPT_URL, "http://www.jtyhjy.com/sts/book_changeBookForChapter.action");
				curl_setopt($ch, CURLOPT_POSTFIELDS, array('disciplineCode'=>$this->disciplineCode,'disciplineId'=>$this->disciplineId,'disciplineType'=>$this->disciplineType,'bookId'=>$book_spider_id));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$data = curl_exec($ch);
				$data = json_decode($data,true);
				$chapter_arr = $data['data']['chapterList'];
				$chapter_data = array();
				foreach($chapter_arr as $v){
					$chapter_data['chapter_name'] = $v['chapterName'];
					$chapter_data['parent_id'] = 0;
					$chapter_data['spider_id'] = $v['chapterId'];
					$chapter_data['book_id'] = $book_id;
					$_result = $chapterModel->where("spider_id=".$v['chapterId'])->find();
					if(!$_result){
						$chapter_id = $chapterModel->add($chapter_data);
					}else{
						$chapter_id = $_result['id'];
					}
					if(!empty($v['paragraphVoList'])){
						foreach($v['paragraphVoList'] as $vv){
							$_chapter_data['chapter_name'] = $vv['paragraphName'];
							$_chapter_data['parent_id'] = $chapter_id;
							$_chapter_data['spider_id'] = $vv['paragraphId'];
							$_chapter_data['book_id'] = $book_id;
							$_result_2 = $chapterModel->where("spider_id=".$vv['paragraphId'])->find();
							if(!$_result_2){
								$_chapter_id = $chapterModel->add($_chapter_data);
							}else{
								$_chapter_id = $_result_2['id'];
							}
						}
					}
				}
			}
		}
		echo 'Spider Sucess!';
	}
	public function downFile($file_path)
	{	
		if(!file_exists($this->dir_path.$this->date)){
			mkdir($this->dir_path.$this->date);
		}
		if(!file_exists($this->dir_path.$this->date.'/'.date('H'))){
			mkdir($this->dir_path.$this->date.'/'.date('H'));
		}
		if(!file_exists($this->dir_path.$this->date.'/'.date('H').'/'.date('i'))){
			mkdir($this->dir_path.$this->date.'/'.date('H').'/'.date('i'));
		}
		preg_match('/jpg|gif|png|bpm/i',$file_path,$matchs);
		$suffix = '.'.$matchs[0];
		$filename = uniqid().time().$suffix;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_REFERER, 'i.jtyhjy.com');
		curl_setopt($ch, CURLOPT_URL, $file_path);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$stream = curl_exec($ch);
		$new_file = $this->dir_path.$this->date.'/'.date('H').'/'.date('i').'/'.$filename;
		$handle = @fopen($new_file, 'w');
		fwrite($handle, $stream);
		curl_close($ch);
		fclose($handle);
		return '/'.$new_file;
	}
	/**
	 * 重新采集sourse_id
	 */
	public function pipei_source(){
		$pointModel = M('tiku_point');
		$tikuModel = M('tiku');
		$point_data = $pointModel->field("knowledgeId,id")->where("course_id=$this->course_id AND level=3")->select();
		//var_dump($point_data);exit;
		foreach($point_data as $pv){
			$point_id = $pv['knowledgeid'];
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie:$this->cookies"));
			curl_setopt($ch, CURLOPT_URL, "http://www.jtyhjy.com/sts/question_findQuestionPage.action");
			curl_setopt($ch, CURLOPT_POSTFIELDS, array('difficults'=>'1,2,3,4,5','disciplineCode'=>$this->disciplineCode,'disciplineId'=>$this->disciplineId,'disciplineType'=>$this->disciplineType,'flag'=>$this->flag,'knowledgeIds'=>$point_id,'knowledgeLevel'=>'3','page'=>'1','queTypeIds'=>$this->queTypeIds,'rows'=>'10'));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$data = curl_exec($ch);
			curl_close($ch);
			$data = json_decode($data,true);
			$total = $data['data']['questionList']['total'];
			$page_num = ceil($total/10);
			//var_dump($data);exit;
			$sourceModel = M('tiku_source');
			//$provinceModel = M('province');
			$page = $page_num;
			while($page>0){
				$tikus = array();
				$tiku = array();
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie:$this->cookies"));
				curl_setopt($ch, CURLOPT_URL, "http://www.jtyhjy.com/sts/question_findQuestionPage.action");
				curl_setopt($ch, CURLOPT_POSTFIELDS, array('difficults'=>'1,2,3,4,5','disciplineCode'=>$this->disciplineCode,'disciplineId'=>$this->disciplineId,'disciplineType'=>$this->disciplineType,'flag'=>$this->flag,'knowledgeIds'=>$point_id,'knowledgeLevel'=>'3','page'=>$page,'queTypeIds'=>$this->queTypeIds,'rows'=>'10'));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
				$data = curl_exec($ch);
				curl_close($ch);
				$data = json_decode($data,true);
				$tikus = $data['data']['questionList']['rows'];
				foreach($tikus as $val){
					$tr = $tikuModel->field("id,source_id")->where("spider_code=".$val['questionId'])->find();
					if($tr['source_id']==90){
						if(!isset($source_id)){
							$sr = $sourceModel->where("source_name='".$this->source_name_default."' AND course_id=$this->course_id")->find();
							if($sr){
								$source_id = $sr['id'];
								//$tiku['source_id'] = $sr['id'];
							}else{
								$source_data['course_id'] = $this->course_id;
								$source_data['source_name'] = $this->source_name_default;
								$source_data['update_time'] = time();
								$source_id = $sourceModel->add($source_data);
								//$tiku['source_id']  = $source_id;
							}
						}
						$tiku['source_id'] = $source_id;
						$tikuModel->where("id=".$tr['id'])->save($tiku);
					}

					unset($tiku);
				}
				
				$page--;
			}
		}
		unset($point_data);
		echo ' OK!';
	}
}