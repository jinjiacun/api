<?php
namespace api\Controller;
use Think\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--字典管理--
*/
class DictController extends BaseController {
	
	protected $_module_name = 'dict';
	protected $id;                         #id
	protected $sn;                         #编号
	protected $cat_id;                     #字典类别id  
	protected $cat_name;                   #字典类别
	protected $name;                       #名称
	protected $sort;                       #排序

	public function get_list($content)
	{
		list($data, $record_count) = parent::get_list($content);

		$list = array();
		if($data)
		{
			foreach($data as $v)
			{
				$list[] = array(
						'id'      => intval($v['Id']),
						'sn'      => urlencode($v['sn']),
						'cat_id'  => intval($v['cat_id']),
						'cat_name'=> urlencode($v['cat_name']),
						'name'    => urlencode($v['name']),
						'sort'    => intval($v['sort']),
					);	
			}
		}

		return array(200, 
				array(
					'list'=>$list,
					'record_count'=> $record_count,
					)
				);
	}
}
