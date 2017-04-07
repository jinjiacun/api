<?php
namespace Hqapi\Controller;
use Hqapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
b、交易明细
表名：sh_detail(沪证明细)
字段：code(代码)、name(名称)、class(市场类型)、rzbalance(融资余额)、rqbalance(融券余额)、rzbuy(融资买入额)、rzrepay(融资偿还额)、rznetbuy(融资净买额)、rqremain(融券余量)、rqsell(融券卖出量)、rqrepay(融券偿还额)、total(融资融券余额)、tradedate(交易日期)
------------------------------------------------------------
##--------------------------------------------------------##
*/
class ShdetailController extends BaseController {
	/**
	 * sql script:
	CREATE TABLE `sh_detail` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `code` varchar(8) DEFAULT NULL,
				  `name` varchar(16) DEFAULT NULL,
				  `class` varchar(16) DEFAULT NULL,
				  `rzbalance` int(11) DEFAULT NULL,
				  `rqbalance` int(11) DEFAULT NULL,
				  `rzbuy` int(11) DEFAULT NULL,
				  `rzrepay` int(11) DEFAULT NULL,
				  `rznetbuy` int(11) DEFAULT NULL,
				  `rqremain` int(11) DEFAULT NULL,
				  `rqsell` int(11) DEFAULT NULL,
				  `rqrepay` int(11) DEFAULT NULL,
				  `total` int(11) DEFAULT NULL,
				  `tradedate` datetime DEFAULT NULL,
				  `machinetime` datetime NOT NULL,
				  PRIMARY KEY (`id`,`machinetime`)
				) ENGINE=InnoDB AUTO_INCREMENT=536 DEFAULT CHARSET=utf8;
	 * */
	 
	 protected $_module_name = 'sh_detail';
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
				  		'class'				=> urlencode($v['class']),
				  		'rzbalance'			=> urlencode($v['rzbalance']),
				  		'rqbalance'			=> urlencode($v['rqbalance']),
				  		'rzbuy'				=> urlencode($v['rzbuy']),
				  		'rzrepay'			=> urlencode($v['rzrepay']),
				  		'rznetbuy'			=> urlencode($v['rznetbuy']),
				  		'rqremain'			=> urlencode($v['rqremain']),
				  		'rqsell'			=> urlencode($v['rqsell']),
				  		'rqrepay'			=> urlencode($v['rqrepay']),
				  		'total'				=> urlencode($v['total']),
				  		'tradedate'			=> urlencode($v['tradedate']),
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