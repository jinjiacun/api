<?php
namespace Hqapi\Controller;
use Hqapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
地域
表名 ：dy_plates
字段：codetype(市场类型)、code(代码)、pname(板块名)、cmd(板块命令行)
------------------------------------------------------------
##--------------------------------------------------------##
*/
class DyplatesController extends BaseController {
	/**
	 * sql script:
	CREATE TABLE `dy_plates` (
		  `codetype` varchar(6) NOT NULL,
		  `code` varchar(8) NOT NULL,
		  `pname` varchar(16) NOT NULL,
		  `cmd` varchar(8) NOT NULL,
		  `updatetime` datetime NOT NULL,
		  PRIMARY KEY (`code`,`pname`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	 * */
	 
	 protected $_module_name = 'dy_plates';
	 protected $id;
	 protected $_key = null;
	 
	 public function get_list($content)
	{
		list($data, $record_count) = parent::get_list($content);

		$list = array();
		if($data)
		{
			foreach($data as $v)
			{
				$list[] = array(
						'id'              	=> intval($v['id']),
						'codetype'			=> urlencode($v['codetype']),
						'code'				=> urlencode($v['code']),
						'pname'				=> urlencode($v['pname']),
						'cmd'				=> "dy_".urlencode($v['cmd']),				  		
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