<?php
namespace Api\Controller;
use Think\Controller;
class ZukespiderController extends Controller {
	var $dir_path;
	var $date;
	var $course_id;
	var $cookies;
	/**
	 * 初始化
	 */
	function _initialize()
	{   //'disciplineCode'=>'2','disciplineId'=>'21','disciplineType'=>'2','flag'=>'3'
		$this->dir_path = 'Public/casepics/';
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
	 * 采集专题、知识点
	 */
	public function spiderTopic(){
		$roundModle = M('course_round');
		$roundData = $roundModle->select();
		foreach($roundData as $rv){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "http://zuke.zujuan.com/Aspx/Synthesis.ashx?action=GetTreeCategories&parentid=".$rv['spider_cateids']);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$data = curl_exec($ch);
			curl_close($ch);
			$data = strip_tags($data,'<li>');
			preg_match_all('/<li[\s|\S]*id=\'(\d+)\'>[\s|\S]+<\/li>/U',$data,$matchs);
			//var_dump($matchs);exit;
			$topicModel = M('zuke_topic');
			$round_id = $rv['id'];
			foreach($matchs[0] as $key => $val){
				$topic['title'] = htmlspecialchars(strip_tags($val),ENT_QUOTES);
				$topic['parent_id'] = 0;
				$topic['round_id'] = $round_id;
				$topic['update_time'] = time();
				$topic['level'] = 1;
				$topic['spider_id'] = $matchs[1][$key];
				if($tp = $topicModel->where("spider_id=".$topic['spider_id'])->find()){
					$result = $tp['id'];
				}else{
					$result = $topicModel->add($topic);
				}
				//检查是否有二级分类
				if($result){
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, "http://zuke.zujuan.com/Aspx/Synthesis.ashx?action=GetTreeCategories&parentid=".$matchs[1][$key]);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$data2 = curl_exec($ch);
					curl_close($ch);
					$data2 = strip_tags($data2,'<li>');
					if(!empty($data2)){
						preg_match_all('/<li[\s|\S]*id=\'(\d+)\'>[\s|\S]+<\/li>/U',$data2,$matchs2);
						foreach($matchs2[0] as $k => $v){
							$topic2['title'] = htmlspecialchars(strip_tags($v),ENT_QUOTES);
							$topic2['parent_id'] = $result;
							$topic2['round_id'] = $round_id;
							$topic2['update_time'] = time();
							$topic2['level'] = 2;
							$topic2['spider_id'] = $matchs2[1][$k];
							if($tp = $topicModel->where("spider_id=".$topic2['spider_id'])->find()){
								$result2 = $tp['id'];
							}else{
								$result2 = $topicModel->add($topic2);
							}
							
							$topicModel->where("id=$result")->save(array('has_child'=>1));
							//检查是否有三级分类
							if($result2){
								$ch = curl_init();
								curl_setopt($ch, CURLOPT_URL, "http://zuke.zujuan.com/Aspx/Synthesis.ashx?action=GetTreeCategories&parentid=".$matchs2[1][$k]);
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
								$data3 = curl_exec($ch);
								curl_close($ch);
								$data3 = strip_tags($data3,'<li>');
								if(!empty($data3)){
									preg_match_all('/<li[\s|\S]*id=\'(\d+)\'>[\s|\S]+<\/li>/U',$data3,$matchs3);
									foreach($matchs3[0] as $k3 => $v3){
										$topic3['title'] = htmlspecialchars(strip_tags($v3),ENT_QUOTES);
										$topic3['parent_id'] = $result2;
										$topic3['round_id'] = $round_id;
										$topic3['level'] = 3;
										$topic3['update_time'] = time();
										$topic3['spider_id'] = $matchs3[1][$k3];
										if($tp = $topicModel->where("spider_id=".$topic3['spider_id'])->find()){
											$result3 = $tp['id'];
										}else{
											$result3 = $topicModel->add($topic3);
										}
										$topicModel->where("id=$result2")->save(array('has_child'=>1));
									}
								}
							}
						}
					}
				}
			}
		}
		echo 'Success!';
	}
	/**
	 * 采集资源
	 */
	public function spiderResource(){
		$roundModel = M('course_round');
		$resourceModel = M('resource');
		$rtrModel = M('round_to_resource');
		$roundData = $roundModel->select();
		foreach($roundData as $v){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "http://zuke.zujuan.com/Aspx/Synthesis.ashx?action=GetAssetHtml&cateids=".$v['spider_cateids']."&bankid=".$v['spider_bankid']);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$data = curl_exec($ch);
			curl_close($ch);
			preg_match_all('/<a href=\'javascript:void\(0\);\' class=\'caseBtn\' assetid=\'(\d+)\'>[\s|\S]+<\/a>/U',$data,$matchs);
			//var_dump($matchs);exit;
			foreach($matchs[0] as $key=>$val){
				$title = strip_tags($val);
				$result = $resourceModel->where("title='".$title."'")->find();
				if(!$result){
					$rsData['title'] = $title;
					$rsData['update_time'] = time();
					$rsData['spider_id'] = $matchs[1][$key];
					$rsId = $resourceModel->add($rsData);
					if($rsId){
						if(!$rtrModel->where("round_id=".$v['id']." AND resource_id=".$rsId)->find()){
							$rtrModel->add(array('round_id'=>$v['id'],'resource_id'=>$rsId));
						}
					}
				}else{
					if(!$rtrModel->where("round_id=".$v['id']." AND resource_id=".$result['id'])->find()){
						$rtrModel->add(array('round_id'=>$v['id'],'resource_id'=>$result['id']));
					}
				}
			}
		}
		echo 'OK!';
	}
	/*
	 * 采集学案
	 */
	public function spiderCase(){
		$this->spiderResource();
		$this->spiderTopic();

		$croundModel = M('course_round');
		$resourceModel = M('resource');
		$topicModel = M('zuke_topic');
		$caseModel = M('zuke_case');
		
		$roundData = $croundModel->select();
		foreach($roundData as $val){
			$resourceData = $resourceModel->join("round_to_resource on round_to_resource.resource_id=resource.id")
			->where("round_to_resource.round_id=".$val['id'])->select();
			foreach($resourceData as $v){
				$topicData = $topicModel->where("round_id=".$val['id']." AND has_child=0")->select();
				//var_dump($topicData);exit;
				foreach($topicData as $vv){
					$url = "http://zuke.zujuan.com/Aspx/Synthesis.ashx?action=GetCasePageData&bankid=".$val['spider_bankid']."&cateStrs=".$vv['spider_id']."&assetId=".$v['spider_id']."&curpage=1&pagesize=200";
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$data = curl_exec($ch);
					curl_close($ch);
					//echo $url;
					$data = json_decode($data,true);
					//var_dump($data);exit;
					foreach($data['caseList'] as $vc){
						$caseData['topic_id'] = $vv['id'];
						//$caseData['content'] = htmlspecialchars(str_replace('【题文】','',$vc['LearningCase']));
						$content = $vc['LearningCase'];
						preg_match_all('/src=[\'|"]\S+[\'|"]/U',$content,$a_m);
						if($a_m[0]){
							$imgs = $a_m[0];
							$imgs = preg_replace('/src=[\'|"]{1}/','',$imgs);
							$imgs = preg_replace('/[\'|"]/','',$imgs);
							//$imgs = preg_replace('/Upload\//','http://static.gz.zujuan.com/',$imgs);
							//var_dump($imgs);exit;
							foreach($imgs as $vl){//下载文件
								$new_file = $this->downFile($vl);
								$content = str_replace($vl,$new_file,$content);
							}
						}
						$caseData['content'] = htmlspecialchars(str_replace('【题文】','',$content),ENT_QUOTES);
						$caseData['update_time'] = time();
						$caseData['resource_id'] = $v['id'];
						$caseData['round_id'] = $val['id'];
						if(!$caseModel->where("topic_id=".$vv['id']." AND content='".$caseData['content']."'")->find()){
							$caseModel->add($caseData);
						}
						
					}
				}
			}
		}
		echo 'OK!';
	}
	/**
	 * 采集试题
	 */
	public function spiderShiti(){
		$this->spiderCase();
		$typeArr = array(1=>'高考真题',2=>'试题精粹');
		$difficultyArr = array(1=>'容易',2=>'一般',3=>'较难');
		$yearArr = array(2016,2015,2014,2013,-1);
		$roundModel = M('course_round');
		$topicModel = M('zuke_topic');
		$tixingModel = M('round_to_tixing');
		$shitiModel = M('zuke_shiti');
		$roundData = $roundModel->select();
		foreach($roundData as $rv){
			$topicData = $topicModel->where("has_child=0 AND round_id=".$rv['id'])->select();
			foreach($topicData as $tv){
				foreach($typeArr as $tpk=>$tpv){
					foreach($difficultyArr as $dk=>$dv){
						foreach($yearArr as $yv){
							$tixingData = $tixingModel->where("round_id=".$rv['id'])->select();
							foreach($tixingData as $txv){
								$url = "http://zuke.zujuan.com/Aspx/Synthesis.ashx?action=GetQuesPageData&bankid=".$rv['spider_bankid']."&cateStrs=".$tv['spider_id']."&quesyear=".$yv."&quesType=".$txv['tixing_id']."&quesDiff=".$dk."&curpage=1&pagesize=200&quesDesType=".$tpk;
								//exit($url);
								$ch = curl_init();
								curl_setopt($ch, CURLOPT_URL, $url);
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
								$data = curl_exec($ch);
								curl_close($ch);
								$data = json_decode($data,true);
								//var_dump($data);exit;
								if($data['dataCount']==0) continue;
								foreach($data['quesList'] as $vc){
									$caseData['topic_id'] = $tv['id'];
									//$caseData['content'] = htmlspecialchars(str_replace('【题文】','',$vc['LearningCase']));
									$content = $vc['QuesBody'];
									preg_match_all('/src=[\'|"]\S+[\'|"]/U',$content,$a_m);
									if($a_m[0]){
										$imgs = $a_m[0];
										$imgs = preg_replace('/src=[\'|"]{1}/','',$imgs);
										$imgs = preg_replace('/[\'|"]/','',$imgs);
										//$imgs = preg_replace('/Upload\//','http://static.gz.zujuan.com/',$imgs);
										//var_dump($imgs);exit;
										foreach($imgs as $vl){//下载文件
											$new_file = $this->downFile($vl);
											$content = str_replace($vl,$new_file,$content);
										}
									}
									$answer = $vc['QuesAnswer'];
									preg_match_all('/src=[\'|"]\S+[\'|"]/U',$answer,$a_m);
									if($a_m[0]){
										$imgs = $a_m[0];
										$imgs = preg_replace('/src=[\'|"]{1}/','',$imgs);
										$imgs = preg_replace('/[\'|"]/','',$imgs);
										//$imgs = preg_replace('/Upload\//','http://static.gz.zujuan.com/',$imgs);
										//var_dump($imgs);exit;
										foreach($imgs as $vl){//下载文件
											$new_file = $this->downFile($vl);
											$answer = str_replace($vl,$new_file,$answer);
										}
									}
									$analysis = $vc['QuesParse'];
									preg_match_all('/src=[\'|"]\S+[\'|"]/U',$analysis,$a_m);
									if($a_m[0]){
										$imgs = $a_m[0];
										$imgs = preg_replace('/src=[\'|"]{1}/','',$imgs);
										$imgs = preg_replace('/[\'|"]/','',$imgs);
										//$imgs = preg_replace('/Upload\//','http://static.gz.zujuan.com/',$imgs);
										//var_dump($imgs);exit;
										foreach($imgs as $vl){//下载文件
											$new_file = $this->downFile($vl);
											$analysis = str_replace($vl,$new_file,$analysis);
										}
									}
									$caseData['answer'] = htmlspecialchars(str_replace('【答案】<br />','',$answer),ENT_QUOTES);
									$caseData['analysis'] = htmlspecialchars(str_replace('【解析】<br />','',$analysis),ENT_QUOTES);
									$caseData['content'] = htmlspecialchars(str_replace('【题文】','',$content),ENT_QUOTES);
									$caseData['update_time'] = time();
									$caseData['tixing_id'] = $txv['tixing_id'];
									$caseData['difficulty'] = $dk;
									$caseData['type'] = $tpk;
									$caseData['year'] = $yv;
									$caseData['round_id'] = $rv['id'];
									$caseData['spider_id'] = $vc['ID'];
									if(!$shitiModel->where("spider_id=".$vc['ID'])->find()){
										$shitiModel->add($caseData);
									}
									
								}
							}
							
						}
					}
				}
			}
		}
		echo 'OK!';
	}
	public function downFile($file_path)
	{
		$file_path = str_replace('Upload/','http://static.gz.zujuan.com/',$file_path);
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
		//curl_setopt($ch, CURLOPT_REFERER, 'i.jtyhjy.com');
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
?>