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
public function getview_by_attr_ids   通过属性值id集合获取属性和属性值的视图
@@input
@param $attr_val_ids   属性值id集合(之间用逗号隔开) 
@@output
@param $attr_id       属性id
@param $attr_val_id   属性值id
@param $attr_name     属性名称
@param $attr_val_name 属性值名称
##--------------------------------------------------------##
public function check_attr_val_is_bind    判定此属性值是否绑定
@@input
@param $attr_val_id  属性值id
@@output
@param $is_bind      0-绑定,-1-没绑定   
##--------------------------------------------------------##
public function del_by_val_ids     通过多个属性值id来删除属性值
@@input
@param $attr_val_ids  属性值id(之间用逗号隔开)
@@output
@param $is_success 0-成功操作，-1-操作失败
##--------------------------------------------------------##
public function add_mul            一次添加多个属性值方法
@@input
@param $attr_id        属性id
@param $attr_val_names 属性值名称(之间用逗号隔开)
@@output
@param $is_success 0-成功操作,-1-操作失败   
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
	
	#通过属性值id集合获取属性和属性值的视图
	public function getview_by_attr_ids($content)
	/*
	@@input
	@param $attr_val_ids   属性值id集合(之间用逗号隔开) 
	@@output
	@param $attr_id       属性id
	@param $attr_val_id   属性值id
	@param $attr_name     属性名称
	@param $attr_val_name 属性值名称
	*/
	{
		$data = $this->fill($content);
		if(!isset($data['attr_val_ids']))
		{
			return C('param_err');
		}
		
		$data['attr_val_ids'] = htmlspecialchars(trim($data['attr_val_ids']));
		
		if('' == $data['attr_val_ids'])
		{
			return C('param_fmt_err');
		}
		
		$list = array();
		$obj = D('AttrView');
		$where['Attr_val.id'] = array('in', $data['attr_val_ids']);
		$tmp_list = $obj->where($where)->select();
		if($tmp_list
		&& 0< count($tmp_list))
		{
			foreach($tmp_list as $v)
			{
				$list[] = array(
						'attr_id'       => intval($v['attr_id']),
						'attr_val_id'   => intval($v['attr_val_id']),
						'attr_name'     => urlencode($v['attr_name']),
						'attr_val_name' => urlencode($v['attr_val_name']),
				);
			}
			unset($tmp_list, $v);
		}
		
		return array(
			200,
			$list
		);
	}
	
	#判定此属性值是否绑定
	public function check_attr_val_is_bind($content)
	/*
	@@input
	@param $attr_val_id  属性值id
	@@output
	@param $is_bind      0-绑定,-1-没绑定
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['attr_val_id']))
		{
			return C('param_err');
		}
		
		$data['attr_val_id'] = intval($data['attr-val_id']);
		
		if(0>= $data['attr_val_id'])
		{
			return C('param_fmt_err');
		}
		
		$count = 0;
		$where = array(
			'attr_val_id' => $data['attr_val_id'],
		);
		$count = M('Cat_attr_val')->where($where)->count();
		if($count
		&& 0< $count)
		{
			return array(
				200,
				array(
					'is_bind'=>0,
					'message'=> urlencode('已绑定')
				)
			);	
		}
		
		return array(
				200,
				array(
					'is_bind'=>-1,
					'message'=> urlencode('未绑定')
				)
			);
	}
	
	#通过多个属性值id来删除属性值
	public function del_by_val_ids($content)
	/*
	@@input
	@param $attr_val_ids  属性值id(之间用逗号隔开)
	@@output
	@param $is_success 0-成功操作，-1-操作失败
	*/
	{
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
		
		if(M('attr_val')->delete($data['attr_val_ids']))
		{
			return array(
				200,
				array(
					'is_success'=> 0,
					'message'   => urlencode('成功操作'),
				)
			);
		}
		
		return array(
				200,
				array(
					'is_success'=> -1,
					'message'   => urlencode('操作失败'),
				)
			);
	}
	
	#一次添加多个属性值方法
	public function add_mul($content)
	/*
	@@input
	@param $attr_id        属性id
	@param $attr_val_names 属性值名称(之间用逗号隔开)
	@@output
	@param $is_success 0-成功操作,-1-操作失败 
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['attr_id'])
		|| !isset($data['attr_val_names'])
		)
		{
			return C('param_err');
		}
		
		$data['attr_id'] = intval($data['attr_id']);
		$data['attr_val_names'] = 
			htmlspecialchars(trim($data['attr_val_names']));
		
		if(0>= $data['attr_id']
		|| '' == $data['attr_val_names']
		)
		{
			return C('param_fmt_err');
		}
		
		$data_list = array();
		$attr_val_name_list = explode(',', $data['attr_val_names']);
		$count = count($attr_val_name_list);
		for($i=0; $i<$count; $i++)
		{
			$data_list[] = array(
				'attr_id' => $data['attr_id'],
				'name'    => $attr_val_name_list[$i],
			);
		}
		$obj = M('Attr_val');
		if($obj->addAll($data_list))
		{
			return array(
				200,
				array(
					'is_success'=> 0,
					'message'   => urlencode('成功操作'),
				),
			);
		}
		
		return array(
			200,
			array(
				'is_success'=>-1,
				'message'   =>urlencode('操作失败'),
			),
		);
	}
}
