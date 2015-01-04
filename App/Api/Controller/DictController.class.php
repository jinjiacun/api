<?php
namespace api\Controller;
use Think\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--字典管理--
 
public function get_name_map          获取编号和名称键值对
@@input
@param $cat_id   类别
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
	
	#获取编号和名称键值对
	public function get_name_map($content)
	/*
	@@input
	@param $cat_id   类别
	*/
	{
		$data = $this->fill($content);
		if(!isset($data['cat_id']))
		{
			return C('param_err');
		}
		
		$data['cat_id'] = intval($data['cat_id']);
		
		if(0>= $data['cat_id'])
		{
			return C('param_fmt_err');
		}
		
		$list = array();
		$where = array(
			'cat_id' => $data['cat_id'],
		);
		$tmp_list = M($this->_module_name)->where($where)->select();
		if($tmp_list
		&& 0< count($tmp_list)
		)
		{
			foreach($tmp_list as $v)
			{
				$list[$v['sn']] = urlencode($v['name']);
			}
		}
		
		return array(
			200,
			$list
		);
	}
}
