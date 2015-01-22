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
@param $is_success 0-成功,-1-失败	,-2-手机号码不存在,-3-短信验证码不正确
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
*/
class UserController extends BaseController {
	private $USER_API_METHOD_LIST = array(
							 'register'          => "RegisterByMobile",      #通过手机号注册
							 'login'             => "LoginByMobile",         #通过手机号登录 【允许get请求】
							 'find_passwd'       => "SetPswordByMobile",     #用户找回密码
							 'update'            => "SetUserInfoByUid",      #更新用户信息
							 'update_photo'      => "SetUserAvatarByUid",    #更新用户头像信息
							 'get_info'          => "GetUserInfoByUid",      #查询用户信息
			                 'check_mobile'      => "ExistsUserInfoByMobile",#检查手机号码
			                 'send_validate'     => "SmsByFindPswd",         #发送手机验证
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
			$content['head_portrait'] = C('api_user_photo_url').$content['head_portrait'];
			$content['head_portrait'] = str_replace('user','',$content['head_portrait']);
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
	@param $mobile   - 手机号
	@param $new_pswd - 新密码
	@param $smscode  - 短信验证码
	@@output
	@param $is_success 0-成功,-1-失败	,-2-手机号码不存在，-3-短信验证码不正确
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
		
		return array(
				200,
				array(
				  'is_success'=>isset($content['status_code'])?
					              $content['status_code']:-1,
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
		elseif(-4 == $re_json['State'])
		{
			$content['status_code'] = -2;
			return false;
		}
		elseif(-5 == $re_json['State'])
		{
			$content['status_code'] = -3;
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
		
		if($this->call_ExistsUserInfoByMobile($data['mobile']))
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
		
		if($this->call_SmsByFindPswd($data['mobile'], $data['imagecode']))
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
	
	private function call_SmsByFindPswd($mobile, $imagecode)
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
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}
