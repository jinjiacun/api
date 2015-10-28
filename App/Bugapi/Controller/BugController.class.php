<?php
namespace Bugapi\Controller;
use Bugapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--bug管理--
------------------------------------------------------------
function of api:

public function add
@@input
@param string $number           编号
@param int    $project_id       项目id
@param int    $project_mod_id   项目模块id
@param int    $put_member       bug提出者
@param int    $get_member       bug接受者
@param string $description      项目模块
@param int    $level            优先级
@param int    $status           状态(0-提出,1-分配,2-执行,3-完成)
@@output
@param $is_success 0-操作成功,-1-操作失败
##--------------------------------------------------------##
*/
class BugController extends BaseController {
	/**
	 * sql script:
	 * create table hr_bug(id int primary key auto_increment,
	                             title varchar(255) comment '标题，简要描述',
	                             number varchar(255) comment '编号',
                                     project_id int not null default 0 comment '项目id',
                                     project_mod_id int not null default 0 comment '项目模块id',
                                     put_member int not null default 0 comment '提出者',
                                     get_member int not null default 0 comment '接受者',
                                     description text comment '描述',
                                     level int not null default 0 comment '优先级',
	                              status int not null default 0 comment '状态(0-提出,1-分配,2-执行,3-完成)',
	                              last_update int not null default 0 comment '最后更新人',
	                              last_update_time int not null default 0 comment '最后更新日期',
	                              add_time int not null default 0 comment '添加日期'
	                              )charset=utf8;
	 * */
	 
	 public $_module_name = 'Bug';
	 public $id;
	 public $title;
	 public $number;
	 public $project_id;
	 public $project_mod_id;
	 public $put_member;
	 public $get_member;
	 public $description;
	 public $level;
	 public $status;
	 public $last_update;
	 public $last_update_time;
	 public $add_time;      //注册时间
         
     public function add($content)
     /*
     @@input
        @param string $title           标题
	 @param string $number          编号
	 @param int    $project_id      项目id
	 @param int    $project_mod_id   项目模块id
	 @param int    $put_member       bug提出者
	 @param int    $get_member       bug接受者
	 @param string $description      描述
	 @param int    $level            优先级
	 @param int    $status           状态(0-提出,1-分配,2-执行,3-完成)
	 @@output
	 @param $is_success 0-操作成功,-1-操作失败
     */
     {
		 $data = $this->fill($content);
		
		if(!isset($data['title'])
		|| !isset($data['number'])
		|| !isset($data['project_id'])
		|| !isset($data['project_mod_id'])
		|| !isset($data['put_member'])
		|| !isset($data['get_member'])
		|| !isset($data['description'])
		|| !isset($data['level'])
		|| !isset($data['status'])
		)
		{
				return C('param_err');
		}
	
	       $data['title']           = htmlspecialchars(trim($data['title']));
		$data['number']          = htmlspecialchars(trim($data['number']));
		$data['project_id']      = intval(trim($data['project_id']));
		$data['project_mod_id']  = intval(trim($data['project_mod_id']));
		$data['put_member']      = intval(trim($data['put_member']));
		$data['get_member']      = intval(trim($data['get_member']));
		$data['description']     = htmlspecialchars(trim($data['description']));
		$data['level']           = intval(trim($data['level']));
		$data['status']          = intval(trim($data['status']));
		
		if('' == $data['title']
		|| '' == $data['number']
		|| 0 >= $data['project_id']
		|| 0 >= $data['project_mod_id']
		|| 0 >= $data['put_member']
		|| 0 >= $data['get_member']
		|| '' == $data['description']
		|| 0 > $data['level']
		|| 0 > $data['status']
		)
		{
				return C('param_fmt_err');
		}
				
        $data['add_time'] = time();
		
		if(M($this->_module_name)->add($data))
		{
			#推送
			$this->_mosquitto_push($data['get_member']);
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
										'id'               => intval($v['id']),
										'title'            => urlencode($v['title']),
										'number'           => urlencode($v['number']),
										'project_id'       => intval($v['project_id']),
										'project_mod_id'   => intval($v['project_mod_id']),
										'put_member'       => intval($v['put_member']),
										'get_member'       => intval($v['get_member']),
										'description'      => urlencode(htmlspecialchars_decode($v['description'])),
										'level'            => intval($v['level']),
										'status'           => intval($v['status']),
										'last_update'      => intval($v['last_update']),
										'last_update_time' => intval($v['last_update_time']),
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
				'id'              => intval($tmp_one['id']),
				'title'           => urlencode($tmp_one['title']),
				'number'          => urlencode($tmp_one['number']),
				'project_id'      => intval($tmp_one['project_id']),
				'project_mod_id'  => intval($tmp_one['project_mod_id']),
				'put_member'      => intval($tmp_one['put_member']),
				'get_member'      => intval($tmp_one['get_member']),
				'description'     => urlencode(htmlspecialchars_decode($tmp_one['description'])),
				'level'           => intval($tmp_one['level']),
				'status'          => intval($tmp_one['status']),
				'last_update'     => intval($tmp_one['last_update']),
				'last_update_time'=> intval($tmp_one['last_update_time']),
				'add_time'    => intval($tmp_one['add_time']), 
			);
		}
		
		return array(
			200,
			$list
		);
	 }
	 
	#查询本人的bug及其级别
	public function get_self_bug($content)
	{
		$data = $this->fill($content);
		
		if(!isset($data['admin_id']))
		{
			return C('param_err');
		}
		
		$data['admin_id'] = htmlspecialchars(trim($data['admin_id']));
		
		if(0 >= $data['admin_id'])
		{
			return C('param_fmt_err');
		}
		
		$tmp_one = M($this->_module_name)->field("level")
		                                 ->where(array('get_member'=>$data['admin_id'],
		                                               'level'=>1,
		                                               'status'=>1))
		                                 ->find();
		
		if($tmp_one)
		{
			return array(
				200,
				array(
					'is_success'=>0,
					'message'=>urlencode('严重bug'),
				),
			);
		}
		$tmp_one = M($this->_module_name)->field("level")
		                                 ->where(array('get_member'=>$data['admin_id'],
		                                               'status'=>1))
		                                 ->find();
		if($tmp_one)
		{
			return array(
					200,
					array(
						'is_success'=>1,
						'message'=>urlencode('有bug'),
					),
				);
		}
		
		return array(
					200,
					array(
						'is_success'=>-1,
						'message'=>urlencode('错误'),
					),
				);
		
	} 
}
?>
