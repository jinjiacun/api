<?php
namespace api\Controller;
use Api\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
class CatattrvalController extends BaseController {
	protected $_module_name = 'cat_attr_val';
	protected $id;
	protected $cat_id;
	protected $attr_id;
	protected $attr_val_id;
	
}