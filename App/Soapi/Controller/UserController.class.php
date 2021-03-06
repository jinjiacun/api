<?php
namespace Soapi\Controller;
use Soapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--用户管理--
------------------------------------------------------------
function of api:
 
#用户注册
public function register
@@input
@param $mobile       手机号码
@param $pswd         密码
@param $nickname     昵称(不设为自动生成)
@@output
@param $is_success 0-成功,-1-失败,-2-手机号码已存在
@param $user_id  用户id
@param $nickname 用户昵称
@param $sex      用户性别
@param $cur_date 当前日期
##--------------------------------------------------------##
#登录
public function login
@@input
@param $mobile   手机号码
@param $pswd     密码
@@output
@param $is_success 0-成功,-1-失败,-2-用户名或者密码错误,-3-用户被限制登录,-4-用户访问的IP被限制
@param $user_id  用户id
@param $head_portrait 头像
@param $nickname 用户昵称
@param $sex      用户性别
@param $cur_date 当前日期
##--------------------------------------------------------##
#获取登录信息
public function get_login_info
@@input
@@output
@param $user_id         会员id
@param $user_mobile     会员手机号码
@param $user_nick_name  会员昵称
@param $user_sex        会员性别
@param $user_login_date 会员登录时间
##--------------------------------------------------------##
#找回密码
public function find_passwd
@@input
@param $mobile - 手机号
@param $new_pswd - 新密码
@@output
@param $is_success 0-成功,-1-失败	,-2-mobile参数不合法,-3-短信验证码不正确
	                                               -4-mobile不存在
##--------------------------------------------------------##
#查询用户信息
public function get_info
@@input
@param $uid  用户id
@@output
@param $user_info 用户信息json
##--------------------------------------------------------##
#判断用户手机号是否存在
public function check_mobile
@@input
@param $mobile 手机号码
@@output
@param $is_exists 0-存在,-1-不存在
##--------------------------------------------------------##
#更新用户信息
public function update
@@input
@param $uid       
@param $nickname  会员昵称
@param $sex       性别(1 - 男，0 - 女，-1 - 未知)
@param $birthday  出生日期（格式：yyyy-MM-dd）
@param $job       职业
@param $address   所在地
@@output
@param $is_success 0-成功操作，-1-操作失败，-2-此用户不存在
##--------------------------------------------------------##
#更新用户头像信息
public function update_photo
@@input
@param $uid
@param $imageUpLoad 
@@output
@param $is_success 0-成功操作，-1-操作失败，-2-此用户不存在
##--------------------------------------------------------##
#发送手机验证码
public function send_validate
@@input
@param $mobile    手机号码
@param $imagecode 图形验证码
@@output
##--------------------------------------------------------##
#获取图片验证码
public function get_pic_validate
@@input
@param $mobile
@@output
@param $pic_url 图形验证码地址
##--------------------------------------------------------##
#获取图片验证码
public function get_pic_validate_ex
@@input
@param $mobile
@@output
@param $pic_url 图形验证码地址
##--------------------------------------------------------##
#修改密码
public function update_passwd
@@input
@param $uid       用户id
@param $old_pswd  旧密码
@param $new_pswd  新密码
@@output
@param $is_success 0-成功操作，-1-操作失败，-2-用户不存在，-3-原密码不正确
##--------------------------------------------------------##
#更新用户状态
public function update_status
@@input
@param $uid    用户id
@param $state  0-关闭  1-正常
@@output
@param $is_success 0-操作成功,-1-操作失败,-3-用户不存在
##--------------------------------------------------------##
#更新用户登录ip
public function update_ip
@@input
@param $uid      用户id
@param $blackip  用户登录IP黑名单，多个IP用竖线分隔，支持带*号IP段
@@output
@param $is_success 0-操作成功,-1-操作失败,-3-用户不存在
##--------------------------------------------------------##
#获取帐号数
public function get_login_amount
@@output
@amount 帐号数
##--------------------------------------------------------##
#获取用户数
public function get_user_amount
@amount 用户数
##--------------------------------------------------------##
#绑定微信帐号
public function login_weixin
@@input
@param $openid 微信返回id
@@output
@param $content 返回用户信息
##--------------------------------------------------------##
#微信注册
public function register_weixin
@@input
@param $nickname 用户昵称
@param $openid   微信openid
@param $userip   用户ip
@@output
@param $is_success 
##--------------------------------------------------------##
#绑定微信
public function bind_weixin
@@input
@param $ui_id   用户id
@param $openid  微信openid
@@output
@param $is_success 
##--------------------------------------------------------##
#微信入口
public function entry_weixin
@@intput
@param $openid   微信openid 
@param $mobile   手机号码
@param $passwd   密码
@param $nickname 昵称
@@output
@param $is_success  是否成功
@param $user_info   用户信息
##--------------------------------------------------------##
#检查登录名是否存在
public function check_loginname
@@input
@param $loginname 登录名
@param $logintype 登录类型(=4,loginname=微信openid),(=5,loginname=QQopenid),
*                        (=6,loginname=微博OpenId)
@@output
@param $is_success 0-存在,-1-不存在,-2-loginname参数不合法,-3-safekey参数不合法
##--------------------------------------------------------##
#通过解除登录账号绑定
public function cancel_bind
@@input
@param $uid        用户id
@param $logintype  登录类型(=0,loginname=用户名),(=1,loginname=手机号),
*                         (=2,loginname=邮箱),(=3,loginname=QQ号),
*                         (=4,loginname=微信OpenId),(=5,loginname=QQOpenId),
*                         (=6,loginname=微博OpenId)
@param $loginname
@@output
@param $is_success 0-成功,-1-操作错误,-2-loginname参数不合法,-3-safekey参数不合法
##--------------------------------------------------------##
#微博openid注册
public function register_weibo
@@input
@param $nickname 昵称
@param $openid   微博openid
@param $userip   用户ip
@@output
@param $is_success 
##--------------------------------------------------------##
#qq的opendid注册
public function register_qq
@@input
@param $nickname 昵称
@param $openid   qq的openid
@param $userip   用户ip
@@output
@param $is_success
##--------------------------------------------------------##
#微博openid登录
public function login_weibo
@@input
@param $openid  微博openid
@@output
@param $content 返回用户信息
##--------------------------------------------------------##
#qq的opendid登录
public function login_qq
@@output
@param $content 返回用户信息
##--------------------------------------------------------##
#绑定微博(openid)
public function bind_weibo
@@input
@param $ui_id   用户id
@param $openid  微博openid
@@output
@param $is_success
##--------------------------------------------------------##
#绑定qq(openid)
public function bind_qq
@@input
@param $ui_id   用户id
@param $openid  qq的openid
@@output
@param $is_success
##--------------------------------------------------------##
#微博入口(openid)
public function entry_weibo
@@intput
@param $openid   微博openid 
@param $mobile   手机号码
@param $passwd   密码
@param $nickname 昵称
##--------------------------------------------------------##
#qq入口(openid)
public function entry_qq
@@intput
@param $openid   qq的openid 
@param $mobile   手机号码
@param $passwd   密码
@param $nickname 昵称
##--------------------------------------------------------##
#头像上传
public function head_photo_upload
@@input
@param $uid      用户id
@param $pic_path 图片路径
@@output
@param $is_success 0-成功,-1-失败,-2-safekey参数不合法，-3-用户不存在，
*                  -4-头像文件保存失败,-5-头像文件超出指定大小限制（暂定100KB）
*/
class UserController extends BaseController {
	private $USER_API_METHOD_LIST = array(
							 'register'          => "RegisterByMobile",            #通过手机号注册
							 'login'             => "LoginByMobile",               #通过手机号登录 【允许get请求】
							 'find_passwd'       => "SetPswordByMobile",           #用户找回密码
							 'update'            => "SetUserInfoByUid",            #更新用户信息
							 'update_photo'      => "SetUserAvatarByUid",          #更新用户头像信息
							 'get_info'          => "GetUserInfoByUid",            #查询用户信息
			                 'check_mobile'      => "ExistsUserInfoByLoginName",   #检查手机号码
			                 'send_validate'     => "SmsByFindPswd",               #发送手机验证
			                 'update_passwd'     => "SetUserNewPswd",              #修改密码
			                 'update_status'     => "SetUserState",                #封号
			                 'update_ip'         => "SetUserBlackIp",              #封ip
			                 'get_user_amount'   => "CountUserInfo",               #获取用户数
			                 'get_login_amount'  => "CountUserLogin",              #获取帐号数
			                 'login_weixin'      => "LoginByWeixinOpenid",         #通过微信OpenId登录
			                 'register_weixin'   => "RegisterByWeixinOpenid",      #通过微信openid注册
			                 'bind_weixin'       => "BindUserLoginByWeixinOpenid", #绑定微信
			                 'check_loginname'   => "ExistsUserInfoByLoginName",   #检查用户名是否存在
			                 'cancel_bind'       => "CanelBingUserLogin",          #解绑定
							 'login_weibo'       => "LoginByWeiboOpenid",		   #通过微薄openid登录
							 'register_weibo'    => "RegisterByWeiboOpenid",	   #通过微博openid注册
							 'bind_weibo'        => "BindUserLoginByWeiboOpenid",  #绑定微博
							 'login_qq'			 => "LoginByQQOpenid ",			   #通过qq的openid登录
							 'register_qq'		 => "RegisterByQQOpenid", 	       #通过qq的openid注册
							 'bind_qq'			 => "BindUserLoginByQQOpenid",	   #绑定qq的openid
							 'head_photo_upload' => "SetUserAvatarByUid",          #上传头像
							 );
							 
