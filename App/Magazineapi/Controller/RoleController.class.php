<?php
namespace Magazineapi\Controller;
use Magazineapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--角色管理--
------------------------------------------------------------
function of api:
*/
class RoleController extends BaseController {
	/**
	 * sql script:
	 * create table so_role(id int primary key auto_increment,
	                         name varchar(255) comment '名称',
	                         `right` int not null default 0 comment '权值',
	                         add_time int not null default 0 comment '添加日期'
	                         )charset=utf8;
	 * */
	 
	 protected $_module_name = 'role';
	 protected $id;         
	 protected $name;
	 protected $right;
	 protected $add_time;
	 
	 #添加
	 public function add($content)
	 /*
	 @@input
     @param string $name
     @param int    $right
	 @@output
	 @param $is_success 0-成功,-1-失败
	 */
	 {
		$data = $this->fill($content);
		
		if(!isset($data['name'])
		|| !isset($data['right'])
		)
		{
			return C('param_err');
		}
	
		if('' == $data['name']
		|| '' == $data['right']
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
				)
			);
		}
	
		return array(
				200,
				array(
					'is_success'=>-1,
					'message'=>C('option_fail'),
				)
			);
	 }
	 
		#通过id查询单条
		public function get_info($content)
		/*
		@@input
		@param $id                id
		@@output
		@param $id                id
		@param $name              名称
		@param $right             权值
		@param $add_time          添加日期
		*/
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
						'id'           => intval($tmp_one['id']),
					    'name'         => urlencode($tmp_one['name']),
						'right'        => intval($tmp_one['right']),
						'add_time'     => intval($tmp_one['add_time']),
				);
			}
		
			return array(
				200,
				$list
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
							'id'           => intval($v['id']),
							'name'         => urlencode($v['name']),
							'right'        => intval($v['right']),
							'add_time'     => intval($v['add_time']),							
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
		
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
}

