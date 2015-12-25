<?php
namespace Bugapi\Controller;
use Bugapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--bug反馈管理--
------------------------------------------------------------
function of api:

public function add
@@input
@param string $name 名称
@@output
@param $is_success 0-操作成功,-1-操作失败
##--------------------------------------------------------##
*/
class BugfeedbackController extends BaseController {
	/**
	 * sql script:
	 * create table hr_bug_feedback(`id` int primary key auto_increment,
	                             `bug_id` int not null default 0 'bug的id',
	                             `create` varchar(255) comment '反馈人',
	                             'option_process' varchar(255) comment '操作过程',
	                             `content` varchar(255) comment '反馈内容',
	                             `status_remark` varchar(255) comment '状态变迁',
	                             `add_time` int not null default 0 comment '添加日期'
	                             )charset=utf8;
	 * */
	 
	 public $_module_name = 'Bug_feedback';
	 public $id;
	 public $bug_id;
	 public $create;
	 public $option_process;
	 public $content;
	 public $status_remark;
	 public $add_time;      //注册时间
         
     public function add($content)
     /*
      @@input
         @param int    $bug_id   bug_id
	  @param string $create  反馈人
	  @param string $content 反馈内容
	  @param string $status_remark 状态变迁
	  @@output
	  @param $is_success 0-操作成功,-1-操作失败
      * */    
     {
		$data = $this->fill($content);
		
		if(!isset($data['bug_id'])
		|| !isset($data['create'])
		|| !isset($data['content'])
		|| !isset($data['status_remark'])
		)
		{
				return C('param_err');
		}
	
	       $data['bug_id']        = intval(trim($data['bug_id']));
		$data['create']        = htmlspecialchars(trim($data['create']));
		$data['content']       = htmlspecialchars(trim($data['content']));
		$data['status_remark'] = htmlspecialchars(trim($data['status_remark']));
	
		if(0 == $data['bug_id']
		|| '' == $data['create']
		|| '' == $data['content']
		|| '' == $data['status_remark'])
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
										'id'           => intval($v['id']),
										'bug_id'       => intval($v['bug_id']),
										'create'       => urlencode($v['create']),
										'option_process'=> urlencode($v['option_process']),
										'content'      => urlencode($v['content']),
										'status_remark'=> urlencode($v['status_remark']),
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
				'id'            => intval($tmp_one['id']),
				'bug_id'        => intval($tmp_one['bug_id']),
				'create'        => urlencode($tmp_one['create']),
				'option_process'=> urlencode($tmp_one['option_process']),
				'content'       => urlencode($tmp_one['content']),
				'status_remark' => urlencode($tmp_one['status_remark']),
				'add_time'       => intval($tmp_one['add_time']), 
			);
		}
		
		return array(
			200,
			$list
		);
	 }
}
?>
