<?php
namespace Hqapi\Controller;
use Hqapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
新股申购
表名:ipos
字段：scode(股票代码)、name(名称)、pcode(申购代码)、totalissue(发行总数)、onlineissue(网上发行)、marketvalue(市值)、plimit(申购上限)、issueprice(发行价格)、newprice(最新价)、closeprice(首日收盘价)、purchasedate(申购日期)、publicdate()、paymentdate(中签缴款日期)、listeddate(上市日期)、iporatio(发行市盈率)、iperatio(行业市盈率)、successrate(中签率)、quotationmultiple(询价累计报价)、quotationnum(配售对象报价家属)、liststatus(上市状态)、wordboard(连续一字板数量)、totalincrease(总涨幅)

------------------------------------------------------------
##--------------------------------------------------------##
*/
class IposController extends BaseController {
	/**
	 * sql script:
CREATE TABLE `ipos` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `scode` varchar(8) DEFAULT NULL,
	  `name` varchar(16) DEFAULT NULL,
	  `pcode` varchar(8) DEFAULT NULL,
	  `totalissue` int(11) DEFAULT NULL,
	  `onlineissue` int(11) DEFAULT NULL,
	  `marketvalue` int(11) DEFAULT NULL,
	  `plimit` int(11) DEFAULT NULL,
	  `issueprice` decimal(18,2) DEFAULT NULL,
	  `newprice` decimal(18,2) DEFAULT NULL,
	  `closeprice` decimal(18,2) DEFAULT NULL,
	  `purchasedate` varchar(12) NOT NULL,
	  `publicdate` varchar(12) NOT NULL,
	  `paymentdate` varchar(12) NOT NULL,
	  `listeddate` varchar(12) NOT NULL,
	  `iporatio` decimal(18,2) DEFAULT NULL,
	  `iperatio` decimal(18,2) DEFAULT NULL,
	  `successrate` decimal(10,4) DEFAULT NULL,
	  `quotationmultiple` decimal(10,2) DEFAULT NULL,
	  `quotationnum` int(11) DEFAULT NULL,
	  `liststatus` varchar(10) DEFAULT NULL,
	  `wordboard` int(11) DEFAULT NULL,
	  `totalincrease` decimal(10,2) DEFAULT NULL,
	  `machinetime` datetime NOT NULL,
	  PRIMARY KEY (`id`,`machinetime`)
	) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
	 * */
	 
	 protected $_module_name = 'ipos';
	 protected $id;
	 
	 public function get_list($content)
	{
		$_cache = S($this->_module_name.__FUNCTION__.$content);
		//if(!$_cache){
			$tmp_content = $this->fill($content);
			unset($content);
			if(isset($tmp_content['where']['type'])){
				$type = intval($tmp_content['where']['type']);
				unset($tmp_content['where']['type']);
				switch($type){
					//type=1  申购数据   当前时间<申购时间
					case 1:{
						$tmp_content['where']['purchasedate'] = array("gt",date("Y-m-d"));
					}break;
					//type=2  待上市数据  申购时间<当前时间<上市时间
					case 2:{
						$tmp_content['where']['purchasedate'] = array("lt",date("Y-m-d"));
						$tmp_content['where']['listeddate'] = array("gt",date("Y-m-d"));
					}break;
					//type=3  已上市数据  上市时间<当前时间
					case 3:{
						$tmp_content['where']['listeddate'] = array("lt",date("Y-m-d"));
					}break;				
				}
				$content = json_encode($tmp_content);
			}
			list($data, $record_count) = parent::get_list($content);
			//print_r($content);
			$list = array();
			if($data)
			{
				foreach($data as $v)
				{
					$list[] = array(
							'id'              	=> intval($v['id']),
					  		'scode'             => urlencode($v['scode']),
		  					'name'              => urlencode($v['name']),
							'pcode'             => urlencode($v['pcode']),
							'totalissue'        => urlencode($v['totalissue']),
							'onlineissue'       => urlencode($v['onlineissue']),
							'marketvalue'       => urlencode($v['marketvalue']),
							'plimit'            => urlencode($v['plimit']),
							'issueprice'        => urlencode($v['issueprice']),
							'newprice'          => urlencode($v['newprice']),
							'closeprice'        => urlencode($v['closeprice']),
							'purchasedate'      => urlencode($v['purchasedate']),
							'publicdate'        => urlencode($v['publicdate']),
							'paymentdate'       => urlencode($v['paymentdate']),
							'listeddate'        => urlencode($v['listeddate']),
							'iporatio'          => urlencode($v['iporatio']),
							'iperatio'          => urlencode($v['iperatio']),
							'successrate'       => urlencode($v['successrate']),
							'quotationmultiple' => urlencode($v['quotationmultiple']),
							'quotationnum'      => urlencode($v['quotationnum']),
							'liststatus'        => urlencode($v['liststatus']),
							'wordboard'         => urlencode($v['wordboard']),
							'totalincrease'     => urlencode($v['totalincrease'])
						);	
				}
			}
			//S($this->_module_name.__FUNCTION__.$content, array($list, $record_count));
		/*
		}else{
			$list         = $_cache[0];
			$record_count = $_cache[1];			
		}
		*/


		return array(200, 
				array(
					'list'=>$list,
					'record_count'=> $record_count,
					)
				);
	}
}