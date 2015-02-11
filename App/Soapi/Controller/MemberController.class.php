<?php
namespace Soapi\Controller;
use Soapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--会员管理--
------------------------------------------------------------
function of api:

public function add
@@input
@param $user_id  会员id
@param $nickname 昵称
@@output
@param $is_success 0-操作成功,-1-操作失败
##--------------------------------------------------------##
*/
class MemberController extends BaseController {
	/**
	 * sql script:
	 * create table so_member(id int primary key auto_increment,
	                             user_id int not null default 0 comment '用户id',	                             
	                             nickname varchar(255) comment '昵称',
	                             state int not null default 1 comment '1-未限制,0-关闭',
	                             ip varchar(255) comment '限制ip,空白',
	                             last_login int not null default 0 comment '最后登录时间',
	                             last_login_ip varchar(255) comment '最后登录ip',
	                             user_agent varchar(255) comment '注册来源',
	                             add_time int not null default 0 comment '添加日期'
	                             )charset=utf8;
	 * */
	 
	 public $_module_name = 'Member';
	 public $id;
	 public $user_id;       //会员id
	 public $nickname;      //昵称
	 public $state;         //是否限制
	 public $ip;            //限制ip
	 public $last_login;    //最后登录时间
	 public $last_login_ip; //最后登录ip
	 public $add_time;      //注册时间
	 
	public function add($content)
	/*
	@@input
	@param $user_id  会员id
	@param $nickname 昵称
	@@output
	@param $is_success 0-操作成功,-1-操作失败
	*/
	{
		$data = $this->fill($content);
		if(!isset($data['user_id']))
		{
			return C('param_err');
		}
		
		$data['user_id'] = intval($data['user_id']);
		$data['nickname'] = htmlspecialchars(trim($data['nickname']));
		
		if(0>= $data['user_id'])
		{
			return C('param_fmt_err');
		}		
		
		$data['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
		$data['add_time'] = time();
		if(M($this->_module_name)->add($data))
		{
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
	
	public function get_list($content)
	{
		list($data, $record_count) = parent::get_list($content);

		$list = array();
		if($data)
		{
			foreach($data as $v)
			{
				$list[] = array(
						'id'             => intval($v['id']),
						 'user_id'       => intval($v['user_id']),
						 'nickname'      => urlencode($v['nickname']),
						 'state'         => intval($v['state']),
						 'ip'            => urlencode($v['ip']),
						 'last_login'    => intval($v['last_login']),
						 'last_login_ip' => urlencode($v['last_login_ip']),
						 'add_time'      => intval($v['add_time']),
						
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
}











