<?php
namespace Soapi\Controller;
use Soapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--查询纪录管理--
------------------------------------------------------------
function of api:

public function add
@@input
@param $user_id 会员id
@param $keyword 查询关键字
@@output
@param $is_success 0-操作成功,-1-操作失败
*/
class QuerylogController extends BaseController {
     /**
	 * sql script:
	  create table so_query_log(id int primary key auto_increment,
								 user_id int not null default 0 comment '会员id',
								 keyword varchar(255) comment '查询关键字',
								 add_time int not null default 0 comment '添加日期'
								 )charset=utf8;
	 * */

	protected $_module_name = "Query_log";
	protected $id;
	protected $user_id;  #会员id
	protected $keyword;  #查询关键字
	protected $add_time; #添加日期
	
	public function add($content)
	/*
	@@input
	@param $user_id 会员id
	@param $keyword 查询关键字
	@@output
	@param $is_success 0-操作成功,-1-操作失败
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['user_id'])
		|| !isset($data['keyword'])
		)
		{
			return C('param_err');
		}
		
		$data['user_id'] = intval($data['user_id']);
		$data['keyword'] = htmlspecialchars(trim($data['keyword']));
		
		if(0>= $data['user_id']
		|| '' == $data['keyword']
		)		
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
					'message'=>C('option_ok'),
				),
			);
		}
		
		return array(
				200,
				array(
					'is_success'=>0,
					'message'=>C('option_ok'),
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
						'user_id'     => intval($v['user_id']),
						'keyword'     => urlencode($v['keyword']),
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
	
}
