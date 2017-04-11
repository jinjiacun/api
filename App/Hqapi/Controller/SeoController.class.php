<?php
namespace Hqapi\Controller;
use Hqapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
5、新三板增发
表名：seo
字段：code(代码)、name(名称)、plandate(预案公告日)、finatype(是否配套增发)、progress(方案进度)、principle(定价原则)、sumfina(募资金额)、host(主承销商)、purpose(增发目的)、industry(管理型行业)
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
		  `plandate` varchar(12) DEFAULT NULL,
		  `finatype` varchar(4) DEFAULT NULL,
		  `progress` varchar(20) DEFAULT NULL,
		  `principle` varchar(10) DEFAULT NULL,
		  `sumfina` decimal(18,2) DEFAULT NULL,
		  `host` varchar(20) DEFAULT NULL,
		  `purpose` varchar(30) DEFAULT NULL,
		  `industry` varchar(30) DEFAULT NULL,
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
				  		'level'             => urlencode($v['level']),
				  		'method'            => urlencode($v['method']),
				  		'plandate'			=> urlencode($v['plandate']),
		  				'finatype'			=> urlencode($v['finatype']),
		  				'progress'			=> urlencode($v['progress']),
		  				'principle'			=> urlencode($v['principle']),
		  				'sumfina'			=> urlencode($v['sumfina']),
		  				'host'				=> urlencode($v['host']),
		  				'purpose'			=> urlencode($v['purpose']),
		  				'industry'			=> urlencode($v['industry']),
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