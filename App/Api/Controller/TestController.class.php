<?php
namespace api\Controller;
use Api\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
class TestController extends BaseController {
	protected $_module_name = 'test';


	public function set_cookie($content)
	{
		$data = $this->fill($content);

		if(!isset($data['user_name'])
		)
		{
			return C('param_err');
		}

		$data['user_name'] = htmlspecialchars(trim($data['user_name']));

		if('' == $data['user_name'])
		{
			return C('param_err');
		}

		session('user_name', $data['user_name']);

		return array(
			200,
			array(
				'is_success'=>0
				)
		);
	}

	public function get_cookie($content)
	{
		if(session('user_name'))
		{
			return array(
				200,
				array(
					'user_name'=>session('user_name')
					),
				);
		}

		return array(
			200,
			array(
				'user_name'=>'',
				),
			);
	}

	public function upload_file($content)
	{
		var_dump($_FILES);
		return array(
			200,
			array()
		);
	}
}