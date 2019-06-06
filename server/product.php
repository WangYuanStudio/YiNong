<?php
/**
 * 产品操作
 *
 * @author 		YowFung
 * @copyright 	网园资讯工作室
 * @license		http://www.wangyuan.info
 * @version 	2017.7.17.1.0
 */


/**************>全局设置及参数<****************/
//设置报头
header('Access-Control-Allow-Headers: accept');
header('Access-Control-Allow-Origin: http://localhost:8080');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Credentials: true');
header('Content-type: application/json; charset=utf-8');
date_default_timezone_set("Asia/Shanghai");			//设置时区
session_start();									//开启SESSION
define('_ID_', 0);									//定义常量
define('_NAME_', 1);
include './config.php';								//包含数据库配置文件
include './safety.php';								//包含数据库防注入转义文件
$GLOBALS['sqlconf'] = $sqlconf；
$GLOBALS['safety'] = new Safety();
$request = file_get_contents('php://input');		//获取请求内容
$request = json_decode($request, true);				//JSON转数组
$GLOBALS['REQUEST'] = $request;						//存入全局变量


/**************>相关函数及方法<****************/
/**
 * 判断产品是否已存在
 * @param 	int 	$type 			判断类型，_ID_或_NAME_，默认为_NAME_
 * @param 	string 	$value 			产品名称
 * @return 	int 					不存在返回0，存在返回1，数据库查询出错返回2
 */
function ProductIsExist($type, $value){
	//连接数据库
	$sqlconf = $GLOBALS['sqlconf'];
	$sqlcon = new mysqli($sqlconf['host'], $sqlconf['user'], $sqlconf['pwd'], $sqlconf['db']);
	if($sqlcon->connect_errno)
		return 2;

	$value = $GLOBALS['safety']->ConvertTextIn(htmlspecialchars($value));	//防注入转义
	if($type == _ID_)
		$sqlstr = "SELECT * FROM product WHERE proid = '".$value."'";
	else
		$sqlstr = "SELECT * FROM product WHERE proname = '".$value."'";

	$result = $sqlcon->query($sqlstr);
	if(!$result)
		return 2;
	else if(count(mysqli_fetch_assoc($result)) == 0)
		return 0;
	else
		return 1;
}

/**
 * 判断分类是否已存在
 * @param 	int 	$type 			判断类型，_ID_或_NAME_，默认为_NAME_
 * @param 	string 	$value 			分类名称或分类ID
 * @return 	int 					不存在返回0，存在返回1，数据库查询出错返回2
 */
function SortIsExist($type, $value){
	//连接数据库
	$sqlconf = $GLOBALS['sqlconf'];
	$sqlcon = new mysqli($sqlconf['host'], $sqlconf['user'], $sqlconf['pwd'], $sqlconf['db']);
	if($sqlcon->connect_errno)
		return 2;

	$value = $GLOBALS['safety']->ConvertTextIn(htmlspecialchars($value));	//防注入转义
	if($type == _ID_)
		$sqlstr = "SELECT * FROM sort WHERE sortid = '".$value."'";
	else
		$sqlstr = "SELECT * FROM sort WHERE sortname = '".$value."'";

	$result = $sqlcon->query($sqlstr);
	if(!$result)
		return 2;
	else if(count(mysqli_fetch_array($result)) == 0)
		return 0;
	else
		return 1;
}

/**
 * 添加新产品
 * @param 	string 	$name 			产品名称
 * @param 	string  $description 	产品描述
 * @param 	string 	$sortid 		分类ID
 * @param 	string 	$picurl 		产品图片地址
 * @return 	int 					添加成功返回产品ID，失败返回0
 */
function AddProduct($name, $description = '', $sortid, $picurl){
	//连接数据库
	$sqlconf = $GLOBALS['sqlconf'];
	$sqlcon = new mysqli($sqlconf['host'], $sqlconf['user'], $sqlconf['pwd'], $sqlconf['db']);
	if($sqlcon->connect_errno)
		return 0;

	$dt = date('Y-m-d H:i:s');
	$sqlstr = "INSERT INTO product(proname, description, sortid, createtime, picurl) VALUES('".$name."','".$description."','".$sortid."','".$dt."','".$picurl."')";
	if($sqlcon->query($sqlstr)){
		$sqlstr = "SELECT * FROM product WHERE proname = '".$name."'";
		$result = $sqlcon->query($sqlstr);
		if(!$result)
			return 0;
		else{
			$result = mysqli_fetch_assoc($result);
			return $result['proid'];
		}
	}
	else
		return 0;
}

