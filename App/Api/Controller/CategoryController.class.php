<?php
namespace api\Controller;
use Think\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--分类管理--
*/
class CategoryController extends BaseController {
	
	protected $_module_name = 'category';
	protected $id;                         #id
	protected $name;                       #name

	public function get_list($content)
	{
		list($data, $record_count) = parent::get_list($content);

		$list = array();
		if($data)
		{
			foreach($data as $v)
			{
				$list[] = array(
						'id'=>intval($v['Id']),
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