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
		$tmp_data["group"] = "cmd";
		$tmp_content = json_encode($tmp_data);
		list($data, $record_count) = A($_type_list[$data['type']])->get_list($tmp_content);
		

		return array(200, 
				array(
					'list'=>$list,
					'record_count'=> $record_count,
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
		

		//解析
		$tmp_list = explode('_', $data['block_num']);
		$prefix   = $tmp_list[0];
		$block    = $tmp_list[1];


		$tmp_data = $data;
		$tmp_data["where"] = array("cmd"=>$block);
		$tmp_content = json_encode($tmp_data);
		list($data, $record_count) = A($_type_list[$prefix])->get_list($tmp_content);

		return array(200, 
				array(
					'list'=>$list,
					'record_count'=> $record_count,
					)
		);
	}
}