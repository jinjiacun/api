<?php
namespace Bugapi\Controller;
use Bugapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--buglog管理--
------------------------------------------------------------
function of api:

public function add
@@input
@param string $app_name      app名称
@param string $version_name  版本名称
@param string $system        系统
@param string $time          日期
@param string $model         手机型号
@param string $loginfo       错误日志
@@output
@param $is_success 0-操作成功,-1-操作失败
##--------------------------------------------------------##
*/
class BuglogController extends BaseController {
	/**
	 * sql script:
	 * create table hr_bug_log(id int primary key auto_increment,
	                             app_name varchar(255) comment 'app名称',
	                             version_name varchar(255) comment '版本名称',
	                             system varchar(255) comment '系统',
	                             time  varchar(255) comment '日期',
	                             model  varchar(255) comment '手机型号',
	                             loginfo text comment '日志错误',
	                             ip_address varchar(255) comment '提交ip',
	                             user_agent varchar(255) comment '手机头',
	                             year int comment '年',
	                             month int comment '月',
	                             day int comment '日',
	                             add_time int not null default 0 comment '添加日期'
	                             )charset=utf8;
	 * */
	 
	 public $_module_name = 'Bug_log';
	 public $id;
	 public $app_name;
	 public $version_name;
	 public $system;
	 public $time;
	 public $model;
	 public $loginfo;
	 public $ip_address;
	 public $user_agent;
	 public $add_time;      //注册时间
         
     public function add($content)
     /*
        @@input
        @param string $app_name      app名称
	 @param string $version_name  版本名称
	 @param string $system        系统
	 @param string $time          日期
	 @param string $model         手机型号
	 @param string $loginfo       错误日志
	 @@output
	 @param $is_success 0-操作成功,-1-操作失败
      * */    
     {
		$data = $this->fill($content);
		
		if(!isset($data['app_name'])
		|| !isset($data['version_name'])
		|| !isset($data['system'])
		|| !isset($data['time'])
		|| !isset($data['model'])
		|| !isset($data['loginfo'])
		)
		{
				return C('param_err');
		}
	
	       $data['app_name']         = htmlspecialchars(trim($data['app_name']));
		$data['version_name']     = htmlspecialchars(trim($data['version_name']));
		$data['system']           = htmlspecialchars(trim($data['system']));
		$data['time']             = htmlspecialchars(trim($data['time']));
		$data['model']            = htmlspecialchars(trim($data['model']));
		$data['loginfo']          = htmlspecialchars(trim($data['loginfo']));
	
		if('' == $data['app_name']
		|| '' == $data['version_name']
		|| '' == $data['system']
		|| '' == $data['time']
		|| '' == $data['model']
		|| '' == $data['loginfo']
		)
		{
				return C('param_fmt_err');
		}
		
		$data['ip_address'] = $this->get_real_ip();
		$data['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
		
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
										'app_name'      => urlencode($v['app_name']),
										'version_name'  => urlencode($v['version_name']),
										'system'        => urlencode($v['system']),
										'time'          => urlencode($v['time']),
										'model'         => urlencode($v['model']),
										'loginfo'       => urlencode($v['loginfo']),
										'ip_address'    => urlencode($v['ip_address']),
										'user_agent'    => urlencode($v['user_agent']),
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
				'id'            => intval($tmp_one['id']),
				'app_name'      => urlencode($tmp_one['app_name']),
				'version_name'  => urlencode($tmp_one['version_name']),
				'system'        => urlencode($tmp_one['system']),
				'time'          => urlencode($tmp_one['time']),
				'model'         => urlencode($tmp_one['model']),
				'loginfo'       => urlencode($tmp_one['loginfo']),
				'ip_address'    => urlencode($tmp_one['ip_address']),
				'user_agent'    => urlencode($tmp_one['user_agent']),
				'add_time'      => intval($tmp_one['add_time']),
			);
		}
		
		return array(
			200,
			$list
		);
	 }
}
?>
