<?php
namespace api\Controller;
use Api\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--会员管理--
------------------------------------------------------------
function of api:

public function register                  用户注册(finish)
@@input 
@param $user_name        用户名(*)
@param $nick_name        昵称(*)
@param $real_name        真实姓名(*)
@param $sex              性别编号
@param $identity_card_no 身份证号
@param $address          联系地址
@param $zipcode          邮政编码
@param $photo            个人照片id(为Media里面的id)
@param $qq               QQ号码
@param $email            电子邮箱
@param $password         密码(*)
@param $mobile           手机号码(*)
@output
@param $is_success 0-注册成功，-1-注册失败, -2-用户已存在, -3-手机号码已存在
##--------------------------------------------------------##
public function check_name                检查用户名(finish)
@@input
@param $user_name 用户名
@@output
@param $is_exists 0-存在,-1-不存在
##--------------------------------------------------------##
public function check_nick_name                检查昵称(finish)
@@input
@param $nick_name 用户名
@@output
@param $is_exists 0-存在,-1-不存在
##--------------------------------------------------------##
public function check_mobile              检查手机号码(finish)
@@input
@param $mobile 手机号码
@@output
@param $is_exists 0-存在,-1-不存在
##--------------------------------------------------------##
public function mobile_validated          手机验证(finish)
@@input
@param $mobile              手机号码
@param $mobile_is_validated 手机号码是否验证(0-未验证，1-已验证)
@@output
@param @is_success 0-成功操作，-1-操作失败
##--------------------------------------------------------##
public function identity_card_validated   身份证验证
@@input
@param $user_id                     用户id
@param $identity_card_is_validated  身份证是否验证(0-未验证， 1-已验证)
@@output 
@param $is_success 0-成功操作, -1-操作失败
##--------------------------------------------------------##
public function update_password           修改密码(finish)
@@input
@param $user_id      用户id
@param $old_password 旧密码
@param $new_password 新密码
@@output
@param $is_suceess 0-成功操作, -1-操作失败, -2-帐号信息不正确
##--------------------------------------------------------##
public function forget_password           忘记密码(finish)
@@input
@param $mobile       手机号码
@param $new_password 新密码
@@output
@param $is_success 0-成功操作， -1-操作失败, -2-此手机号不存在
##--------------------------------------------------------##
public function login                     登录(finish)
@@input
@param $user_name 用户名/昵称/手机号码
@param $password  密码
@@output
@param $is_success 0-成功登录，-1-登录失败， -2-用户名不存在， -3-密码不正确
##--------------------------------------------------------##
public function get_info                  用户信息查询(finish)
@@input
@param $user_id 用户id
@@output
##--------------------------------------------------------##
public function get_info_ex               用户信息查询(finish)
@@input
@param $user_name 用户
@@output
##--------------------------------------------------------##
public function check_is_login            检查用户是否登录(finish)
@@output
@param $is_exists 0-用户登录状态, -1-用户未登录
@param $user_id   如果上面是用户登录状态，这里为用户的id
------------------------------------------------------------
*/
class UserController extends BaseController {

	protected $_module_name = 'user';
	protected $id;               		  #关键字
	protected $user_name;        		  #用户名
	protected $password;                  #密码
	protected $nick_name;        		  #昵称
	protected $real_name;		 		  #真实姓名
	protected $sex;              		  #性别
	protected $mobile;           		  #认证手机号
    protected $mobile_is_validated;       #手机是否验证
	protected $identity_card_0;   		  #身份证号正
	protected $identity_card_1;   		  #身份证号反
    protected $identity_card_is_validated;#身份证是否验证
    protected $identity_card_no;          #身份证号码
    protected $zipcode;                   #邮政编码
	protected $province;                  #省份
	protected $city;                      #城市
	protected $district;                  #地区
	protected $town;                      #镇
	protected $address;           		  #联系地址
    protected $address_id;                #默认收获地址
	protected $photo;             		  #个人照片
	protected $qq;                		  #QQ号码
	protected $email;             		  #E-mail
	protected $make_collections_account;  #收款账户
	protected $add_time;                  #注册日期
    
	#覆盖add方法
	public function add($content)
	{
		//$this->register($content);
	}
	
