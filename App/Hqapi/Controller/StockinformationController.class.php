<?php
namespace Hqapi\Controller;
use Hqapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
股票品种
------------------------------------------------------------
##--------------------------------------------------------##
*/
class StockinformationController extends BaseController {
	/**
	 * sql script:
	CREATE TABLE `stock_information` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `market` int(11) NOT NULL,
	  `code` varchar(8) NOT NULL,
	  `name` varchar(16) NOT NULL,
	  `spell` varchar(8) NOT NULL,
	  `kws` varchar(50) NOT NULL,
	  `status` int(11) NOT NULL,
	  `machinetime` datetime NOT NULL,
	  PRIMARY KEY (`id`,`machinetime`)
	) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

	 * */
	 
	 protected $_module_name = 'stock_information';
	 protected $id;
	 
	 /**
	 @@input
	 @params $market 市场类型
	 @params $code   品种
	 @name   
	 */
	 public function get_info($content)
	 {
		$data = $this->fill($content);
		$market_map_ex = C('market_map_ex');
		
		if(!isset($data['market'])
		|| !isset($data['code']))
		{
			return C('param_err');
		}
		
		$data['market'] = htmlentities(trim($data['market']));		
		$data['code']   = htmlspecialchars(trim($data['code']));
		
		if('' === $data['market']
		|| '' === $data['code'])

		{
			return C('param_fmt_err');
		}
		
		$data['market'] = $market_map_ex[$data['market']];
		$list = array();
		$tmp_one = M($this->_module_name)->where($data)->find();
		if($tmp_one)
		{
			$list = array(
				'id'                     => intval($tmp_one['id']),
				'market'                 => intval($tmp_one['market']),
				'code'                   => urlencode($tmp_one['code']),
				'name'                   => urlencode($tmp_one['name']),
				'spell'                  => urlencode($tmp_one['spell']),
				'kws'                    => urlencode($tmp_one['kws']),
				'status'				 => intval($tmp_one['status']),
			);
		}
		
		return array(
			200,
			$list
		);
	 }

