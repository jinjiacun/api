<?php
namespace Soapi\Controller;
use Soapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
class TestController extends BaseController {
	protected $_module_name = 'test';
	
	public function test_user($content)
	{
		$url = "http://192.168.1.31:8300/Api/RegisterByMobile";
		$params = array(
			'nickname'  => 'jime',
			'mobile'    => '15021725013',
			'validated' => 0,
			'pswd'      => '123456',
			'userip'    => '192.168.1.113',
		);
		$params['safekey'] = $this->mk_passwd($params);
		
		echo $this->post($url, $params);
	}
	
		
	
	
}
