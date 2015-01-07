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
		$data = $this->fill($content);
		$data['where'] = isset($data['where'])?$data['where']:array();
		$data['order'] = isset($data['order'])?$data['order']:array('id'=>'desc');
		$obj  = M($this->_module_name);
		//$page_index = 1;
		//$page_size  = 10;
		$data = $obj->where($data['where'])
		            ->order($data['order'])
		            ->select();
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

		return array(200, $list);
	}

	public function add($content)
	{}
	
}
