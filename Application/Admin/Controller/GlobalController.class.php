<?php
namespace Admin\Controller;
use Think\Controller;
class GlobalController extends Controller{
	/**
	 * 初始化
	 *
	*/
	function _initialize()
	{
		$this->checkLogin();
		$this->checkAuth();
	}
	public function checkLogin(){
		$not_auth = array('verifycode','login','logout');
		if(!in_array(strtolower(ACTION_NAME),$not_auth)){
			if(!isset($_SESSION['admin_id'])){
				redirect('/index.php/admin/admin/login');
			}else{
				$Model = M('admin');
				$Model = M('admin');
				$data = $Model
				->field("admin.*,admin_group.title")
				->join('admin_group_access on admin_group_access.uid=admin.id')
				->join('admin_group on admin_group_access.group_id=admin_group.id')
				->where("admin.id=".$_SESSION['admin_id'])->find();
				$this->assign('admin_info',$data);
			}
		}
	}
	public function checkAuth(){
		$not_auth = array('admin/verifycode','admin/login','admin/logout','index/index','index/welcome');
		if(!in_array(strtolower(CONTROLLER_NAME).'/'.strtolower(ACTION_NAME),$not_auth)){
			$auth = new \Think\Auth();
			if(!$auth->check(ucwords(MODULE_NAME).'/'.ucwords(CONTROLLER_NAME).'/'.ucwords(ACTION_NAME),$_SESSION['admin_id'])){
		     	die('无操作权限');
		    }
		}
	}
	/**
	 * 获取所有课程
	 */
	public function getCourse(){
		$Course = M('Tiku_course');
		$data = $Course->where('status=1')->select();
		return $data;
	}
	/**
     * 数据列表
     *
     * @param $conditions 条件
     * @param $orders 排序
     * @param $listRows 每页显示数量
     * @param $joind 是否表关联
     * @param $table 关联表
     * @param $join 
     * @param $fields 取字段
     */
    public function getList($conditions = '', $orders = '' , $listRows = '')
    {
        $condition = !empty($conditions) ? $conditions : '' ;
        $pageCount = $this->dao->where($condition)->count();
        $listRows = empty($listRows) ? 15 : $listRows;
        $orderd = empty($orders) ? 'id DESC' : $orders;
        $paged = new page($pageCount, $listRows);
        $dataContentList = $this->dao->Where($condition)->Order($orderd)->Limit($paged->firstRow.','.$paged->listRows)->select();
        $pageContentBar = $paged->show();
        $this->assign('dataContentList', $dataContentList);
        $this->assign('pageContentBar', $pageContentBar);
        $this->display();
    }

    /**
     * 数据列表,表关联
     *
     * @param $conditions 条件
     * @param $orders 排序
     * @param $listRows 每页显示数量
     * @param $joind 是否表关联
     * @param $table 关联表
     * @param $join 
     * @param $fields 取字段
     */
    public function getJoinList($conditions = '', $orders = '' , $listRows = '', $table = '', $join = '', $fields = '')
    {
        $condition = !empty($conditions) ? $conditions : '' ;
        $pageCount = $this->dao->Where($condition)->Table($table)->Join($join)->Field($fields)->count();
        $listRows = empty($listRows) ? 15 : $listRows;
        $orderd = empty($orders) ? 'id DESC' : $orders;
        $paged = new page($pageCount, $listRows);
        $dataContentList = $this->dao->Table($table)->join($join)->field($fields)->Where($condition)->Order($orderd)->Limit($paged->firstRow.','.$paged->listRows)->select();
        $pageContentBar = $paged->show();
        $this->assign('dataContentList', $dataContentList);
        $this->assign('pageContentBar', $pageContentBar);
        $this->display();
    }

    /**
     * 数据集
     *
     * @param $conditions 条件
	 *
     */
    public function getDetail($conditions = '', $viewCount = false)
    {
        empty($conditions) && self::_message('errorUri', '查询条件丢失', U('Index/index'));
        $contentDetail = $this->dao->Where($conditions)->find();
        empty($contentDetail) && self::_message('errorUri', '记录不存在', U('Index/index'));
		//更新查看次数
		$viewCount && $this->dao->setInc($viewCount, $conditions);
        $this->assign('contentDetail', $contentDetail);
        $this->display($contentDetail['template']);
    }

