<?php
namespace Soapi\Controller;
use Api\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--新闻管理--
------------------------------------------------------------
function of api:
 

#添加
public function add
@@input
@param $exposal_id  入库id 
@param $company_id  企业id
@@output
@param $is_success 0-操作成功，-1-操作失败
##--------------------------------------------------------##
#查询
public function get_list
##--------------------------------------------------------##
*/
class CompanymapController extends BaseController {
	/**
	 * sql script:
	 * create table so_company_map(id int primary key auto_increment,
								  exposal_id int not null default 0 comment '曝光/可信企业id',
								  company_id int not null default 0 comment '企业id',
	                              add_time int not null default 0 comment '添加日期'
	                             )charset=utf8;
	 * */
	 
	 protected $_module_name = 'company_map';
	 protected $id;
	 protected $exposal_id;  #曝光/可信企业id
	 protected $company_id;  #企业id
}
