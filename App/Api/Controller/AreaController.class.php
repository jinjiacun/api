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

	public function add($content)
	{}
	
}
