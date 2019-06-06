<?php
/**
 * 后台用户操作
 *
 * @author 		YowFung
 * @copyright 	网园资讯工作室
 * @license		http://www.wangyuan.info
 * @version 	2017.7.18.2.0
 */

/**************>全局设置及参数<****************/
//设置报头
header('Access-Control-Allow-Headers: accept, content-type');
header('Access-Control-Allow-Origin: http://localhost:8080');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Credentials: true');
header('Content-type: application/json; charset=utf-8');
session_start();								//开启SESSION
include './config.php';							//包含数据库配置文件
include './safety.php';							//包含数据库防注入转义文件
$request = file_get_contents('php://input');	//获取请求内容
$request = json_decode($request, true);			//JSON转数组
$GLOBALS['REQUEST'] = $request;					//存入全局变量


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

//处理请求：管理员登录
if($act == 'login'){
	//重定义返回数组
	$back = array('code'=>'0','msg'=>'','token'=>'');

	//检查格式
	if(empty($request['username']) || empty($request['password']))
		$back['msg']  = '用户名或密码不能为空';
	else{
		//连接数据库
		$sqlcon = new mysqli($sqlconf['host'], $sqlconf['user'], $sqlconf['pwd'], $sqlconf['db']);
		if($sqlcon->connect_errno)
			$back['msg'] = '服务器异常';
		else{
			//查询并验证用户名
			$sqlstr = 'SELECT * FROM admin WHERE keyname="username"';
			$result = $sqlcon->query($sqlstr);
			if($result == false)
				$back['msg'] = '服务器异常';
			else{
				//防注入转义
				$safety = new Safety();
				$username = $safety->ConvertTextIn($request['username']);

				//验证用户名
				$result = mysqli_fetch_assoc($result);
				if($result['value'] != $username)
					$back['msg'] = '用户名错误';
				else{
					//查询并验证密码
					$sqlstr = 'SELECT * FROM admin WHERE keyname="password"';
					$result = $sqlcon->query($sqlstr);
					if($result == false)
						$back['msg'] = '服务器异常';
					else{
						$result = mysqli_fetch_assoc($result);
						if($result['value'] != sha1(md5($request['password'])))
							$back['msg'] = '密码错误';
						else{
							//生成token并存入session
							$token = array();
							$codeset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
							for($i = 0; $i < 16; $i++)
								$token[$i] = $codeset[mt_rand(0,strlen($codeset)-1)];
							$token = implode('', $token);
							$password = sha1(md5($request['password']));
							$session_data = array('username' => $username, 'password' =>$password);
							$_SESSION[$token] = $session_data;

							//设置返回信息
							$back['code'] = '1';
							$back['token'] = $token;
						}
					}
				}
			}
		}
	}
}					

//处理请求：验证是否已登录
if($act == 'verify'){
	//重定义返回数组
	$back = array('code'=>'0');

	//验证token
	if(VerifyToken())
		$back['code'] = '1';
}

//处理请求：退出登录
if($act == 'logout'){
	//验证token
	if(!VerifyToken())
		$back['msg'] = 'token错误';
	else{
		unset($_SESSION[$request['token']]);		//注销token
		$back['code'] = '1';
	}
}

//处理请求：修改管理员用户信息
if($act == 'change'){
	//检查格式并验证旧密码
	if(empty($request['token']) || !VerifyToken())
		$back['msg'] = 'token错误';
	else if(empty($request['old_password']) || sha1(md5($request['old_password'])) !=  $_SESSION[$request['token']]['password'])
		$back['msg'] = '旧密码不正确';
	else if(empty($request['new_password']))
		$back['msg'] = '新密码不能为空';
	else{
		//收集信息
		$password = $request['new_password'];
		$username = $_SESSION[$request['token']]['username'];
		if(!empty($request['new_username']))
			$username = $request['new_username'];
		
		//检查长度
		if(mb_strlen($username) < 4 || mb_strlen($username) > 10)
			$back['msg'] = '用户名长度应为4-10位';
		else if(mb_strlen($password) < 4 || mb_strlen($password) > 18)
			$back['msg'] = '密码长度应为4-18位';
		else{
			//用户名防注入转义，加密密码
			$safety = new Safety();
			$username = $safety->ConvertTextIn($username);
			$password = sha1(md5($password));

			//连接数据库
			$sqlcon = new mysqli($sqlconf['host'], $sqlconf['user'], $sqlconf['pwd'], $sqlconf['db']);
			if($sqlcon->connect_errno)
				$back['服务器异常'];
			else{
				//更新数据库
				$sqlstr1  = "UPDATE admin SET value='".$username."' WHERE keyname='username'"; 
				$sqlstr2  = "UPDATE admin SET value='".$password."' WHERE keyname='password'";
				if($sqlcon->query($sqlstr1) && $sqlcon->query($sqlstr2)){
					$back['code'] = '1';
					unset($_SESSION[$request['token']]);		//注销token
				}
				else
					$back['msg'] = '修改失败';
			}
		}
	}
}

//返回响应
$back = json_encode($back);
exit($back);
