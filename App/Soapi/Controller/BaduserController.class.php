<?php
namespace Soapi\Controller;
use Soapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--非文明用户管理--
------------------------------------------------------------
function of api:
 

#添加
public function add
@@input
@param $user_id 用户id
@@output
@param $is_success 0-操作成功，-1-操作失败
##--------------------------------------------------------##
*/
class BaduserController extends BaseController {
	/**
	 * sql script:
	 * create table so_bad_user(id int primary key auto_increment,
	                              user_id int not null default 0 comment '用户id',
	                              add_time int not null default 0 comment '添加日期'
	                             )charset=utf8;
	 * */
	
	protected $_module_name = 'bad_user';
	protected $id;
	protected $user_id;
	protected $add_time;
	
	#添加
	public function add($content)
	/*
	@@input
	@param $user_id 用户id
	@@output
	@param $is_success 0-操作成功，-1-操作失败
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['user_id']))
		{
			return C('param_err');
		}
		
		$data['user_id'] = intval($data['user_id']);
		
		if(0>= $data['user_id'])
		{
			return C('param_fmt_err');
		}
		
		$data['add_time'] = time();
		
		if(M($this->_module_name)->add($data))
		{
			return array(
				200,
				array(
					'is_success'=>0,
					'message'=>C('option_ok')
				),
			);
		}
		
		return array(
			200,
			array(
				'is_success'=>-1,
				'message'=>urlencode('option_fail')
			),
		);
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}		
