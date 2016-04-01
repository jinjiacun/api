<?php
namespace Magazineapi\Controller;
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
	      'admin'     =>'Admin',
	      
	      //'position'  =>'Position',
	      //'positionhr'=>'Position_hr',
	      //'part'      =>'Part',
	      
	      //'mod'       => 'Project_mod',
	      //'project'   => 'Project',
	      //'admin1'    => 'Admin1',
	      //'category'  => 'Category',
	      
	      'source_id' => 'Resume_source',
	   );
	   
	   $_map_list = array();
	   
	   $label_name = 'name';
	   
	   foreach($_team as $k=>$v)
	   {
	      if('admin' == $k || 'admin1' == $k) $label_name = 'admin_name';
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
