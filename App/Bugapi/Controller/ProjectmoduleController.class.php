<?php
namespace Bugapi\Controller;
use Bugapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--项目模块管理--
------------------------------------------------------------
function of api:

public function add
@@input
@param string $project_id 项目id
@param string $name       名称
@@output
@param $is_success 0-操作成功,-1-操作失败
##--------------------------------------------------------##
*/
class ProjectmoduleController extends BaseController {
	/**
	 * sql script:
	 * create table hr_project_mod(id int primary key auto_increment,
	                             project_id int not null default 0 comment '项目id',
	                             name varchar(255) comment '名称',
	                             add_time int not null default 0 comment '添加日期'
	                             )charset=utf8;
	 * */
	 
	 public $_module_name = 'Project_mod';
	 public $id;
	 public $project_id;
	 public $name;
	 public $add_time;      //注册时间
         
     public function add($content)
     /*
      @@input
         @param string $project_id 项目id
	  @param string $name      名称
	  @@output
	  @param $is_success 0-操作成功,-1-操作失败
      * */    
     {
		$data = $this->fill($content);
		
		if(!isset($data['name'])
		|| !isset($data['project_id'])
		)
		{
				return C('param_err');
		}
	
	       $data['project_id'] = intval(trim($data['project_id']));
		$data['name']       = htmlspecialchars(trim($data['name']));
	
		if('' == $data['name']
		|| 0 == $data['project_id']
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
										'name'        => urlencode($v['name']),
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
				'name'        => urlencode($tmp_one['name']),
                              'project_id' => intval($tmp_one['project_id']),
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
