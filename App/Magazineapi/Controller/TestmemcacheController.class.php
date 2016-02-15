<?php
namespace Soapi\Controller;
use Soapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
class TestmemcacheController extends BaseController {
	public function index($content)
	{
		S('test','memcache');
		$test = S('test');
	
		return array(
				200,
				array(
					'is_success'=>0,
					'message'=>C('option_ok'),
				)				
		);
	}
}