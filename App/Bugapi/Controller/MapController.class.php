<?php
namespace Bugapi\Controller;
use Bugapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--快捷映射管理--
-----------------------------------------------------------
##--------------------------------------------------------##
*/
class MapController extends BaseController {
	/**
	 * sql script:
	 *
	 * */
	 
	 public $_module_name = '';
	 
	 public function get_map()
	 {
	   $_team = array(
	      'role'      =>'Role',
	      'position'  =>'Position',
	      'positionhr'=>'Position_hr',
	      'part'      =>'Part',
	      'admin'     =>'Admin',
	      'mod'       => 'Project_mod',
	   );
	   
	   $_map_list = array();
	   
	   $label_name = 'name';
	   
	   foreach($_team as $k=>$v)
	   {
	      if('admin' == $k) $label_name = 'admin_name';
	      else $label_name = 'name';
	      $tmp_list = M($v)->field('id,'.$label_name)->select();
	      foreach($tmp_list as $s_v)
	      {
		$_map_list[$k][intval($s_v['id'])] = urlencode($s_v[$label_name]);
	      }
	   }
	   unset($k, $v);
	   
	   return array(
	    200,
	    $_map_list
	   );
	 }
}
?>
