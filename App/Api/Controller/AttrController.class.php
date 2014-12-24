<?php
namespace api\Controller;
use Api\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
class AttrController extends BaseController {
	protected $_module_name = 'attr';
	protected $id;
	protected $name;
	protected $add_time;

	public function get_list($content)
	{
		list($data, $record_count) = parent::get_list($content);

		$list = array();
		if($data)
		{
			foreach($data as $v)
			{
				$list[] = array(
						'id'=>intval($v['id']),
						'name'=>urlencode($v['name']),
					);	
			}
		}

		return array(200, array(
							'list'=>$list,
							'record_count'=>$record_count)
					);
	}
}