	 /**
	 @@input
	 @params $market 市场类型
	 @params $code   品种代码
	 */
	 public function get_status($content){
	 	$tmp_data     = $this->fill($content);
	 	$trade_status = C('trade_status');

	 	#判定上海黄金还是现货黄金
	 	if($tmp_data['market'] == '5b00'
	 	|| $tmp_data['market'] == '5900'){
	 		return $this->get_td_status($tmp_data['market'], $tmp_data['code'], $trade_status);
	 	}

	 	list($status_code, $list) = $this->get_info($content);
	 	$trade_map    = C('trade_map');
	 	
	 	$history      = C('history');
	 	$time_limit   = C('time_limit');
	 	$h=date("H");
	 	$current_time = time();

	 	//正常查询
	 	if($status_code <> 200
	 	|| count($list) == 0){
	 		return C('over_err');
	 	}

	 	if(1 <> $list['status']){
	 		return array(
	 			200,
	 			$trade_status[$trade_map[$list['status']]],
	 			);
	 	}

	 	#是否停牌
	 	$tmp_where['where']['code'] = $tmp_data['code'];
	 	$tmp_tfpxx_data = A('Hqapi/Tfpxx')->get_list(json_encode($tmp_where));
	 	if(count($tmp_tfpxx_data[1]['list']) > 0){
	 		#判定当前时间是否在停牌时间区间内(包括广义截至时间，为空则为无限)
	 		if(($tmp_tfpxx_data[1]['list'][0]['haltstopdate'] == '' 
	 			&& $current_time > strtotime(urldecode($tmp_tfpxx_data[1]['list'][0]['haltdate'])))
	 		||  ($current_time > strtotime(urldecode($tmp_tfpxx_data[1]['list'][0]['haltdate']))
	 			&& $current_time < strtotime(urldecode($tmp_tfpxx_data[1]['list'][0]['haltstopdate'])))){	 			
	 			return array(
	 				200,
	 				$trade_status['stop'],
	 			);
	 		}
	 	}

	 	if(1 == $list['status']){
	 		$current_date = date("Y-m-d");
	 		/*
	 		print_r($current_date);
	 		print_r($history);
	 		if(in_array($current_date, $history)){
	 			echo 'success';
	 		}
	 		die;
	 		*/
	 		$wk_day   =date('w');
	 		#是否周六周日
	 		if($wk_day == 6
	 		|| $wk_day == 7){
	 			return array(
	 				200,
	 				$trade_status['rest'],
	 			);
	 		}
	 		
	 		$bid_s_time = strtotime(sprintf("%s %s", $current_date, $time_limit['bid_time']['s']));
	 		$bid_e_time = strtotime(sprintf("%s %s", $current_date, $time_limit['bid_time']['e']));
	 		$trading_am_s_time = strtotime(sprintf("%s %s", $current_date, $time_limit['am_time']['s']));
	 		$trading_am_e_time = strtotime(sprintf("%s %s", $current_date, $time_limit['am_time']['e']));
	 		$trading_pm_s_time = strtotime(sprintf("%s %s", $current_date, $time_limit['pm_time']['s']));
	 		$trading_pm_e_time = strtotime(sprintf("%s %s", $current_date, $time_limit['pm_time']['e']));
	 		//print_r($trading_am_s_time);
	 		//die;
	 		#是否节假日
	 		if(in_array($current_date, $history)){
	 			return array(
	 				200,
	 				$trade_status['rest'],
	 				);
	 		}	 		
	 		#集合竞价
	 		elseif($current_time > $bid_s_time
	 		&&      $current_time < $bid_e_time){
	 			return array(
	 				200,
	 				$trade_status['bid'],
	 				);
	 		}	 		
	 		#交易中
	 		elseif(($current_time > $trading_am_s_time
	 		&&      $current_time < $trading_am_e_time)
	 			||($current_time > $trading_pm_s_time
	 		&&      $current_time < $trading_pm_e_time)){
	 			return array(
	 				200,
	 				$trade_status['trading'],
	 				);
	 		}

	 		#休市
	 		else{
	 			return array(
	 				200,
	 				$trade_status['rest'],
	 				);
	 		}
	 	}
	 }


