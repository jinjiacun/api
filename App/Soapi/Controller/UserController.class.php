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
@param $is_success 0-成功,-1-失败,-2-用户名或者密码错误
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
@param $is_success 0-成功,-1-失败	,-2-手机号码不存在
##--------------------------------------------------------##
#查询用户信息
public function get_info
@@input
@param $uid  用户id
@@output
@param $user_info 用户信息json
##--------------------------------------------------------##
*/
class UserController extends BaseController {
	private $USER_API_METHOD_LIST = array(
							 'register'    => "RegisterByMobile",    #通过手机号注册
							 'login'       => "LoginByMobile",       #通过手机号登录 【允许get请求】
							 'find_passwd' => "SetPswordByMobile",   #用户找回密码
							 'update'      => "SetUserInfoByUid",    #更新用户信息
							 'update_ex'   => "SetUserAvatarByUid",  #更新用户信息
							 'get_info'    => "GetUserInfoByUid",    #查询用户信息
							 );
	#通过手机注册
	public function register($content)
	/**
	@@input
	@param mobile       手机号码
	@param pswd         密码
	@param $nickname     昵称(不设为自动生成)
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
		
		$content = array(
			'user_id'  =>0,
			'nickname' =>isset($data['nickname'])?$data['nickname']:'',
			'sex'      =>-1,
			'cur_date' =>''
		);
		
		if($this->call_RegisterByMobile($data['mobile'], $data['pswd'], &$content))
		{
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
		$params = array(
			'mobile'   => $mobile,
			'pswd'     => $pswd,
			'nickname' => '' == $content['nickname']?
			              $this->make_nickname():
			              $content['nickname'],
			'validated'=> 0,
			'userip'   => $this->get_real_ip(),
		);
		$params['safekey']  = $this->mk_passwd($params);
		$url = C('api_user_url').$this->USER_API_METHOD_LIST['register'];
		$back_str = $this->post($url, $params);
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
		$content = array(
			'user_id'  =>0,
			'nickname' =>'',
			'sex'      =>-1,
			'cur_date' =>''
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
			'is_success'=>-1,
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
			'userip'   => $this->get_real_ip(),
		);
		$url = C('api_user_url').$this->USER_API_METHOD_LIST['login'];
		$back_str = $this->post($url, $params);
		$re_json = json_decode($back_str, true);
		if($re_json
		&& 1 == $re_json['State'])
		{
			$back_content = $re_json['Descr'];
			$r_list = explode('|', $back_content);
			$index=0;
			$content['user_id']  = $r_list[$index++];
			$content['nickname'] = $r_list[$index++];
			$content['head_portrait'] = $r_list[$index++];
			$content['sex']      = $r_list[$index++];
			$content['cur_date'] = $r_list[$index++];
			return true;
		}
		elseif(-3 == $re_json['State'])
		{
			$content['status_code'] = -2;
			return false;
		}
		return false;
	}
	
	#找回密码
	public function find_passwd($content)
	/*
	@@input
	@param $mobile - 手机号
	@param $new_pswd - 新密码
	@@output
	@param $is_success 0-成功,-1-失败	,-2-手机号码不存在
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['mobile'])
		|| !isset($data['new_pswd'])
		)
		{
			return C('param_err');
		}
		
		$data['mobile']   = htmlspecialchars(trim($data['mobile']));
		$data['new_pswd'] = htmlspecialchars(trim($data['new_pswd']));
		
		if('' != $data['mobile']
		|| '' != $data['new_pswd'])
		{
			return C('param_fmt_err');
		}
		
		if($this->call_SetPswordByMobile($data['mobile'],
						 $data['new_pswd']))
		{
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
				  'is_success'=>-1,
				  'message'=>C('option_fail'),
				),
			);
	}
	
	
	private function call_SetPswordByMobile($mobile, $new_pswd)
	{
		$params = array(
			'mobile'   => $mobile,
			'pswd'     => $pswd,
			'userip'   => $this->get_real_ip(),
		);
		$params['safekey']  = $this->mk_passwd($params, 1);
		$url = C('api_user_url').$this->USER_API_METHOD_LIST['find_passwd'];
		$back_str = $this->post($url, $params);
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
		elseif(-3 == $re_json['State'])
		{
			$content['status_code'] = -2;
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
		
		if(!isset($data['user_id']))
		{
			return C('param_err');
		}
		
		$data['uid'] = intval($data['uid']);
		
		if(0>= $data['uid'])
		{
			return C('param_fmt_err');
		}
		
		if($this->call_GetUserInfoByUid($data['uid']))
		{
			return array(
				200,
				array(
				
				),
			);
		}
		
		return array(
			200,
			array()
		);
	}
	
	private function call_GetUserInfoByUid($uid)
	{
		$params = array(
			'uid'=>$uid,
			'yyyyMMdd'=>date("yyyyMMdd"),
		);
		$params['safekey']  = $this->mk_passwd($params, 4);
		$url = C('api_user_url').$this->USER_API_METHOD_LIST['get_info'];
		$back_str = $this->post($url, $params);
		$re_json = json_decode($back_str, true);
		if($re_json
		&& 1 == $re_json['State'])
		{	
			return true;
		}
		return false;
	}
}
