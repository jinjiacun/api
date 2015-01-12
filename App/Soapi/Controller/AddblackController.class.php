<?php
namespace Soapi\Controller;
use Soapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--加黑管理--
------------------------------------------------------------
function of api:
 

#添加加黑
* 约束条件(每个会员一天只能对一个企业加黑)
public function add
@@input
@param $user_id
@param $company_id
@@output
@param $is_success 0-操作成功，-1-操作失败，-2-超过了加黒条数
##--------------------------------------------------------##
public function get_list
* 
#统计加黑人数
public function stat_user_all_amount
@@input
@param $company_id 企业名称
@@output
@param $content 人数
##-----------------------------r---------------------------##
#统计加黑人用户信息
public function get_user_by_company_id
@@input
@param $company_id 企业名称
@@output
@param $user_id 用户id
##--------------------------------------------------------##
*/
class AddblackController extends BaseController {
	/**
	 * sql script:
	 * create table so_add_black(id int primary key auto_increment,
	                        user_id int not null default 0,
	                        company_id int not null default 0,
	                        add_time int not null default 0 comment '添加日期'
	                             )charset=utf8;
	 * */
	protected $_module_name = "add_black";
	protected $id;
	protected $user_id;    #会员id
	protected $company_id; #企业id
	protected $add_time;   #添加日期
	
	#添加加黑
	#* 约束条件(每个会员一天只能对一个企业加黑)
	public function add($content)
	/*
	@@input
	@param $user_id
	@param $company_id
	@@output
	@param $is_success 0-操作成功，-1-操作失败，-2-超过了加黒条数
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['user_id'])
		|| !isset($data['company_id'])
		)
		{
			return C('param_err');
		}
		
		$data['user_id'] = intval($data['user_id']);
		$data['company_id'] = intval($data['company_id']);
		
		if(0>= $data['user_id']
		|| 0>= $data['company_id']
		)
		{
			return C('param_fmt_err');
		}
		
		#检查
		if(!$this->check_may($data['user_id'], $data['company_id']))
		{
			return array(
				200,
				array(
					'is_success'=> -2,
					'message'   => urlencode('24小时之内只能一次加黑'),
				)
			);
		}
		
		$data['add_time'] = time();
		
		if(M($this->_module_name)->add($data))
		{
			return array(
				200,
				array(
					'is_success'=>0,
					'message'=> C('option_ok'),
					'id'=> M()->getLastInsID(),
				),
			);
		}
		
		return array(
			200,
			array(
				'is_success'=>-1,
				'message'=>C('option_fail')
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
					    'user_id'    => intval($v['user_id']),
	                    'company_id' => intval($v['company_id']),
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
		
	#检查是否可以加黑
	private function check_may($user_id, $company_id)
	{
		if(0>= $user_id
		|| 0>= $company_id
		)
		{
			return false;
		}
		
		$content = array(
			'user_id'    => $user_id,
			'company_id' => $company_id,
		);
		$star_time = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
		$end_time  = mktime(23, 59, 59, date('m'), date('d'), date('Y'));
		$content['add_time'] = array(array('gt', $star_time),
		                             array('lt', $end_time));
		if(M($this->_module_name)->where($content)->find())
		{
			return true;
		}
		
		return false;
	}
	
	#统计加黑人数
	public function stat_user_all_amount($content)
	/*
	@@input
	@param $company_id 企业名称
	@@output
	@param $content 人数
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['company_id']))
		{
			return C('param_err');
		}
		
		$data['company_id'] = intval($data['company_id']);
		
		if(0>= $data['company_id'])
		{
			return C('param_fmt_err');
		}
		
		$content = array(
			'company_id'=> $data['company_id'],
		);
		
		$re_count = 0;
		$re_count = M($this->_module_name)->where($content)->count();
		
		return array(
			200,
			$re_count
		);
	}
	
	#统计加黑人用户信息
	public function get_user_by_company_id($content)
	/*
	@@input
	@param $company_id 企业名称
	@@output
	@param $user_id 用户id
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['company_id']))
		{
			return C('param_err');
		}
		
		$data['company_id'] = intval($data['company_id']);
		
		if(0 >= $data['company_id'])
		{
			return C('param_fmt_err');
		}
		
		$list = array();
		$tmp_list = M($this->_module_name)->distinct(true)
		                                  ->field('user_id')
		                                  ->where($data)->select();
		if($tmp_list
		&& 0< count($tmp_list))
		{
			foreach($tmp_list as $v)
			{
				$list[] = array(
					'user_id'=>intval($v['user_id']),
				);
			}
			unset($v, $tmp_list);
		}
		
		return array(
			200,
			$list
		);
	}
}