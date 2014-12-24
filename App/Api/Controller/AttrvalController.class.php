<?php
namespace api\Controller;
use Api\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
class AttrvalController extends BaseController {
	protected $_module_name = 'attr_val';
	protected $id;
	protected $attr_id;
	protected $name;
	protected $add_time;
}