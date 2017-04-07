<?php
namespace Hqapi\Controller;
use Hqapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
5、新三板增发
表名：seo
字段：code(代码)、name(名称)、progress(方案进度)、num(增发数量)、price(增发价格)、peratio(市盈率)、dpratio(折溢价率)、mode(发行方式)、reslut(结果)、newdate(最新公告日)、firstdate(首次公告日)
------------------------------------------------------------
##--------------------------------------------------------##
*/
class SeoController extends BaseController {
	/**
	 * sql script:
	CREATE TABLE `seo` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `code` varchar(10) DEFAULT NULL,
		  `name` varchar(10) DEFAULT NULL,
		  `progress` varchar(20) DEFAULT NULL,
		  `num` decimal(18,4) DEFAULT NULL,
		  `price` decimal(18,2) DEFAULT NULL,
		  `peratio` decimal(18,2) DEFAULT NULL,
		  `dpratio` decimal(18,2) DEFAULT NULL,
		  `mode` varchar(10) DEFAULT NULL,
		  `reslut` varchar(20) DEFAULT NULL,
		  `newdate` varchar(12) DEFAULT NULL,
		  `firstdate` varchar(12) DEFAULT NULL,
		  `machinetime` datetime NOT NULL,
		  PRIMARY KEY (`id`,`machinetime`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
	 * */
	 
	 protected $_module_name = 'seo';
	 protected $id;
	 
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
						'code'            	=> urlencode($v['code']),
				  		'name'				=> urlencode($v['name']),
				  		'progress'          => urlencode($v['progress']),
						'num'				=> urlencode($v['num']),
						'price'				=> urlencode($v['price']),
						'peratio'			=> urlencode($v['peratio']),
						'dpratio'			=> urlencode($v['dpratio']),
						'mode'				=> urlencode($v['mode']),
						'reslut'			=> urlencode($v['reslut']),
						'newdate'			=> urlencode($v['newdate']),
						'firstdate'			=> urlencode($v['firstdate'	]),
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