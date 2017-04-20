<?php
namespace Hqapi\Controller;
use Hqapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
6、新三板分红
表名：nmdividend
字段：code(代码)、name(名称)、explains(方案说明)、perdividend(每股派息)、baseequity(基本股本)、reportdate(报告期)、publicdate(实施公告期)、exdividenddate(除权除息日)、dividenddate(派息日)
------------------------------------------------------------
##--------------------------------------------------------##
*/
class NmdividendController extends BaseController {
	/**
	 * sql script:
	CREATE TABLE `nmdividend` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `code` varchar(10) DEFAULT NULL,
		  `name` varchar(10) DEFAULT NULL,
		  `explains` varchar(50) DEFAULT NULL,
		  `perdividend` decimal(4,4) DEFAULT NULL,
		  `baseequity` decimal(18,2) DEFAULT NULL,
		  `reportdate` varchar(12) DEFAULT NULL,
		  `publicdate` varchar(12) DEFAULT NULL,
		  `exdividenddate` varchar(12) DEFAULT NULL,
		  `dividenddate` varchar(12) DEFAULT NULL,
		  `machinetime` datetime NOT NULL,
		  PRIMARY KEY (`id`,`machinetime`)
		) ENGINE=InnoDB AUTO_INCREMENT=3673 DEFAULT CHARSET=utf8;
	 * */
	 
	 protected $_module_name = 'nmdividend';
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
					  		'explains'          => urlencode($v['explains']),
			  				'perdividend'		=> urlencode($v['perdividend']),
			  				'baseequity'		=> urlencode($v['baseequity']),
			  				'reportdate'		=> urlencode($v['reportdate']),
			  				'publicdate'		=> urlencode($v['publicdate']),
			  				'exdividenddate'	=> urlencode($v['exdividenddate']),
			  				'dividenddate'		=> urlencode($v['dividenddate']),
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