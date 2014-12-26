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
public function getattrlist_by_attrval_ids 通过多个属性值id获取属性id和属性值id(和名称)
@@input
@param $attr_val_ids  属性值的id集合(之间用逗号隔开)
@@output
@param $attr_id       属性id
@param $attr_val_id   属性值id
@param $attr_val_name 属性值名称
##--------------------------------------------------------##
public function getattrlist_map_by_attrval_ids 通过多个属性值id获取属性id和属性值id(和名称)的映射(属性值id=>{属性值id，属性id,属性值名称})
@@input
@param $attr_val_ids  属性值的id集合(之间用逗号隔开)
@@output
@param $attr_id       属性id
@param $attr_val_id   属性值id
@param $attr_val_name 属性值名称
##--------------------------------------------------------##
public function getlist_by_attr_ids    通过属性id集合获取属性值的名称和id
@@input
@param $attr_ids      属性的id集合(之间用逗号隔开)
@@output
@param $attr_id        属性id
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

	public function get_list($content)
	{
		list($data, $record_count) = parent::get_list($content);

		$list = array();
		if($data)
		{
			foreach($data as $v)
			{
				$list[] = array(
						'id'      => intval($v['id']),
						'attr_id' => intval($v['attr_id']),
						'name'    => urlencode($v['name']),
					);
			}
			unset($data, $v);
		}

		return array(200,array(
							'list'=> $list,
							'record_count' => $record_count
							)

			);
	}

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
	
	#通过多个属性值id获取属性id和属性值id(和名称)
	public function getattrlist_by_attrval_ids($content)
	/*
	@@input
	@param $attr_val_ids  属性值的id集合(之间用逗号隔开)
	@@output
	@param $attr_id       属性id
	@param $attr_val_id   属性值id
	@param $attr_val_name 属性值名称
	*/
	{
		$list = array();
		$data = $this->fill($content);
		
		if(!isset($data['attr_val_ids']))
		{
			return C('param_err');
		}
		
		$data['attr_val_ids'] = 
		   htmlspecialchars(trim($data['attr_val_ids']));
		
		if('' == $data['attr_val_ids'])
		{
			return C('param_fmt_err');
		}
		
		$where['id'] = array('in', $data['attr_val_ids']);
		$tmp_list = M('Attr_val')->where($where)->select();
		if($tmp_list
		&& 0< count($tmp_list))
		{
			foreach($tmp_list as $v)
			{
				$list[] = array(
						'attr_id'       => intval($v['attr_id']),
						'attr_val_id'   => intval($v['id']),
						'attr_val_name' => urlencode($v['name']),
				);
			}
			unset($tmp_list, $v);
		}
		
		return array(
			200,
			$list
		);
	}
	
	#通过多个属性值id获取属性id和属性值id(和名称)的映射
	#(属性值id=>{属性值id，属性id,属性值名称})
	public function getattrlist_map_by_attrval_ids($contet)
	/*
	@@input
	@param $attr_val_ids  属性值的id集合(之间用逗号隔开)
	@@output
	@param $attr_id       属性id
	@param $attr_val_id   属性值id
	@param $attr_val_name 属性值名称
	*/
	{
		$list = array();
		list(,$tmp_list) = $this->getattrlist_by_attrval_ids($content);
		if(0< count($tmp_list))
		{
			foreach($tmp_list as $v)
			{
				$attr_val_id = intval($v['attr_val_id']);
				$list[$attr_val_id] = $v;
			}
			unset($tmp_list, $v, $attr_val_id);
		}
		
		return array(
			200,
			$list
		);
	}
	
	#通过属性id集合获取属性值的名称和id
	public function getlist_by_attr_ids($content)
	/*
	@@input
	@param $attr_ids      属性的id集合(之间用逗号隔开)
	@@output
	@param $attr_id        属性id
	@param $attr_val_id    属性值id
	@param $attr_val_name  属性值名称
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

		$where['attr_id'] = array("in", $data['attr_ids']);

		$list = array();
		$tmp_list = M('Attr_val')->where($where)->select();
		
		if($tmp_list
		&& 0<= count($tmp_list)
		)
		{
			foreach($tmp_list as $v)
			{
				$list[] = array(
						'attr_id'       =>urlencode($v['attr_id']),
						'attr_val_id'   =>urlencode($v['id']),
						'attr_val_name' =>urlencode($v['name']),
					);
			}
		}

		return array(
				200,
				$list,
			);
	}
}