	#通过手机注册
	public function register($content)
	/**
	@@input
	@param mobile       手机号码
	@param pswd         密码
	@param $nickname    昵称(不设为自动生成)
	@param $sem         是否推广用户(默认0,1为推广用户)
	@@output
	@param $is_success 0-成功,-1-失败
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['mobile'])
		|| !isset($data['pswd'])
		)
		{
			return C('param_err');
		}
		
		$data['mobile'] = htmlspecialchars(trim($data['mobile']));
		$data['pswd']   = htmlspecialchars(trim($data['pswd']));
		$data['sem']    = intval($data['sem']);
		
		$content = array(
			'user_id'  =>0,
			'nickname' =>isset($data['nickname'])?$data['nickname']:'',
			'sex'      =>-1,
			'cur_date' =>'',
			'sem'      => $data['sem'],
		);
		
		
		if($this->call_RegisterByMobile($data['mobile'], $data['pswd'], &$content))
		{
			//同步用户信息
			$user_info = array(
				'user_id'=>$content['user_id'],
				'nickname'=>$content['nickname'],
			);
			A('Soapi/Member')->add(json_encode($user_info));
			#get($mobile='', $uname='', $url='', $preurl='', $agent='', $screen='', $remark='')
			#判定是否是手机			
			if(!isset($data['agent'])||''==$data['agent'])#手机
			{
				$data['agent'] = $_SERVER['HTTP_USER_AGENT'];
			}
			$begin = microtime(true);
			//调用资源库
			$result = $this->get($data['mobile'],$data['uname'],$data['url'], $data['preurl'], $data['agent'], $data['screen'], $data['remark']);		
			$end = microtime(true);
			$diff_time = $end-$begin;
			$log_content = sprintf("mobile:%s	pswd:%s	user_time:%s	now:%s\n",$data['mobile'], $data['pswd'], $diff_time, time());
			file_put_contents(__PUBLIC__."log/user_register_lib_time.log", $log_content,  FILE_APPEND);
			
			return array(
				200,
				array(
					'is_success'=>0,
					'message'=>C('option_ok'),
					'message'  =>C('option_ok'),
					'user_id'  =>$content['user_id'],
					'nickname' =>$content['nickname'],
					'sex'      =>$content['sex'],
					'cur_date' =>$content['cur_date'],
				),
			);
		}
		if(isset($content['status_code'])
		&& -3 == $content['status_code']
		)
		{
			return array(
				200,
				array(
					'is_success'=>$content['status_code'],
					'message'=>$content['message'],
				)
			);
		}
		
		
		return array(
				200,
				array(
					'is_success'=>isset($content['status_code'])?
					              $content['status_code']:-1,
					'message'=>C('option_fail'),
				),
			);
	}
	
	private function call_RegisterByMobile($mobile, $pswd, $content)
	{
		$obj_des = new \Org\Util\DES1("ODMQHZUK");
		$mobile = $obj_des->encrypt($mobile);
		$params = array(
			'mobile'   => $mobile,
			'pswd'     => $pswd,
			'nickname' => '' == $content['nickname']?
			              $this->make_nickname():
			              $content['nickname'],
			'validated'=> 0,
			'userip'   => $this->get_real_ip(),
		);
		$params['nickname'] = urlencode($params['nickname']);
		$params['sem'] = $content['sem'];
		//var_dump($params);		
		$params['safekey']  = $this->mk_passwd($params);
		//var_dump($params);
		//$params['nickname'] = urlencode($params['nickname']);
		//var_dump($params);
		$url = C('api_user_url').$this->USER_API_METHOD_LIST['register'];
		//var_dump($url);
		$begin_time = microtime(true);
		$back_str = $this->post($url, $params);
		$end_time = microtime(true);
		//var_dump($back_str);
		//var_dump($back_str);
		$re_json = json_decode($back_str, true);
		if($re_json
		&& 1 == $re_json['State'])
		{
			$diff_time = $end_time-$begin_time;
			$log_content = sprintf("mobile:%s	pswd:%s	user_time:%s	now:%s\n",$mobile, $pswd, $diff_time, time());
			file_put_contents(__PUBLIC__."log/user_register_time.log", $log_content,  FILE_APPEND);
			$back_content = $re_json['Descr'];
			$r_list = explode('|', $back_content);
			$content['user_id']  = $r_list[0];
			$content['nickname'] = $r_list[1];
			$content['sex']      = $r_list[2];
			$content['cur_date'] = $r_list[3];
			return true;
		}
		elseif(-4 == $re_json['State'])
		{
			$content['status_code'] = -2;
			return false;
		}
		elseif(-3 == $re_json['State'])
		{
			$content['status_code'] = -3;
			$content['message'] = urlencode("safekey参数不合法");
			return false;
		}
		return false;
	}
	
	#登录
	public function login($content)
	/*
	@@input
	@param $mobile   手机号码
	@param $pswd     密码
	@@output
	@param $user_id       用户id
	@param $head_portrait 头像
	@param $nickname      用户昵称
	@param $sex           用户性别
	@param $cur_date      当前日期
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['mobile'])
		|| !isset($data['pswd']))
		{
			return C('param_err');
		}
		
		$data['mobile'] = htmlspecialchars(trim($data['mobile']));
		$data['pswd']   = htmlspecialchars(trim($data['pswd']));
		
		if('' == $data['mobile']
		|| '' == $data['pswd'])
		{
			return C('param_fmt_err');
		}
		
		#检查手机号是否存在:begin
		/**
		 * if 手机号码不存在
		 *   调用注册接口
		 * */
		#检查手机号是否存在:end
		
		$content = array(
			'user_id'  =>0,
			'nickname' =>'',
			'sex'      =>-1,
			'cur_date' =>'',
			'userip'   =>isset($data['userip'])?$data['userip']:'',
		);
		if($this->call_LoginByMobile($data['mobile'],
		                             $data['pswd'],
					     &$content))
		{
			if('' == $content['head_portrait'])
			{
				$content['head_portrait'] = C('api_user_domain').'/useravatar.gif';
			}
			session('user_id',         $content['user_id']);
			session('user_mobile',     $data['mobile']);
			session('user_nick_name',  $content['nickname']);
			session('user_sex',        $content['user_sex']);
			session('user_login_date', $content['cur_date']);
			//修改登录信息
			$user_info['data'] = array(
				'last_login'   => time(),
				'last_login_ip'=> $content['userip']
			);
			$user_info['where']['user_id'] = $content['user_id'];
			A('Soapi/Member')->update(json_encode($user_info));
			return array(
				200,
				array(
				'is_success'      =>0,
				'message'         =>C('option_ok'),
				'head_portrait'   =>$content['head_portrait'],
				'user_id'  		  =>$content['user_id'],
				'nickname' 		  =>$content['nickname'],
				'sex'      		  =>$content['sex'],
				'cur_date'   	  =>$content['cur_date'],
				),
			);
		}
		
