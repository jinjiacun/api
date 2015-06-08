<?php
namespace Soapi\Controller;
use Soapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--版本管理--
------------------------------------------------------------
function of api:
 

#通过id查询一条信息
public function get_info
@@input
@param $id
@@output
@param $id
@param $version_sn   版本号
@param $down_url     下载地址
@param $must_update  是否强制更新(0-不强制,1-强制
@param $content      内容
@param $app_sys      系统名称
@param $app_time     添加日期
##--------------------------------------------------------##
*/
class VersionController extends BaseController {
	 /**
	 * sql script:
	  create table so_version(id int primary key auto_increment,
								 version_sn varchar(255) comment '版本号',
								 down_url varchar(255) comment '下载地址',
								 must_update int not null default 0 comment '是否强制更新',
								 content varchar(255) comment '内容',
								 app_sys varchar(255) comment 'android/ios',
								 add_time int not null default 0 comment '添加日期'
								 )charset=utf8;
	 **/
     
     protected $_module_name = 'Version';
     protected $id;
     protected $version_sn;
     protected $down_url;
     protected $must_update;
     protected $content;
     protected $app_sys;
     protected $add_time;
     
     
    #通过id查询一条信息
	public function get_info($content)
	/*
	@@input
	@param $id
	@@output
	@param $id
	@param $version_sn   版本号
	@param $down_url     下载地址
	@param $must_update  是否强制更新(0-不强制,1-强制)
	@param $content      内容
	@param $app_sys      系统名称
	@param $app_time     添加日期
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
				'version_sn'      => urlencode($tmp_one['version_sn']),
				'down_url'     => urlencode($tmp_one['down_url']),
				'must_update'  => urlencode($tmp_one['must_update']),
				'content'      => urlencode($tmp_one['content']),
				'app_sys'      => urlencode($tmp_one['app_sys']),
			);
		}
		
		return array(
			200,
			$list
		);
	}
}
