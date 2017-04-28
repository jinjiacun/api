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
		$_cache = S($this->_module_name.__FUNCTION__.$content);
		$real_map   = array('a'=>array(),'b'=>array());
		$real_params = "";
		$real_array = array();
		if(!$_cache){
			list($data, $record_count) = parent::get_list($content);

			$list = array();
			if($data)
			{
				foreach($data as $v)
				{
					$b_arr = json_decode(file_get_contents(C('real_url').'SH'.$v['bcode']), true);
					$a_arr = json_decode(file_get_contents(C('real_url').'SH'.$v['acode']), true);
					$list[] = array(
							'id'              	=> intval($v['id']),
					  		'bcode'             => urlencode($v['bcode']),
					  		'bname'				=> urlencode($v['bname']),
					  		'bprice'            => sprintf("%.2f",$b_arr[0]['close']),
					  		'bclose'            => doubleval($b_arr[0]['pclose']),
					  		'americaprice'      => doubleval(0.00),
					  		'acode'             => urlencode($v['acode']),
					  		'aname'             => urlencode($v['aname']),
					  		'aprice'            => sprintf("%.2f",$a_arr[0]['close']),
					  		'aclose'            => doubleval($a_arr[0]['pclose']),
						);	
					//$real_map['a'][intval($v['id'])] = 'SH'.$v['acode'];
					//$real_map['b'][intval($v['id'])] = 'SH'.$v['bcode'];
				}
			}
			
			S($this->_module_name.__FUNCTION__.$content, array($list, $record_count));
		}else{
			$list         = $_cache[0];
			$record_count = $_cache[1];	
			if($list && count($list) > 0){
				foreach($list as $k=>$v){
					$b_arr = json_decode(file_get_contents(C('real_url').'SH'.$v['bcode']), true);
					$a_arr = json_decode(file_get_contents(C('real_url').'SH'.$v['acode']), true);
					$list[$k]['bprice'] = sprintf("%.2f",$b_arr[0]['close']);
					$list[$k]['bclose'] = doubleval($b_arr[0]['pclose']);
					$list[$k]['aprice'] = sprintf("%.2f",$a_arr[0]['close']);
					$list[$k]['aclose'] = doubleval($a_arr[0]['pclose']);
				}
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