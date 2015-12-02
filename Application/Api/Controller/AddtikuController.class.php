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
	{
		$this->dir_path = 'Public/tikupics/';
		$this->date = date('Ymd');
		$this->course_id = 3;//数学
		$this->cookies = 'jsessionid=DD4603F1AA07570C579CEBB3F0BE442B';
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
    public function addtiku(){
        $content = $_POST['content'];
		$answer = $_POST['post.answer'];
		$analysis = $_POST['post.analysis'];
		$data['type'] = 1;//题型，1表示单选题
		$data['course_id'] =$this->course_id;//科目，3表示数学
		$data['difficulty_id'] = mt_rand(1,5);//难度系数
		$data['feature_id'] = rand(1,3);//题目特点
		$data['source_id'] = 2;
		$data['update_time'] = time();
		$data['create_time'] = time();
		//$content = '①对任意两向量a,b,均有:|a|-|b|&lt;|a|+|b|;<br />②对任意两向量a,b,a-b与b-a是相反向量;<br />③在△ABC中,<img src="0e5e6fe9ab59cf7293c79efbad0f3321.png" style="vertical-align:middle;" />+<img src="060cfa9c7418c3496215e69518513a3b.png" style="vertical-align:middle;" />-<img src="03598a0fdc0bbfb772c817c289310e4a.png" style="vertical-align:middle;" />=0';
		$pattern = '/src="/i';
		$replacement = 'src="/Files/images/'.$this->date.'/';
		$data['content'] = preg_replace($pattern, $replacement, $content);
		$data['answer'] = preg_replace($pattern, $replacement, $answer);
		$data['analysis'] = preg_replace($pattern, $replacement, $analysis);
		
		$dir = 'Files/images/';
		if(!file_exists($dir.$this->date)){
			mkdir($dir.$this->date);
		}
		$Model = M('tiku');
		if($tiku_id = $Model->add($data)){
			$_model = M('tiku_to_point');
			$_data['tiku_id']=$tiku_id;
			$rand_arr = array(12,13,15,16,17,18);
			$rand_index = mt_rand(0,5);
			$_data['point_id'] = $rand_arr[$rand_index];
			if($_model->add($_data)){
				echo 'success';
			}
		}else{
			echo 'failed';
		}
	}
	
	public function addTikuByShijuan(){
		$data['course_id'] =$this->course_id;//科目，3表示数学
		$data['difficulty_id'] = mt_rand(1,5);//难度系数
		$data['feature_id'] = rand(1,3);//题目特点
		$data['update_time'] = time();
		$data['create_time'] = time();
		$grade = trim($_POST['grade']);
		$grade_arr = array('高一'=>'1','高二'=>'2','高三'=>'3');
		if(empty($grade)){
			$s = trim($_POST['source_name']);
			//$s = '2015届甘肃省天水市高三一轮复习基础知识检测理科数学试卷（带解析）';
			preg_match('/高\W{3}/i',$s,$mat);
			if($mat){
				$grade = $mat[0];
			}
		}
		$source_data['grade'] = $grade_arr[$grade];
		
		$content = $_POST['content'];
		
		$pattern = '/<div class="shiti">\w+<\/div>/i';
		$pattern_2 = '/<h3>/i';
		$result = preg_split($pattern_2, $content,0,PREG_SPLIT_NO_EMPTY);
		$pattern_3 = '/<div class="shiti">/i';
		//preg_match_all($pattern,$content,$matchs);
		//var_dump($result);exit;
		foreach($result as $val){
			//var_dump($val);
			preg_match('/\s*\S+<\/h3>/',$val,$matchs);
			$type_name = trim(str_replace('</h3>','',$matchs[0]));
			//echo $type_name;
			$val = preg_replace('/\s*\S+<\/h3>\s*/','',$val);
			$result_2 = preg_split($pattern_3,$val,0,PREG_SPLIT_NO_EMPTY);
			//var_dump($result_2);exit;
			foreach($result_2 as $v){
				//var_dump($v);exit;
				preg_match('/<p>[\w|\W]+<p class="btns">/i',$v,$m);
				$q = preg_replace('/（本小题满分\d+分）<br>/i', '', $m[0]);
				//echo $q;exit;
				$q = preg_replace('/\s*<p class="btns">/','',$q);
				preg_match_all('/src="\S+"/i',$q,$mt);
				$img_path = str_replace('src=','',$mt[0]);
				$img_path = str_replace('"','',$img_path);
				foreach($img_path as $vl){//下载文件
					$this->downFile($vl);
				}
				//最终的题目内容
				$q = preg_replace('/http:\/\/tiku.21cnjy.com\/tikupic\/\w{2}\/\w{2}/','/'.$this->dir_path.$this->date,$q);
				$v = str_replace(' class="paper_viewall"','',$v);
				$v = str_replace(' target=_blank','',$v);
				preg_match_all('/<a href=[\s|\S]+>答案与解析<\/a>/i',$v,$ms);
				//var_dump($ms);exit;
				$url_path = str_replace('<a href=\'','',$ms[0][0]);
				$url_path = str_replace('\'>答案与解析</a>','',$url_path);
				$url_path = 'http://tiku.21cnjy.com'.$url_path;
				
				$html = file_get_contents($url_path);
				//echo $url_path;exit;
				preg_match('/<p><span class="option">答案<\/span><i>[\w|\W]+<\/i><\/p>/U',$html,$a_match);
				//var_dump($a_match);exit;
				$a_match = str_replace('<p><span class="option">答案</span><i>','',$a_match[0]);
				//最终的答案
				$a_match = str_replace('</i></p>','',$a_match);
				preg_match_all('/src="\S+"/i',$a_match,$a_m);
				if($a_m){
					$a_match = preg_replace('/http:\/\/tiku.21cnjy.com\/tikupic\/\w{2}\/\w{2}/','/'.$this->dir_path.$this->date,$a_match);
					$img_path = str_replace('src=','',$a_m[0]);
					$img_path = str_replace('"','',$img_path);
					foreach($img_path as $vl){//下载文件
						$this->downFile($vl);
					}
				}
				preg_match('/<span class="parsing">解析<\/span><i>试题分析：[\w|\W]+<\/i><\/p>/U',$html,$as_match);
				//var_dump($a_match);exit;
				$as_match = str_replace('<span class="parsing">解析</span><i>试题分析：','',$as_match[0]);
				//最终的解析
				$analysis = str_replace('</i></p>','',$as_match);
				//echo $analysis;exit;
				preg_match_all('/src="\S+"/i',$analysis,$a_m);
				if($a_m){
					$analysis = preg_replace('/http:\/\/tiku.21cnjy.com\/tikupic\/\w{2}\/\w{2}/','/'.$this->dir_path.$this->date,$analysis);
					$img_path = str_replace('src=','',$a_m[0]);
					$img_path = str_replace('"','',$img_path);
					foreach($img_path as $vl){//下载文件
						$this->downFile($vl);
					}
				}
				//插入数据
				//$data['type'] = 1;//题型，1表示单选题
				$data['content'] = $q;
				$data['answer'] = $a_match;
				$data['analysis'] = $analysis;
				
				$source_name = trim($_POST['source_name']);
				$typeModel = M('tiku_type');
				if($type_name=='选择题'){$type_name='单选题';}
				$result = $typeModel->where("type_name='$type_name'")->find();
				if($result){
					$data['type_id'] = $result['id'];
				}else{
					$data['type_id'] = $typeModel->data(array('type_name'=>$type_name))->add();
					$courseTypeModel = M('course_to_type');
					$result_3 = $courseTypeModel->where("course_id=$this->course_id AND type_id=".$data['type_id'])->find();
					if(!$result_3){
						$courseTypeModel = $courseTypeModel->data(array('course_id'=>$this->course_id,'type_id'=>$data['type_id']))->add();
					}
				}
				
				$source_data['year'] = trim($_POST['year']);
				$source_data['source_name'] = trim($_POST['source_name']);
				$source_data['source_type_id'] = 0;
				$province = trim($_POST['province']);
				$provinceModel = M('province');
				$result = $provinceModel->where("province_name='$province'")->find();
				if($result){
					$source_data['province_id'] = $result['id'];
				}else{
					$source_data['province_id'] = $provinceModel->data(array('province_name'=>$province))->add();
				}
				$sourceModel = M('tiku_source');
				$result = $sourceModel->where("source_name='$source_name'")->find();
				if($result){
					$data['source_id'] = $result['id'];
				}else{
					$data['source_id'] = $sourceModel->add($source_data);
				}
				$Model = M('tiku');
				$tiku_id = $Model->add($data);
				$_model = M('tiku_to_point');
				$_data['tiku_id']=$tiku_id;
				$rand_arr = array(12,13,15,16,17,18);
				$rand_index = mt_rand(0,5);
				$_data['point_id'] = $rand_arr[$rand_index];
				$_model->add($_data);
				//echo $question;exit;
				//var_dump($q);exit;
			}
			
		}
		
		//var_dump($result);
	}
	public function test(){
		$Model = M('temp');
		for($i=1000;$i<10000;$i++){
			$Model->add(array('status'=>$i));
		}
		exit;
		$arr = preg_split('/<p[\s|\S]*>|<br \/>/U',$str);
		var_dump($arr);exit;
		$str = preg_replace('/\s{2,}/',' ',$str);
		echo $str;exit;
		preg_match('/\s/',$str,$match);
		//$count = preg_match_all('/(&nbsp;)/', $match[0],$m);
		//echo $count;
		var_dump($match);exit;
		for($i=0;$i<$count;$i++){
			$underline .= '_';
		}
		//echo $count;exit;
		$str = preg_replace('/[\'|"]mso-spacerun:yes[\'|"]>(&nbsp;){3,}[\s|\S]*<\/span>/','"mso-spacerun:yes">'.$underline.'</span>',$str);
		echo $str;exit;
		// preg_match_all('/<p[\s|\S]*<\/p>/U',$str,$matchs);
		// $count = count($matchs[0]);
		// if($count){
			// $i = $count-1;
			// while($i>0){
				// if(preg_match('/[ABCD](．|\.){1}/',strip_tags($matchs[0][$i]),$m) || preg_match('/（[ABCD]）/',strip_tags($matchs[0][$i]),$m)){
					// $str = str_replace($matchs[0][$i],'',$str);
				// }
				// $i--;
			// }
		// }
		// echo $str;exit;

		$str = strip_tags($str,'<img>');
		//$str= preg_replace('/\s*/','',$str);
		echo $str;exit;
		$str = preg_replace('/\n/','',$str);
		$result = preg_match('/A(．|\.){1}[^\n]+B(．|\.)/',$str,$match);

		if(!$result){ if(preg_match('/A(．|\.){1}[\s|\S]+\n/U',$str,$match)){}else{preg_match('/（A）[\s|\S]+（B）/U',$str,$match);$match[0] = preg_replace('/（A）|（B）/','',$match[0]);}}
		//var_dump($match);exit;
		$a = preg_replace('/A．|A\.|B．|B\.|&nbsp;/','',$match[0]);
		//echo $a;exit;
		$result = preg_match('/B(．|\.){1}[^\n]+C(．|\.)/',$str,$match);
		if(!$result){ if(preg_match('/B(．|\.){1}[\s|\S]+\n/U',$str,$match)){}else{preg_match('/（B）[\s|\S]+（C）/U',$str,$match);$match[0] = preg_replace('/（B）|（C）/','',$match[0]);}}
		//var_dump($match);exit;
		$b = preg_replace('/B．|B\.|C．|C\.|&nbsp;/i','',$match[0]);
		$result = preg_match('/C(．|\.){1}[^\n]+D(．|\.)/',$str,$match);
		if(!$result){ if(preg_match('/C(．|\.){1}[\s|\S]+\n/U',$str,$match)){}else{preg_match('/（C）[\s|\S]+（D）/U',$str,$match);$match[0] = preg_replace('/（C）|（D）/','',$match[0]);}}
		$c = preg_replace('/C．|C\.|D．|D\.|&nbsp;/i','',$match[0]);
		$result = preg_match('/&nbsp;\s*D(．|\.){1}[\s|\S]+/i',$str,$match);
		if(!$result){ if(preg_match('/D(．|\.){1}[\s|\S]+/i',$str,$match)){}else{preg_match('/（D）[\s|\S]+/',$str,$match);$match[0] = preg_replace('/（D）/','',$match[0]);}}
		$d = preg_replace('/D．|D\.|&nbsp;/i','',$match[0]);
		$option_arr = array($a,$b,$c,$d);
		var_dump($option_arr);exit;
		echo $d;exit;
		echo $str;exit;
		mkdir('pub/s');
		if(!file_exists($this->dir_path.$this->date)){
			mkdir($this->dir_path.$this->date.'/'.date('i'));
		}exit;
		//echo uniqid().time();exit;
		$s = "<p class=MsoNormal align=left style='text-align:left'><span style='font-size:
11.0pt;mso-bidi-font-size:10.5pt;font-family:宋体;color:black'>若集合<img width=13 height=15
src=\"http://i.jtyhjy.com/hstsnew/stsdoc/2/02/quehtml/20150523/body16128250.files/image004.gif\" v:shapes=\"Picture_x0020_78\"><span
lang=EN-US>A</span>＝<span lang=EN-US>{</span>－<span lang=EN-US>1</span>，<span
lang=EN-US>1}</span>，<span lang=EN-US>B</span>＝<span lang=EN-US>{0</span>，<span
lang=EN-US>2}</span>，则集合</span><span lang=EN-US style='font-size:11.0pt;
mso-bidi-font-size:12.0pt;font-family:宋体;color:black;position:relative;
top:8.0pt;mso-text-raise:-8.0pt'><img width=193 height=29
src=\"http://i.jtyhjy.com/hstsnew/stsdoc/2/02/quehtml/20150716/body16216131.files/image002.gif\" v:shapes=\"_x0000_i1025\"></span><span
style='font-size:11.0pt;mso-bidi-font-size:12.0pt;font-family:宋体;color:black'>中的元素的个数为（
）<span lang=EN-US><o:p></o:p></span></span></p>";
		preg_match_all('/src=[\'|"]{1}\S+[\'|"]{1}/i',$s,$a_m);
		//var_dump($a_m);exit;
		if($a_m){
			//$analysis = preg_replace('/http:\/\/tiku.21cnjy.com\/tikupic\/\w{2}\/\w{2}/','/'.$this->dir_path.$this->date,$analysis);
			// $img_path = str_replace('src=','',$a_m[0]);
			// $img_path = str_replace('"','',$img_path);
			$imgs = $a_m[0];
			$imgs = preg_replace('/src=[\'|"]{1}/','',$imgs);
			$imgs = preg_replace('/[\'|"]/','',$imgs);
			//var_dump($imgs);exit;
			foreach($imgs as $vl){//下载文件
				$new_file = $this->downFile($vl);
				$s = str_replace($vl,$new_file,$s);
			}
		}
		echo $s;

	}
	/**
	 * 采集题库
	 * 采集源：http://www.jtyhjy.com/sts/
	 */
	public function spider_tiku(){
		$pointModel = M('tiku_point');
		$tikuModel = M('tiku');
		$point_data = $pointModel->field("knowledgeId,id")->where("course_id=$this->course_id AND level=3")->select();
		//var_dump($point_data);exit;
		foreach($point_data as $pv){
			$queTypeIds = 13648;//采集源题型ID
			$point_id = $pv['knowledgeid'];
			$type_id = 3;//本地题型ID
			$is_xuanzheti = false;//如果是选择题，设置为true
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie:$this->cookies"));
			curl_setopt($ch, CURLOPT_URL, "http://www.jtyhjy.com/sts/question_findQuestionPage.action");
			curl_setopt($ch, CURLOPT_POSTFIELDS, array('difficults'=>'1,2,3,4,5','disciplineCode'=>'2','disciplineId'=>'21','disciplineType'=>'2','flag'=>'3','knowledgeIds'=>$point_id,'knowledgeLevel'=>'3','page'=>'1','queTypeIds'=>$queTypeIds,'rows'=>'10'));
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
				curl_setopt($ch, CURLOPT_POSTFIELDS, array('difficults'=>'1,2,3,4,5','disciplineCode'=>'2','disciplineId'=>'21','disciplineType'=>'2','flag'=>'3','knowledgeIds'=>$point_id,'knowledgeLevel'=>'3','page'=>$page,'queTypeIds'=>$queTypeIds,'rows'=>'10'));
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
					$source_name = trim($val['queSource']);
					$result = $sourceModel->where("source_name='$source_name'")->find();
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
					}else{
						
						preg_match('/mso-spacerun:yes[\'|"]>(&nbsp;){3,}[\s|\S]*<\/span>/U',$content,$match);
						$count = preg_match_all('/(&nbsp;)/', $match[0],$m);
						$underline = '';
						for($i=0;$i<$count;$i++){
							$underline .= '_';
						}
						//echo $count;exit;
						$content = preg_replace('/[\'|"]mso-spacerun:yes[\'|"]>(&nbsp;){3,}[\s|\S]*<\/span>/','"mso-spacerun:yes">'.$underline.'</span>',$content);
					}
					
					$content = strip_tags(trim($content),"<p><a><span><img>");
					$content= preg_replace('/<p[\s|\S]*>/U','',$content,1);
					$content = preg_replace('/<\/p>/','',$content,1);
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
		echo $page_num.' Pages,'.$total.' Totals Spider Success!Then Check The Chapter Id!';
		$this->checkChapter();
	}
	public function checkChapter(){
		$tikuModel = M('tiku');
		$matchingModel = M('matching');
		$max = $tikuModel->where("chapter_id=0")->order('id desc')->find();
		//echo $max['id'];exit;
		for($i=1;$i<=$max['id'];$i++){
			$result = $tikuModel->where("id=$i")->find();
			$_result = $matchingModel->where("spider_code=".$result['spider_code'])->find();
			//echo $matchingModel->getLastSql();exit;
			if($_result){
				$data['chapter_id'] = $_result['chapter_id'];
				$tikuModel->data($data)->where("id=".$result['id'])->save();
				//echo $tikuModel->getLastSql();exit;
			}
			
		}
		echo 'Check Chapter Success!';
	}
	/**
	 * 采集知识点
	 * 采集源：http://www.jtyhjy.com/sts/
	 */
	public function spider_point(){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie:$this->cookies"));
		curl_setopt($ch, CURLOPT_URL, "http://www.jtyhjy.com/sts/initPage_initQuestionPageForKnowledge.action");
		curl_setopt($ch, CURLOPT_POSTFIELDS, array('disciplineCode'=>'2','disciplineId'=>'21','disciplineType'=>'2'));
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
			$result = $Model->where("point_name='".$p_point['point_name']."' AND course_id=$this->course_id")->find();
			if(!$result){
				$point_id = $Model->add($p_point);
			}else{
				$point_id = $result['id'];
			}
			foreach($val['knowledgeList'] as $v){
				$c_point['diKnowledgeId'] = $v['diKnowledgeId'];
				$c_point['knowledgeId'] = $v['knowledgeId'];
				$c_point['level'] = $v['level'];
				$c_point['point_name'] = $v['name'];
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
	 */
	public function spider_children_point(){
		$Model = M('tiku_point');
		$result = $Model->where("level=2 AND course_id=$this->course_id")->select();
		if($result){
			foreach($result as $val){
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie:$this->cookies"));
				curl_setopt($ch, CURLOPT_URL, "http://www.jtyhjy.com/sts/knowledge_findKnowledgeByParentId.action");
				curl_setopt($ch, CURLOPT_POSTFIELDS, array('disciplineCode'=>'2','disciplineId'=>'21','parentId'=>$val['knowledgeid']));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$data = curl_exec($ch);
				$data = json_decode($data,true);
				curl_close($ch);
				$point_arr = $data['data'];
				foreach($point_arr as $v){
				$c_point['knowledgeId'] = $v['knowledgeId'];
				$c_point['level'] = $v['level'];
				$c_point['point_name'] = $v['name'];
				$c_point['parent_id'] = $val['id'];
				$c_point['course_id'] = $this->course_id;
				$_result = $Model->where("point_name='".$c_point['point_name']."' AND parent_id=".$val['id'])->find();
				
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
		//var_dump($chapter_data);exit;
		foreach($chapter_data as $v){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie:$this->cookies"));
			curl_setopt($ch, CURLOPT_URL, "http://www.jtyhjy.com/sts/question_findQuestionPage.action");
			curl_setopt($ch, CURLOPT_POSTFIELDS, array('difficults'=>'1,2,3,4,5','disciplineCode'=>'2','disciplineId'=>'21','disciplineType'=>'2','flag'=>'2','paragradphIds'=>$v['spider_id'],'page'=>1,'queTypeIds'=>'13646,13647,13648','rows'=>'10'));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$data = curl_exec($ch);
			$data = json_decode($data,true);
			$total = $data['data']['questionList']['total'];
			$page_num = ceil($total/10);
			$page=1;
			while($page<=$page_num){
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie:$this->cookies"));
				curl_setopt($ch, CURLOPT_URL, "http://www.jtyhjy.com/sts/question_findQuestionPage.action");
				curl_setopt($ch, CURLOPT_POSTFIELDS, array('difficults'=>'1,2,3,4,5','disciplineCode'=>'2','disciplineId'=>'21','disciplineType'=>'2','flag'=>'2','paragradphIds'=>$v['spider_id'],'page'=>$page,'queTypeIds'=>'13646,13647,13648','rows'=>'10'));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$data = curl_exec($ch);
				$data = json_decode($data,true);
				$tikus = $data['data']['questionList']['rows'];
				foreach($tikus  as $val){
					$result = $matchingModel->where("spider_code=".$val['questionId'])->find();
					if(!$result){
						$matching_data['spider_code'] = $val['questionId'];
						$matching_data['chapter_id'] = $v['id'];
						$matchingModel->data($matching_data)->add();
					}
				}
				$page++;
			}
		}
		echo 'Spider Sucess!';
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
			curl_setopt($ch, CURLOPT_POSTFIELDS, array('disciplineCode'=>'2','disciplineId'=>'21','disciplineType'=>'2','versionId'=>$ver['spider_id']));
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
				curl_setopt($ch, CURLOPT_POSTFIELDS, array('disciplineCode'=>'2','disciplineId'=>'21','disciplineType'=>'2','bookId'=>$book_spider_id));
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
}