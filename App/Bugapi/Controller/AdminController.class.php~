<?php
namespace Bugapi\Controller;
use Bugapi\Controller;
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
	 * create table hr_admin(id int primary key auto_increment,
	                         number varchar comment '编号',
	                         admin_name varchar(255) comment '管理员名称',
	                         passwd varchar(255) comment '管理员密码',
	                         name varchar(255) comment '姓名',
	                         status int not null default 0 comment '状态',
				   part int not null default 0 comment '部门', 
	                         role int not null default 0 comment '角色',
	                         position int not null default 0 comment '岗位',
	                         add_time int not null default 0 comment '添加日期'
	                         )charset=utf8;
	 * */
	 
	 protected $_module_name = 'admin';
	 protected $id;
	 protected $number;
	 protected $admin_name; #管理员用户名
	 protected $passwd;     #管理员密码
	 protected $name;       #姓名
	 protected $status;     #状态
	 protected $part;       #部门
	 protected $role;       #角色
	 protected $position;   #岗位
	 protected $add_time;   #新增日期
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
	
	#添加管理员
	public function add($content)
	/*
	 @@input
	 @param string $number      编号
	 @param string $admin_name  名称
	 @param string $passwd      密码
	 @param string $name        姓名
	 @param string $status      状态
	 @param int    $part        部门
	 @param int    $role        角色
	 @param int    $position    岗位
	 @@output
	 @param $is_success 0-成功,-1-失败
	 */
	{
		$data = $this->fill($content);
		
		if(!isset($data['number'])
		|| !isset($data['admin_name'])
		|| !isset($data['passwd'])
		|| !isset($data['name'])
		|| !isset($data['status'])
		|| !isset($data['part'])
		|| !isset($data['role'])
		|| !isset($data['position'])
		)
		{
			return C('param_err');
		}
		
		$data['number']     = htmlspecialchars(trim($data['number'])); 
		$data['admin_name'] = htmlspecialchars(trim($data['admin_name']));
		$data['passwd']     = htmlspecialchars(trim($data['passwd']));
		$data['name']       = htmlspecialchars(trim($data['name']));
		$data['status']      = htmlspecialchars(trim($data['status']));
		$data['part']       = intval(trim($data['part']));
		$data['role']       = intval(trim($data['role']));
		$data['position']   = intval(trim($data['position']));
		
		
		if('' == $data['number']
		|| '' == $data['admin_name']
		|| '' == $data['passwd']
		|| '' == $data['name']
		|| '' == $data['status']
		|| 0 >= $data['part']
		|| 0 >= $data['role']
		|| 0 >= $data['position']
		)
		{
			return C('param_fmt_err');
		}
		
		$data['passwd'] = md5($data['passwd']);
		
		$data['add_time'] = time();
		
		if(M($this->_module_name)->add($data))
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
	 
	
	public function update_passwd($content)
	/*
	 @@input
	 @param $admin_name  名称
	 @param $passwd      新密码
	 @@output
	 @param $is_success 0-成功,-1-失败
	 * */
	{
		$data = $this->fill($content);
		
		if(!isset($data['admin_name'])
		|| !isset($data['passwd']))
		{
			return C('param_err');
		}
				
		$data['admin_name'] = htmlspecialchars(trim($data['admin_name']));
		$data['passwd'] = htmlspecialchars(trim($data['passwd']));
		
		if('' == $data['admin_name']
		|| '' == $data['passwd'])
		{
			return C('param_fmt_err');
		}
		
		if(false !== M($this->_module_name)->where(array('admin_name'=>$data['admin_name']))
		                                   ->save(array('passwd'=>md5($data['passwd']))))
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
	 
	 
	public function get_list($content)
	{
		list($data, $record_count) = parent::get_list($content);

		$list = array();
		if($data)
		{
			foreach($data as $v)
			{
				$list[] = array(
						'id'          => intval($v['id']),
						'number'      => urlencode($v['number']),
						'admin_name'  => urlencode($v['admin_name']),
						'name'        => urlencode($v['name']),
						'status'      => $v['status'],
						'part'        => intval($v['part']),
						'role'        => intval($v['role']),
						'position'    => intval($v['position']),
						'add_time'    => intval($v['add_time']),
						
					);	
			}
		}

		return array(200, 
				array(
					'list'=>$list,
					'record_count'=> $record_count,
					)
				);
	}
	 
	#查询名称是否存在
	public function exists_name($content)
	/*
	@@input
	@param admin_name  企业名称 
	@@output
	@param $is_exists 0-存在,-1-不存在
	*/
	{
		$data = $this->fill($content);
		if(!isset($data['admin_name']))
		{
			return C('param_err');
		}
		
		$data['admin_name'] = htmlspecialchars(trim($data['admin_name']));
		
		if('' == $data['admin_name'])
		{
			return C('param_fmt_err');
		}
		
		if($this->__exists('admin_name', $data['admin_name']))
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
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
}
