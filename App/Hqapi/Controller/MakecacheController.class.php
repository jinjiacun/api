<?php
namespace Hqapi\Controller;
use Hqapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
class MakecacheController extends BaseController{
      private function general_block($conn, $block_type){
      	$_map_block = array();
      	//查询小板块
	$sql ="select distinct(cmd),pname,count(1) as amount from $block_type group by cmd";
	$result = mysql_query($sql,$conn);
	$i = 0;
	while($row = mysql_fetch_array($result))
	{
		/*
		if(!is_dir(sprintf($template_str, $row['cmd']))){
			if(!mkdir(sprintf($template_str,$row['cmd']))){
				return false;
			}
		}
		*/
		$_map_block[$block_type][$row['cmd']] = array('pname'=>urlencode($row['pname']), 'amount'=>$row['amount']);
		#查询板块代码
		#$_map_block_code += general_code_cache_by_block($conn, $block_type, $row['cmd']);
	}

	return $_map_block;
      }

      private function general_block_code($conn, $block_type){
	$_map_code = array();
	$market_type = C('market_map');

	//查询小板块
	$sql ="select codetype,code,cmd from $block_type "; //SQL语句
	$result = mysql_query($sql,$conn); //查询
	$i = 0;
	while($row = mysql_fetch_array($result))
	{
		$code = $market_type[$row['codetype']].$row['code'];
		$_map_code[$block_type][$row['cmd']][$code] = '';
	}

	return $_map_code;
      }      
      

      public function general_block_cache(){
	$conn=mysql_connect(C('DB_HOST'),
			    C('DB_USER'),
			    C('DB_PWD')) or die("error connecting") ; 
	mysql_query("set names 'utf8'"); 
	mysql_select_db(C('DB_NAME'));

	#生产板块目录
	$block_type_l = C('block_type');
	$len = count($block_type_l);
	$g_block_map = array();
	$g_code_map  = array();
	$cache       = $config['cache'];

	for($i=0; $i< $len; $i++){
		$tmp_g_block_map = $this->general_block($conn, $block_type_l[$i]);
		$g_block_map     = array_merge($g_block_map, $tmp_g_block_map);
		
		$tmp_g_code_map  = $this->general_block_code($conn, $block_type_l[$i]);
		$g_code_map      = array_merge($g_code_map,$tmp_g_code_map);
	}

	mysql_close($conn);	
	#general map cache

	$str = '<?php return '. var_export($g_block_map, true).';';
	file_put_contents(__PUBLIC__.'/cache/'.C('cache_block'), $str);
	
	#general cmd code map cache
	$str = '<?php return '. var_export($g_code_map, true).';';
	file_put_contents(__PUBLIC__."/cache/".C('cache_code'), $str);

	return array(200,1);
      }
}