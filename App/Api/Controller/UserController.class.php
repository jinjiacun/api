<?php
namespace api\Controller;
use Api\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--会员管理--
*/
class UserController extends BaseController {

	protected $_module_name = 'user';
	protected $id;               		 #关键字
	protected $user_name;        		 #用户名
	protected $password;                 #密码
	protected $nick_name;        		 #昵称
	protected $real_name;		 		 #真实姓名
	protected $sex;              		 #性别
	protected $mobile;           		 #认证手机号
	protected $is_validated;             #是否验证(0-未验证,1-已验证)
	protected $identity_card_0;   		 #身份证号正
	protected $identity_card_1;   		 #身份证号反
	protected $province;                 #省份
	protected $city;                     #城市
	protected $district;                 #地区
	protected $address;           		 #联系地址
	protected $photo;             		 #个人照片
	protected $qq;                		 #QQ号码
	protected $email;             		 #E-mail
	protected $make_collections_account; #收款账户
	protected $add_time;                 #注册日期

	#检查用户名
	/**
	*@@input
	*@param $user_name  用户名
	*@@output
	*@param $is_exists  是否存在(0-存在，1-不存在)
	*/
	public function check_name($content)
	{
		$data = $this->fill($content);

		if(!isset($data['user_name']))
		{
			return C('param_err');
		}

		if($this->do_get_user_name($data['user_name']))
		{
			return array(
						200,
						array(
							'is_exists'=>0,
							'message'=>urlencode('此用户名已存在'),
							),
				);
		}

		return array(
					200,
					array(
						'is_exists'=>1,
						'message'=>urlencode('此用户名不存在'),
						),
			);
	}

	#获取用户名
	private function do_get_user_name($user_name = '')
	{
		if('' == $user_name)
		{
			return false;
		}

		$where = array(
				'user_name'=>$user_name,
			);
		$res = M('User')->where($where)->find();
		if($res)
		{
			return true;
		}

		return false;
	} 

	#修改密码
	/**
	*@@input
	*@param $id            用户id
	*@param $old_password  用户旧密码
	*@param $new_password  新密码
	*@@output
	*@param $is_success    是否成功修改(0-修改成功，-1-修改失败,-2-不存在此账号)
	*/
	public function update_password($content)
	{
		$data = $this->fill($content);

		if(!isset($data['user_name'])
		|| !isset($data['old_password'])
		|| !isset($data['new_password'])
		)
		{	
			return C('param_err');
		}

		#检查是否存在此用户信息
		if(!$this->do_get_userinfo_by_user_name_passwd($content['user_name'],
			                                           $content['old_password']))
		{
			return 	array(200,
						array(
							'is_success'=>-2,
							'message'   =>urlencode('此账号不存在')
							)
					);
		}

		$where = array(
				'user_name'=>$data['user_name'],
				'password'=>md5($data['old_password']),
			);
		$res = M('User')->where($where)->update(array('password'=>md5($content['new_password']))); 
		if($res)
		{
			return array(
						200,
						array(
							'is_success'=>0,
							'message'   =>urlencode('修改成功')
							)
					);
		}

		return array(
			        200,
					array(
					'is_success'=>-1,
					'message'   =>urlencode('修改失败')
					)
					);
	}

	#通过用户名和密码查询用户
	private function do_get_userinfo_by_user_name_passwd($user_name, $password)
	{
		$where = array(
					'user_name'=>$user_name,
					'password' =>md5($password),
			);
		$res = M('User')->where($where)->find();
		if($res)
			return true;

		return false;
	}

	#忘记密码
	/**
	*@@input
	*@param $mobile        手机号码
	*@param $new_password  新密码
	*/
	public function forget_password($content)
	{

	}



	#登录
	/**
	*@@input
	*@param $user_name  用户名
	*@param $password   密码
	*/
	public function login($content)
	{
		$data = $this->fill($content);
		session('user_name', $data['user_name']);
		return array(
			200,
			array(
				'is_success'=>0
				),
		);
	}

	#获取登录session
	public function get_session($content)
	{
		return array(
			200,
			session('user_name'));
	}
}