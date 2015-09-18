<?php
namespace Bugapi\Controller;
use Bugapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--项目成员管理--
------------------------------------------------------------
function of api:

public function add
@@input
@param int $project_id   项目id
@param int $position_id  职位id
@param int admin_id      用户id
@param string $resource 资源id,多个用逗号隔开
@@output
@param $is_success 0-操作成功,-1-操作失败
##--------------------------------------------------------##
*/
class ProjectmemController extends BaseController {
	/**
	 * sql script:
	 * create table hr_project_mem(id int primary key auto_increment,
	                             project_id int not null default 0 comment '项目id',
	                             position_id int not null default 0 comment '职位id',
	                             admin_id int not null default 0 comment '用户id',
	                             add_time int not null default 0 comment '添加日期'
	                             )charset=utf8;
	 * */
	 
	 public $_module_name = 'Project_mem';
	 public $id;
	 public $project_id;
     public $position_id;
     public $admin_id;
	 public $add_time;      //注册时间
         
     public function add($content)
     /*
      @@input
	  @param int $project_id  项目id
	  @param int $position_id 职位id
	  @param int admin_id     用户id
	  @@output
	  @param $is_success 0-操作成功,-1-操作失败
      * */    
     {
		$data = $this->fill($content);
		
		if(!isset($data['project_id'])
		|| !isset($data['position_id'])
		|| !isset($data['admin_id'])
		)
		{
				return C('param_err');
		}
	
		$data['project_id']  = intval(trim($data['project_id']));
		$data['position_id'] = intval(trim($data['position_id']));
		$data['admin_id']    = intval(trim($data['admin_id']));
	
		if(0 > $data['project_id']
		|| 0 > $data['position_id']
		|| 0 > $data['admin_id']
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
										'project_id'  => intval($v['project_id']),
										'position_id' => intval($v['position_id']),
										'admin_id'    => intval($v['admin_id']),
										'add_time'    => intval($v['add_time']),
										
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
				'project_id'  => intval($tmp_one['project_id']),
				'position_id' => intval($tmp_one['position_id']),
				'admin_id'    => intval($tmp_one['admin_id']),
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
