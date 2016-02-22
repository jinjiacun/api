<?php
namespace Bugapi\Controller;
use Bugapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--项目状态管理--
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
class ProjectstatusController extends BaseController {
	/**
	 * sql script:
	 * create table hr_project_status(id int primary key auto_increment,
	                           project_id int not null default 0 comment '项目id',
	                           status int not null default 0 comment '项目状态',
	                           description text comment '状态描述',
                               `create` varchar(255) comment '项目创建人',
                               is_current int not null default 0 comment '当前状态(1-当前状态,0-历史状态)',
	                           add_time int not null default 0 comment '添加日期'
	                          )charset=utf8;
	 * */
	 
	 public $_module_name = 'Project_status';
	 public $id;
	 public $protect_id;
	 public $status;
	 public $description;
	 public $create;
	 public $is_current;
	 public $add_time;      //注册时间
         
     public function add($content)
     /*
     @@input
     @param int         $project_id       项目id
     @param int         $status           项目状态
     @param string      $description      项目状态描述
	 @param string      $create           创建人
	 @param int         $is_current       是否是当前状态
	 @@output
	 @param $is_success 0-操作成功,-1-操作失败
     */
     {
		$data = $this->fill($content);
		
		if(!isset($data['project_id'])
		|| !isset($data['status'])
		|| !isset($data['description'])
		|| !isset($data['create'])
		|| !isset($data['is_current'])
		)
		{
				return C('param_err');
		}
	
	    $data['project_id']        = intval(trim($data['project_id']));
		$data['status']            = intval(trim($data['status']));
		$data['description'] 	   = htmlspecialchars(trim($data['description']));
	    $data['create']            = intval(trim($data['create']));
	    $data['is_current']        = intval(trim($data['is_current']));
	
		if(0 > $data['project_id']
		|| 0 > $data['status']
		|| '' == $data['description']
		|| 0 > $data['create']
		|| 0 > $data['is_current']
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
								'id'=>M()->getLastInsID(),
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
										'id'               => intval($v['id']),
										'project_id'       => intval($v['project_id']),
	                                    'status'           => intval($v['status']),
	                                    'description'      => urlencode($v['description']),
                                        'create'           => intval($v['create']),
                                        'is_current'       => intval($v['is_current']),
										'add_time'         => intval($v['add_time']),
										
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
				'id'               => intval($tmp_one['id']),
				'project_id'       => intval($tmp_one['project_id']),
				'status'           => intval($tmp_one['status']),
				'description'      => urlencode($tmp_one['description']),
				'create'           => intval($tmp_one['create']),
				'is_current'       => intval($tmp_one['is_current']),
				'add_time'         => intval($tmp_one['add_time']), 
			);
		}
		
		return array(
			200,
			$list
		);
	 }
	 
	 
}
?>
