<?php
namespace Soapi\Controller;
use Soapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--接口统计管理--
------------------------------------------------------------
function of api:

##--------------------------------------------------------##
*/
class ApistatController extends BaseController {
	/**
	 * sql script:
	 * create table so_api_stat(id int primary key auto_increment,
	                              name varchar(255) comment '接口名称',
	                              run_time double not null default 0 comment '运行时间',
	                              type int not null default 0 comment '0-文本,1-图片,2-异常',
	                              add_time int not null default 0 comment '添加日期'
	                             )charset=utf8;
	 * */
	 
	    protected $_module_name = 'Api_stat';
        protected $id;
        protected $name;
        protected $run_time;
        protected $type;
        protected $add_time;
        
        public function add_ex($content)
        {
			$data = $this->fill($content);
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
			
			$list = $data;
			
			return array(200, array('list'=>$list, 
									'record_count'=>$record_count));
		}
}
