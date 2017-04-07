<?php
namespace Hqapi\Controller;
use Hqapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
3、新三板挂牌
表名：listed
字段：code(代码)、name(名称)、newprice(最新价)、marketvalue(市值)、mode(转让方式)、hosted(主办券商)、flowequity(流通股本)、totalequity(总股本)、industry(所属行业)、listdate(挂牌日期)
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
			  `newprice` decimal(18,2) DEFAULT NULL,
			  `marketvalue` decimal(18,2) DEFAULT NULL,
			  `mode` varchar(20) DEFAULT NULL,
			  `hosted` varchar(20) DEFAULT NULL,
			  `flowequity` decimal(18,2) DEFAULT NULL,
			  `totalequity` decimal(18,2) DEFAULT NULL,
			  `industry` varchar(20) DEFAULT NULL,
			  `listdate` varchar(12) DEFAULT NULL,
			  `machinetime` datetime NOT NULL,
			  PRIMARY KEY (`id`,`machinetime`)
			) ENGINE=InnoDB AUTO_INCREMENT=11817 DEFAULT CHARSET=utf8;
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
				  		'newprice'			=> urlencode($v['newprice']),
			  			'marketvalue'		=> urlencode($v['marketvalue']),
			  			'mode'				=> urlencode($v['mode']),
			  			'hosted'			=> urlencode($v['hosted']),
			  			'flowequity'		=> urlencode($v['flowequity']),
			  			'totalequity'		=> urlencode($v['totalequity']),
			  			'industry'			=> urlencode($v['industry']),
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