/**
 * 添加新分类
 * @param 	string 	$name 			分类名称
 * @return 	int 					添加成功返回分类ID，失败返回0
 */
function AddSort($name){
	//连接数据库
	$sqlconf = $GLOBALS['sqlconf'];
	$sqlcon = new mysqli($sqlconf['host'], $sqlconf['user'], $sqlconf['pwd'], $sqlconf['db']);
	if($sqlcon->connect_errno)
		return 0;

	$name = $GLOBALS['safety']->ConvertTextIn(htmlspecialchars($name));
	$sqlstr = "INSERT INTO sort(sortname) VALUES('".$name."')";
	if($sqlcon->query($sqlstr)){
		$sqlstr = "SELECT * FROM sort WHERE sortname = '".$name."'";
		$result = $sqlcon->query($sqlstr);
		if(!$result)
			return 0;
		else{
			$result = mysqli_fetch_assoc($result);
			return $result['sortid'];
		}
	}
	else
		return 0;
}

/**
 * 修改产品信息
 * @param 	string 	$proid 			产品ID
 * @param 	string 	$name 			产品名称，为NULL则不修改
 * @param 	string  $description 	产品描述，为NULL则不修改
 * @param 	string 	$sortid 		分类ID，为NULL则不修改
 * @param 	string 	$picurl 		产品图片地址
 * @return 	bool 					修改成功返回Ture，失败返回Flase
 */
function EditProduct($proid, $name = NULL, $description = NULL, $sortid = NULL, $picurl = NULL){
	$content = "";
	$name 		 != NULL && $content .= "proname = '".$name."',";
	$description != NULL && $content .= "description = '".$description."',";
	$sortid 	 != NULL && $content .= "sortid = '".$sortid."',";
	$picurl 	 != NULL && $content .= "picurl = '".$picurl."',";
	$content = mb_substr($content, 0, mb_strlen($content)-1);
	if($content == '')
		return true;

	//连接数据库
	$sqlconf = $GLOBALS['sqlconf'];
	$sqlcon = new mysqli($sqlconf['host'], $sqlconf['user'], $sqlconf['pwd'], $sqlconf['db']);
	if($sqlcon->connect_errno)
		return false;

	$sqlstr = "UPDATE product SET ".$content." WHERE proid = '".$proid."'";
	if($sqlcon->query($sqlstr))
		return true;
	else
		return false;
}

/**
 * 修改分类名称
 * @param 	string 	$sortid 		分类ID
 * @param 	string 	$name 			新分类名称
 * @return 	bool 					修改成功返回Ture，失败返回Flase
 */
function EditSort($sortid, $name){
	//连接数据库
	$sqlconf = $GLOBALS['sqlconf'];
	$sqlcon = new mysqli($sqlconf['host'], $sqlconf['user'], $sqlconf['pwd'], $sqlconf['db']);
	if($sqlcon->connect_errno)
		return false;

	$sqlstr = "UPDATE sort SET sortname = '".$name."' WHERE sortid = '".$sortid."'";
	if($sqlcon->query($sqlstr))
		return true;
	else
		return false;
}

/**
 * 删除产品
 * @param 	string 	$proid 			产品ID
 * @return 	bool 					删除成功返回Ture，失败返回Flase
 */
function DelProduct($proid){
	//连接数据库
	$sqlconf = $GLOBALS['sqlconf'];
	$sqlcon = new mysqli($sqlconf['host'], $sqlconf['user'], $sqlconf['pwd'], $sqlconf['db']);
	if($sqlcon->connect_errno)
		return false;

	$sqlstr = "DELETE FROM product WHERE proid = '".$proid."'";
	if($sqlcon->query($sqlstr)){
		@unlink('../product_images/'.$proid.'.jpg');		//同时也删除产品图片
		return true;
	}
	else
		return false;
}

/**
 * 删除分类
 * @param 	string 	$sortid 		分类ID
 * @return 	bool 					删除成功返回Ture，失败返回Flase
 */
