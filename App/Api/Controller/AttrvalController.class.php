<?php
namespace api\Controller;
use Api\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--属性值管理--
------------------------------------------------------------
function of api:

public function getlist_by_attrval_ids  通过多个属性值id获取属性值的名称和id  
@@input 
@param $attr_val_ids   属性值的id集合(之间用逗号隔开)
@@output
@param $attr_val_id    属性值id
@param $attr_val_name  属性值名称
##--------------------------------------------------------##
*/
class AttrvalController extends BaseController {
	protected $_module_name = 'attr_val';
	protected $id;
	protected $attr_id;
	protected $name;
	protected $add_time;

	#通过多个属性值id获取属性值的名称和id
	public function getlist_by_attrval_ids($content)
	/*
	@@input 
	@param $attr_val_ids   属性值的id集合(之间用逗号隔开)
	@@output
	@param $attr_val_id    属性值id
	@param $attr_val_name  属性值名称
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['attr_val_ids']))
		{
			return C('param_err');
		}
		
		$data['attr_val_ids'] = 
		  htmlspecialchars(trim($data['attr_val_ids']));
		
		$list          = array();
		$attr_val_id   = 0;
		$attr_val_name = '';  
		$where['id'] = array('in', $data['attr_val_ids']);
		$tmp_list = M('Attr_val')->field("id,name")->where($where)->select();
		
		
		if($tmp_list
		&& 0< count($tmp_list))
		{
			foreach($tmp_list as $v)
			{
				$attr_val_id   = $v['id'];
				$attr_val_name = $v['name'];
				$list[$attr_val_id] = $attr_val_name;
			}
		}
		
		return array(
				200,
				$list
		);
	}
}
