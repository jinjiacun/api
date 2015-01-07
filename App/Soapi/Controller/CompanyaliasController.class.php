<?php
namespace Soapi\Controller;
use Api\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--企业别名管理--
------------------------------------------------------------
function of api:
 

#

#查询企业别名
public function get_list
##--------------------------------------------------------##
*/
class CompanyaliasController extends BaseController {
	/**
	 * sql script:
	 * create table so_comment(id int primary key auto_increment,
	                           name varchar(255) comment '别名',
	                           add_time int not null default 0 comment '添加日期'
	                           )charset=utf8;
	 * */
	protected $_module_name = 'company_alias';
	
	protected $id;
	protected $company_id;   #企业id
	protected $name;         #企业
	protected $add_time;
	
	
}
