<?php
namespace Soapi\Controller;
use Soapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--管理员管理--
------------------------------------------------------------
function of api:

#登录
public function login
@@input
@param $admin_name #管理员用户名
@param $passwd     #管理员密码
@@output
@param $is_success 0-成功,-1-失败
##--------------------------------------------------------##
#获取登录信息
public function get_login_info
@@output
@param $admin_name #管理员用户名
##--------------------------------------------------------##
*/
class AdminController extends BaseController {
	/**
	 * sql script:
	 * create table so_admin(id int primary key auto_increment,
	                         admin_name varchar(255) comment '管理员名称',
	                         passwd varchar(255) comment '管理员密码',
	                         add_time int not null default 0 comment '添加日期'
	                             )charset=utf8;
	 * */
	 
	 protected $_module_name = 'admin';
	 protected $id;         
	 protected $admin_name; #管理员用户名
	 protected $passwd;     #管理员密码
	 
	 #登录
	 public function login($content)
	 /*
	 @@input
	 @param $admin_name #管理员用户名
	 @param $passwd     #管理员密码
	 @@output
	 @param $is_success 0-成功,-1-失败
	 */
	 {
		$data = $this->fill($content);
		
		if(!isset($data['admin_name'])
		|| !isset($data['passwd'])
		)
		{
			return C('param_err');
		}
	
		if('' == $data['admin_name']
		|| '' == $data['passwd']
		)
		{
			return C('param_fmt_err');
		}
		
		$data['passwd'] = md5($data['passwd']);
	
		$tmp_one = M($this->_module_name)->where($data)
		                                 ->find();
	    if($tmp_one)
	    {
			session('admin_name',$data['admin_name']);
			return array(
				200,
				array(
					'is_success'=>0,
					'message'=>C('option_ok'),
				)
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
	@@output
	@param $admin_name #管理员用户名
	*/
	{
		if(session('admin_name'))
		{
			return array(
				200,
				session('admin_name')
			);
		}
		
		return array(
				200,
				''
		);
	}
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
}
