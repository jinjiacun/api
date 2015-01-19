<?php
namespace Soapi\Controller;
use Soapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--企业别名管理--
------------------------------------------------------------
function of api:
 

#添加企业别名
public function add
	@@input
	@param $company_id 企业id
	@param $name       企业别名
@@output
@param $is_success 0-操作成功,-1-操作失败
##--------------------------------------------------------##
#查询企业别名
public function get_list
##--------------------------------------------------------##
#查询企业别名是否存在
public function exists_name
@@input
@param name  企业别名 
@@output
@param $is_exists 0-存在,-1-不存在
*/
class CompanyaliasController extends BaseController {
	/**
	 * sql script:
	 * create table so_company_alias(id int primary key auto_increment,
							   company_id int not null default 0 comment '企业id',
	                           name varchar(255) comment '别名',
	                           add_time int not null default 0 comment '添加日期'
	                           )charset=utf8;
	 * */
	protected $_module_name = 'company_alias';
	
	protected $id;
	protected $company_id;   #企业id
	protected $name;         #企业
	protected $add_time;
	
	#添加企业别名
	public function add($content)
	/*
	@@input
	@param $company_id 企业id
	@param $name       企业别名
	@@output
	@param $is_success 0-操作成功,-1-操作失败
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['company_id'])
		|| !isset($data['name'])
		)
		{
			return C('param_err');
		}
		
		$data['company_id'] = intval($data['company_id']);
		$data['name']       = htmlspecialchars(trim($data['name']));
		
		if(0>= $data['company_id']
		|| ''==$data['name'] 
		)
		{
			return C('param_err');
		}
		
		$data['add_time'] = time();
		if(M($this->_module_name)->add($data))
		{
			return array(
				200,
				array(
					'is_success'=>0,
					'messsage'=>C('option_ok'),
					'id'=> M()->getLastInsID(),
				)
			);
		}
		
		return array(
				200,
				array(
					'is_success'=>-1,
					'messsage'=>C('option_fail')
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
						'id'         => intval($v['id']),
						'company_id' => intval($v['company_id']),
						'name'       => urlencode($v['name']),
						'add_time'   => intval($v['add_time']),
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
	
	#查询企业别名是否存在
	public function exists_name($content)
	/*
	@@input
	@param name  企业别名 
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