function DelSort($sortid){
	//连接数据库
	$sqlconf = $GLOBALS['sqlconf'];
	$sqlcon = new mysqli($sqlconf['host'], $sqlconf['user'], $sqlconf['pwd'], $sqlconf['db']);
	if($sqlcon->connect_errno)
		return false;

	$sqlstr = "DELETE FROM sort WHERE sortid = '".$sortid."'";
	if($sqlcon->query($sqlstr))
		return true;
	else
		return false;
}

/**
 * 查询产品
 * @param 	string 	$where 			查询限定条件，为空则查询全部
 * @param 	string 	$order			排序方式,为空则默认按发布时间逆序
 * @return 	Array					以数组形式返回查询到的所有数据，若未查询到则返回NULL
 */
function SelectProduct($where = NULL, $order = 'CreateTime DESC'){
	//连接数据库
	$sqlconf = $GLOBALS['sqlconf'];
	$sqlcon = new mysqli($sqlconf['host'], $sqlconf['user'], $sqlconf['pwd'], $sqlconf['db']);
	if($sqlcon->connect_errno)
		return array();

	if($where != NULL)
		$where = "WHERE ".$where;
	else
		$where = '';
	$sqlstr = "SELECT * FROM product ".$where." ORDER BY ".$order;
	$result = $sqlcon->query($sqlstr);
	if(!$result)
		return NULL;
	else{
		$data = array();
		$i = 0;
		while($row = mysqli_fetch_assoc($result)){
			$data[$i] = $row;
			$data[$i]['proname'] = $GLOBALS['safety']->ConvertTextOut($data[$i]['proname']);
			$data[$i]['description'] = $GLOBALS['safety']->ConvertTextOut($data[$i]['description']);
			$i++;
		}
		// print_r($data);
		if(count($data) == 0)
			return NULL;
		else
			return $data;
	}
}


/**
 * 查询所有分类
 * @return 	Array					以数组形式返回查询到的所有数据，若未查询到则返回空数组
 */
function SelectSort(){
	//连接数据库
	$sqlconf = $GLOBALS['sqlconf'];
	$sqlcon = new mysqli($sqlconf['host'], $sqlconf['user'], $sqlconf['pwd'], $sqlconf['db']);
	if($sqlcon->connect_errno)
		return array();

	$sqlstr = "SELECT * FROM sort";
	$result = $sqlcon->query($sqlstr);
	if(!$result)
		return NULL;
	else{
		$temp = array();
		$i = 0;
		//遍历查询结果
		while($row = mysqli_fetch_assoc($result)){
			$temp[$i] = $row;
			$temp[$i]['sortname'] = $GLOBALS['safety']->ConvertTextOut($temp[$i]['sortname']);
			$i++;
		}
		if(count($temp) == 0)
			return NULL;
		else
			return $temp;
	}
}

/**
 * 验证Token
 * @return 	bool 					验证成功返回True，失败返回False
 */
function VerifyToken(){
	$request = $GLOBALS['REQUEST'];
	return !empty($request['token']) && isset($_SESSION[$request['token']]);
}



/**************>数据请求及处理<****************/
//获取请求数据
$request = $GLOBALS['REQUEST'];
if(count($request) == 0)
	exit();

//获取请求动作
if(empty($request['action']))
	exit();
$act = $request['action'];

//定义返回数组变量
$back = array('code'=>0, 'msg'=>'');

//处理请求：发布新品
if($act == 'addproduct'){
	//验证token并检查参数
	if(!VerifyToken())
		$back['msg'] = 'token错误';
	else if(empty($request['proname']))
		$back['msg'] = '产品名称不能为空';
	else if(mb_strlen($request['proname']) > 20)
		$back['msg'] = '产品名称过长';
	else if(empty($request['description']))
		$back['msg'] = '产品描述不能为空';
	else if(empty($request['picurl']) || !file_exists($request['picurl']))
		$back['msg'] = '产品图片地址无效';
	else if(empty($request['sortid']))
		$back['msg'] = '产品分类不能为空';
	else if(ProductIsExist(_NAME_, $request['proname']) == 1)
		$back['msg'] = '产品名称已存在，请更换名称';
	else if(ProductIsExist(_NAME_, $request['proname']) == 2)
		$back['msg'] = '服务器异常';
	else if(SortIsExist(_ID_, $request['sortid']) == 0)
		$back['msg'] = '产品分类不存在';
	else if(SortIsExist(_ID_, $request['sortid']) == 2)
		$back['msg'] = '服务器异常';
	else{
		//整理信息
		$name 		 = $request['proname'];
		$name 		 = $GLOBALS['safety']->ConvertTextIn(htmlspecialchars($name));
		$sortid 	 = $request['sortid'];
		$description = $request['description'];
		$description = $GLOBALS['safety']->ConvertTextIn(htmlspecialchars($description));
		$picurl 	 = $request['picurl'];

		//发布产品
		$back['code'] = AddProduct($name, $description, $sortid, $picurl);
		if($back['code'] == 0)
			$back['msg'] = '发布产品出错';
	}
}

