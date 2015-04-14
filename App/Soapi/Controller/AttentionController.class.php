<?php
namespace Soapi\Controller;
use Soapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--我的关注管理--
------------------------------------------------------------
function of api:

##--------------------------------------------------------##
*/
class AttentionController extends BaseController {
		/**
	 * sql script:
	 * create table so_attention(id int primary key auto_increment,
	                             user_id int not null default 0 comment '用户id',
	                             company_id int not null default 0 comment '企业id',
	                             add_time int not null default 0 comment '添加日期'
	                            )charset=utf8;
	 * */
	 
	 protected $_module_name = 'Attention';
	 protected $id;         
	 protected $user_id;
	 protected $company_id;
	 protected $add_time;
	 
	 
	 #添加我的关注
	 public function add($content)
	 /*
	 @@input
	 @param $user_id
	 @param $company_id
	 @@output
	 @param $is_success 0-成功,-1-失败,-2-不允许操作
	 */
	{
		$data = $this->fill($content);
		
		if(!isset($data['user_id'])
		|| !isset($data['company_id'])
		)
		{
			return C('param_err');
		}
		
		$data['user_id']      = intval($data['user_id']);
		$data['company_id']   = intval($data['company_id']);
		
		if(0 >= $data['user_id']
		|| 0 >= $data['company_id']
		)
		{
			return C('param_fmt_err');
		}
		
		$data['add_time'] = time();
		
		if(!$this->__check_exists(array('company_id'=>$data['company_id'],
		                         'user_id'=>$data['user_id'])))
		{
			return array(
				200,
				array(
					'is_success'=>-2,
					'message'=>urlencode('不允许操作'),
				)
			);
		}
		
		
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
	
	public function get_list($content)
	{
		list($data, $record_count) = parent::get_list($content);

		$list = array();
		if($data)
		{
			foreach($data as $v)
			{
				$list[] = array(
						 'id'            => intval($v['id']),
						 'user_id'       => intval($v['user_id']),
						 'user_nickname' => $this->_get_nickname($v['user_id']),
						 'company_id'    => intval($v['company_id']),
						 'company_name'  => A('Soapi/Company')->get_name_by_id($v['company_id']),
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
