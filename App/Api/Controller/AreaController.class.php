<?php
namespace api\Controller;
use Api\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--地区管理--
------------------------------------------------------------
function of api:
------------------------------------------------------------
*/
class AreaController extends BaseController {
    protected $_module_name = 'area';
	protected $areaid;
    protected $areaname;
	protected $parentid;
	protected $arrparentid;
    protected $child;
    protected $arrchildid;
    protected $listorder;    

	public function get_list($content)
	{
		list($data, $record_count) = parent::get_list($content);

		$list = array();
		if($data)
		{
			foreach($data as $v)
			{
				$list[] = array(
						'areaid'      =>intval($v['areaid']),
						'areaname'    =>urlencode($v['areaname']),
						'parentid'    =>intval($v['parentid']),
						'arrparentid' =>$v['arrparentid'],
						'child'       =>intval($v['child']),
						'arrchildid'  =>urlencode($v['arrchildid']),
						'listorder'   =>intval($v['listorder']),
					);	
			}
			unset($data, $v);
		}

		return array(200, array(
							'list'=>$list,
							'record_count'=>$record_count)
					);
	}

	public function add($content)
	{}
	
}
