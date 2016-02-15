<?php
namespace Magazineapi\Controller;
use Magazineapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--管理员管理--
------------------------------------------------------------
function of api:

#登录
public function login
@@input
@param $name #管理员用户名
@param $passwd     #管理员密码
@@output
@param $is_success 0-成功,-1-失败
##--------------------------------------------------------##
#获取登录信息
public function get_login_info
@@output
@param $name #管理员用户名
##--------------------------------------------------------##
*/
class Usercontroller extends BaseController {
	/**
	 * sql script:
	 * create table so_user(id int primary key auto_increment,
	                         name varchar(255) comment '管理员名称',
	                         passwd varchar(255) comment '管理员密码',
	                         nickname varchar(255) comment '昵称',
	                         sex int not null default 0 comment '性别(0-男,1-女)',
	                         last_time int not null default 0 comment '最后登录日期',
	                         last_ip varchar(255) comment '最后登录ip',
	                         add_time int not null default 0 comment '添加日期'
	                         )charset=utf8;
	 * */
	 
	 protected $_module_name = 'user';
	 protected $id;         
	 protected $name;       #用户名
	 protected $passwd;     #密码
	 protected $last_time;  #最后登录时间
	 protected $last_ip;    #最后登录ip
	 
	 #登录
	 public function login($content)
	 /*
	 @@input
	 @param $name #管理员用户名
	 @param $passwd     #管理员密码
	 @@output
	 @param $is_success 0-成功,-1-失败
	 */
	 {
		$data = $this->fill($content);
		
		if(!isset($data['name'])
		|| !isset($data['passwd'])
		)
		{
			return C('param_err');
		}
	
		if('' == $data['name']
		|| '' == $data['passwd']
		)
		{
			return C('param_fmt_err');
		}
		
		$data['passwd'] = md5($data['passwd']);
	
		$tmp_one = M($this->_module_name)->field("id, sex, nickname")
		                                 ->where($data)
		                                 ->find();
	    if($tmp_one)
	    {			
			return array(
				200,
				array(
					'is_success'=>0,
					'message' =>C('option_ok'),
					'id'      =>$tmp_one['id'],
					'nickname'=>$tmp_one['nickname'],
					'sex'     =>$tmp_one['sex'],
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
	
	#添加
	public function add($content)
	/*
	 @@input
	 @param $name        名称
	 @param $passwd      密码
	 @@output
	 @param $is_success 0-成功,-1-失败
	 */
	{
		$data = $this->fill($content);
		
		if(!isset($data['name'])
		|| !isset($data['passwd'])
		)
		{
			return C('param_err');
		}
		
		$data['name']       = htmlspecialchars(trim($data['name']));
		$data['passwd']     = htmlspecialchars(trim($data['passwd']));
		
		
		if('' == $data['name']
		|| '' == $data['passwd']
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
	 @param $name  名称
	 @param $passwd      新密码
	 @@output
	 @param $is_success 0-成功,-1-失败
	 * */
	{
		$data = $this->fill($content);
		
		if(!isset($data['name'])
		|| !isset($data['passwd']))
		{
			return C('param_err');
		}
				
		$data['name']   = htmlspecialchars(trim($data['name']));
		$data['passwd'] = htmlspecialchars(trim($data['passwd']));
		
		if('' == $data['name']
		|| '' == $data['passwd'])
		{
			return C('param_fmt_err');
		}
		
		if(false !== M($this->_module_name)->where(array('name'=>$data['name']))
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
						'name'        => urlencode($v['name']),
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
	@param name  用户名称 
	@@output
	@param $is_exists 0-存在,-1-不存在
	*/
	{
		$data = $this->fill($content);
		if(!isset($data['name']))
		{
			return C('param_err');
		}
		
		$data['name'] = htmlspecialchars(trim($data['name']));
		
		if('' == $data['name'])
		{
			return C('param_fmt_err');
		}
		
		if($this->__exists('name', $data['name']))
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
