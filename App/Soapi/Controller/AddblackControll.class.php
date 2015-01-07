<?php
namespace Soapi\Controller;
use Api\Controller;
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
*/
class CompanyController extends BaseController {
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
		
	}
		
	#检查是否可以加黑
	private function check_may($user_id, $company_id)
	{
		
	}
}
