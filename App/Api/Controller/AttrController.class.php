<?php
namespace api\Controller;
use Api\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--属性管理--
------------------------------------------------------------
function of api:

public function get_list
@@input
@param $page_index
@param $page_size
@param $where
@@output
@param $id
@param $name
##--------------------------------------------------------##
public function getlist_by_attr_ids  通过多个属性id获取属性的名称和id  
@@input 
@param $attr_ids   属性的id集合(之间用逗号隔开)
@@output
@param $attr_id    属性id
@param $attr_name  属性名称
##--------------------------------------------------------##
*/
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
			unset($data, $v);
		}

		return array(200, array(
							'list'=>$list,
							'record_count'=>$record_count)
					);
	}
	
	#通过多个属性id获取属性的名称和id  
	public function getlist_by_attr_ids($content)
	/*
	@@input 
	@param $attr_ids   属性的id集合(之间用逗号隔开)
	@@output
	@param $attr_val_id    属性id
	@param $attr_val_name  属性名称
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['attr_ids']))
		{
			return C('param_err');
		}
		
		$data['attr_ids'] = htmlspecialchars(trim($data['attr_ids']));

		if('' == $data['attr_ids'])
		{
			return C('param_fmt_err');
		}

		$attr_id   = 0;
		$attr_name = '';
		$list      = array();
		$where['id'] = array('in', $data['attr_ids']);
		$tmp_list = M('Attr')->field('id,name')->where($where)->select();
		if($tmp_list
		&& 0<=count($tmp_list))
		{
			foreach($tmp_list as $v)
			{
				$attr_id   = intval($v['id']);
				$attr_name = urlencode($v['name']);
				$list[$attr_id] = $attr_name;
			}
		}

		return array(
			200,
			$list
		);
	}
}
