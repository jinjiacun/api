<?php
namespace Hqapi\Controller;
use Hqapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
板块信息
表名 ：ahparity
字段：name(名称)、hcode(H股代码)、hnewprice(H股最新价)、hchg(H股涨跌幅)、acode(A股代码)、anewprice(A股最新价)、achg(A股涨跌幅)、parity(比价(A/H))、premium(溢价A/H)
------------------------------------------------------------
##--------------------------------------------------------##
*/
class BlockController extends BaseController {
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
	 
	 protected $_module_name = '';
	 protected $id;

	/**
	*查询板块分类
	*@@input
	*@param $type 板块类型
	*/
	public function get_class($content){
		$_type_list = array("GN"=>"Hqapi/Gnplates",//概念
							"HY"=>"Hqapi/Hyplates",//行业
							"DY"=>"Hqapi/Dyplates" //地域
							);
		$_cache = S($this->_module_name.__FUNCTION__.$content);
		if(!$_cache){
			$data = $this->fill($content);
			$list = array();
			
			if(!isset($data['type']))
			{
				return C('param_err');
			}
		
			$data['type'] = htmlspecialchars(trim($data['type']));
			
			
			if('' == $data['type'])
			{
				return C('param_fmt_err');
			}
			
			if(!in_array($data['type'], array_keys($_type_list))){
				return C('param_fmt_err');	
			}

			$tmp_data = $data;
			//$tmp_data["group"] = "cmd";
			$tmp_content = json_encode($tmp_data);
			if(A($_type_list[$data['type']]))
				list($status_code, $r_content) = A($_type_list[$data['type']])->get_list_ex($tmp_content);
			
			if($r_content && count($r_content)>0){
				foreach($r_content['list'] as $k=>$v){
					unset($r_content['list'][$k]['id'],
						  $r_content['list'][$k]['codetype'],
						  $r_content['list'][$k]['code']
						);
				}
			}
			
			S($this->_module_name.__FUNCTION__.$content, array($status_code, $r_content));
		}
		else{
			$status_code  = $_cache[0];
			$r_content    = $_cache[1];
		}
		

		return array($status_code, 
				array(
					'list'=>$r_content['list'],
					'record_count'=> $r_content['record_count'],
					)
		);
		
	}

	/**
	*通过板块获取品种
	*
	*@@input
	*@param $block_num 板块编号
	*/
	public function get_block_info($content){
		$_type_list = array("gn"=>"Hqapi/Gnplates",//概念
							"hy"=>"Hqapi/Hyplates",//行业
							"dy"=>"Hqapi/Dyplates" //地域
							);
		$_cache = S($this->_module_name.__FUNCTION__.$content);
		if(!$_cache)
		{
			$data = $this->fill($content);
			$list = array();
			
			if(!isset($data['block_num']))
			{
				return C('param_err');
			}
		
			$data['block_num'] = htmlspecialchars(trim($data['block_num']));
			
			
			if('' == $data['block_num'])
			{
				return C('param_fmt_err');
			}

			if (!preg_match("/^(gn|hy|dy)\_BK[0-9]+?/", $data['block_num'])) {
	            return C('param_fmt_err');
	        }
			
			//解析
			$tmp_list = explode('_', $data['block_num']);
			$prefix   = $tmp_list[0];
			$block    = $tmp_list[1];


			$tmp_data = $data;
			$tmp_data["where"] = array("cmd"=>$block);
			$tmp_content = json_encode($tmp_data);
			if(A($_type_list[$prefix]))
				$tmp_re = A($_type_list[$prefix])->get_list($tmp_content);
			$status = $tmp_re[0];
			$record_count = $tmp_re[1]['record_count'];
			$list   = $tmp_re[1]['list'];
			$_market_map = C('market_map');

			if($list && count($list)>0){
				foreach($list as $k=>$v){
					unset($list[$k]['id'],$list[$k]['cmd'],$list[$k]['pname']);
					$_arr = json_decode(file_get_contents(C('real_url').$_market_map[$v['codetype']].$v['code']), true);
					$list[$k]['name']  		= urlencode($_arr[0]['name']);//股票名称
					$list[$k]['price'] 		= doubleval($_arr[0]['close']);//最新价
					$list[$k]['hight'] 		= doubleval($_arr[0]['high']);//最高价
					$list[$k]['lower'] 		= doubleval($_arr[0]['low']);//最低价
					$list[$k]['pclose']		= doubleval($_arr[0]['pclose']);//昨收
					$list[$k]['open']  		= doubleval($_arr[0]['open']);//开盘价
					$list[$k]['turnover']	= doubleval($_arr[0]['turnover']);//成交额
					$list[$k]['volume']		= doubleval($_arr[0]['volume']);//成交量
					$list[$k]['turnrate']   = doubleval($_arr[0]['turnrate']);//换手率
					$list[$k]['earning']    = doubleval($_arr[0]['earning']);//市盈率
				}
			}
			S($this->_module_name.__FUNCTION__.$content, array($status, $list, $record_count));
		}else{
			$_market_map = C('market_map');
			$status       = $_cache[0];
			$list         = $_cache[1];
			$record_count = $_cache[2];
			if($list && count($list)>0){
				foreach($list as $k=>$v){
					$_arr = json_decode(file_get_contents(C('real_url').$_market_map[$v['codetype']].$v['code']), true);
					$list[$k]['price'] 		= doubleval($_arr[0]['close']);//最新价
					$list[$k]['hight'] 		= doubleval($_arr[0]['high']);//最高价
					$list[$k]['lower'] 		= doubleval($_arr[0]['low']);//最低价
					$list[$k]['pclose']		= doubleval($_arr[0]['pclose']);//昨收
					$list[$k]['open']  		= doubleval($_arr[0]['open']);//开盘价
					$list[$k]['turnover']	= doubleval($_arr[0]['turnover']);//成交额
					$list[$k]['volume']		= doubleval($_arr[0]['volume']);//成交量
					$list[$k]['turnrate']   = doubleval($_arr[0]['turnrate']);//换手率
					$list[$k]['earning']    = doubleval($_arr[0]['earning']);//市盈率
				}				
			}
		}


		return array($status, 
				array(
					'list'=>$list,
					'record_count'=> $record_count,
					)
		);

	}
	
