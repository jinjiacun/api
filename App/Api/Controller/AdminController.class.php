<?php
namespace api\Controller;
use Api\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--系统管理员帐号管理--
------------------------------------------------------------
function of api:
public function login              登录
@@input
@param $admin_name 管理员用户名
@param $password   管理员密码
@@output
@param $is_success 0-成功登录，-1-登录失败, -2-用户名不存在, -3-用户名和密码不正确

public function is_login           是否登录
@@output
@param $is_exists 0-已经登录，-1-未登录
------------------------------------------------------------
*/
class AdminController extends BaseController {
      protected $_module_name = 'admin';
	  protected $id;
	  protected $admin_name;
      protected $password;	
      protected $last_login;         #最后登录时间
	  protected $login_ip;           #最后登录ip
	  protected $add_time;           #添加日期

	  #登录
	  public function login($data)
	  {
		$data = $this->fill($content);
		
		if(!isset($data['admin_name'])
		|| !isset($data['password'])
		)
		{
			return C('param_err');
		}		
		
		$data['admin_name'] = htmlspecialchars(trim($data['admin_name']));
		$data['password']   = htmlspecialchars(trim($data['password']));

		if('' == $data['admin_name']
		|| '' == $data['password']
		)
		{
			return C('param_fmt_err');
		}

		if(!$this->check_admin_name($data['admin_name']))
		{
			return array(
					200,
					array(
						'is_success'=>-2,
						'message'=>urlencode('用户名不存在')
						);
				);
		}

		if(!$this->check_admin_name_passwd($data['admin_name'], $data['password']))
		{
			return array(
					200,
					array(
						'is_success'=>-3,
						'message'=>urlencode('用户名和密码不正确')
						);
				);
		}

		$where = array(
				'admin_name'=>$data['admin_name'],
				'password' => md5($data['password'])
			);

		$tmp_one = M('admin')->where($where)->find();
		if($tmp_one)
		{
			session('admin_name') = $data['admin_name'];
			return array(
					200,
					array(
						'is_success'=>0,
						'message'=>urlencode('登录成功')
						)
				);
		}

		return array(
				200,
				array(
					'is_success'=>-1,
					'message'=>urlencode('登录失败'),
					)
			);
	  }	

	  #检查用户名是否存在
	  private function check_admin_name($admin_name= '')
	 {	
		 if('' == trim($adminn_name))
			return false;

		$where = array(
					'admin_name'=>$admin_name
					);
	
		$tmo_one = M('admin')->where($where)->find();
		if($tmp_one)
			return true;

		return false;
	
	 }

	  #检查用户名和密码是否合法
	  private function check_admin_name_passwd($admin_name = '', $password = '')
	  {
		if('' == trim($admin_name)
		|| '' == trim($password)
		)
		{
			return false;
		}
        
		$where = array(
				'admin_name'=> $admin_name,
				'password' => md5($password),
				);		
		if(M('admin')->where($where)->find())
		{
			return true;
		}
		
		return false;
		
	  }
	  
}