    /**
     * 数据集,表关联
     * 此处查询条件可能为数组
     * @param $conditions 条件
     * @param $joind 是否表关联
     * @param $table 关联表
     * @param $join 
     * @param $fields 取字段
     */
    public function getJoinDetail($conditions = '', $viewCount = false, $table = '', $join = '', $fields = '')
    {
        empty($conditions) && self::_message('errorUri', '查询条件丢失', U('Index/index'));
		
		$condition1 = is_array($conditions) ? $conditions[0] : $conditions;
		$condition2 = is_array($conditions) ? $conditions[1] : $conditions;

        $contentDetail = $this->dao->Table($table)->Join($join)->Field($fields)->Where($condition1)->find();
        empty($contentDetail) && self::_message('errorUri', '记录不存在', U('Index/index'));
		//更新查看次数
		$viewCount && $this->dao->setInc($viewCount, $condition2);
        $this->assign('contentDetail', $contentDetail);
        $this->display($contentDetail['template']);
    }
	
    /**
     * 验证码
     *
     */
    public function verify()
    {
        import('ORG.Util.Image');
        Image::buildImageVerify();
    }

    /**
     * 输出信息
     *
     * @param $type
     * @param $content
     * @param $jumpUrl
     * @param $time
     * @param $ajax
     */
    protected function _message($type = 'success', $content = '更新成功', $jumpUrl = __URL__, $time = 3, $ajax = false)
    {
        $jumpUrl = empty($jumpUrl) ? __URL__ : $jumpUrl ;
		$this->assign('type',$type);
		$this->assign('head_title','跳转提示');
        switch ($type){
            case 'success':
                $this->assign('jumpUrl', $jumpUrl);
                $this->assign('waitSecond', $time);
                $this->success($content, $ajax);
                break;
            case 'error':
                $this->assign('jumpUrl', 'javascript:history.back(-1);');
                $this->assign('waitSecond', $time);
                $this->assign('message', $content);
                $this->error($content, $ajax);
                break;
            case 'errorUri':
                $this->assign('jumpUrl', $jumpUrl);
                $this->assign('waitSecond', $time);
                $this->assign('message', $content);
                $this->error($content, $ajax);
                break;
            default:
                die('error type');
                break;
        }
		exit;
    }
	/**
	 * 获取省份
	 */
	public function getProvince(){
		if($data = cache('province_data')){
				return $data;
		}else{
			$provinceModel = M('Province');
			$result = $provinceModel->where('prov_status=1')->order("prov_id ASC")->select();
			cache('province_data',$result,C('DATA_CACHE_TIME'));
			return $result;
		}
	}
	/**
	 * 根据省份id获取城市列表
	 */
	public function getCity(){
		$prov_id = $this->_post('prov_id');
		$cityModel = M('City');
		$result = $cityModel->where('province='.$prov_id)->select();
		return $result;
	}
	/**
	 * 文件上传
	 */
	public function upload($path){
		import("ORG.Net.UploadFile");
		$upload = new UploadFile();
		$upload->maxSize = C('UPLOAD_FILE_MAX');
		$upload->allowExts = C('UPLOAD_FILE_EXT');
		$upload->savePath = $path;
		
		if($upload->upload()){//成功返回数组
			return $upload->getUploadFileInfo();
		}else{//失败返回字符串
			return $upload->getErrorMsg();
		}
	}
	// public function ajaxReturn($status){
		// $data['status'] = $status;
		// $this->ajaxReturn($data);
	// }
	/**
	 * 设置、获取数据缓存
	 */
	 public function dataCache($name,$value='',$time=0){
	 	if($data = cache($name)){
	 		return $data;
	 	}else{
	 		cache($name,$value,$time);
			return cache($name);
	 	}
	 }
	 public function findChild(&$data, $parent_id = 0) {
        $rootList = array();
        foreach ($data as $key => $val) {
            if ($val['parent_id'] == $parent_id) {
                $rootList[]   = $val;
                unset($data[$key]);
            }
        }
        return $rootList;
    }

    public function getTree(&$data, $parent_id = 0) {
        $Model = M('tiku_point');
        $childs = $this->findChild($data, $parent_id);
		
        if (empty($childs)) {
            return null;
        }
        foreach ($childs as $key => $val) {
        	$result = $Model->where("parent_id=".$val['id'])->find();
            if ($result) {
                $treeList = $this->getTree($data, $val['id']);
                if ($treeList !== null) {
                    $childs[$key]['childs'] = $treeList;
                }
            }
        }

        return $childs;
    }
}
?>