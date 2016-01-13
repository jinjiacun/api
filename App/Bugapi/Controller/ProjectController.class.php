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
	                           number varchar(255) comment '编号',
	                           name varchar(255) comment '项目名称',
                               description varchar(255) comment '项目描述',
                               create varchar(255) comment '项目创建人',
                               last_time int not null default 0 comment '最后更新时间',
                               status int not null default 0 comment '项目状态(0-开发中，1-已经上线)',
	                           add_time int not null default 0 comment '添加日期'
	                          )charset=utf8;
	 * */
	 
	 public $_module_name = 'Project';
	 public $id;
	 public $number;
	 public $name;
     public $description;
     public $create;
     public $last_time;
     public $status;
	 public $add_time;      //注册时间
         
     public function add($content)
     /*
     @@input
     @param string      $number           编号
	 @param string      $name             项目名称
	 @param string      $description      项目描述
	 @param int         $status           项目状态
	 @param string      $create           创建人
	 @@output
	 @param $is_success 0-操作成功,-1-操作失败
     */
     {
		$data = $this->fill($content);
		
		if(!isset($data['number'])
		|| !isset($data['name'])
		//|| !isset($data['description'])
		|| !isset($data['create'])
		)
		{
				return C('param_err');
		}
	
	       $data['number']            = htmlspecialchars(trim($data['number']));
		$data['name']              = htmlspecialchars(trim($data['name']));
		//$data['description'] 	     = htmlspecialchars(trim($data['description']));
	       $data['create']            = htmlspecialchars(trim($data['create']));
	
		if('' == $data['number']
		|| '' == $data['name']
		//|| '' == $data['description']
		|| '' == $data['create']
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
										'number'           => urlencode($v['number']),
										'name'             => urlencode($v['name']),
										'description'      => urlencode($v['description']),
										'create'           => urlencode($v['create']),
										'last_time'        => intval($v['last_time']),
										'status'           => intval($v['status']),
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
				'number'           => urlencode($tmp_one['number']),
				'name'             => urlencode($tmp_one['name']),
				'description'      => urlencode($tmp_one['description']),
				'create'           => urlencode($tmp_one['create']),
				'last_time'        => intval($tmp_one['last_time']),
				'status'           => intval($tmp_one['status']),
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