//处理请求：修改产品信息
if($act == 'editproduct'){
	//验证token并检查格式
	if(!VerifyToken())
		$back['msg'] = 'token错误';
	else if(empty($request['proid']) || ProductIsExist(_ID_, $request['proid']) == 0)
		$back['msg'] = '产品不存在';
	else if(ProductIsExist(_ID_, $request['proid']) == 2)
		$back['msg'] = '服务器异常';
	else if(empty($request['proname']) && empty($request['description']) && empty($request['sortid']) && empty($request['picurl']))
		$back['msg'] = '无欲修改的参数';
	else{
		//收集信息
		$proid 		 = $request['proid'];
		$name 		 = empty($request['proname']) ? NULL : $request['proname'];
		$name 		 = $name == NULL ? NULL : $GLOBALS['safety']->ConvertTextIn(htmlspecialchars($name));
		$description = empty($request['description']) ? NULL : $request['description'];
		$description = $description == NULL ? NULL : $GLOBALS['safety']->ConvertTextIn(htmlspecialchars($description));
		$sortid 	 = empty($request['sortid']) ? NULL : $request['sortid'];
		$picurl 	 = empty($request['picurl']) ? NULL : $request['picurl'];
		
		//修改产品信息
		if(EditProduct($proid, $name, $description, $sortid, $picurl))
			$back['code'] = 1;
		else
			$back['msg'] = '修改产品信息出错';
	}
}

//处理请求：删除产品
if($act == 'deleteproduct'){
	//验证token并检查格式
	if(!VerifyToken())
		$back['msg'] = 'token错误';
	else if(empty($request['proid']) || ProductIsExist(_ID_, $request['proid']) == 0)
		$back['msg'] = '产品不存在';
	else if(ProductIsExist(_ID_, $request['proid']) == 2)
		$back['msg'] = '服务器异常';	
	else{
		if(DelProduct($request['proid']))
			$back['code'] = 1;
		else
			$back['msg'] = '删除产品出错';
	}
}

//处理请求：上传产品图片
if($act == 'uploadpic'){
	//验证token并检查格式
	if(!VerifyToken())
		$back['msg'] = 'token错误';
	if(!isset($_FILES['propic']))
		$back['msg'] = '上传失败，无法获取图片对象';
	else{
		//获取图片信息
		$file = $_FILES['propic'];
		$name = $file['name'];
		$type = $file['type'];
		$size = $file['size'];
		$temp = $file['tmp_name'];
		$err  = $file['error'];

		//检查格式
		if($err > 0)
			$back['msg'] = '上传出错，错误代码：'.$err;
		else if($type != 'image/gif' && $type != 'image/jpeg' && $type != 'image/pjpeg' && $type != 'image/png' && $type != 'image/x-png')
			$back['msg'] = '文件格式不支持';
		else if($size / 1024 > 1024)
			$back['msg'] = '文件不能超过1M';
		else{
			$path = time().urlencode($name);
			$path = '../product_images/'.$path;
			$path = iconv('UTF-8', 'GB2312', $path);
			// print_r($path);
			if(!move_uploaded_file($temp, $path))
				$back['msg'] = '图片保存失败';
			else{
				$back['code'] = 1;
				$back['msg'] = $path;
			}
		}
	}
}

//处理请求：检查产品名称是否已存在
if($act == 'check'){
	//检查格式
	if(empty($request['proname']) && empty($request['proid']))
		$back['msg'] = '产品ID或名称不能为空';
	else{
		if(!empty($request['proname']))
			$exist = ProductIsExist(_NAME_, $request['proname']);
		else
			$exist = ProductIsExist(_ID_, $request['proid']);
		if($exist == 1)
			$back['code'] = 1;
		else if($exist == 0)
			$back['code'] = '0';
		else{
			$back['code'] = 2;
			$back['msg'] = '服务器异常';
		}
	}
}

