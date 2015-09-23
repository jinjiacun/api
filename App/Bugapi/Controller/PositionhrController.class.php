<?php
namespace Bugapi\Controller;
use Bugapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--简历岗位管理--
------------------------------------------------------------
function of api:

public function add
@@input
@param int    $part_id      部门id
@param string $name         资源名称
@param int    $status       状态(默认0，开启)
@param string $description  资源描述
@@output
@param $is_success 0-操作成功,-1-操作失败
##--------------------------------------------------------##
*/
class PositionhrController extends BaseController {
	/**
	 * sql script:
	 * create table hr_position_hr(id int primary key auto_increment,
	                             part_id int not null default 0 comment '部门id',
	                             name varchar(255) comment '名称',
                                     status int not null default 0 comment '状态(默认0，开启)',
                                     description varchar(255) comment '描述',
                                     start_time int not null default 0 comment '开启日期',
	                             add_time int not null default 0 comment '添加日期'
	                             )charset=utf8;
	 * */
	 
	 public $_module_name = 'position_hr';
	 public $id;
	 public $part_id;
	 public $name;
        public $status;
        public $description;
        public $start_time;
	 public $add_time;     
         
	 public function add($content)
	 /*
	 @@input
	 @param int    $part_id      部门id
	 @param string $name         资源名称
	 @param string $status       状态(默认0,开启)
	 @param string $description  资源描述
	 @@output
	 @param $is_success 0-操作成功,-1-操作失败
	 */
	 {
		$data = $this->fill($content);
	
		if(!isset($data['part_id'])
		|| !isset($data['name'])
		|| !isset($data['status'])
		|| !isset($data['description'])
		|| !isset($data['start_time'])
		)
		{
				return C('param_err');
		}
	
	       $data['part_id']      = intval(trim($data['part_id']));
		$data['name']         = htmlspecialchars(trim($data['name']));
		$data['status']       = htmlspecialchars(trim($data['status']));
		$data['description']  = htmlspecialchars(trim($data['description']));
	
		if('' == $data['name']
		|| '' == $data['url']
		|| '' == $data['description']
		|| 0 > $data['is_partition']
		)
		{
				return C('param_fmt_err');
		}
	
	       $data['start_time'] = time();
		$data['add_time'] = $data['start_time'];
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
											'part_id'     => intval($v['part_id']),
											'name'        => urlencode($v['name']),
											'status'      => urlencode($v['status']),
											'description' => urlencode($v['description']),
											'start_time'  => intval($v['start_time']),
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
					'part_id'     => intval($tmp_one['part_id']),
					'name'        => urlencode($tmp_one['name']),
					'status'      => urlencode($tmp_one['status']),
					'description' => urlencode($tmp_one['description']),
					'start_time'  => intval($tmp_one['start_time']),
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
