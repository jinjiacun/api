<?php
namespace Bugapi\Controller;
use Bugapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--会员管理--
------------------------------------------------------------
function of api:

public function add
@@input
@param string $name        项目名称
@param string $description 项目描述
@param string $start_time  开始日期
@param string $end_time    结束日期
@@output
@param $is_success 0-操作成功,-1-操作失败
##--------------------------------------------------------##
*/
class ProjectController extends BaseController {
	/**
	 * sql script:
	 * create table hr_project(id int primary key auto_increment,
	                             name varchar(255) comment '项目名称',
                                 description varchar(255) comment '项目描述',
                                 status int not null default 0 comment '项目状态,默认0(0-立项,1-需求确认,2-ui设计,3-前端html处理，4-项目开发,5-测试,6-上线)',
                                 view_right varchar(255) default 0 comment '查看权限,默认0(0-开放,-1-不开发,部分开放-多个用户id，之间用逗号隔开)',
                                 process_time varchar(255) comment '进程状态(进入某个状态,连接状态数字标识，后接冒号然后unix时间戳，例如:0:123433423,1:1234343)',
                                 preplanning_time varchar(255) comment '同进程状态,在创建项目时录入所有规划',
                                 start_time int not null default 0 comment '项目开始日期',
                                 end_time int not null default 0 comment '项目结束日期',
	                             add_time int not null default 0 comment '添加日期'
	                             )charset=utf8;
	 * */
	 
	 public $_module_name = 'Project';
	 public $id;
	 public $name;
     public $description;
     public $start_time;
     public $end_time;
	 public $add_time;      //注册时间
         
     public function add($content)
     /*
     @@input
	 @param string      $name        项目名称
	 @param string      $description 项目描述
	 @param int         $status      状态
	 @param string      $view_right  
	 @param string      $
	 @param string $start_time  开始日期
	 @param string $end_time    结束日期
	 @@output
	 @param $is_success 0-操作成功,-1-操作失败
     */
     {
		$data = $this->fill($content);
		
		if(!isset($data['name'])
		|| !isset($data['description'])
		|| !isset($data['start_time'])
		|| !isset($data['end_time'])
		)
		{
				return C('param_err');
		}
	
		$data['name']        = htmlspecialchars(trim($data['name']));
		$data['description'] = htmlspecialchars(trim($data['description']));
		$data['start_time']  = intval(trim($data['start_time']));
		$data['end_time']    = intval(trim($data['end_time']));
	
		if('' == $data['name']
		|| '' == $data['description']
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
								'is_success'=>-1,
								'message'=>C('option_fail'),
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
										'name'        => urlencode($v['name']),
										'description' => urlencode($v['description']),
										'start_time'  => intval($v['start_time']),
										'end_time'    => intval($v['end_time']),
										'add_time'    => intval($v['add_time']),
										
								);	
				}
		}

		return array(200, 
					array(
							'list'=>$list,
							'record_count'=> $record_count,
							)
					
	 }
	 
	 public function get_info($content)
	 {
		$data = $this->fill($content);
		
		if(!isset($data['id']))
		{
			return C('param_err');
		}
		
		$data['id'] = intval($data['id']);
		
		if(0>= $data['id'])
		{
			return C('param_fmt_err');
		}
		
		$list = array();
		$tmp_one = M($this->_module_name)->find($data['id']);
		if($tmp_one)
		{
			$list = array(
				'id'          => intval($tmp_one['id']),
				'name'        => urlencode($tmp_one['name']),
                'description' => urlencode($tmp_one['description']),
                'start_time'  => intval($tmp_one['start_time']),
                'end_time'    => intval($tmp_one['end_time']),
				'add_time'    => intval($tmp_one['add_time']), 
			);
		}
		
		return array(
			200,
			$list
		);
	 }
	 
	 
}
?>
