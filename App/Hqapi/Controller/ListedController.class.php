<?php
namespace Hqapi\Controller;
use Hqapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
3、新三板挂牌
表名：listed
字段：code(代码)、name(名称)、hosted(主办券商)、listdate(挂牌日期)、totalequity(总股本)、flowequity(流通股本)、revenue(兴业收入)、netprofit(净利润)、sumasset(总资产)
------------------------------------------------------------
##--------------------------------------------------------##
*/
class ListedController extends BaseController {
	/**
	 * sql script:
	CREATE TABLE `listed` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `code` varchar(10) DEFAULT NULL,
		  `name` varchar(10) DEFAULT NULL,
		  `hosted` varchar(20) DEFAULT NULL,
		  `flowequity` decimal(18,2) DEFAULT NULL,
		  `totalequity` decimal(18,2) DEFAULT NULL,
		  `revenue` decimal(18,2) DEFAULT NULL,
		  `netprofit` decimal(18,2) DEFAULT NULL,
		  `sumasset` decimal(18,2) DEFAULT NULL,
		  `listdate` varchar(12) DEFAULT NULL,
		  `machinetime` datetime NOT NULL,
		  PRIMARY KEY (`id`,`machinetime`)
		) ENGINE=InnoDB AUTO_INCREMENT=12484 DEFAULT CHARSET=utf8;
	 * */
	 
	 protected $_module_name = 'listed';
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
				  		'hosted'			=> urlencode($v['hosted']),
		  				'flowequity'        => urlencode($v['flowequity']),
		  				'totalequity'       => urlencode($v['totalequity']),
		  				'revenue'           => urlencode($v['revenue']),
		  				'netprofit'			=> urlencode($v['netprofit']),
		  				'sumasset'			=> urlencode($v['sumasset']),
			  			'listdate'			=> urlencode($v['listdate']),
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