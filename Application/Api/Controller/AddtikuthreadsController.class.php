<?php
namespace Api\Controller;
use Think\Controller;
class AddtikuthreadsController extends Threads {
	public function spider_tiku(){
		//这里创建线程池.
		$pool[] = new threads('a');
		$pool[] = new threads('b');
		$pool[] = new threads('c');
		$pool[] = new threads('d');
		$pool[] = new threads('e');
		//启动所有线程,使其处于工作状态
		foreach ($pool as $w) {
		    $w->start();
			//$w->running = false;
		}
		
		//派发任务给线程
		for ($i = 1; $i < 6; $i++) {
		    $worker_content = rand(10, 99);
		    while (true) {
		        foreach ($pool as $worker) {
		            //参数为空则说明线程空闲
		            if ($worker->param=='') {
		                $worker->param = $i;
		                echo "[{$worker->name}] Thread free,input param {$worker_content},last param [{$worker->lurl}] result [{$worker->res}].\n";
		                break 2;
		            }
		        }
		        sleep(3);
		    }
		}
		echo "waitting excute complete\n";
		
		//等待所有线程运行结束
		while (count($pool)) {
		    //遍历检查线程组运行结束
		    foreach ($pool as $key => $threads) {
		        if ($worker->param=='') {
		            // echo "[{$threads->name}]线程空闲,上次参数[{$threads->lurl}]结果[{$threads->res}].\n";
		            // echo "[{$threads->name}]线程运行完成,退出.\n";设置结束标志
		            $threads->runing = false;
		            unset($pool[$key]);
		        }
		    }
		    echo "waiting...\n";
		    sleep(1);
		}	
	}
}
namespace Api\Controller;
class Threads extends \Thread {
	var $difficulty;
	var $name;
	var $param;
	var $status;
	var $dir_path;
	var $date;
	var $course_id;
	var $cookies;
	var $running;
	var $arg = '';
	public function __construct($name){
		$this->param    = 0;
		$this->runing = true;
		$this->difficulty = 0;
		$this->name = $name;
		$this->status = 0;
		$this->dir_path = 'Public/tikupics/';
		$this->date = date('Ymd');
		$this->course_id = 3;//数学
		$this->cookies = 'jsessionid=9C2363E70DEE26A9B1D5AF249BFCED78';
	}
	public function run(){
		while ($this->runing) {
			//echo '{'.$this->name.'}';
            if ($this->param != 0) {
                //$nt          = rand(1, 10);
                //echo "Thead [{$this->name}]get the param::{$this->param},need 1 second.\n";
                $this->param   = 0;
                $Model = M('temp');
                
            } else {
                echo "Thread [{$this->name}] waitting..\n";
            }
            sleep(1);
        }

	}
}

?>