<?php
namespace Hqapi\Controller;
use Hqapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
2、融资融券
a、总量
表名：rzrqtotal
字段：tradedate(交易日期)、market(市场)、rzremain(本日融资余额)、rzbuy(日本融资买入额)、rqremain(本日融券余额)、rzrqremain(本日融资融券余额)
------------------------------------------------------------
##--------------------------------------------------------##
*/
class RzrqtotalController extends BaseController {
	/**
	 * sql script:
	CREATE TABLE `rzrqtotal` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `tradedate` datetime DEFAULT NULL,
			  `market` varchar(16) DEFAULT NULL,
			  `rzremain` varchar(16) DEFAULT NULL,
			  `rzbuy` varchar(16) DEFAULT NULL,
			  `rqremain` varchar(16) DEFAULT NULL,
			  `rzrqremain` varchar(16) DEFAULT NULL,
			  `machinetime` datetime NOT NULL,
			  PRIMARY KEY (`id`,`machinetime`)
			) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
	 * */
	 
	 protected $_module_name = 'rzrqtotal';
	 protected $id;
	 

	 public function get_list($content)
	{
		$_cache = S($this->_module_name.__FUNCTION__.$content);
		if(!$_cache){
			list($data, $record_count) = parent::get_list($content);

			$list = array();
			if($data)
			{
				foreach($data as $v)
				{
					$list[] = array(
							'id'              	=> intval($v['id']),
							'tradedate'       	=> urlencode($v['tradedate']),
				  			'market'			=> urlencode($v['market']),
				  			'rzremain'			=> urlencode($v['rzremain']),
				  			'rzbuy'				=> urlencode($v['rzbuy']),
				  			'rqremain'			=> urlencode($v['rqremain']),
				  			'rzrqremain'		=> urlencode($v['rzrqremain']),
						);	
				}
			}
			S($this->_module_name.__FUNCTION__.$content, array($list, $record_count));
		}else{
			$list         = $_cache[0];
			$record_count = $_cache[1];			
		}

		return array(200, 
				array(
					'list'=>$list,
					'record_count'=> $record_count,
					)
				);
	}
}