		return array(
			200,
			array(
			'is_success'=>isset($content['status_code'])?
					              $content['status_code']:-1,
			'message'=>C('option_fail'),
			)
		);
	}
	
	#获取登录信息
	public function get_login_info($content)
	/*
	@@input
	@@output
	@param $is_exists       0-存在,-1-不存在
	@param $user_id         会员id
	@param $user_mobile     会员手机号码
	@param $user_nick_name  会员昵称
	@param $user_sex        会员性别
	@param $user_login_date 会员登录时间
	*/
	{
		if(session('user_id'))
		{
			return array(
				200,
				array(
					'is_success'       =>0,
					'message'          => C('is_exists'),
					'user_id'          => session('user_id'),
					'user_mobile'      => session('user_mobile'),
					'user_nick_name'   => session('user_nick_name'),
					'user_sex'         => session('user_sex'),
					'user_logiin_date' => session('user_login_date'),
				),
			);
		}
		
		return array(
			200,
			array(
				'is_exists'=>-1,
				'message'=>C('no_exists')
			)
		);
	}
	
	private function call_LoginByMobile($mobile, $pswd, $content)
	{
		$params = array(
			'mobile'   => $mobile,
			'pswd'     => $pswd,
			'userip'   => ''==$content['userip']?$this->get_real_ip():$content['userip'],
		);
		$url = C('api_user_url').$this->USER_API_METHOD_LIST['login'];
		$begin_time = microtime(true);
		$this->__debug(serialize($params));
		$back_str = $this->post($url, $params);
		$this->__debug(sprintf("back:%s\n",$back_str));
		/*
		if(!is_null(json_decode($back_str)))
		{
			return false;
		}
		*/
		$re_json = json_decode($back_str, true);
		$end_time = microtime(true);
		if($re_json
		&& 1 == $re_json['State'])
		{
			$diff_time = $end_time - $begin_time;
			$log_content = sprintf("mobile:%s	pswd:%s	user_time:%s	now:%s\n",$mobile, $pswd, $diff_time, time());
			file_put_contents(__PUBLIC__."log/user_login_time.log", $log_content,  FILE_APPEND);
			$back_content = $re_json['Descr'];
			$r_list = explode('|', $back_content);
			$index=0;
			$content['user_id']  = $r_list[$index++];
			$content['nickname'] = $r_list[$index++];
			$content['head_portrait'] = $r_list[$index++];
			$content['sex']      = $r_list[$index++];
			$content['cur_date'] = $r_list[$index++];
			$content['head_portrait'] = C('api_user_photo_url').$content['head_portrait'];
			$content['head_portrait'] = str_replace('user','',$content['head_portrait']);
			//添加登录日志
			/*
			A('Soapi/Memlog')->add(json_encode(array(
												'user_id'=>$content['user_id'],
												'userip'=>$params['userip'],
												'add_time'=>time()
			)));
			*/
			return true;
		}
		elseif(-3 == $re_json['State'])
		{
			$content['status_code'] = -2;
			return false;
		}
		elseif(-4 == $re_json['State'])
		{
			$content['status_code'] = -3;
			return false;
		}
		elseif(-5 == $re_json['State'])
		{
			$content['status_code'] = -4;
			return false;
		}
		return false;
	}
	
	#找回密码
	public function find_passwd($content)
	/*
	@@input
	@param $mobile   - 手机号
	@param $new_pswd - 新密码
	@param $smscode  - 短信验证码
	@@output
	@param $is_success 0-成功,-1-失败	,-2-mobile参数不合法,-3-短信验证码不正确
	*                                    -4-mobile不存在
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['mobile'])
		|| !isset($data['new_pswd'])
		|| !isset($data['smscode'])
		)
		{
			return C('param_err');
		}
		
		$data['mobile']   = htmlspecialchars(trim($data['mobile']));
		$data['new_pswd'] = htmlspecialchars(trim($data['new_pswd']));
		$data['smscode']  = htmlspecialchars(trim($data['smscode']));
		
		if('' == $data['mobile']
		|| '' == $data['new_pswd']
		|| '' == $data['smscode']
		)
		{
			return C('param_fmt_err');
		}
		
		unset($content);
		$content = array();
		if($this->call_SetPswordByMobile($data['mobile'],
						 $data['new_pswd'],$data['smscode'], &$content))
		{
			return array(
				200,
				array(
				  'is_success'=>0,
				  'message'=>C('option_ok'),
				),
			);
		}
		
		if(isset($content['status_code']))
		{
			return array(
				200,
				array(
					'is_success'=>$content['status_code'],
					'message'=>$content['message']
				)
			);
		}
		
		return array(
				200,
				array(
				  'is_success'=>-1,
				  'message'=>C('option_fail'),
				),
			);
	}
	
	
	private function call_SetPswordByMobile($mobile, $new_pswd,
	                                        $smscode, $content)
	{
		$params = array(
			'mobile'   => $mobile,
			'new_pswd' => $new_pswd,
			'smscode'  => $smscode,
			'userip'   => $this->get_real_ip(),
		);
		//$params['safekey']  = $this->mk_passwd($params, 1);
		$url = C('api_user_url').$this->USER_API_METHOD_LIST['find_passwd'];
		$back_str = $this->post($url, $params);		
		//var_dump($back_str);
		$re_json = json_decode($back_str, true);
		if($re_json
		&& 1 == $re_json['State'])
		{
			$back_content = $re_json['Descr'];
			$r_list = explode('|', $back_content);
			$content['user_id']  = $r_list[0];
			$content['nickname'] = $r_list[1];
			$content['sex']      = $r_list[2];
			$content['cur_date'] = $r_list[3];
			return true;
		}
		elseif(-4 == $re_json['State'])
		{
			$content['status_code'] = -2;
			$content['message'] = urlencode('mobile不存在');
			return false;
		}
		elseif(-3 == $re_json['State'])
		{
			$content['status_code'] = -3;
			$content['message'] = urlencode('短信验证码不正确');
			return false;
		}
		elseif(-2 == $re_json['State'])
		{
			$content['status_code'] = -4;
			$content['message'] = urlencode('mobile参数不合法');
			return false;
		}
		return false;
	}
	
	#查询用户信息
	public function get_info($content)
	/*
	@@input
	@param $uid  用户id
	@@output
	@param $user_info 用户信息json
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['uid']))
		{
			return C('param_err');
		}
		
		$data['uid'] = intval($data['uid']);
		
		if(0>= $data['uid'])
		{
			return C('param_fmt_err');
		}
		
		$content = array();
				
		
		if($this->call_GetUserInfoByUid($data['uid'], &$content))
		{
			$content['UI_Avatar'] = C('api_user_photo_url').$content['UI_Avatar'];
			$content['UI_Avatar'] = str_replace('user','',$content['UI_Avatar']);
			return array(
				200,
				array(
				$content
				),
			);
		}
		
		return array(
			200,
			array()
		);
	}
	
	private function call_GetUserInfoByUid($uid, $content)
	{
		$params = array(
			'uid'=>$uid,
			'yyyyMMdd'=>date("Ymd"),
		);
		$params['safekey']  = $this->mk_passwd($params, 3);
		$url = C('api_user_url').$this->USER_API_METHOD_LIST['get_info'];
		$back_str = $this->post($url, $params);
		$this->__debug(sprintf("user_info:%s\n", $back_str));
		$re_json = json_decode($back_str, true);
		if($re_json)
		{	
			$content = $re_json;
			return true;
		}
		return false;
	}
	
	
	
	#判断用户手机号是否存在
	public function check_mobile($content)
	/*
	@@input
	@param $mobile 手机号码
	@@output
	@param $is_exists 0-存在,-1-不存在
	*/
	{
		$data = $this->fill($content);
		if(!isset($data['mobile']))
		{
			return C('param_err');
		}
		
		$data['mobile'] = htmlspecialchars(trim($data['mobile']));
		
		if('' == $data['mobile'])
		{
			return C('param_fmt_err');
		}
		
		if($this->call_ExistsUserInfoByLoginName($data['mobile'],1))
		{
			return array(
				200,
				array(
					'is_exists'=>0,
					'message'=>C('is_exists'),
				),
			);
		}
		
		return array(
				200,
				array(
					'is_exists'=>-1,
					'message'=>C('no_exists'),
				),
			);
	}
	
	/*
	private function call_ExistsUserInfoByMobile($mobile)
	{
		$params = array(
			'mobile'=>$mobile,
			'yyyyMMdd'=>date("Ymd"),
		);
		$params['safekey']  = $this->mk_passwd($params, 4);
		$url = C('api_user_url').$this->USER_API_METHOD_LIST['check_mobile'];
		$back_str = $this->post($url, $params);
		$re_json = json_decode($back_str, true);
		if($re_json
		&& 1 == $re_json['State'])
		{	
			return true;
		}
		return false;
	}
	*/
	private function call_ExistsUserInfoByLoginName($loginname, $logintype, $content)
	{
		$params = array(
			'loginname'=>$loginname,
			'logintype'=>$logintype,
			'yyyyMMdd'=>date("Ymd"),
		);
		$params['safekey']  = $this->mk_passwd($params, 4);
		$url = C('api_user_url').$this->USER_API_METHOD_LIST['check_mobile'];
		$back_str = $this->post($url, $params);
		//var_dump($back_str);
		$re_json = json_decode($back_str, true);
		if($re_json
		&& 1 == $re_json['State'])
		{	
			return true;
		}
		elseif(-2 == $re_json['State'])
		{
			$content['status_code'] = -2;
			$content['message'] = urlencode('loginname参数不合法');
			return false;
		}
		elseif(-3 == $re_json['State'])
		{
			$content['status_code'] = -3;
			$content['message'] = urlencode('safekey参数不合法');
			return false;
		}
		return false;
	}
	
	#更新用户信息
	public function update($content)
	/*
	@@input
	@param $uid       
	@param $nickname  会员昵称
	@param $sex       性别(1 - 男，0 - 女，-1 - 未知)
	@param $birthday  出生日期（格式：yyyy-MM-dd）
	@param $job       职业
	@param $address   所在地
	@@output
	@param $is_success 0-成功操作，-1-操作失败，-2-此用户不存在
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['uid'])
		|| !isset($data['nickname'])
		|| !isset($data['sex'])
		|| !isset($data['birthday'])
		|| !isset($data['job'])
		|| !isset($data['address'])
		)
		{
			return C('param_err');
		}
		
		$data['uid']      = intval($data['uid']);
		$data['nickname'] = htmlspecialchars(trim($data['nickname']));
		$data['sex']      = intval($data['sex']); 
		$data['birthday'] = htmlspecialchars(trim($data['birthday']));
		$data['job']      = htmlspecialchars(trim($data['job']));
		$data['address']  = htmlspecialchars(trim($data['address']));
						
		if(0 >= $data['uid']
		
		)
		{
			return C('param_fmt_err');
		}
		
		unset($content);
		$content = array();
		
		if($this->call_SetUserInfoByUid($data['uid'], 
		                                $data['nickname'], 
		                                $data['sex'],
		                                $data['birthday'],
		                                $data['job'],
		                                $data['address'],
		                                &$content))
		{
			//同步昵称修改
			$user_info['data'] = array(
				'nickname' => $data['nickname'],
			);
			$user_info['where']['user_id'] = $data['uid'];
			A('Soapi/Member')->update(json_encode($user_info));
			A('Soapi/Usernickname')->item_update($data['uid'], $data['nickname']);
			return array(
				200,
				array(
					'is_success'=>0,
					'message'=>C('option_ok'),
				),
			);
		}
		
		return array(
				200,
				array(
					'is_success'=>isset($content['status_code'])?
					              $content['status_code']:-1,
					'message'=>C('option_fail'),
				),
			);
	}
	
	private function call_SetUserInfoByUid($uid, $nickname, $sex, 
	                                       $birthday, $job, $address, 
	                                       $content)
	{
		$params = array(
			'uid'      =>  $uid,
			'nickname' =>  $nickname,
			'sex'      =>  $sex,
			'birthday' =>  $birthday,
			'job'      =>  $job,
			'address'  =>  $address,
			'userip'   =>  $this->get_real_ip(),
			'yyyyMMdd' =>  date("Ymd"),
		);
		$params['safekey']  = $this->mk_passwd($params, 2);
		$url = C('api_user_url').$this->USER_API_METHOD_LIST['update'];
		#转化urlencode
		$params['job']     = urlencode($params['job']);
		$params['address'] = urlencode($params['address']);		
		$params['nickname'] = urlencode($params['nickname']);
		$back_str = $this->post($url, $params);
		$re_json = json_decode($back_str, true);
		if($re_json
		&& 1 == $re_json['State'])
		{	
			return true;
		}
		elseif(-4 == $re_json['State'])
		{
			$content['status_code'] = -2;
			return false;
		}
		return false;
	}
	
	#发送手机验证码
	public function send_validate($content)
	/*
	@@input
	@param $mobile    手机号码
	@param $imagecode 图形验证码
	@@output
	@param $is_success 0-成功,-1-失败,-2-图片验证码不正确,-3-图片验证码接口报错
	*                        -4-短信验证码接口报错,-5-mobile参数不合法
	*/
	{
		$data = $this->fill($content);
		if(!isset($data['mobile'])
		|| !isset($data['imagecode'])
		)
		{
			return C('param_err');
		}
		
		$data['mobile']     = htmlspecialchars(trim($data['mobile']));
		$data['imagecode']  = htmlspecialchars(trim($data['imagecode']));
		
		if('' == $data['mobile']
		|| '' == $data['imagecode']
		)
		{
			return C('param_fmt_err');
		}
		
		unset($content);
		$content = array();
		
		if($this->call_SmsByFindPswd($data['mobile'], $data['imagecode'], &$content))
		{
			return array(
				200,
				array(
					'is_success'=>0,
					'message'=>C('option_ok'),
				),
			);
		}
		
		if(isset($content['status_code']))
		{
			return array(
				200,
				array(
					'is_success'=>$content['status_code'],
					'message'=> $content['message'],
				),
			);
		}
		return array(
				200,
				array(
					'is_success'=>-1,
					'message'=>C('option_fail'),
				),
			);
	}
	
	private function call_SmsByFindPswd($mobile, $imagecode, $content)
	{
		$params = array(
			'mobile'    =>  $mobile,
			'imagecode' =>  $imagecode,
		);
		$params['safekey']  = $this->mk_passwd($params, 5);
		$url = C('api_user_url').$this->USER_API_METHOD_LIST['send_validate'];
		$back_str = $this->post($url, $params);
		$re_json = json_decode($back_str, true);
		if($re_json
		&& 1 == $re_json['State'])
		{	
			return true;
		}
		elseif(-4 == $re_json['State'])
		{
			$content['status_code'] = -2;
			$content['message']     = urlencode('图片验证码不正确');
			return false;
		}
		elseif(-3 == $re_json['State'])
		{
			$content['status_code'] = -3;
			$content['message']     = urlencode('图片验证码接口报错');
			return false;
		}
		elseif(-5 == $re_json['State'])
		{
			$content['status_code'] = -4;
			$content['message']     = urlencode('图片验证码接口报错');
			return false;
		}
		elseif(-2 == $re_json['State'])
		{
			$content['status_code'] = -5;
			$content['message']     = urlencode('mobile参数不合法');
			return false;
		}
		return false;
	}
	
	#获取图片验证码
	public function get_pic_validate($content)
	/*
	@@input
	@param $mobile
	@@output
	@param $pic_url 图形验证码地址
	*/
	{
		$data = $this->fill($content);
		return array(
			200,
			C('api_user_pic_url').$data['mobile']
		);
	}
	
	#获取图片验证码
	public function get_pic_validate_ex($content)
	/*
	@@input
	@param $mobile
	@@output
	@param $pic_url 图形验证码地址
	*/
	{
		$data = $this->fill($content);
		return array(
			200,
			array(
				'img_url'=>C('api_user_pic_url').$data['mobile']
			)
		);
	}
	
	#修改密码
	public function update_passwd($content)
	/*
	@@input
	@param $uid       用户id
	@param $old_pswd  旧密码
	@param $new_pswd  新密码
	@@output
	@param $is_success 0-成功操作，-1-操作失败，-2-用户不存在，-3-原密码不正确
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['uid'])
		|| !isset($data['old_pswd'])
		|| !isset($data['new_pswd'])		
		)
		{
			return C('param_err');
		}
		
		$data['uid']       = intval($data['uid']);
		$data['old_pswd']  = htmlspecialchars(trim($data['old_pswd']));
		$data['new_pswd']  = htmlspecialchars(trim($data['new_pswd']));
		
		if(0>= $data['uid']
		|| '' == $data['old_pswd']
		|| '' == $data['new_pswd']
		)
		{
			return C('param_fmt_err');
		}
		
		unset($content);
		$content = array();
		if($this->call_SetUserNewPswd($data['uid'],
		                              $data['old_pswd'],
		                              $data['new_pswd'],
		                              &$content))
		{
			return array(
				200,
				array(
					'is_success'=>0,
					'message'=>C('option_ok')
				)
			);
		}
		
		if(isset($content['status_code']))
		{
			return array(
				200,
				array(
					'is_success'=>$content['status_code'],
					'message'=> $content['message'],
				),
			);
		}
		return array(
				200,
				array(
					'is_success'=>-1,
					'message'=>C('option_fail'),
				),
			);
	}
	
	private function call_SetUserNewPswd($uid, $old_pswd, $new_pswd, $content)
	{
		$params = array(
			'uid'       => $uid,
			'old_pswd'  => $old_pswd,
			'new_pswd'  => $new_pswd,
		);
		//$params['safekey']  = $this->mk_passwd($params, 5);
		$url = C('api_user_url').$this->USER_API_METHOD_LIST['update_passwd'];
		$back_str = $this->post($url, $params);
		$re_json = json_decode($back_str, true);
		if($re_json
		&& 1 == $re_json['State'])
		{	
			return true;
		}
		elseif(-2 == $re_json['State'])
		{
			$content['status_code'] = -2;
			$content['message']     = urlencode('用户不存在');
			return false;
		}
		elseif(-3 == $re_json['State'])
		{
			$content['status_code'] = -3;
			$content['message']     = urlencode('原密码不正确');
			return false;
		}

		return false;
	}
	
	#更新用户状态
	public function update_status($content)
	/*
	@@input
	@param $uid    用户id
	@param $state  0-关闭  1-正常
	@@output
	@param $is_success 0-操作成功,-1-操作失败,-2-用户不存在
	*/
	{
			$data = $this->fill($content);
			if(!isset($data['uid'])
			|| !isset($data['state'])
			)
			{
				return C('param_err');
			}
			
			$data['uid'] = intval($data['uid']);
			$data['state'] = intval($data['state']);
			
			if(0>= $data['uid'])
			{
				return C('param_fmt_err');
			}
			
			unset($content);
			$content = array();
			if($this->call_SetUserState($data['uid'],
										$data['state'], 
										&$content))
			{
				//同步更新状态
				$user_info['data'] = array(
					'state'=>$data['state'],
				);
				$user_info['where']['user_id'] = $data['uid'];
				A('Soapi/Member')->update(json_encode($user_info));
				return array(
					200,
					array(
						'is_success'=>0,
						'message'=>C('option_ok')
					)
				);
			}
		
			if(isset($content['status_code']))
			{
				return array(
					200,
					array(
						'is_success'=>$content['status_code'],
						'message'=> $content['message'],
					),
				);
			}
			return array(
					200,
					array(
						'is_success'=>-1,
						'message'=>C('option_fail'),
					),
				);
	}
	
	private function call_SetUserState($uid, $state, $content)
	{
		$params = array(
			'uid'       => $uid,
			'state'     => $state
		);
		$params['safekey']  = $this->mk_passwd($params, 6);
		$url = C('api_user_url').$this->USER_API_METHOD_LIST['update_status'];
		$back_str = $this->post($url, $params);
		$re_json = json_decode($back_str, true);
		if($re_json
		&& 1 == $re_json['State'])
		{	
			return true;
		}
		elseif(-3 == $re_json['State'])
		{
			$content['status_code'] = -2;
			$content['message']     = urlencode('用户不存在');
			return false;
		}

		return false;
	}

	#更新用户登录ip
	public function update_ip($content)
	/*
	@@input
	@param $uid      用户id
	@param $blackip  用户登录IP黑名单，多个IP用竖线分隔，支持带*号IP段
	@@output
	@param $is_success 0-操作成功,-1-操作失败,-3-用户不存在
	*/
	{
		$data = $this->fill($content);
		if(!isset($data['uid'])
		|| !isset($data['blackip'])
		)
		{
			return C('param_err');
		}
			
		$data['uid'] = intval($data['uid']);
		$data['blackip'] = $data['blackip'];
		
		if(0>= $data['uid'])
		{
			return C('param_fmt_err');
		}
			
		unset($content);
		$content = array();
		if($this->call_SetUserBlackIp($data['uid'],
									$data['blackip'], 
									&$content))
		{
			return array(
				200,
				array(
					'is_success'=>0,
					'message'=>C('option_ok')
				)
			);
		}
		
		if(isset($content['status_code']))
		{
			return array(
				200,
				array(
					'is_success'=>$content['status_code'],
					'message'=> $content['message'],
				),
			);
		}
		return array(
				200,
				array(
					'is_success'=>-1,
					'message'=>C('option_fail'),
				),
			);
	}
	
	private function call_SetUserBlackIp($uid, $blackip, $content)
	{
		$params = array(
			'uid'       => $uid,
			'blackip'     => $blackip
		);
		$params['safekey']  = $this->mk_passwd($params, 7);
		$url = C('api_user_url').$this->USER_API_METHOD_LIST['update_ip'];
		$back_str = $this->post($url, $params);
		$re_json = json_decode($back_str, true);
		if($re_json
		&& 1 == $re_json['State'])
		{	
			return true;
		}
		elseif(-3 == $re_json['State'])
		{
			$content['status_code'] = -2;
			$content['message']     = urlencode('用户不存在');
			return false;
		}

		return false;
	}
	
	
	#获取帐号数
	public function get_login_amount($content)
	/*
	@@output
	@amount 帐号数
	*/
	{
		$amount = 0;
		
		$content = array();
		if($this->call_CountUserLogin(&$content))
		{
			return array(
				200,
				array(
					'is_success'=>0,
					'amount'    => $content['amount'],
					'message'=>C('option_ok')
				)
			);
		}
		
		if(isset($content['status_code']))
		{
			return array(
				200,
				array(
					'is_success'=>$content['status_code'],
					'message'=> $content['message'],
				),
			);
		}
		return array(
				200,
				array(
					'is_success'=>-1,
					'message'=>C('option_fail'),
				),
			);
	}
	
	public function call_CountUserLogin($content)
	{
		$params = array(
			'yyyyMMdd' =>  date("Ymd"),
		);
		$params['safekey']  = $this->mk_passwd($params, 8);
		$url = C('api_user_url').$this->USER_API_METHOD_LIST['get_login_amount'];
		$back_str = $this->post($url, $params);
		$re_json = json_decode($back_str, true);
		if($re_json
		&& 1 <= $re_json['State'])
		{	
			$content['amount'] = $re_json['State'];
			return true;
		}
		elseif(0 == $re_json['State'])
		{
			$content['status_code'] = -2;
			$content['message']     = urlencode('用户不存在');
			return false;
		}

		return false;
	}
	
	#获取用户数
	public function get_user_amount($content)
	/*
	@amount 用户数
	*/
	{
		$amount = 0;
		
		$content = array();
		if($this->call_CountUserInfo(&$content))
		{
			return array(
				200,
				array(
					'is_success'=>0,
					'amount'    => $content['amount'],
					'message'=>C('option_ok')
				)
			);
		}
		
		if(isset($content['status_code']))
		{
			return array(
				200,
				array(
					'is_success'=>$content['status_code'],
					'message'=> $content['message'],
				),
			);
		}
		return array(
				200,
				array(
					'is_success'=>-1,
					'message'=>C('option_fail'),
				),
			);
		
		return array(
			200,
			array(
				'amount'=>$amount
			)
		);
	}
	
	private function call_CountUserInfo($content)
	{
		$params = array(
			'yyyyMMdd' =>  date("Ymd"),
		);
		$params['safekey']  = $this->mk_passwd($params, 8);
		$url = C('api_user_url').$this->USER_API_METHOD_LIST['get_user_amount'];
		$back_str = $this->post($url, $params);
		$re_json = json_decode($back_str, true);
		if($re_json
		&& 1 <= $re_json['State'])
		{	
			$content['amount'] = $re_json['State'];
			return true;
		}
		elseif(0 == $re_json['State'])
		{
			$content['status_code'] = -2;
			$content['message']     = urlencode('用户不存在');
			return false;
		}

		return false;
	}
	
	#绑定微信帐号
	public function login_weixin($content)
	/*
	@@input
	@param $openid 微信返回id
	@@output
	@param $is_success 0-成功,-1-失败,-2-微信OpenId参数不合法,-3-微信OpenId不存在或密码错误,
	* -4-用户被限制登录,-5-用户访问的IP被限制,-6-接口报错
	* -100-输入的参数存在空值
	@param $user_id 用户id
	@param $head_portrait 头像
	@param $nickname 用户昵称
	@param $sex 用户性别
	@param $cur_date 当前日期
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['openid']))
		{
			return C('param_err');
		}
		
		$data['openid'] = htmlspecialchars($data['openid']);
		
		if('' == $data['openid'])
		{
			return C('param_fmt_err');
		}
		
		$content = array();
		if($this->call_LoginByWeixinOpenid($data['openid'], &$content))
		{
			return array(
				200,
				array(
					'is_success'    =>0,
					'user_id'       => $content['user_id'],
					'head_portrait' => $content['head_portrait'],
					'nickname'      => $content['nickname'],
					'sex'           => $content['sex'],
					'cur_date'      => $content['cur_date'],
					'message'=>C('option_ok')
				)
			);
		}
		if(isset($content['status_code']))
		{
			return array(
				200,
				array(
					'is_success'=>$content['status_code'],
					'message'=>$content['message'],
				)
			);
		}
		
		return array(
				200,
				array(
					'is_success'=>-1,
					'message'=>C('option_fail'),
				),
		);		
	}
	
	private function call_LoginByWeixinOpenid($openid, $content)
	{
		$params = array(
			'openid'=>$openid,
			'userip'=>$this->get_real_ip(),
		);
		$url = C('api_user_url').$this->USER_API_METHOD_LIST['login_weixin'];		
		$back_str = $this->post($url, $params);
		$re_json = json_decode($back_str, true);
		if($re_json
		&& 1 <= $re_json['State'])
		{	
			$back_content = $re_json['Descr'];
			$r_list = explode('|', $back_content);
			$index=0;
			$content['user_id']  = $r_list[$index++];
			$content['nickname'] = $r_list[$index++];
			$content['head_portrait'] = $r_list[$index++];
			$content['sex']      = $r_list[$index++];
			$content['cur_date'] = $r_list[$index++];
			$content['head_portrait'] = C('api_user_photo_url').$content['head_portrait'];
			$content['head_portrait'] = str_replace('user','',$content['head_portrait']);
			return true;
		}
		elseif(-1 == $re_json['State'])
		{
			$content['status_code'] = -100;
			$content['message']     = urlencode('输入的参数存在空值');
			return false;
		}
		elseif(-2 == $re_json['State'])
		{
			$content['status_code'] = -2;
			$content['message']     = urlencode('微信OpenId参数不合法');
			return false;
		}
		elseif(-3 == $re_json['State'])
		{
			$content['status_code'] = -3;
			$content['message']     = urlencode('微信OpenId不存在或密码错误');
			return false;
		}
		elseif(-4 == $re_json['State'])
		{
			$content['status_code'] = -4;
			$content['message']     = urlencode('用户被限制登录');
			return false;
		}
		elseif(-5 == $re_json['State'])
		{
			$content['status_code'] = -5;
			$content['message']     = urlencode('用户访问的IP被限制');
			return false;
		}
		elseif(0 == $re_json['State'])
		{
			$content['status_code'] = -6;
			$content['message']     = urlencode('接口报错');
			return false;
		}

		return false;
	}
	
	#微信注册
	public function register_weixin($content)
	/*
	@@input
	@param $openid    微信openid
	@param $nickname  昵称
	@@output
	@param $is_success
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['openid'])
		)
		{
			return C('param_err');
		}
		
		$data['openid'] = htmlspecialchars($data['openid']);
		
		if('' == $data['openid'])
		{
			return C('param_fmt_err');
		}
		
		$content = array();
		if($this->call_RegisterByWeixinOpenid($data['openid'],$data['nickname'], &$content))
		{
			//同步用户信息
			$user_info = array(
				'user_id'=>$content['user_id'],
				'nickname'=>$content['nickname'],
			);
			A('Soapi/Member')->add(json_encode($user_info));
			return array(
				200,
				array(
					'is_success'=>0,
					'head_portrait'   =>$content['head_portrait'],
					'user_id'  		  =>$content['user_id'],
					'nickname' 		  =>$content['nickname'],
					'sex'      		  =>$content['sex'],
					'cur_date'   	  =>$content['cur_date'],
					'message'=>C('option_ok')
				)
			);
		}
		if(isset($content['status_code']))
		{
			return array(
				200,
				array(
					'is_success'=>$content['status_code'],
					'message'=>$content['message'],
				)
			);
		}
		
		return array(
				200,
				array(
					'is_success'=>-1,
					'message'=>C('option_fail'),
				),
		);		
		
	}
	
	private function call_RegisterByWeixinOpenid($openid, $nickname='', $content)
	{
		$params = array(
			'nickname'=>'' == $nickname?$this->make_nickname():$nickname,
			'openid'=>$openid,
			'userip'=>$this->get_real_ip(),
		);
		$params['safekey']  = $this->mk_passwd($params, 9);
		$url = C('api_user_url').$this->USER_API_METHOD_LIST['register_weixin'];
		$back_str = $this->post($url, $params);
		$re_json = json_decode($back_str, true);
		if($re_json
		&& 1 <= $re_json['State'])
		{	
			$back_content = $re_json['Descr'];
			$r_list = explode('|', $back_content);
			$index=0;
			$content['user_id']  = $r_list[$index++];
			$content['nickname'] = $r_list[$index++];
			$content['head_portrait'] = $r_list[$index++];
			$content['sex']      = $r_list[$index++];
			$content['cur_date'] = $r_list[$index++];
			$content['head_portrait'] = C('api_user_photo_url').$content['head_portrait'];
			$content['head_portrait'] = str_replace('user','',$content['head_portrait']);
			return true;
		}
		elseif(-1 == $re_json['State'])
		{
			$content['status_code'] = -100;
			$content['message']     = urlencode('输入的参数存在空值');
			return false;
		}
		elseif(-2 == $re_json['State'])
		{
			$content['status_code'] = -2;
			$content['message']     = urlencode('微信openid参数不合法');
			return false;
		}
		elseif(-3 == $re_json['State'])
		{
			$content['status_code'] = -3;
			$content['message']     = urlencode('safekey参数不合法');
			return false;
		}
		elseif(-4 == $re_json['State'])
		{
			$content['status_code'] = -4;
			$content['message']     = urlencode('微信openid已存在');
			return false;
		}
		elseif(0 == $re_json['State'])
		{
			$content['status_code'] = -6;
			$content['message']     = urlencode('接口报错');
			return false;
		}

		return false;
	}
	
	#绑定微信
	public function bind_weixin($content)
	/*
	@@input
	@param $uid   用户id
	@param $openid  微信id
	@@output
	@param $is_success 0-成功，-1-操作失败,-2-微信openid参数不合法,
	                   -3-safekey参数不合法,-4-微信openid已存在,
			   -6-绑定失败
	@param $user_id 用户id
	@param $head_portrait 头像
	@param $nickname 用户昵称
	@param $sex 用户性别
	@param $cur_date 当前日期
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['uid'])
		|| !isset($data['openid'])
		)
		{
			return C('param_err');
		}
		
		$data['uid'] = intval($data['uid']);
		$data['openid'] = htmlspecialchars($data['openid']);
		
		if(0>= $data['uid']
		|| '' == $data['openid']
		)
		{
			return C('param_fmt_err');
		}
		
		$content = array();
		if($this->call_BindUserLoginByWeixinOpenid($data['uid'],$data['openid'], &$content))
		{
			return array(
				200,
				array(
					'is_success'=>0,
					'user_id'       => $content['user_id'],
					'head_portrait' => $content['head_portrait'],
					'nickname'      => $content['nickname'],
					'sex'           => $content['sex'],
					'cur_date'      => $content['cur_date'],
					'message'=>C('option_ok')
				)
			);
		}
		if(isset($content['status_code']))
		{
			return array(
				200,
				array(
					'is_success'=>$content['status_code'],
					'message'=>$content['message'],
				)
			);
		}
		
		return array(
				200,
				array(
					'is_success'=>-1,
					'message'=>C('option_fail'),
				),
		);		
	}
	
	private function call_BindUserLoginByWeixinOpenid($uid, $openid, $content)
	{
		$params = array(
			'ui_id'=>$uid,
			'openid'=>$openid,
			'userip'=>$this->get_real_ip(),
		);
		//var_dump($params);
		$params['safekey']  = $this->mk_passwd($params, 10);
		//var_dump($params);
		$url = C('api_user_url').$this->USER_API_METHOD_LIST['bind_weixin'];
		$back_str = $this->post($url, $params);
		//var_dump($back_str);
		$re_json = json_decode($back_str, true);
		if($re_json
		&& 1 <= $re_json['State'])
		{	
			//$content['user_info'] = $re_json['Descr'];
			$back_content = $re_json['Descr'];
			$r_list = explode('|', $back_content);
			$index=0;
			$content['user_id']  = $r_list[$index++];
			$content['nickname'] = $r_list[$index++];
			$content['head_portrait'] = $r_list[$index++];
			$content['sex']      = $r_list[$index++];
			$content['cur_date'] = $r_list[$index++];
			$content['head_portrait'] = C('api_user_photo_url').$content['head_portrait'];
			$content['head_portrait'] = str_replace('user','',$content['head_portrait']);
			return true;
		}
		elseif(-1 == $re_json['State'])
		{
			$content['status_code'] = -100;
			$content['message']     = urlencode('输入的参数存在空值');
			return false;
		}
		elseif(-2 == $re_json['State'])
		{
			$content['status_code'] = -2;
			$content['message']     = urlencode('微信openid参数不合法');
			return false;
		}
		elseif(-3 == $re_json['State'])
		{
			$content['status_code'] = -3;
			$content['message']     = urlencode('safekey参数不合法');
			return false;
		}
		elseif(-4 == $re_json['State'])
		{
			$content['status_code'] = -4;
			$content['message']     = urlencode('微信openid已存在');
			return false;
		}
		elseif(0 == $re_json['State'])
		{
			$content['status_code'] = -6;
			$content['message']     = urlencode('绑定失败');
			return false;
		}

		return false;
	}
	
	#微信入口
	public function entry_weixin($content)
	/*
	@@intput
	@param $openid   微信id 
	@param $mobile   手机号码
	@param $passwd   密码
	@param $nickname 昵称
	@param $head_photo 头像
	@param $sem      是否推广用户(默认0，1-为推广用户)
	@@output
	@param $is_success  是否成功(0-成功,-1-操作失败，-2-密码错误)
	@param $user_info   用户信息
	*/
	/***
	 * logic:
	 * 1.检查是否跳过;
	 * 2.如果跳过，微信单独注册;
	 * 3.不是2步骤,检查手机号码是否存在，如果不存在,则进行手机号码+密码的注册,并且绑定微信和修改昵称+头像;
	 * 4.如果手机号码存在，检查密码是否存在，如果密码不正确，返回密码错误;
	 * 5.如果密码正确，绑定微信openid;
	 * */
	{
		$data = $this->fill($content);
		if(!isset($data['openid']))
		{
			return C('param_err');
		}		
		$data['openid']     = htmlspecialchars(trim($data['openid']));
		$data['mobile']     = htmlspecialchars(trim($data['mobile']));
		$data['passwd']     = htmlspecialchars(trim($data['passwd']));
		$data['nickname']   = htmlspecialchars(trim($data['nickname']));
		$data['head_photo'] = htmlspecialchars(trim($data['head_photo']));
		$data['sem']        = intval(trim($data['sem']));
		
		if('' == $data['openid'])
		{
			return C('param_fmt_err');
		}
		#1.检查是否跳过;
		if('' == $data['mobile']
		&& '' == $data['passwd']
		//&& '' == $data['nickname']
		//&& '' == $data['head_photo']
		)
		{
			#2.如果跳过，微信单独注册;
			//todo:
			$params = array(
				'openid'=> $data['openid'],
				'nickname'=> $data['nickname'],
			);
			$re_back = $this->register_weixin(json_encode($params));
			if($_r = $this->tmp_upload_head($re_back, $data))
				return $_r;
			return $re_back;
		}
		else
		{
			#3.不是2步骤,检查手机号码是否存在，如果不存在,则进行手机号码+密码的注册,并且绑定微信和修改昵称+头像;
			$params = array(
				'mobile' => $data['mobile'],
			);
			list($status_code, $content) = $this->check_mobile(json_encode($params));
			if(200 == $status_code
			&& -1 == $content['is_exists'])#手机号码不存在
			{				
				#进行手机号码+密码的注册,并且绑定微信和修改昵称+头像;
				$params = array(
					'mobile'  =>$data['mobile'],
					'pswd'    =>$data['passwd'],
					'nickname'=>$data['nickname'],
					'sem'     =>$data['sem'],
				);
				list($status_code, $content) = $this->register(json_encode($params));
				//var_dump($content);
				if(200 == $status_code
				&& 0 == $content['is_success']
				)
				{
					$params = array(
						'openid'=>$data['openid'],
						'uid'   =>$content['user_id'],
					);
					#绑定微信号码
					list($status_code, $content) = $this->bind_weixin(json_encode($params));
					if(200 == $status_code
					&& 0 == $content['is_success']
					)
					{
						#登录
						$params = array(
							'mobile'=>$data['mobile'],
							'pswd'=>$data['passwd'],
						);
						$re_back = $this->login(json_encode($params));
						#修改头像
						if($_r = $this->tmp_upload_head($re_back, $data))
							return $_r;	
						return $re_back;
					}
				}
				else
				{
					$content['module'] = urlencode('注册模块');
					return array($status_code,$content);
				}
			}
			
			$params = array(
				'mobile'=>$data['mobile'],
				'pswd'=>$data['passwd'],
			);
			list($status_code, $content) = $this->login(json_encode($params));
			#检查帐号不正确
			if(200 == $status_code
			&& -2 == $content['is_success']
			)
			{
				#返回密码错误
				return array(
					200,
					array(
						'is_success'=>-2,
						'message'   =>urlencode('密码错误'),
					)
				);
			}
			
			#5.如果密码正确，绑定微信openid;
			$params = array(
						'openid'=>$data['openid'],
						'uid'   =>$content['user_id'],
			);
			#绑定微信号码
			list($status_code, $content) = $this->bind_weixin(json_encode($params));
			
			if(200 == $status_code
			&& 0 == $content['is_success']
			)
			{
				#登录
				$params = array(
					'mobile'=>$data['mobile'],
					'pswd'=>$data['passwd'],
				);
				return $this->login(json_encode($params));
			}
			else
			{
				return array(
					$status_code,
					$content
				);
			}
			
			return array(
				200,
				array(
					'is_success'=>-1,
					'message'=>urlencode('操作失败'),
				),
			);
		}
	}
	
	#检查登录名是否存在
	public function check_loginname($content)
	/*
	@@input
	@param $loginname 登录名
	@param $logintype 登录类型(=4,loginname=微信openid),(=5,loginname=QQopenid),
	*                        (=6,loginname=微博OpenId)
	@@output
	@param $is_success 0-存在,-1-不存在,-2-loginname参数不合法,-3-safekey参数不合法
	*/
	{
		$data = $this->fill($content);
		if(!isset($data['loginname'])
		|| !isset($data['logintype'])
		)
		{
			return C('param_err');
		}
		
		$data['loginname'] = htmlspecialchars(trim($data['loginname']));
		$data['logintype'] = intval($data['logintype']);
		
		if('' == $data['loginname']
		|| 0 >= $data['logintype']
		)
		{
			return C('param_fmt_err');
		}
		
		$content = array();
		if($this->call_ExistsUserInfoByLoginName($data['loginname'],$data['logintype'], &$content))
		{
			$re_back = array();
			//登录
			switch($data['logintype'])
			{
				case 4://微信
					{
						$param = array(
							'openid'=>$data['loginname']
						);
						$re_back = $this->login_weixin(json_encode($param));
					}				break;
				case 5://qq
					{
						$param = array(
							'openid'=>$data['loginname']
						);
						$re_back = $this->login_qq(json_encode($param));
					}
					break;
				case 6://微博
					{
						$param = array(
							'openid'=>$data['loginname']
						);
						$re_back = $this->login_weibo(json_encode($param));
					}
					break;
			}
			return array(
				200,
				array(
					'is_exists'=>0,
					'message'=>C('is_exists'),
					'user_id'       => $re_back[1]['user_id'],
					'head_portrait' => $re_back[1]['head_portrait'],
					'nickname'      => $re_back[1]['nickname'],
					'sex'           => $re_back[1]['sex'],
					'cur_date'      => $re_back[1]['cur_date'],
					
					
				),
			);
		}
		
		if(isset($content['status_code']))
		{
			return array(
				200,
				array(
					'is_success'=>$content['status_code'],
					'message'   =>$content['message'],
				)
			);
		}
		
		return array(
				200,
				array(
					'is_exists'=>-1,
					'message'=>C('no_exists'),
				),
			);
		
	}
	
	#通过解除登录账号绑定
	public function cancel_bind($content)
	/*
	@@input
	@param $uid        用户id
	@param $logintype  登录类型(=0,loginname=用户名),(=1,loginname=手机号),
	*                         (=2,loginname=邮箱),(=3,loginname=QQ号),
	*                         (=4,loginname=微信OpenId),(=5,loginname=QQOpenId),
	*                         (=6,loginname=微博OpenId)
	@param $loginname
	@@output
	@param $is_success 0-成功,-1-操作错误,-2-loginname参数不合法,-3-safekey参数不合法
	*/
	{
		
		$data = $this->fill($content);
		
		if(!isset($data['uid'])
		|| !isset($data['logintype'])
		|| !isset($data['loginname'])
		)
		{
			return C('param_err');
		}
		
		$data['uid'] = intval($data['uid']);
		$data['logintype'] = intval($data['logintype']);
		$data['loginname'] = htmlspecialchars(trim($data['loginname']));
		
		if(0>= $data['uid']
		|| 0 > $data['logintype']
		|| '' == $data['loginname']
		)
		{
			return C('param_fmt_err');
		}
		
		$content = array();
		if($this->call_CanelBingUserLogin($data['uid'],$data['logintype'], 
		                                  $data['loginname'], &$content))
		{			
			return array(
				200,
				array(
					'is_success'=>0,
					'message'=>C('option_ok')
				)
			);
		}
		if(isset($content['status_code']))
		{
			return array(
				200,
				array(
					'is_success'=>$content['status_code'],
					'message'=>$content['message'],
				)
			);
		}
		
		return array(
				200,
				array(
					'is_success'=>-1,
					'message'=>C('option_fail'),
				),
		);		
	}
	
	private function call_CanelBingUserLogin($uid, $logintype, $loginname, $content)
	{
		$params = array(
			'ui_id'=>$uid,
			'logintype'=>$logintype,
			'loginname'=>$loginname,
		);
		$params['safekey']  = $this->mk_passwd($params, 11);
		$url = C('api_user_url').$this->USER_API_METHOD_LIST['cancel_bind'];
		$back_str = $this->post($url, $params);
		$re_json = json_decode($back_str, true);
		if($re_json
		&& 1 <= $re_json['State'])
		{	
			$content['user_info'] = $re_json['Descr'];
			return true;
		}
		elseif(-2 == $re_json['State'])
		{
			$content['status_code'] = -2;
			$content['message']     = urlencode('loginname参数不合法');
			return false;
		}
		elseif(-3 == $re_json['State'])
		{
			$content['status_code'] = -3;
			$content['message']     = urlencode('safekey参数不合法');
			return false;
		}

		return false;
	}
	
	#微博openid注册
	public function register_weibo($content)
	/*
	@@input
	@param $nickname 昵称
	@param $openid   微博openid
	@param $userip   用户ip
	@@output
	@param $is_success
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['openid'])
		)
		{
			return C('param_err');
		}
		
		$data['openid'] = htmlspecialchars($data['openid']);
		
		if('' == $data['openid'])
		{
			return C('param_fmt_err');
		}
		
		$content = array();
		if($this->call_RegisterByWeiboOpenid($data['openid'],$data['nickname'],$data['userip'], &$content))
		{
			//同步用户信息
			$user_info = array(
				'user_id'=>$content['user_id'],
				'nickname'=>$content['nickname'],
			);
			A('Soapi/Member')->add(json_encode($user_info));
			return array(
				200,
				array(
					'is_success'=>0,
					'head_portrait'   =>$content['head_portrait'],
					'user_id'  		  =>$content['user_id'],
					'nickname' 		  =>$content['nickname'],
					'sex'      		  =>$content['sex'],
					'cur_date'   	  =>$content['cur_date'],
					'message'=>C('option_ok')
				)
			);
		}
		if(isset($content['status_code']))
		{
			return array(
				200,
				array(
					'is_success'=>$content['status_code'],
					'message'=>$content['message'],
				)
			);
		}
		
		return array(
				200,
				array(
					'is_success'=>-1,
					'message'=>C('option_fail'),
				),
		);		
	}
	
	private function call_RegisterByWeiboOpenid($openid, $nickname='',$userip='', $content)
	{
		$params = array(
			'nickname'=>'' == $nickname?$this->make_nickname():$nickname,
			'openid'=>$openid,
			'userip'=>'' == $userip?$this->get_real_ip():$userip,
		);
		$params['safekey']  = $this->mk_passwd($params, 9);
		$url = C('api_user_url').$this->USER_API_METHOD_LIST['register_weibo'];
		$back_str = $this->post($url, $params);
		$re_json = json_decode($back_str, true);
		if($re_json
		&& 1 <= $re_json['State'])
		{	
			$back_content = $re_json['Descr'];
			$r_list = explode('|', $back_content);
			$index=0;
			$content['user_id']  = $r_list[$index++];
			$content['nickname'] = $r_list[$index++];
			$content['head_portrait'] = $r_list[$index++];
			$content['sex']      = $r_list[$index++];
			$content['cur_date'] = $r_list[$index++];
			$content['head_portrait'] = C('api_user_photo_url').$content['head_portrait'];
			$content['head_portrait'] = str_replace('user','',$content['head_portrait']);
			return true;
		}
		elseif(-1 == $re_json['State'])
		{
			$content['status_code'] = -100;
			$content['message']     = urlencode('输入的参数存在空值');
			return false;
		}
		elseif(-2 == $re_json['State'])
		{
			$content['status_code'] = -2;
			$content['message']     = urlencode('微信openid参数不合法');
			return false;
		}
		elseif(-3 == $re_json['State'])
		{
			$content['status_code'] = -3;
			$content['message']     = urlencode('safekey参数不合法');
			return false;
		}
		elseif(-4 == $re_json['State'])
		{
			$content['status_code'] = -4;
			$content['message']     = urlencode('微信openid已存在');
			return false;
		}
		elseif(0 == $re_json['State'])
		{
			$content['status_code'] = -6;
			$content['message']     = urlencode('接口报错');
			return false;
		}

		return false;
	}

	#qq的opendid注册
	public function register_qq($content)
	/*
	@@input
	@param $nickname 昵称
	@param $openid   qq的openid
	@param $userip   用户ip
	@@output
	@param $is_success
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['openid'])
		)
		{
			return C('param_err');
		}
		
		$data['openid'] = htmlspecialchars($data['openid']);
		
		if('' == $data['openid'])
		{
			return C('param_fmt_err');
		}
		
		$content = array();
		if($this->call_RegisterByQQOpenid($data['openid'],$data['nickname'],$data['userip'], &$content))
		{
			//同步用户信息
			$user_info = array(
				'user_id'=>$content['user_id'],
				'nickname'=>$content['nickname'],
			);
			A('Soapi/Member')->add(json_encode($user_info));
			return array(
				200,
				array(
					'is_success'=>0,
					'head_portrait'   =>$content['head_portrait'],
					'user_id'  		  =>$content['user_id'],
					'nickname' 		  =>$content['nickname'],
					'sex'      		  =>$content['sex'],
					'cur_date'   	  =>$content['cur_date'],
					'message'=>C('option_ok')
				)
			);
		}
		if(isset($content['status_code']))
		{
			return array(
				200,
				array(
					'is_success'=>$content['status_code'],
					'message'=>$content['message'],
				)
			);
		}
		
		return array(
				200,
				array(
					'is_success'=>-1,
					'message'=>C('option_fail'),
				),
		);		
	}
	
	private function call_RegisterByQQOpenid($openid, $nickname='', $userip='', $content)
	{
		$params = array(
			'nickname'=>'' == $nickname?$this->make_nickname():$nickname,
			'openid'=>$openid,
			'userip'=>'' == $userip?$this->get_real_ip():$userip,
		);
		$params['safekey']  = $this->mk_passwd($params, 9);
		$url = C('api_user_url').$this->USER_API_METHOD_LIST['register_qq'];
		$back_str = $this->post($url, $params);
		$re_json = json_decode($back_str, true);
		if($re_json
		&& 1 <= $re_json['State'])
		{	
			$back_content = $re_json['Descr'];
			$r_list = explode('|', $back_content);
			$index=0;
			$content['user_id']  = $r_list[$index++];
			$content['nickname'] = $r_list[$index++];
			$content['head_portrait'] = $r_list[$index++];
			$content['sex']      = $r_list[$index++];
			$content['cur_date'] = $r_list[$index++];
			$content['head_portrait'] = C('api_user_photo_url').$content['head_portrait'];
			$content['head_portrait'] = str_replace('user','',$content['head_portrait']);
			return true;
		}
		elseif(-1 == $re_json['State'])
		{
			$content['status_code'] = -100;
			$content['message']     = urlencode('输入的参数存在空值');
			return false;
		}
		elseif(-2 == $re_json['State'])
		{
			$content['status_code'] = -2;
			$content['message']     = urlencode('微信openid参数不合法');
			return false;
		}
		elseif(-3 == $re_json['State'])
		{
			$content['status_code'] = -3;
			$content['message']     = urlencode('safekey参数不合法');
			return false;
		}
		elseif(-4 == $re_json['State'])
		{
			$content['status_code'] = -4;
			$content['message']     = urlencode('微信openid已存在');
			return false;
		}
		elseif(0 == $re_json['State'])
		{
			$content['status_code'] = -6;
			$content['message']     = urlencode('接口报错');
			return false;
		}

		return false;
	}


	#微博openid登录
	public function login_weibo($content)
	/*
	@@input
	@param $openid  微博openid
	@param $userip  用户ip
	@@output
	@param $content 返回用户信息
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['openid']))
		{
			return C('param_err');
		}
		
		$data['openid'] = htmlspecialchars($data['openid']);
		
		if('' == $data['openid'])
		{
			return C('param_fmt_err');
		}
		
		$content = array();
		if($this->call_LoginByWeiboOpenid($data['openid'], $data['userip'],&$content))
		{
			return array(
				200,
				array(
					'is_success'    =>0,
					'user_id'       => $content['user_id'],
					'head_portrait' => $content['head_portrait'],
					'nickname'      => $content['nickname'],
					'sex'           => $content['sex'],
					'cur_date'      => $content['cur_date'],
					'message'=>C('option_ok')
				)
			);
		}
		if(isset($content['status_code']))
		{
			return array(
				200,
				array(
					'is_success'=>$content['status_code'],
					'message'=>$content['message'],
				)
			);
		}
		
		return array(
				200,
				array(
					'is_success'=>-1,
					'message'=>C('option_fail'),
				),
		);		
	}
	
	private function call_LoginByWeiboOpenid($openid, $userip, $content)
	{
		$params = array(
			'openid'=>$openid,
			'userip'=>''==$userip?$this->get_real_ip():$userip,
		);
		$url = C('api_user_url').$this->USER_API_METHOD_LIST['login_weibo'];		
		$back_str = $this->post($url, $params);
		$re_json = json_decode($back_str, true);
		if($re_json
		&& 1 <= $re_json['State'])
		{	
			$back_content = $re_json['Descr'];
			$r_list = explode('|', $back_content);
			$index=0;
			$content['user_id']  = $r_list[$index++];
			$content['nickname'] = $r_list[$index++];
			$content['head_portrait'] = $r_list[$index++];
			$content['sex']      = $r_list[$index++];
			$content['cur_date'] = $r_list[$index++];
			$content['head_portrait'] = C('api_user_photo_url').$content['head_portrait'];
			$content['head_portrait'] = str_replace('user','',$content['head_portrait']);
			return true;
		}
		elseif(-1 == $re_json['State'])
		{
			$content['status_code'] = -100;
			$content['message']     = urlencode('输入的参数存在空值');
			return false;
		}
		elseif(-2 == $re_json['State'])
		{
			$content['status_code'] = -2;
			$content['message']     = urlencode('微博OpenId参数不合法');
			return false;
		}
		elseif(-3 == $re_json['State'])
		{
			$content['status_code'] = -3;
			$content['message']     = urlencode('微博OpenId不存在或密码错误');
			return false;
		}
		elseif(-4 == $re_json['State'])
		{
			$content['status_code'] = -4;
			$content['message']     = urlencode('用户被限制登录');
			return false;
		}
		elseif(-5 == $re_json['State'])
		{
			$content['status_code'] = -5;
			$content['message']     = urlencode('用户访问的IP被限制');
			return false;
		}
		elseif(0 == $re_json['State'])
		{
			$content['status_code'] = -6;
			$content['message']     = urlencode('接口报错');
			return false;
		}

		return false;
	}

	#qq的opendid登录
	public function login_qq($content)
	/*
	@@input
	@param $openid  微博openid
	@param $userip  用户ip
	@@output
	@param $content 返回用户信息
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['openid']))
		{
			return C('param_err');
		}
		
		$data['openid'] = htmlspecialchars($data['openid']);
		
		if('' == $data['openid'])
		{
			return C('param_fmt_err');
		}
		
		$content = array();
		if($this->call_LoginByQQOpenid($data['openid'], $data['userip'],&$content))
		{
			return array(
				200,
				array(
					'is_success'    =>0,
					'user_id'       => $content['user_id'],
					'head_portrait' => $content['head_portrait'],
					'nickname'      => $content['nickname'],
					'sex'           => $content['sex'],
					'cur_date'      => $content['cur_date'],
					'message'=>C('option_ok')
				)
			);
		}
		if(isset($content['status_code']))
		{
			return array(
				200,
				array(
					'is_success'=>$content['status_code'],
					'message'=>$content['message'],
				)
			);
		}
		
		return array(
				200,
				array(
					'is_success'=>-1,
					'message'=>C('option_fail'),
				),
		);		
	}
	
	private function call_LoginByQQOpenid($openid, $userip, $content)
	{
		$params = array(
			'openid'=>$openid,
			'userip'=>''==$userip?$this->get_real_ip():$userip,
		);
		$url = C('api_user_url').$this->USER_API_METHOD_LIST['login_qq'];		
		$back_str = $this->post($url, $params);
		$re_json = json_decode($back_str, true);
		if($re_json
		&& 1 <= $re_json['State'])
		{	
			$back_content = $re_json['Descr'];
			$r_list = explode('|', $back_content);
			$index=0;
			$content['user_id']  = $r_list[$index++];
			$content['nickname'] = $r_list[$index++];
			$content['head_portrait'] = $r_list[$index++];
			$content['sex']      = $r_list[$index++];
			$content['cur_date'] = $r_list[$index++];
			$content['head_portrait'] = C('api_user_photo_url').$content['head_portrait'];
			$content['head_portrait'] = str_replace('user','',$content['head_portrait']);
			return true;
		}
		elseif(-1 == $re_json['State'])
		{
			$content['status_code'] = -100;
			$content['message']     = urlencode('输入的参数存在空值');
			return false;
		}
		elseif(-2 == $re_json['State'])
		{
			$content['status_code'] = -2;
			$content['message']     = urlencode('微信OpenId参数不合法');
			return false;
		}
		elseif(-3 == $re_json['State'])
		{
			$content['status_code'] = -3;
			$content['message']     = urlencode('微信OpenId不存在或密码错误');
			return false;
		}
		elseif(-4 == $re_json['State'])
		{
			$content['status_code'] = -4;
			$content['message']     = urlencode('用户被限制登录');
			return false;
		}
		elseif(-5 == $re_json['State'])
		{
			$content['status_code'] = -5;
			$content['message']     = urlencode('用户访问的IP被限制');
			return false;
		}
		elseif(0 == $re_json['State'])
		{
			$content['status_code'] = -6;
			$content['message']     = urlencode('接口报错');
			return false;
		}

		return false;
	}
	
	#绑定微博(openid)
	public function bind_weibo($content)
	/*
	@@input
	@param $ui_id   用户id
	@param $openid  微博openid
	@param $userip  用户ip
	@@output
	@param $is_success
	@param $is_success 0-成功，-1-操作失败,-2-微信openid参数不合法,
	                   -3-safekey参数不合法,-4-微信openid已存在,
			   -6-绑定失败
	@param $user_id 用户id
	@param $head_portrait 头像
	@param $nickname 用户昵称
	@param $sex 用户性别
	@param $cur_date 当前日期
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['uid'])
		|| !isset($data['openid'])
		)
		{
			return C('param_err');
		}
		
		$data['uid'] = intval($data['uid']);
		$data['openid'] = htmlspecialchars($data['openid']);
		
		if(0>= $data['uid']
		|| '' == $data['openid']
		)
		{
			return C('param_fmt_err');
		}
		
		$content = array();
		if($this->call_BindUserLoginByWeiboOpenid($data['uid'],$data['openid'], &$content))
		{
			return array(
				200,
				array(
					'is_success'=>0,
					'user_id'       => $content['user_id'],
					'head_portrait' => $content['head_portrait'],
					'nickname'      => $content['nickname'],
					'sex'           => $content['sex'],
					'cur_date'      => $content['cur_date'],
					'message'=>C('option_ok')
				)
			);
		}
		if(isset($content['status_code']))
		{
			return array(
				200,
				array(
					'is_success'=>$content['status_code'],
					'message'=>$content['message'],
				)
			);
		}
		
		return array(
				200,
				array(
					'is_success'=>-1,
					'message'=>C('option_fail'),
				),
		);		
	}
	
	private function call_BindUserLoginByWeiboOpenid($uid, $openid, $userip='', $content)
	{
		$params = array(
			'ui_id'=>$uid,
			'openid'=>$openid,
			'userip'=>''== $userip?$this->get_real_ip():$userip,
		);
		//var_dump($params);
		$params['safekey']  = $this->mk_passwd($params, 10);
		//var_dump($params);
		$url = C('api_user_url').$this->USER_API_METHOD_LIST['bind_weibo'];
		$back_str = $this->post($url, $params);
		//var_dump($back_str);
		$re_json = json_decode($back_str, true);
		if($re_json
		&& 1 <= $re_json['State'])
		{	
			//$content['user_info'] = $re_json['Descr'];
			$back_content = $re_json['Descr'];
			$r_list = explode('|', $back_content);
			$index=0;
			$content['user_id']  = $r_list[$index++];
			$content['nickname'] = $r_list[$index++];
			$content['head_portrait'] = $r_list[$index++];
			$content['sex']      = $r_list[$index++];
			$content['cur_date'] = $r_list[$index++];
			$content['head_portrait'] = C('api_user_photo_url').$content['head_portrait'];
			$content['head_portrait'] = str_replace('user','',$content['head_portrait']);
			return true;
		}
		elseif(-1 == $re_json['State'])
		{
			$content['status_code'] = -100;
			$content['message']     = urlencode('输入的参数存在空值');
			return false;
		}
		elseif(-2 == $re_json['State'])
		{
			$content['status_code'] = -2;
			$content['message']     = urlencode('微博openid参数不合法');
			return false;
		}
		elseif(-3 == $re_json['State'])
		{
			$content['status_code'] = -3;
			$content['message']     = urlencode('safekey参数不合法');
			return false;
		}
		elseif(-4 == $re_json['State'])
		{
			$content['status_code'] = -4;
			$content['message']     = urlencode('微博openid已存在');
			return false;
		}
		elseif(0 == $re_json['State'])
		{
			$content['status_code'] = -6;
			$content['message']     = urlencode('绑定失败');
			return false;
		}

		return false;
	}

	#绑定qq(openid)
	public function bind_qq($content)
	/*
	@@input
	@param $ui_id   用户id
	@param $openid  qq的openid
	@param $userip  用户ip
	@@output
	@param $is_success
	@@output
	@param $is_success
	@param $is_success 0-成功，-1-操作失败,-2-微信openid参数不合法,
	                   -3-safekey参数不合法,-4-微信openid已存在,
			   -6-绑定失败
	@param $user_id 用户id
	@param $head_portrait 头像
	@param $nickname 用户昵称
	@param $sex 用户性别
	@param $cur_date 当前日期
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['uid'])
		|| !isset($data['openid'])
		)
		{
			return C('param_err');
		}
		
		$data['uid'] = intval($data['uid']);
		$data['openid'] = htmlspecialchars($data['openid']);
		
		if(0>= $data['uid']
		|| '' == $data['openid']
		)
		{
			return C('param_fmt_err');
		}
		
		$content = array();
		if($this->call_BindUserLoginByQQOpenid($data['uid'],$data['openid'], &$content))
		{
			return array(
				200,
				array(
					'is_success'=>0,
					'user_id'       => $content['user_id'],
					'head_portrait' => $content['head_portrait'],
					'nickname'      => $content['nickname'],
					'sex'           => $content['sex'],
					'cur_date'      => $content['cur_date'],
					'message'=>C('option_ok')
				)
			);
		}
		if(isset($content['status_code']))
		{
			return array(
				200,
				array(
					'is_success'=>$content['status_code'],
					'message'=>$content['message'],
				)
			);
		}
		
		return array(
				200,
				array(
					'is_success'=>-1,
					'message'=>C('option_fail'),
				),
		);		
	}
	
	private function call_BindUserLoginByQQOpenid($uid, $openid, $userip='', $content)
	{
		$params = array(
			'ui_id'=>$uid,
			'openid'=>$openid,
			'userip'=>''== $userip?$this->get_real_ip():$userip,
		);
		//var_dump($params);
		$params['safekey']  = $this->mk_passwd($params, 10);
		//var_dump($params);
		$url = C('api_user_url').$this->USER_API_METHOD_LIST['bind_qq'];
		$back_str = $this->post($url, $params);
		//var_dump($back_str);
		$re_json = json_decode($back_str, true);
		if($re_json
		&& 1 <= $re_json['State'])
		{	
			//$content['user_info'] = $re_json['Descr'];
			$back_content = $re_json['Descr'];
			$r_list = explode('|', $back_content);
			$index=0;
			$content['user_id']  = $r_list[$index++];
			$content['nickname'] = $r_list[$index++];
			$content['head_portrait'] = $r_list[$index++];
			$content['sex']      = $r_list[$index++];
			$content['cur_date'] = $r_list[$index++];
			$content['head_portrait'] = C('api_user_photo_url').$content['head_portrait'];
			$content['head_portrait'] = str_replace('user','',$content['head_portrait']);
			return true;
		}
		elseif(-1 == $re_json['State'])
		{
			$content['status_code'] = -100;
			$content['message']     = urlencode('输入的参数存在空值');
			return false;
		}
		elseif(-2 == $re_json['State'])
		{
			$content['status_code'] = -2;
			$content['message']     = urlencode('微信openid参数不合法');
			return false;
		}
		elseif(-3 == $re_json['State'])
		{
			$content['status_code'] = -3;
			$content['message']     = urlencode('safekey参数不合法');
			return false;
		}
		elseif(-4 == $re_json['State'])
		{
			$content['status_code'] = -4;
			$content['message']     = urlencode('微信openid已存在');
			return false;
		}
		elseif(0 == $re_json['State'])
		{
			$content['status_code'] = -6;
			$content['message']     = urlencode('绑定失败');
			return false;
		}

		return false;
	}

	#微博入口(openid)
	public function entry_weibo($content)
	/*
	@@intput
	@param $openid   微博openid 
	@param $mobile   手机号码
	@param $passwd   密码
	@param $nickname 昵称
	@param $sem      是否推广用户(默认0，1-为推广用户)
	*/
	{
		$data = $this->fill($content);
		if(!isset($data['openid']))
		{
			return C('param_err');
		}		
		$data['openid'] = htmlspecialchars(trim($data['openid']));
		$data['mobile'] = htmlspecialchars(trim($data['mobile']));
		$data['passwd'] = htmlspecialchars(trim($data['passwd']));
		$data['nickname'] = htmlspecialchars(trim($data['nickname']));
		$data['head_photo'] = htmlspecialchars(trim($data['head_photo']));
		$data['sem']        = intval(trim($data['sem']));
		
		if('' == $data['openid'])
		{
			return C('param_fmt_err');
		}
		#1.检查是否跳过;
		if('' == $data['mobile']
		&& '' == $data['passwd']
		//&& '' == $data['nickname']
		//&& '' == $data['head_photo']
		)
		{
			#2.如果跳过，微博单独注册;
			//todo:
			$params = array(
				'openid'=> $data['openid'],
				'nickname'=> $data['nickname'],
			);
			$re_back = $this->register_weibo(json_encode($params));
			if($_r = $this->tmp_upload_head($re_back, $data))
				return $_r;
			return $re_back;
		}
		else
		{
			#3.不是2步骤,检查手机号码是否存在，如果不存在,则进行手机号码+密码的注册,并且绑定微博和修改昵称+头像;
			$params = array(
				'mobile' => $data['mobile'],
			);
			list($status_code, $content) = $this->check_mobile(json_encode($params));
			if(200 == $status_code
			&& -1 == $content['is_exists'])#手机号码不存在
			{
				#进行手机号码+密码的注册,并且绑定微信和修改昵称+头像;
				$params = array(
					'mobile'  =>$data['mobile'],
					'pswd'    =>$data['passwd'],
					'nickname'=>$data['nickname'],
					'sem'     =>$data['sem'],
				);
				list($status_code, $content) = $this->register(json_encode($params));				
				
				if(200 == $status_code
				&& 0 == $content['is_success']
				)
				{
					$params = array(
						'openid'=>$data['openid'],
						'uid'   =>$content['user_id'],
					);
					#绑定微信号码
					list($status_code, $content) = $this->bind_weibo(json_encode($params));
					if(200 == $status_code
					&& 0 == $content['is_success']
					)
					{			
						
						#登录
						$params = array(
							'mobile'=>$data['mobile'],
							'pswd'=>$data['passwd'],
						);						
						$re_back = $this->login(json_encode($params));
						#修改头像
						if($_r = $this->tmp_upload_head($re_back, $data))
							return $_r;	
						return $re_back;
					}
				}
			}
			
			$params = array(
				'mobile'=>$data['mobile'],
				'pswd'=>$data['passwd'],
			);
			list($status_code, $content) = $this->login(json_encode($params));
			#检查帐号不正确
			if(200 == $status_code
			&& -2 == $content['is_success']
			)
			{
				#返回密码错误
				return array(
					200,
					array(
						'is_success'=>-2,
						'message'   =>urlencode('密码错误'),
					)
				);
			}
			
			#5.如果密码正确，绑定微博openid;
			$params = array(
						'openid'=>$data['openid'],
						'uid'   =>$content['user_id'],
			);
			#绑定微信号码
			list($status_code, $content) = $this->bind_weibo(json_encode($params));
			
			if(200 == $status_code
			&& 0 == $content['is_success']
			)
			{
				#登录
				$params = array(
					'mobile'=>$data['mobile'],
					'pswd'=>$data['passwd'],
				);
				return $this->login(json_encode($params));
			}
			else
			{
				return array(
					$status_code,
					$content
				);
			}
			
			return array(
				200,
				array(
					'is_success'=>-1,
					'message'=>urlencode('操作失败'),
				),
			);
		}
	}
	
	#qq入口(openid)
	public function entry_qq($content)
	/*
	@@intput
	@param $openid   qq的openid 
	@param $mobile   手机号码
	@param $passwd   密码
	@param $nickname 昵称
	@param $sem      是否推广用户(默认0，1-为推广用户)
	*/
	{
		$data = $this->fill($content);
		if(!isset($data['openid']))
		{
			return C('param_err');
		}		
		$data['openid'] = htmlspecialchars(trim($data['openid']));
		$data['mobile'] = htmlspecialchars(trim($data['mobile']));
		$data['passwd'] = htmlspecialchars(trim($data['passwd']));
		$data['nickname'] = htmlspecialchars(trim($data['nickname']));
		$data['head_photo'] = htmlspecialchars(trim($data['head_photo']));
		$data['sem']        = intval(trim($data['sem']));
		
		if('' == $data['openid'])
		{
			return C('param_fmt_err');
		}
		#1.检查是否跳过;
		if('' == $data['mobile']
		&& '' == $data['passwd']
		//&& '' == $data['nickname']
		//&& '' == $data['head_photo']
		)
		{
			#2.如果跳过，qq单独注册;
			//todo:
			$params = array(
				'openid'=> $data['openid'],
				'nickname'=> $data['nickname'],
			);
			$re_back = $this->register_qq(json_encode($params));
			if($_r = $this->tmp_upload_head($re_back, $data))
				return $_r;
			return $re_back;
		}
		else
		{
			#3.不是2步骤,检查手机号码是否存在，如果不存在,则进行手机号码+密码的注册,并且绑定qq和修改昵称+头像;
			$params = array(
				'mobile' => $data['mobile'],
			);
			list($status_code, $content) = $this->check_mobile(json_encode($params));
			if(200 == $status_code
			&& -1 == $content['is_exists'])#手机号码不存在
			{
				#进行手机号码+密码的注册,并且绑定微信和修改昵称+头像;
				$params = array(
					'mobile'  =>$data['mobile'],
					'pswd'    =>$data['passwd'],
					'nickname'=>$data['nickname'],
					'sem'     =>$data['sem'],
				);
				list($status_code, $content) = $this->register(json_encode($params));
				if(200 == $status_code
				&& 0 == $content['is_success']
				)
				{
					$params = array(
						'openid'=>$data['openid'],
						'uid'   =>$content['user_id'],
					);
					#绑定微信号码
					list($status_code, $content) = $this->bind_qq(json_encode($params));
					if(200 == $status_code
					&& 0 == $content['is_success']
					)
					{
						#登录
						$params = array(
							'mobile'=>$data['mobile'],
							'pswd'=>$data['passwd'],
						);
						$re_back = $this->login(json_encode($params));
						#修改头像
						if($_r = $this->tmp_upload_head($re_back, $data))
							return $_r;	
						return $re_back;
					}
				}
			}
			
			$params = array(
				'mobile'=>$data['mobile'],
				'pswd'=>$data['passwd'],
			);
			list($status_code, $content) = $this->login(json_encode($params));
			#检查帐号不正确
			if(200 == $status_code
			&& -2 == $content['is_success']
			)
			{
				#返回密码错误
				return array(
					200,
					array(
						'is_success'=>-2,
						'message'   =>urlencode('密码错误'),
					)
				);
			}
			
			#5.如果密码正确，绑定qq的openid;
			$params = array(
						'openid'=>$data['openid'],
						'uid'   =>$content['user_id'],
			);
			#绑定微信号码
			list($status_code, $content) = $this->bind_qq(json_encode($params));
			
			if(200 == $status_code
			&& 0 == $content['is_success']
			)
			{
				#登录
				$params = array(
					'mobile'=>$data['mobile'],
					'pswd'=>$data['passwd'],
				);
				return $this->login(json_encode($params));
				
			}
			else
			{
				return array(
					$status_code,
					$content
				);
			}
			
			return array(
				200,
				array(
					'is_success'=>-1,
					'message'=>urlencode('操作失败'),
				),
			);
		}
	}
	
	#头像上传
	public function head_photo_upload($content)
	/*
	@@input
	@param $uid      用户id
	@param $pic_path 图片路径
	@@output
	@param $is_success 0-成功,-1-失败,-2-safekey参数不合法，-3-用户不存在，
	*                  -4-头像文件保存失败,-5-头像文件超出指定大小限制（暂定100KB）
	*/
	{
		$data = $this->fill($content);
		if(!isset($data['uid'])
		//|| !isset($data['pic_path'])
		)
		{
			return C('param_err');
		}
		
		$data['uid'] = intval($data['uid']);
		//$data['pic_path'] = htmlspecialchars(trim($data['pic_path']));
		//$data['pic_path'] = __PUBLIC__."tmp/tmp.jpg";
		/*
		if(!file_exists($data['pic_path']))
		{
			return array(
				200,
				array(
					'is_success'=>-2,
					'message'=>urlencode('图片不存在'),
				),
			);
		}
		*/
		
		
		if(0>= $data['uid']
		|| '' == $data['pic_path']
		)
		{
			return C('param_fmt_err');
		}
		
		$content = array();
		if($this->call_SetUserAvatarByUid($data['uid'],$data['pic_path'], &$content))
		{
			return array(
				200,
				array(
					'is_success'=>0,
					'message'=>C('option_ok')
				)
			);
		}
		if(isset($content['status_code']))
		{
			return array(
				200,
				array(
					'is_success'=>$content['status_code'],
					'message'=>$content['message'],
				)
			);
		}
		
		return array(
				200,
				array(
					'is_success'=>-1,
					'message'=>C('option_fail'),
				),
		);		
		
	}
	
	private function call_SetUserAvatarByUid($uid, $pic_path, $content)
	{
		$params = array(
			'uid'=>$uid,
			'yyyyMMdd'=>date("Ymd"),
		);
		$params['safekey']  = $this->mk_passwd($params, 3);
		
		 //读取图片
		 //$fp  = fopen($pic_path, "rb");
		 //$buf = fread($fp, filesize($pic_path));
		 //fclose($fp);
		 $stime=microtime(true);
		 $buf = $this->get_remote_data($pic_path);
		 //var_dump($buf);
		 //file_get_contents($pic_path);
		 $sstime=microtime(true);
		  
	     $filename = "tmp.jpg";
	     $varname  = "imageUpLoad";
	     $key      = "$varname\";filename=\"$filename\"\r\n";
	     $handler  = $key;
	     $params[$key]         = $buf;
		
		$url = C('api_user_url').$this->USER_API_METHOD_LIST['head_photo_upload'];
		$back_str = $this->post($url, $params);
		$endtime = microtime(true);
		file_put_contents(__PUBLIC__.'/log/diff.txt',$stime.','.$sstime.','.$endtime.','.($sstime-$stime).','.($endtime-$sstime).'\r\n', FILE_APPEND);
		//var_dump($back_str);
		$re_json = json_decode($back_str, true);
		if($re_json
		&& 1 <= $re_json['State'])
		{	
			return true;
		}
		elseif(-2 == $re_json['State'])
		{
			$contact['status_code'] = -2;
			$contact['message'] = urlencode("safekey参数不合法");
			return false;
		}
		elseif(-3 == $re_json['State'])
		{
			$contact['status_code'] = -3;
			$contact['message'] = urlencode("用户不存在");
			return false;
		}
		elseif(-4 == $re_json['State'])
		{
			$contact['status_code'] = -4;
			$contact['message'] = urlencode("头像文件保存失败");
			return false;
		}
		elseif(-5 == $re_json['State'])
		{
			$contact['status_code'] = -5;
			$contact['message'] = urlencode("头像文件超出指定大小限制（暂定100KB）");
			return false;
		}
		elseif(0 == $re_json['State'])
		{
			$contact['status_code'] = -1;
			$contact['message'] = urlencode("用户头像更新失败");
			return false;
		}

		return false;
	}
	
	private function tmp_upload_head($re_back, $data)
	{
		//下载头像并上传
		if(200 == $re_back[0]
		&& 0 == $re_back[1]['is_success']
		&& '' != $data['head_photo'])
		{
			/*
			$param = array(
				'net_pic_url'=>$data['head_photo']
			);	
			list($status_code, $content) = $this->down_net_pic(json_encode($param));
			unset($param);
			if(200 == $status_code
			&& 0 == $content['is_success'])
			{
			*/
				//上传用户头像
				$uid = $re_back[1]['user_id'];
				$param = array(
					"uid"=>$uid,
					'pic_path'=>$data['head_photo'],
				);
				list($s_status_code, $s_content) = $this->head_photo_upload(json_encode($param));
				if(200 != $s_status_code
				|| 0 != $s_content['is_sucess'])
				{
					return array(
						$s_status_code,
						$s_content
					);
				}
			//}
			//unset($param);
		}
	}
	
	#获取远程数据
	private function get_remote_data($url)
	{
		$ch = curl_init ();  
        curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, 'GET' );  
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );  
        curl_setopt ( $ch, CURLOPT_URL, $url );  
        ob_start ();  
        curl_exec ( $ch );  
        $return_content = ob_get_contents ();  
        ob_end_clean ();  
          
        $return_code = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );  
        return $return_content;  
	}
	
	/*
	public function test_head($content)
	{
		$data = $this->fill($content);
		$re_back = array(
			200,
			array(
				'is_success'=>0,
				'user_id'=>$data['user_id'],
			)
		);
		
		$r = $this->tmp_upload_head($re_back, $data);
		var_dump($r);
	}
	*/
	
}
