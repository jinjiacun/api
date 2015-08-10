<?php
namespace Soapi\Controller;
use Soapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--接口方法及其参数管理--
------------------------------------------------------------
function of api:

##--------------------------------------------------------##
#添加接口参数
public function add
*/
class ApiController extends BaseController {
	/**
	 * sql script:
	 * create table so_api_stat(id int primary key auto_increment,
								  interface varchar(255) comment '接口名称',
								  method varchar(255) comment '方法名词',
	                              param text comment '参数',
	                              add_time int not null default 0 comment '添加日期'
	                             )charset=utf8;
	 * */
	 
	    protected $_module_name = 'Api';
        protected $id;
        protected $interface;
        protected $method;
        protected $param;
        protected $add_time;

		public function add($content)
		{
			$data = $this->fill($content);
			
		}		
}
