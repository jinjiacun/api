<?php
namespace Hqapi\Controller;
use Hqapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
4、新三板做市
表名：marketmaker
字段：code(代码)、name(名称)、num(做市商总数)、tips(提示信息)、totalequity(总股本)、totalvalue(总市值)、closeprice(做市首日前收盘价)、chg(做市首日涨跌幅)、startdate(做市起始日)
------------------------------------------------------------
##--------------------------------------------------------##
*/
class MarketmakerController extends BaseController {
	/**
	 * sql script:
	CREATE TABLE `marketmaker` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `code` varchar(10) DEFAULT NULL,
		  `name` varchar(10) DEFAULT NULL,
		  `num` int(11) DEFAULT NULL,
		  `tips` varchar(20) DEFAULT NULL,
		  `totalequity` decimal(18,2) DEFAULT NULL,
		  `totalvalue` decimal(18,2) DEFAULT NULL,
		  `closeprice` decimal(18,2) DEFAULT NULL,
		  `chg` decimal(4,2) DEFAULT NULL,
		  `startdate` varchar(12) DEFAULT NULL,
		  `machinetime` datetime NOT NULL,
		  PRIMARY KEY (`id`,`machinetime`)
		) ENGINE=InnoDB AUTO_INCREMENT=1607 DEFAULT CHARSET=utf8;
	 * */
	 
	 protected $_module_name = 'marketmaker';
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
							'code'            	=> urlencode($v['code']),
					  		'name'				=> urlencode($v['name']),
					  		'num'				=> urlencode($v['num']),
							'tips' 				=> urlencode($v['tips']),
							'totalequity' 		=> urlencode($v['totalequity']),
							'totalvalue' 		=> urlencode($v['totalvalue']),
							'closeprice' 		=> urlencode($v['closeprice']),
							'chg' 				=> urlencode($v['chg']),
							'startdate' 		=> urlencode($v['startdate']),
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