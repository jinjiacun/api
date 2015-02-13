<?php
namespace Soapi\Controller;
use Soapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--下载管理--
------------------------------------------------------------
function of api:

#下载
public function index
@@input
@@output
@param $is_success 0-成功,-1-失败
##--------------------------------------------------------##
*/
class DownController extends BaseController {
	
	#下载
	public function index($content)
	/*
	@@input
	@@output
	@param $is_success 0-成功,-1-失败
	*/
	{
		$os = getOS();
		$url =  'http://'.$_SERVER['HTTP_HOST'].'/Public/app/';
		switch($os)
		{
			case 'android':
				$url .= 'android'.'/APP_pic.png';
				break;
			case 'iphone':
				$url .= 'ios'.'/APP_pic.png';
				break;
		}
		
		
		return array(
			200,
			array(
				'url'=>$url
			)
		);
	}
}