    #用户注册
    /*
    @@input 
	@param $user_name        用户名(*)
	@param $nick_name        昵称(*)
	@param $real_name        真实姓名(*)
	@param $sex              性别编号
	@param $identity_card_no 身份证号
	@param $address          联系地址
	@param $zipcode          邮政编码
	@param $photo            个人照片id(为Media里面的id)
	@param $qq               QQ号码
	@param $email            电子邮箱
	@param $password         密码(*)
	@param $mobile           手机号码(*)
	@output
	@param $is_success 0-注册成功，-1-注册失败, -2-用户已存在, -3-手机号码已存在
	*/
	public function register($content)
	{
		$data = $this->fill($content);
		
		if(!isset($data['user_name'])
		|| !isset($data['nick_name'])
		|| !isset($data['real_name'])
		|| !isset($data['password'])
		|| !isset($data['mobile'])
		)
		{
			return C('param_err');
		}

		$data['user_name'] = htmlspecialchars(trim($data['user_name']));
		$data['nick_name'] = htmlspecialchars(trim($data['nick_name']));
		$data['real_name'] = htmlspecialchars(trim($data['real_name']));
		$data['password']  = htmlspecialchars(trim($data['password']));
		$data['mobile']    = htmlspecialchars(trim($data['mobile']));

		if('' == $data['user_name']
		|| '' == $data['nick_name']
		|| '' == $data['real_name']
		|| '' == $data['password']
		|| '' == $data['mobile']
		)
		{
			return C('param_fmt_err');
		}

	    #检查用户名
		if($this->do_get_user_name($data['user_name']))
		{
			return array(
					200,
					array(
						'is_success'=>-2,
                        'message'=>urlencode('此用户名已存在'),
						),
					);
		}	
		
		#检查手机号码
		if($this->do_get_mobile($data['mobile']))
		{
			return array(
					200,
					array(
						'is_success'=>-3,
						'message'=>urlencode('此手机号码已注册'),
						),
						);
		}		

	    #检查昵称
		if($this->do_get_nick_name($data['nick_name']))
		{	
			return array(
					200,
					array(
						'is_success'=>-4,
						'message'   =>urlencode('此昵称已存在'),
						),
						);
		}	

		#加密密码
        $data['password'] = md5($data['password']);

		if(M('User')->add($data))
		{
			return array(
						200,
                        array('is_success'=>0,
                              'message'=>urlencode('成功注册'),
                              ),
                           );
		}
		else
		{
			return array(200,
                         array(
							'is_success'=>-1,
                            'message'=>urlencode('注册失败'),
							),
                         );
		}
	}

