<?php
/**
 * 公司网站信息操作
 *
 * @author 		YowFung
 * @copyright 	网园资讯工作室
 * @license		http://www.wangyuan.info
 * @version 	2017.7.18.1.0
 */


/**************>全局设置及参数<****************/
//设置报头
header('Access-Control-Allow-Headers: accept');
header('Access-Control-Allow-Origin: http://localhost:8080');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Credentials: true');
header('Content-type: application/json; charset=utf-8');
session_start();									//开启SESSION
define("_CONVERT_IN_", 0);							//定义常量
define("_CONVERT_OUT_", 1);
include './config.php';								//包含数据库配置文件
include './safety.php';								//包含数据库防注入转义文件
$request = file_get_contents('php://input');		//获取请求内容
$request = json_decode($request, true);				//JSON转数组
$GLOBALS['REQUEST'] = $request;						//存入全局变量


/**************>相关函数及方法<****************/
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
$back = array('code'=>'0', 'msg'=>'');

//处理请求：上传轮播图片
if($act == 'uploadpic'){
	//验证token并检查格式
	if(!VerifyToken())
		$back['msg'] = 'token错误';
	else if(empty($request['picid']) || $request['picid'] < 1 || $request['picid'] > 3)
		$back['msg'] = '请求的参数错误';
	else{
		//获取图片信息
		print_r($_FILES);
		$file = $_FILES['picture'];
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
		else if($size / 1024 > 2048)
			$back['msg'] = '文件不能超过2M';
		else{
			//保存图片
			$path = time().urlencode($name);
			$path = '../company_images/'.$path;
			$path = iconv('UTF-8', 'GB2312', $path);
			print_r($path);
			if(!move_uploaded_file($temp, $path))
				$back['msg'] = '图片保存失败';
			else{
				//连接数据库
				$sqlcon = new mysqli($sqlconf['host'], $sqlconf['user'], $sqlconf['pwd'], $sqlconf['db']);
				if($sqlcon->connect_errno){
					$back['msg'] = '服务器异常';
					@unlink($path);
				}else{
					//更新数据库
					$sqlstr = "UPDATE company SET value = '".$path."' WHERE keyname = 'picurl".$request['picid']."'";
					if(!$sqlcon->query($sqlstr))
						$back['msg'] = '服务器异常';
					else{
						$back['code'] = '1';
						$back['msg'] = $path;
					}
				}
			}
		}
	}
}

//处理请求：获取轮播图地址
if($act == 'getpicurl'){
	//检查参数
	if(empty($request['picid']) || $request['picid'] < 1 || $request['picid'] > 3)
		$back['msg'] = '请求的参数错误';
	else{
		//连接数据库
		$sqlcon = new mysqli($sqlconf['host'], $sqlconf['user'], $sqlconf['pwd'], $sqlconf['db']);
		if($sqlcon->connect_errno)
			$back['msg'] = '服务器异常';
		else{
			//查询数据库
			$sqlstr = "SELECT value FROM company WHERE keyname = 'picurl".$request['picid']."'";
			$result = $sqlcon->query($sqlstr);
			if(!$result)
				$back['msg'] = '服务器异常';
			else{
				$result = mysqli_fetch_assoc($result);
				// print_r($result);
				$back['msg'] = $result['value'];
				$back['code'] = '1';
			}
		}
	}
}

//处理请求：修改公司简介信息
if($act == 'editintro'){
	//检查参数
	if(!VerifyToken())
		$back['msg'] = 'token错误';
	else if(empty($request['newintro']))
		$back['msg'] = '公司简介内容不能为空';
	else{
		//内容处理
		$content = $request['newintro'];
		$content = trim($content);					//去首尾空
		$content = htmlspecialchars($content);		//HTML转义

		//防注入处理
		$safety = new Safety();
		$content = $safety->ConvertTextIn($content);

		//连接数据库
		$sqlcon = new mysqli($sqlconf['host'], $sqlconf['user'], $sqlconf['pwd'], $sqlconf['db']);
		if($sqlcon->connect_errno)
			$back['msg'] = '服务器异常';
		else{
			//更新数据库
			$sqlstr = "UPDATE company SET value='".$content."' WHERE keyname = 'introduction'";
			if(!$sqlcon->query($sqlstr))
				$back['msg'] = '服务器异常';
			else
				$back['code'] = '1';
		}
	}
}

//处理请求：获取公司简介信息
if($act == 'getintro'){
	//重定义返回数组
	$back = array('content'=>'');

	//连接数据库
	$sqlcon = new mysqli($sqlconf['host'], $sqlconf['user'], $sqlconf['pwd'], $sqlconf['db']);
	if(!$sqlcon->connect_errno){
		//查询数据库
		$sqlstr = "SELECT value FROM company WHERE keyname = 'introduction'";
		$result = $sqlcon->query($sqlstr);
		if($result){
			//获取内容
			$content = mysqli_fetch_assoc($result);
			$content = $content['value'];

			//防注入解除
			$safety = new Safety();
			$content = $safety->ConvertTextOut($content);

			//输出内容
			$back['content'] = $content;
		}
	}
}


//返回处理的信息
$back = json_encode($back);
exit($back);