	 private function get_td_status($market, $code, $trade_status){
	 	$wk_day   =date('w');
	 	$current_time = time();
	 	$current_date = date("Y-m-d");
	 	#上海黄金
	 	if($market == '5900'){
	 		if($wk_day >0 && $wk_day <=6){#竞价中(非法定节日的19:50~19:59,或者法定节假日后的第一个交易日的7:50~7:59)
	 			if($wk_day == 1){
	 				if(($current_time > strtotime($current_date.' 07:50:00')
	 				 && $current_time < strtotime($current_date.' 07:59:00'))
	 				||($current_time > strtotime($current_date.' 19:50:00')
	 				&& $current_time < strtotime($current_date.' 19:59:00'))){
	 					return array(
	 						200,
	 						$trade_status['bid'],
	 					);
	 				}elseif(($current_time > strtotime($current_date.' 09:00:00')#交易中(夜间20:00到次日02:30,上午9:00至11:30，下午13:30到15:30)
	 				     && $current_time < strtotime($current_date.' 11:30:00'))
	 				   ||
	 					    ($current_time > strtotime($current_date.' 13:30:00')
	 					 &&  $current_time < strtotime($current_date.' 15:30:00'))
	 			       || 
	 			           ($current_time > strtotime($current_date.' 20:00:00')
	 			         && $current_time <= strtotime($current_date.' 23:59:59'))
	 				     ){
	 					return array(
	 						200,
	 						$trade_status['trading']
	 						);
	 				}elseif($current_time > strtotime($current_date." 15:31:00")
	 				&&      $current_time < strtotime($current_date." 15:45:00")){#交收(非法定节假日的15:31~15:45)
	 					return array(
	 						200,
	 						$trade_status['delivery'],
	 						);
	 				}else{
	 					return array(
	 						200,
	 						$trade_status['rest']
	 						);
	 				}
	 			}elseif($wk_day>1 && $wk_day <6){
	 				if($current_time > strtotime($current_date.' 19:50:00')
	 				&& $current_time < strtotime($current_date.' 19:59:00')){
	 					return array(
	 						200,
	 						$trade_status['bid'],
	 					);
	 				}elseif(($current_time > strtotime($current_date.' 00:00:00')
	 					 &&  $current_time < strtotime($current_date.' 02:30:00'))
	 				||		($current_time > strtotime($current_date.' 09:00:00')#交易中(夜间20:00到次日02:30,上午9:00至11:30，下午13:30到15:30)
	 				     && $current_time < strtotime($current_date.' 11:30:00'))
	 				||
	 					    ($current_time > strtotime($current_date.' 13:30:00')
	 					 &&  $current_time < strtotime($current_date.' 15:30:00'))
	 			    || 
	 			           ($current_time > strtotime($current_date.' 20:00:00')
	 			         && $current_time <= strtotime($current_date.' 23:59:59'))
	 				     ){
	 					return array(
	 						200,
	 						$trade_status['trading']
	 						);
	 				}elseif($current_time > strtotime($current_date." 15:31:00")
	 				&&      $current_time < strtotime($current_date." 15:45:00")){#交收(非法定节假日的15:31~15:45)
	 					return array(
	 						200,
	 						$trade_status['delivery'],
	 						);
	 				}else{
	 					return array(
	 						200,
	 						$trade_status['rest']
	 						);
	 				}
	 			}elseif($wk_day == 6){
	 				if(($current_time > strtotime($current_date.' 00:00:00')
	 					 &&  $current_time < strtotime($current_date.' 02:30:00'))){
	 					return array(
	 						200,
	 						$trade_status['trading']
	 						);
	 				}
	 			}
	 		}else{#休市(除上以外)
	 			return array(
	 					200,
	 					$trade_status['rest']
	 				);
	 		}
	 	}
	 	elseif($market == '5b00' 
	 	&&     $code   == 'XAU')
	 	#现货黄金
	 		
	 		#交易中(非国际法定节假日6:00~第二天4:00)
	 		##前一天为非交易日，判定当天的6:00:00~23:59:59
	 		##当天的前一天为交易日，当天不是交易日，判定时间00:00:00~04:00:00
	 		if($wk_day == 1){
	 			if($current_time >= strtotime($current_date.' 06:00:00')
	 			&& $current_time <= strtotime($current_date.' 23:59:59')){
	 				return array(
	 					200,
	 					$trade_status['trading']
	 					);
	 			}else{
	 				return array(
	 					200,
	 					$trade_status['rest']
	 				);
	 			}
	 		}else if($wk_day == 6){
	 			if($current_time >= strtotime($current_date.' 00:00:00')
	 			&& $current_time < strtotime($current_date.' 04:00:00')){
	 				return array(
	 					200,
	 					$trade_status['trading']
	 					);
	 			}else{
	 				return array(
	 					200,
	 					$trade_status['rest']
	 				);
	 			}
	 		}
	 		##前一天为交易日,判定当天的00:00-4:00及其6:00~24:00
	 		elseif($wk_day>2 && $wk_day < 6){
	 			if(($current_time >= strtotime($current_date.' 00:00:00')
	 			&& $current_time < strtotime($current_date.' 04:00:00'))
	 				||($current_time >= strtotime($current_date.' 06:00:00')
	 			&& $current_time < strtotime($current_date.' 23:59:59'))){
	 				return array(
	 					200,
	 					$trade_status['trading']
	 					);
	 			}else{
	 				return array(
	 					200,
	 					$trade_status['rest']
	 				);
	 			}
	 		}else{#休市(非国际法定节假日4:00~6:00)
	 			return array(
	 					200,
	 					$trade_status['rest']
	 				);
	 		}
	 }
}