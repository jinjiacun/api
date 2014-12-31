<?php
namespace api\Controller;
use Think\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--分类管理--
------------------------------------------------------------
function of api:
 
public function get_category_info_by_id         通过分类获取分类信息
@@input
@param $cat_id          分类id
@@output
@param $id                  分类id
@param $name                分类名称
@param $add_time            添加日期
##--------------------------------------------------------##    
public function get_category_attr               获取分类属性及其属性值(finish)
@@input
@param $cat_id 分类id
@@output
@param $attr_id        属性id
@param $attr_name	   属性名称
@param $attr_val_id    属性值id
@param $attr_val_name  属性值名称
##--------------------------------------------------------##    
public function get_category_attr_val_id        获取分类属性值列表(finish)
@@input
@param $cat_id         分类id 
@@output
@param $attr_val_id    属性值id
##--------------------------------------------------------##    
public function get_category_attr_count         获取分类属性值个数(finish)
@@input
@param $cat_id  分类id 
@@output
@param $count   当前分类下的属性值的个数
##--------------------------------------------------------##    
public function get_category_attr_val_stat      获取分类属性值的绑定值
@@input
@param $cat_id  分类id
@@output
@param $goods_attr_val_ids  此分类下商品统计的绑定的属性值，之间用逗号隔开
##--------------------------------------------------------##    
public function get_categorys_attr              获取多个分类属性
@@input
@param $cat_ids 分类id(之间用逗号隔开)
@@output
@param $attr_id        属性id
@param $attr_name	   属性名称
@param $attr_val_id    属性值id
@param $attr_val_name  属性值名称
##--------------------------------------------------------##    
public function get_category_attr_val_show      获取分类显示属性值(finish)
@@input
@param $cat_id         分类id
@@output
@param $cat_id         分类id
@param $cat_name       分类名称
@param $attr_id        属性id
@param $attr_name      属性名称
@param $attr_val_id    属性值id
@param $attr_val_name  属性值名称
##--------------------------------------------------------##
public function chage_category_attr_val_show    改变分类显示属性值
@@input
@param $cat_id           分类id
@param $attr_id          要改变的属性id
@param $attr_val_id      要改变的属性值id
@param $chg_val          改变的值
@@output
@param $is_success 0-操作成功,-1-操作失败
##--------------------------------------------------------##
public function get_category_attr_val_map_stat 
* 获取当前分类绑定属性值和商品绑定属性值的统计数
@@input
@param $cat_id 当前分类id
@@output
@param $attr_val_id 属性值id
@param $stat        上面对应的属性值的统计值
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
						'id'          		 => intval($v['id']),
						'name'        		 => urlencode($v['name']),
						'add_time'           => intval($v['add_time']),
					);	
			}
			unset($data, $v);
		}

		return array(200, array(
							'list'=>$list,
							'record_count'=>$record_count)
					);
	}
	
	#通过分类获取分类信息
	public function get_category_info_by_id($content)
	/*
	@@input
	@param $cat_id              分类id
	@@output
	@param $id                  分类id
	@param $name                分类名称
	@param $add_time            添加日期
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
		$tmp_one = M('Category')->find($data['cat_id']);
		if($tmp_one)
		{	
			$list = array(
				'id'                 => intval($tmp_one['id']),
				'name'	             => urlencode($tmp_one['name']),
			);
		}
		
		return array(
			200,
			$list
		);
	}

	#获取分类属性及其属性值
	public function get_category_attr($content)           
	/*
	@@input
	@param $cat_id 分类id
	@@output
	@param $attr_id        属性id
	@param $attr_name	   属性名称
	@param $attr_val_id    属性值id
	@param $attr_val_name  属性值名称
    */
	{
		$data = $this->fill($content);
		
		if(!isset($data['cat_id']))
		{
			return C('param_err');
		}
		
		$data['cat_id'] = intval($data['cat_id']);
		
		if(0 >= $data['cat_id'])
		{
			return C('param_fmt_err');
		}
		
		
		$attr_id_list      = array();
		$attr_val_id_list  = array();
		$attr_id           = 0;
		$attr_val_id       = 0;
		#查询此分类下的属性值
		$where = array(
			'cat_id' => $data['cat_id'],
		);
		$tmp_list = M('Cat_attr_val')->where($where)->select();
		if($tmp_list
		&& 0<count($tmp_list))
		{
			foreach($tmp_list as $v)
			{
				$attr_id     = intval($v['attr_id']);
				$attr_val_id = intval($v['attr_val_id']);
				
				$attr_id_list[]     = $attr_id;
				$attr_val_id_list[] = $attr_val_id;
			}
			unset($v);
		}
		unset($attr_id, $attr_val_id);
		
		#格式化输出
		$attr_name_list     = array();
		$attr_val_name_list = array();
		
		##查询目标属性名称
		if(0 < count($attr_id_list))
		{
			$attr_ids         = implode(',', $attr_id_list);
			unset($attr_id_list);
			$obj = A('Api/Attr');
			$tmp_data = array(
				'attr_ids' => $attr_ids,
			);
			list(, $attr_name_list) = 
			          $obj->getlist_by_attr_ids(json_encode($tmp_data));
			unset($tmp_data, $attr_ids);
		}
		
		##查询目标属性值名称
		if(0 <count($attr_val_id_list))
		{
			$attr_val_ids         = implode(',', $attr_val_id_list);
			unset($attr_val_id_list);
			$obj = A('Api/Attrval');
			$tmp_data = array(
				'attr_val_ids' => $attr_val_ids,
			);
			list(, $attr_val_name_list) = 
			       $obj->getlist_by_attrval_ids(json_encode($tmp_data));
			unset($tmp_data, $attr_val_ids);
		}
		
		$list          = array();
		$attr_id       = 0;
		$attr_name     = '';
		$attr_val_id   = 0;
		$attr_val_name = '';
		if($tmp_list
		&&	0 <count($tmp_list)
		)
		{
			foreach($tmp_list as $k=>$v)
			{
				$attr_id       = intval($v['attr_id']);
				$attr_name     = $attr_name_list[$attr_id];
				$attr_val_id   = intval($v['attr_val_id']);
				$attr_val_name = $attr_val_name_list[$attr_val_id];

				$list[] = array(
						'attr_id'       => intval($attr_id),
						'attr_name'     => $attr_name,
						'attr_val_id'   => intval($attr_val_id),
						'attr_val_name' => urlencode($attr_val_name),
				);
			}
			unset($map_list, $k, $v);
		}
		
		return array(
			200,
			$list
		);
	}
	
	#获取分类属性值列表
	public function get_category_attr_val_id($content)
	/*
	@@input
	@param $cat_id         分类id 
	@@output
	@param $attr_val_id    属性值id
	*/
	{
		$list = array();
		
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
		
		$where = array(
			'cat_id' => $data['cat_id'],
		);
		$tmp_list = M('Cat_attr_val')->field("attr_val_id")
		                             ->where($where)
		                             ->select();
		if($tmp_list
		&& 0 < count($tmp_list)
		)
		{
			foreach($tmp_list as $v)
			{
				$list[] = array(
						'attr_val_id' => intval($v['attr_val_id']),
					);
			}
			unset($tmp_list, $v);
		}
		
		return array(
			200,
			$list
		);
	}
	
	#获取分类属性值个数
	public function get_category_attr_count($content)
	/*
	@@input
	@param $cat_id  分类id 
	@@output
	@param $count   当前分类下的属性值的个数
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
		
		$count = 0;
		$where = array(
			'cat_id'=> $data['cat_id'],
		);
		$count = M('Cat_attr_val')->where($where)->count();
		
		return array(
			200,
			$count
		);
	}

	#获取分类属性值的绑定值
	public function get_category_attr_val_stat($content)
	/*
	@@input
	@param $cat_id  分类id
	@@output
	@param $goods_attr_val_ids  此分类下商品统计的绑定的属性值，之间用逗号隔开
	*/
	{
		$goods_attr_val_ids = '';
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
		
		$obj = M('Category')->find($data['cat_id']);
		if($obj
		&& $obj->goods_attr_val_ids)
			$goods_attr_val_ids = $obj->goods_attr_val_ids;
		
		return array(
			200,
			array(
				'goods_attr_val_ids' => $goods_attr_val_ids,
				),
			);
	}
	
	#获取多个分类属性
	public function get_categorys_attr($content)
	/*           
	@@input
	@param $cat_ids 分类id(之间用逗号隔开)
	@@output
	@param $attr_id        属性id
	@param $attr_name	   属性名称
	@param $attr_val_id    属性值id
	@param $attr_val_name  属性值名称
	*/ 
	{
		
	}
	
	#获取分类显示属性值
	public function get_category_attr_val_show($content)
	/*      
	@@input
	@param $cat_id         分类id
	@@output
	@param $cat_id         分类id
	@param $cat_name       分类名称
	@param $attr_id        属性id
	@param $attr_name      属性名称
	@param $attr_val_id    属性值id
	@param $attr_val_name  属性值名称
	*/
	{
		$list = array();
		
		$data = $this->fill($content);
		
		if(!isset($data['cat_id']))
		{
			return C('param_err');
		}
		
		$data['cat_id'] = intval($data['cat_id']);
		
		if(0 >= $data['cat_id'])
		{
			return C('param_fmt_err');
		}
		                                 
		$tmp_list = D('CategoryAttrView')
		                             ->where($data)
		                             ->select();
        
		if($tmp_list
		&& 0<= count($tmp_list))
		{
			foreach($tmp_list as $k=> $v)
			{
				$goods_stat = intval($v['goods_stat']);
				if(0 < $goods_stat)
				{
					$list[] = array(
						'cat_id'         => intval($v['cat_id']),
						'cat_name'       => urlencode($v['cat_name']),
						'attr_id'        => intval($v['attr_id']),
						'attr_name'      => urlencode($v['attr_name']),
						'attr_val_id'    => intval($v['attr_val_id']),
						'attr_val_name'  => urlencode($v['attr_val_name']),
					);
				}
			}
			unset($tmp_list, $k, $v);
		}
		
		return array(
			200,
			$list
		);
	}
	
	#改变分类显示属性值
	public function chage_category_attr_val_show($content)
	/*    
	@@input
	@param $cat_id           分类id
	@param $attr_id          要改变的属性id
	@param $attr_val_id      要改变的属性值id
	@param $chg_val          改变的值
	@@output
	@param $is_success 0-操作成功,-1-操作失败
	*/
	{
		$data = $this->fill($content);
		if(!isset($data['cat_id'])
		|| !isset($data['attr_id'])
		|| !isset($data['attr_val_id'])
		|| !isset($data['chg_val'])
		)
		{
			return C('param_err');
		}
		
		$data['cat_id']      = intval($data['cat_id']);
		$data['attr_id']     = intval($data['attr_id']);
		$data['attr_val_id'] = intval($data['attr_val_id']);
		$data['chg_val']     = intval($data['chg_val']);
		           
		if(0 >= $data['cat_id']
		|| 0 >= $data['attr_id']
		|| 0 >= $data['attr_val_id'])
		{
			return C('param_fmt_err');
		}
		
		$where = array(
			'cat_id'       => $data['cat_id'],
			'attr_id'      => $data['attr_id'],
			'attr_val_id'  => $data['attr_val_id'],
		);
		
		if(0 > $data['chg_val'])
		{
			if(M('Cat_attr_val')->where($where)->setDec('goods_stat', abs($data['chg_val'])))
			{
				return array(
					200,
					array(
						'is_success' => 0,
						'message'    => urlencode('操作成功'),
					),
				);
			}
		}
		elseif(0 < $data['chg_val'])
		{
			if(M('Cat_attr_val')->where($where)->setInc('goods_stat', abs($data['chg_val'])))
			{
				return array(
					200,
					array(
						'is_success' => 0,
						'message'    => urlencode('成功操作'),
					),
				);
			}
		}
		
		return array(
				200,
				array(
					'is_success'=>-1,
					'message'   =>urlencode('操作失败'),
				),
			);
	}
	
	#获取当前分类绑定属性值和商品绑定属性值的统计数
	public function get_category_attr_val_map_stat($content)
	/*
	@@input
	@param $cat_id 当前分类id
	@@output
	@param $attr_val_id 属性值id
	@param $stat        上面对应的属性值的统计值
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
		
		$category_info = M('Category')->find($data['cat_id']);
		$list = array();
		$attr_id_list      = explode(',', $category_info->attr_val_id);
		$bind_attr_id_list = explode(',', $category_info->goods_attr_val_ids); 
		
		if($attr_id_list
		&& 0< count($attr_id_list))
		{
			$count = count($attr_id_list);
			for($i=0; $i< $count; $i++)
			{
				$list[] = array(
					'attr_val_id' => intval($attr_id_list[$i]),
					'stat'        => intval($bind_attr_id_list[$i])
				);
			}
		}
		
		return array(
			200,
			$list
		);
	}
}
