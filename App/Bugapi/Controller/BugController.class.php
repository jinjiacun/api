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
@param string $title       标题
@param int    $project     项目名称
@param int    $put_member  bug提出者
@param int    $get_member  bug接受者
@param int    $do_member1  bug执行者，多个之间用逗号隔开
@param int    $problem_level 问题等级(越大越严重)
@param int    $time_level    时间等级(越大越紧急)
@param int    $status        状态(0-提出,1-分配,2-执行,3-完成)
@param int    $close_time    关闭时间
@@output
@param $is_success 0-操作成功,-1-操作失败
##--------------------------------------------------------##
*/
class BugController extends BaseController {
	/**
	 * sql script:
	 * create table hr_bug(id int primary key auto_increment,
	                                title varchar(255) comment '标题',
                                     project int not null default 0 comment '项目',
                                     put_member int not null default 0 comment '提出者',
                                     get_member int not null default 0 comment '接受者',
                                     do_member varchar(255) not null default 0 comment '执行者,多个之间用逗号隔开',
                                     problem_level int not null default 0 comment '问题等级,越大越严重',
                                     time_level int not null default 0 comment '时间紧迫等级，越大越紧急',
	                                 status int not null default 0 comment '状态(0-提出,1-分配,2-执行,3-完成)',
                                     close_time int not null default 0 comment '关闭日期',
	                                 add_time int not null default 0 comment '添加日期'
	                                 )charset=utf8;
	 * */
	 
	 public $_module_name = 'Bug';
	 public $id;
	 public $project;
	 public $put_member;
	 public $get_member;
	 public $do_member;
	 public $problem_level;
	 public $time_level;
	 public $status;
	 public $close_time;
	 public $add_time;      //注册时间
         
     public function add($content)
     /*
     @@input
	 @param string $title       标题
	 @param int    $project     项目名称
	 @param int    $put_member  bug提出者
	 @param int    $get_member  bug接受者
	 @param int    $do_member1  bug执行者，多个之间用逗号隔开
	 @param int    $problem_level 问题等级(越大越严重)
	 @param int    $time_level    时间等级(越大越紧急)
	 @param int    $status        状态(0-提出,1-分配,2-执行,3-完成)
	 @param int    $close_time    关闭时间
	 @@output
	 @param $is_success 0-操作成功,-1-操作失败
     */
     {
		 $data = $this->fill($content);
		
		if(!isset($data['title'])
		|| !isset($data['project'])
		|| !isset($data['put_member'])
		|| !isset($data['get_member'])
		|| !isset($data['problem_level'])
		|| !isset($data['time_level'])
		)
		{
				return C('param_err');
		}
	
		$data['title']         = htmlspecialchars(trim($data['title']));
		$data['project']       = intval(trim($data['project']));
		$data['put_member']    = intval(trim($data['put_member']));
		$data['get_member']    = intval(trim($data['get_member']));
		$data['problem_level'] = intval(trim($data['problem_level']));
		$data['time_level']    = intval(trim($data['time_level']));
		
		if('' == $data['title']
		|| 0 >= $data['project']
		|| 0 >= $data['put_member']
		|| 0 >= $data['get_member']
		|| 0 > $data['problem_level']
		|| 0 > $data['time_level']
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
										'id'            => intval($v['id']),
										'title'         => urlencode($v['title']),
										'project'       => intval($v['project']),
										'put_member'    => intval($v['put_member']),
										'get_member'    => intval($v['get_member']),
										'do_member'     => $v['do_member'],
										'problem_level' => intval($v['problem_level']),
										'time_level'    => intval($v['time_level']),
										'status'        => intval($v['status']),
										'close_time'    => intval($v['close_time']),
										'add_time'      => intval($v['add_time']),
										
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
				'title'         => urlencode($tmp_one['title']),
				'project'       => intval($tmp_one['project']),
				'put_member'    => intval($tmp_one['put_member']),
				'get_member'    => intval($tmp_one['get_member']),
				'do_member'     => $tmp_one['do_member'],
				'problem_level' => intval($tmp_one['problem_level']),
				'time_level'    => intval($tmp_one['time_level']),
				'status'        => intval($tmp_one['status']),
				'close_time'    => intval($tmp_one['close_time']),
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