	public function get_code_raise($content){
		$data = $this->fill($content);			
		
		if(!isset($data['code'])){
			return C('param_err');
		}

		$url = C('real_url');
		$block_type = C('block_type');
		$code_list = array();
		$block_code_list = array();

		$raise_list = $this->get_raise();
		$cache_block = include_once(__PUBLIC__."/cache/block.php");
		$cache_code  = include_once(__PUBLIC__."/cache/block_code.php");

		foreach($data['code'] as $v){
			$_tmp = explode('_', $v);
			$block_type = $_tmp[0];
			$block_code = $_tmp[1];
			$code = $this->my_max($block_type.'_plates', 
			                      $block_code, 
					      $raise_list, 
					      $cache_code);
                        $block_code_list[$block_code] = array('cmd'  => $block_code,
					             'pname'=> $cache_block[$block_type.'_plates'][$block_code]['pname'],
						     'amount'=> $cache_block[$block_type.'_plates'][$block_code]['amount'],
						     'code'  => $code
                                                     );
                        $code_list[] = $code;
		}
		/*
		$i = 0;
		foreach($cache_block['gn_plates'] as $k=>$v){
			$i++;
			$code = $this->my_max('gn_plates', $k, $raise_list, $cache_code);
			$block_code_list[$k] = array('cmd'=>$k,'pname'=>$v['pname'],'amount'=>$v['amount'], 'code' => $code);
			$code_list[] = $code;
			
			if($i > 20)
			      break;
		}
		*/
		
		$code_str     = implode(",", $code_list);
		$code_content = $this->request_by_curl($url.$code_str, $code_str);
		$_tmp_list = json_decode($code_content, true);
		$code_list = array();
		foreach($_tmp_list as $v){
			$code_list[$v['symbol']] = $v;
		}
		unset($_tmp_list, $v);
		foreach($block_code_list as $k=>$v){
			$block_code_list[$k]['price'] = $code_list[$v['code']]['close'];
			$block_code_list[$k]['changerate'] = $code_list[$v['code']]['changerate'];
			$block_code_list[$k]['name'] = urlencode($code_list[$v['code']]['name']);
		}
		
		return array(200, $block_code_list); 
	}	

	private function my_max($block_type, $block_num, $raise_list, $cache_code){
		$code_list  = array();
		$stock_list = array();
			
		$tmp_list = $cache_code[$block_type][$block_num];
		$i = 0;
		foreach($tmp_list as $k=>$v){
			if(isset($raise_list[$k]))
				$code_list[$k] = doubleval($raise_list[$k]);
		}

		if(is_array($code_list))
			$max_raise_code = array_search(max($code_list), $code_list);
		else
			$max_raise_code = $code_list;

		return $max_raise_code;
	}

	private function get_raise(){
		$raise_url  = C('raise_url');

		//获取涨跌幅
		$raise_content = $this->request_by_curl($raise_url, '');
		$raise_list    = json_decode($raise_content, true);	

		return $raise_list;
	}
}