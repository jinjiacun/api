<?php
namespace Hqapi\Controller;
use Hqapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
sh_abparity(上证AB股比价)
表名 ：ahparity
字段：bcode(B股代码)、bname(B股名称)、acode(A股代码)、aname(A股名称)
------------------------------------------------------------
##--------------------------------------------------------##
*/
class ShabparityController extends BaseController {
	/**
	 * sql script:
	CREATE TABLE `sh_abparity` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `bcode` varchar(8) DEFAULT NULL,
		  `bname` varchar(10) DEFAULT NULL,
		  `acode` varchar(8) DEFAULT NULL,
		  `aname` varchar(10) DEFAULT NULL,
		  `machinetime` datetime NOT NULL,
		  PRIMARY KEY (`id`,`machinetime`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
	 * */
	 
	 protected $_module_name = 'sh_abparity';
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
				  		'bcode'             => urlencode($v['bcode']),
				  		'bname'				=> urlencode($v['bname']),
				  		'bprice'            => doubleval(0.00),
				  		'americaprice'      => doubleval(0.00),
				  		'acode'             => urlencode($v['acode']),
				  		'aname'             => urlencode($v['aname']),
				  		'aprice'            => doubleval(0.00),
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