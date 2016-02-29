<?php
namespace Bugapi\Controller;
use Bugapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--项目关系管理--
------------------------------------------------------------
function of api:

public function add
@@input

@@output
@param $is_success 0-操作成功,-1-操作失败
##--------------------------------------------------------##
*/
class ProjectrelController extends BaseController {
	/**
	 * sql script:
	 * create table hr_project_rel(id int primary key auto_increment,
                                     project int not null default 0 comment '项目',
	                             member int not null default 0 comment '成员',
	                             add_time int not null default 0 comment '添加日期'
	                             )charset=utf8;
	 * */
	 
	 public $_module_name = 'Project';
	 public $id;
	 public $project;
     public $member;
	 public $add_time;      //注册时间
         
         
}
?>