//处理请求：获取产品列表
if($act == 'getlist'){
	//重定义返回数组
	$back = array('code'=>0, 'msg'=>'', 'list'=>array());

	//检查格式
	if(!empty($request['pagesize']) && empty($request['pageindex']))
		$back['msg'] = '未定义分页索引';
	else{
		//定义查询条件
		$where = '';
		!empty($request['sortid']) 	 && $where .= " sortid = '".$request['sortid']."' AND";
		!empty($request['searchtext']) && $where .= " (proname like '%".$GLOBALS['safety']->ConvertTextIn(htmlspecialchars($request['searchtext']))."%' OR description like '%".$GLOBALS['safety']->ConvertTextIn(htmlspecialchars($request['searchtext']))."%') AND";
		$where = mb_substr($where, 0, mb_strlen($where)-4);

		//查询产品
		$result = SelectProduct($where);
		if(count($result) == 0)
			$back['msg'] = '查询无内容';
		else{
			//处理页码信息
			$itemcount = count($result);
			$pagesize  = !empty($request['pagesize']) ? $request['pagesize']  : $itemcount;
			$pageindex = !empty($request['pagesize']) ? $request['pageindex'] : 1;
			if($pagesize <= 0 || $pageindex <= 0)
				$back['msg'] = '分页定义出错';
			else{
				$pagecount = ceil($itemcount / $pagesize);
				if($pageindex > $pagecount)
					$back['msg'] = '分页索引超出页码范围';
				else{
					//进行分页处理
					$items = array();
					for($i = 1; $i <= $itemcount; $i++){
						if($i % $pagesize == 0)
							$index1 = floor($i / $pagesize) - 1;
						else
							$index1 = floor($i / $pagesize);
						$index2 = $i % $pagesize == 0 ? $pagesize-1 : $i % $pagesize -1;
						$items[$index1][$index2] = $result[$i-1];
					}
					$back['list'] = $items[$pageindex-1];
					$back['code']  = $pagecount;
				}
			}
		}
	}
}

//处理请求：获取分类列表
if($act == 'getsort'){
	//重定义返回数组
	$back = array('code'=>0, 'msg'=>'', 'sort'=>array());

	$result = SelectSort();
	if(count($result) == 0)
		$back['msg'] = '查询无内容';
	else{
		$back['sort'] = $result;
		$back['code'] = 1;
	}
}

//处理请求：添加分类
if($act == 'addsort'){
	//验证token并检查格式
	if(!VerifyToken())
		$back['msg'] = 'token错误';
	else if(empty($request['name']))
		$back['msg'] = '分类名称不能为空';
	else if(SortIsExist(_NAME_, $request['name']) == 1)
		$back['msg'] = '该分类已存在，请勿重复添加';
	else{
		$name = $GLOBALS['safety']->ConvertTextIn(htmlspecialchars($request['name']));
		$back['code'] = AddSort($name);
		if($back['code'] == 0)
			$back['msg'] = '添加分类出错';
	}
}

//处理请求：修改分类
if($act == 'editsort'){
	//验证token并检查格式
	if(!VerifyToken())
		$back['msg'] = 'token错误';
	else if(empty($request['sortid']))
		$back['msg'] = '分类ID不能为空';
	else if(empty($request['newname']))
		$back['msg'] = '分类名称不能为空';
	else if(SortIsExist(_NAME_, $request['newname']) == 1)
		$back['msg'] = '分类名称已存在，不能重复';
	else{
		$newname = $GLOBALS['safety']->ConvertTextIn(htmlspecialchars($request['newname']));
		if(EditSort($request['sortid'], $newname))
			$back['code'] = 1;
		else
			$back['msg'] = '修改分类名称出错';
	}
}

//处理请求：删除分类
if($act == 'deletesort'){
	//验证token并检查格式
	if(!VerifyToken())
		$back['msg'] = 'token错误';
	else if(empty($request['sortid']))
		$back['msg'] = '分类ID不能为空';
	else if(SortIsExist(_ID_, $request['sortid']) == 0)
		$back['msg'] = '分类不存在';
	else{
		if(DelSort($request['sortid']))
			$back['code'] = 1;
		else
			$back['msg'] = '删除分类名称出错';
	}
}

//返回处理的信息
$back = json_encode($back);
exit($back);