	#检查用户名
	/**
	*@@input
	*@param $user_name  用户名
	*@@output
	*@param $is_exists  是否存在(0-存在，-1-不存在)
	*/
	public function check_name($content)
	{
		$data = $this->fill($content);

		if(!isset($data['user_name']))
		{
			return C('param_err');
		}

		$data['user_name'] = htmlspecialchars(trim($data['user_name']));

		if('' == $data['user_name'])
		{
			return C('param_fmt_err');
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
						'is_exists'=>-1,
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

	#检查昵称
	/**
	*@@input
	*@param $nick_name  
	*@@output
	*@param $is_exists  是否存在(0-存在，-1-不存在)
	*/
	public function check_nick_name($content)
	{
		$data = $this->fill($content);

		if(!isset($data['nick_name']))
		{
			return C('param_err');
		}

		$data['nick_name'] = htmlspecialchars(trim($data['nick_name']));

		if('' == $data['nick_name'])
		{
			return C('param_fmt_err');
		}

		if($this->do_get_nick_name($data['nick_name']))
		{
			return array(
						200,
						array(
							'is_exists'=>0,
							'message'=>urlencode('此已存在'),
							),
				);
		}

		return array(
					200,
					array(
						'is_exists'=>-1,
						'message'=>urlencode('此不存在'),
						),
			);
	}
	
	#获取昵称
	private function do_get_nick_name($nick_name = '')
	{
		if('' == $nick_name)
		{
			return false;
		}

		$where = array(
				'nick_name' => $nick_name,
			);
		$res = M('User')->where($where)->find();
		if($res)
		{
			return true;
		}

		return false;
	}

	

	#检查手机号码
	/*
	@@input
	@param $mobile 手机号码
	@@output
	@param $is_exists 0-存在,-1-不存在
	*/
	public function check_mobile($content)
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

		if($this->do_get_mobile($data['mobile']))
		{
			return array(
					200,
					array('is_exists'=>0,
						  'message'=>urlencode('此手机号码已存在'),
						),
				);
		}

		return array(
				200,
				array('is_exists'=>-1,
					  'message'=>urlencode('此手机号码不存在'),
					),
			);
	}

	private function do_get_mobile($mobile)
	{
		$where = array(
				'mobile'=>$mobile,
			);

		$tmp_one = M('User')->where($where)->find();
		if($tmp_one)
			return true;

		return false;
	}

	#手机验证
	/*
	@@input
	@param $mobile              手机号码
	@param $mobile_is_validated 手机号码是否验证(0-未验证，1-已验证)
	@@output
	@param @is_success 0-成功操作，-1-操作失败
	*/
	public function mobile_validated($content)
	{
		$data = $this->fill($content);

		if(!isset($data['mobile'])
		|| !isset($data['mobile_is_validated'])
		)
		{
			return C('param_err');
		}

		$data['mobile'] = htmlspecialchars(trim($data['mobile']));
		$data['mobile_is_validated'] = intval($data['mobile_is_validated']);

		if('' == $data['mobile']
		|| !in_array($data['mobile_is_validated'], array(0,1))
		)
		{	
			return C('param_fmt_err');
		}

		$where = array(
			'mobile'=>$data['mobile'],
			);
		$update_data = array(
			'mobile_is_validated'=>$data['mobile_is_validated'],
			);
		if(M('User')->where($where)->save($update_data))
		{
			return array(
					200,
					array(
						'is_success'=>0,
						'message'=>urlencode('操作成功')
						),
					);
		}

		return array(
				200,
				array(
					'is_success'=>-1,
					'message'=>urlencode('操作失败')
					),
			);
	}

	#身份证验证
	/*
	@@input
	@param $user_id                     用户id
	@param $identity_card_is_validated  身份证是否验证(0-未验证， 1-已验证)
	@@output 
	@param $is_success 0-成功操作, -1-操作失败
	*/
	public function identity_card_validated($content)
	{
		$data = $this->fill($content);

		if(!isset($data['user_id'])
		|| !isset($data['identity_card_is_validated'])
		)
		{
			return C('param_err');
		}

		$data['user_id'] = intval($data['user_id']);
		$data['identity_card_is_validated'] = intval($data['identity_card_is_validated']);

		if(0 >= $data['user_id']
		|| !in_array($data['identity_card_is_validated'], array(0,1))
		)
		{
			return C('param_fmt_err');
		}

		$where = array(
			'Id'=>$data['user_id'],
			);
		$update_data = array(
			'identity_card_is_validated'=>$data['identity_card_is_validated']
			);
		if(M('User')->where($where)->save($update_data))
		{
			return array(200,
						array('is_success'=>0,
							  'message'=>urlencode('成功操作')));
		}
		return array(200,
					 array('is_success'=>-1,
					 	   'message'=>urlencode('操作失败')));
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

		$data['user_name'] = htmlspecialchars(trim($data['user_name']));
		$data['old_password'] = htmlspecialchars(trim($data['old_password']));
		$data['new_password'] = htmlspecialchars(trim($data['new_password']));

		if('' == $data['user_name']
		|| '' == $data['old_password']
		|| '' == $data['new_password']
		)
		{
			return C('param_fmt_err');
		}

		#检查是否存在此用户信息
		if(!$this->do_get_userinfo_by_user_name_passwd($date['user_name'],
			                                           $data['old_password']))
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
		$res = M('User')->where($where)->save(array('password'=>md5($data['new_password']))); 
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
	private function do_get_userinfo_by_user_name_passwd($user_name='', $password='')
	{
		if('' == trim($user_name)
		|| '' == trim($password)
		)
		{
			return false;
		}

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
	/*
	@@input
	@param $mobile       手机号码
	@param $new_password 新密码
	@@output
	@param $is_success 0-成功操作， -1-操作失败, -2-此手机号不存在
	*/
	public function forget_password($content)
	{
		$data = $this->fill($content);

		if(!isset($data['mobile'])
		|| !isset($data['new_password'])
		)
		{
			return C('param_err');
		}

		$data['mobile']       = htmlspecialchars(trim($data['mobile']));
		$data['new_password'] = htmlspecialchars(trim($data['new_password']));

		if('' == $data['mobile']
		|| '' == $data['new_password']
		)
		{
			return C('param_fmt_err');
		}
		
		#检查手机号是否存在
		if(!$this->do_get_mobile($data['mobile']))
		{
			return array(200,
						array('is_success'=>-2,
                             'message'=>urlencode('此手机号码不存在')));
		}

		$where = array(
			'mobile' => $data['mobile'],
			);
		$update_data = array(
			'password' => md5($data['new_password'])
			);

		if(M('User')->where($where)->save($update_data))
		{
			return array(
				200,
				array(
					'is_success'=>0,
					'message'=>urlencode('成功操作'),
					)
				);
		}

		return array(
			200,
			array(
				'is_success'=>-1,
				'messsage'=>urlencode('操作失败'),
				)
			);
	}

	#登录
	/**
	*@@input
	*@param $user_name  用户名/昵称/手机号码
	*@param $password   密码
	*/
	public function login($content)
	{
		$data = $this->fill($content);
		if(!isset($data['user_name'])
		|| !isset($data['password'])
		)
		{	
			return C('param_err');
		}

		$data['user_name'] = htmlspecialchars(trim($data['user_name']));
		$data['password']  = htmlspecialchars(trim($data['password']));

		if('' == $data['user_name']
		|| '' == $data['password']
		)
		{
			return C('param_fmt_err');
		}

		$where = array(
					'user_name'=>$data['user_name'],
					'password'=>md5($data['password'])
			);
		$tmp_one = M('User')->where($where)->find();
		if($tmp_one)
		{
			$user_info = $this->do_getuserinfo_by_username($data['user_name']);
			session('user_name',   $data['user_name']);
			session('user_id',     $user_info['id']);
			session('nick_name',   $user_info['nick_name']);
			session('user_mobile', $user_info['user_mobile']);
			return array(
				200,
				array(
					'is_success'=>0,
                    'message'=>urlencode('成功登录'),
					),
			);
		}
		
		#昵称登录
		$where = array(
				'nick_name' => $data['user_name'],
				'password'  => md5($data['password'])
		);
		$tmp_one = M('User')->where($where)->find();
		if($tmp_one)
		{
			$user_info = $this->do_getuserinfo_by_nickname($data['user_name']);
			session('user_name',   $data['user_name']);
			session('user_id',     $user_info['id']);
			session('nick_name',   $user_info['nick_name']);
			session('user_mobile', $user_info['user_mobile']);
			return array(
				200,
				array(
					'is_success'=>0,
                    'message'=>urlencode('成功登录'),
					),
			);
		}
		
		#手机号码登录
		$where = array(
				'mobile'   => $data['user_name'],
				'password' => md5($data['password'])
		);
		$tmp_one = M('User')->where($where)->find();
		if($tmp_one)
		{
			$user_info = $this->do_getuserinfo_by_mobile($data['user_name']);
			session('user_name',   $data['user_name']);
			session('user_id',     $user_info['id']);
			session('nick_name',   $user_info['nick_name']);
			session('user_mobile', $user_info['user_mobile']);
			return array(
				200,
				array(
					'is_success'=>0,
                    'message'=>urlencode('成功登录'),
					),
			);
		}
		
		return array(
				200,
				array(
					'is_success'=>-1,
					'message'=>urlencode('登录失败'),
					),
			);
	}

	#用户信息查询
	/*
	@@input
	@param $user_id 用户id
	@@output
	*/
	public function get_info($content)                  
	{
		$data = $this->fill($content);

		if(!isset($data['user_id']))
		{
			return C('param_err');
		}

		$data['user_id'] = intval($data['user_id']);

		if(0 >= $data['user_id'])
		{
			return C('param_fmt_err');
		}

		$list  = array(); 
		$where = array(
				'id' => $data['user_id'],
			);
		$tmp_one = M('User')->where($where)->find();
		if($tmp_one)
		{
			$list = array(
				'id'                         => urlencode($tmp_one['id']),
				'user_name'                  => urlencode($tmp_one['user_name']),
				'password'                   => urlencode($tmp_one['password']),
				'nick_name'                  => urlencode($tmp_one['nick_name']),
				'real_name'                  => urlencode($tmp_one['real_name']),
				'sex'                        => urlencode($tmp_one['sex']),
				'mobile'                     => urlencode($tmp_one['mobile']),
			    'mobile_is_validated'        => urlencode($tmp_one['mobile_is_validated']),
				'identity_card_0'            => urlencode($tmp_one['identity_card_0']),
				'identity_card_1'            => urlencode($tmp_one['identity_card_1']),
			    'identity_card_is_validated' => urlencode($tmp_one['identity_card_is_validated']),
			    'identity_card_no'           => urlencode($tmp_one['identity_card_no']),
			    'zipcode'                    => urlencode($tmp_one['zipcode']),
				'province'                   => urlencode($tmp_one['province']),
				'city'                       => urlencode($tmp_one['city']),
				'district'                   => urlencode($tmp_one['district']),
				'town'                       => urlencode($tmp_one['town']),
				'address'                    => urlencode($tmp_one['address']),
			    'address_id'                 => urlencode($tmp_one['address_id']),
				'photo'                      => urlencode($tmp_one['photo']),
				'qq'                         => urlencode($tmp_one['qq']),
				'email'                      => urlencode($tmp_one['email']),
				'make_collections_account'   => urlencode($tmp_one['make_collections_account']),
				'add_time'                   => urlencode($tmp_one['add_time']),
				);
		}

		return array(
					200,
					$list
				);
	}

	private function do_getuserinfo_by_id($user_id = 0)
	{
		if(0 == $user_id)
		{
			return array();
		}

		$where = array(
			'Id'=>$user_id,
			);

		$tmp_one = M('User')->where($where)->find();
		if($tmp_one)
			return $tmp_one;

		return array();
	}

	#用户信息查询
	/*
	@@input
	@param $user_name 用户
	@@output
	*/
	public function get_info_ex($content)
	{
		$data = $this->fill($content);

		if(!isset($data['user_name']))
		{
			return C('param_err');
		}

		$data['user_name'] = htmlspecialchars(trim($data['user_name']));

		if('' == $data['user_name'])
		{
			return C('param_fmt_err');
		}

		$where = array(
					'user_name' => $data['user_name'],
			);
		$tmp_one = M('User')->where($where)->find();
		if($tmp_one)
		{
			return array(200,
						$tmp_one
						);
		}

		return array(
				200,
				array()
			);
	}               

	private function do_getuserinfo_by_username($user_name = '')
	{
		if('' == $user_name)
		{
			return array();
		}

		$where = array(
			'user_name' => $user_name,
			);

		$tmp_one = M('User')->where($where)->find();
		if($tmp_one)
		{
			return $tmp_one;
		}

		return array();
	}
	
	private function do_getuserinfo_by_nickname($nick_name = '')
	{
		if('' == $nick_name)
		{
			return array();
		}

		$where = array(
			'nick_name' => $nick_name,
			);

		$tmp_one = M('User')->where($where)->find();
		if($tmp_one)
		{
			return $tmp_one;
		}

		return array();
	}
	
	private function do_getuserinfo_by_mobile($mobile = '')
	{
		if('' == $mobile)
		{
			return array();
		}

		$where = array(
			'mobile' => $mobile,
			);

		$tmp_one = M('User')->where($where)->find();
		if($tmp_one)
		{
			return $tmp_one;
		}

		return array();
	}

	#检查用户是否登录
	public function check_is_login($content)
	{
		if(session('user_name'))
		{
			return array(200,
						array(
							'is_exists'=>0,
							'user_name'=>session('user_name'),
							'user_id'  =>session('user_id'),
							),
						);
		}
		return array(200,
					array(
						'is_exists'=>-1,
						'user_name'=>null,
						'user_id'=>0,
						));
	}
}
