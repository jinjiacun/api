<?php
namespace Soapi\Controller;
use Soapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--企业点赞管理--
------------------------------------------------------------
function of api:
 

#添加
public function add
@@input
@param $company_id 企业id
@param $user_id    用户id
@@output
@param $is_success 0-操作成功，-1-操作失败,-2-不允许操作
##--------------------------------------------------------##
#获取点赞总数
public function amount
@@input
@param $company_id 企业id
@@output
@param $is_success 0-成功,-1-失败
@param $amount  总数
##--------------------------------------------------------##
*/
class CompanyassistController extends BaseController {
	/**
	 * sql script:
	 * create table so_company_assist(id int primary key auto_increment,
	                              company_id int not null default 0 comment '新闻id',
	                              user_id int not null default 0 comment '用户id',
	                              add_time int not null default 0 comment '添加日期'
	                             )charset=utf8;
	 * */
	 
	 protected $_module_name = 'Company_assist';
	 protected $company_id;       #企业id
	 protected $user_id;          #用户id
	 
	 #添加
	 public function add($content)
	 /*
		@@input
		@param $company_id 新闻id
		@param $user_id 用户id
		@@output
		@param $is_success 0-操作成功，-1-操作失败,-2-不允许操作,-3-此企业不存在
	 */
	 {
		 $data = $this->fill($content);
		 
		 if(!isset($data['company_id'])
		 || !isset($data['user_id']))
		 {
			 return C('param_err');
		 }
		 
		 $data['company_id'] = intval($data['company_id']);
		 $data['user_id'] = intval($data['user_id']);
		 
		 if(0>= $data['company_id']
		 || 0>= $data['user_id'])
		 {
			 return C('param_fmt_err');
		 }
		 
		 
		 if(!$this->__check(array('company_id'=>$data['company_id'],
		                         'user_id'=>$data['user_id'])))
		 {
			 return array(
				200,
				array(
					'is_success'=>-2,
					'message'=>urlencode('不允许操作'),
				),
			 );
		 }
		 
		 if(!M('Company')->find($data['company_id']))
		 {
			 return array(
				200,
				array(
					'is_success'=> -3,
					'message' => urlencode('此企业不存在'),
				),
			 );
		 }
		 
		 $data['add_time'] = time();
		 
		 if(M($this->_module_name)->add($data))
		 {
			 //自动增加统计
			 $news_obj = A('Soapi/Company');
			 if($news_obj->__assist(array('id'=>$data['company_id']),'assist_amount'))
			 {
				$tmp_param = array(
					'id'=> $data['company_id'],
				);
				list(,$tmp_content) = A('Soapi/Company')->get_info(json_encode($tmp_param));
				return array(
					200,
					array(
						'is_success'=>0,
						'message'=>C('option_ok'),
						'amount'=>$tmp_content['assist_amount'],
					),
				); 
			 }
		 }
		 
		 return array(
				200,
				array(
					'is_success'=>-1,
					'message'=>C('option_fail'),
				),
		);
	 }
	 
	 
	 #获取点赞总数
	public function amount($content)
	/*
	@@input
	@param $company_id 企业id
	@@output
	@param $is_success 0-成功,-1-失败
	@param $amount  总数
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
		
		$amount = 0;
		
		$amount = M($this->_module_name)->where($data)->count();
		
		return array(
			200,
			array(
				'is_success'=>0,
				'message'=>C('option_ok'),
				'amount'=>$amount,
			)
		);
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
 }
