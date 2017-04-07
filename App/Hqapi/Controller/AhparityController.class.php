<?php
namespace Hqapi\Controller;
use Hqapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
7、AH股比价
表名 ：ahparity
字段：name(名称)、hcode(H股代码)、hnewprice(H股最新价)、hchg(H股涨跌幅)、acode(A股代码)、anewprice(A股最新价)、achg(A股涨跌幅)、parity(比价(A/H))、premium(溢价A/H)
------------------------------------------------------------
##--------------------------------------------------------##
*/
class AhparityController extends BaseController {
	/**
	 * sql script:
	CREATE TABLE `ahparity` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` varchar(10) DEFAULT NULL,
		  `hcode` varchar(8) DEFAULT NULL,
		  `hnewprice` decimal(18,3) DEFAULT NULL,
		  `hchg` decimal(4,2) DEFAULT NULL,
		  `acode` varchar(8) DEFAULT NULL,
		  `anewprice` decimal(18,2) DEFAULT NULL,
		  `achg` decimal(4,2) DEFAULT NULL,
		  `parity` decimal(5,2) DEFAULT NULL,
		  `premium` decimal(5,4) DEFAULT NULL,
		  `machinetime` datetime NOT NULL,
		  PRIMARY KEY (`id`,`machinetime`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
	 * */
	 
	 protected $_module_name = 'ahparity';
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
				  		'name'				=> urlencode($v['name']),
				  		'hcode'				=> urlencode($v['hcode']),
		  				'hnewprice'			=> urlencode($v['hnewprice']),
		  				'hchg'				=> urlencode($v['hchg']),
						'acode'				=> urlencode($v['acode']),
						'anewprice'			=> urlencode($v['anewprice']),
						'achg'				=> urlencode($v['achg']),
						'parity'			=> urlencode($v['parity']),
						'premium'			=> urlencode($v['premium']),
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