<?php
namespace Bugapi\Controller;
use Bugapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--职位管理--
------------------------------------------------------------
function of api:

public function add
@@input
@param string $number 编号
@param string $name   名称
@param int    $create 创建人
@@output
@param $is_success 0-操作成功,-1-操作失败
##--------------------------------------------------------##
*/
class PositionController extends BaseController {
	/**
	 * sql script:
	 * create table hr_position(id int primary key auto_increment,
	                             number varchar(255) comment '编号',
	                             name varchar(255) comment '名称',
	                             create int not null default 0 comment '创建人',
	                             add_time int not null default 0 comment '添加日期'
	                             )charset=utf8;
	 * */
	 
	 public $_module_name = 'Position';
	 public $id;
	 public $number;
	 public $name;
	 public $create;
	 public $add_time;      //注册时间
         
     public function add($content)
     /*
      @@input
         @param string $number 编号
	  @param string $name  名称
	  @param int    $create 创建人
	  @@output
	  @param $is_success 0-操作成功,-1-操作失败
      * */    
     {
		$data = $this->fill($content);
		
		if(!isset($data['name'])
		|| !isset($data['number'])
		|| !isset($data['create'])
		)
		{
				return C('param_err');
		}
	
	       $data['number'] = htmlspecialchars(trim($data['number']));
		$data['name']   = htmlspecialchars(trim($data['name']));
		$data['create'] = intval(trim($data['create']));
	
		if('' == $data['name']
		|| '' == $data['number']
		|| 0 > $data['create']
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
										'number'      => urlencode($v['number']),
										'name'        => urlencode($v['name']),
										'create'      => intval($v['create']),
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
				'number'      => urlencode($tmp_one['number']),
				'name'        => urlencode($tmp_one['name']),
				'create'      => intval($tmp_one['create']),
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
