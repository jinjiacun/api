<?php
namespace api\Controller;
use Think\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--分类管理--
------------------------------------------------------------
function of api:

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
@param $attr_id        属性id
@param $attr_val_id    属性值id
##--------------------------------------------------------##
public function chage_category_attr_val_show    改变分类显示属性值
@@input
@param $cat_id           分类id
@param $option_list      进行运算操作的列表，用逗号隔开
@@output
@param $is_success 0-操作成功,-1-操作失败
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
						'id'          		 => intval($v['Id']),
						'name'        		 => urlencode($v['name']),
						'attr_val_id' 		 => urlencode($v['attr_val_id']),
						'goods_attr_val_ids' => urlencode($v['goods_attr_val_ids']),
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
						'attr_id'       => $attr_id,
						'attr_name'     => $attr_name,
						'attr_val_id'   => $attr_val_id,
						'attr_val_name' => $attr_val_name,
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
	@param $attr_id        属性id
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
		
		if(0 >= $data['cat_id'])
		{
			return C('param_fmt_err');
		}


		$tmp_row = M('Category')->field('attr_val_id,goods_attr_val_ids')
		                             ->find($data['cat_id']);
		if($tmp_row
		&& 0<= count($tmp_row))
		{
			#
			$obj = A('Attrval');
			$where = array(
				'attr_val_ids'=>$tmp_row->attr_val_id
			);
			list(,$map_attr_val) = 
			      $obj->getattrlist_map_by_attrval_ids(json_encode($where));

			$cat_attr_val_id_list   = explode(',', $tmp_row->attr_val_id);
			$goods_attr_val_id_list = explode(',', $tmp_row->goods_attr_val_ids);
			if(0< count($cat_attr_val_id_list))
			{
				foreach($cat_attr_val_id_list as $k=> $v)
				{
					if(0 < $goods_attr_val_id_list[$k])
					{
						$list[] = array('attr_val_id'=>intval($v),
							            'attr_id'    =>intval($map_attr_val[$v]['attr_id']));
					}	
				}
				unset($cat_attr_val_id_list, $goods_attr_val_id_list, $k, $v);
			}
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
	@param $option_list      进行运算操作的列表，用逗号隔开
	@@output
	@param $is_success 0-操作成功,-1-操作失败
	*/
	{
		$data = $this->fill($content);
		if(!isset($data['cat_id'])
		|| !isset($data['option_list'])
		)
		{
			return C('param_err');
		}
		
		$data['cat_id'] = intval($data['cat_id']);
		$data['option_list'] = 
		           htmlspecialchars(trim($data['option_list']));
		           
		if(0>= $data['cat_id']
		|| '' == $data['option_list'])
		{
			return C('param_fmt_err');
		}
		
		$option_list = explode(',', $data['option_list']);
		
		#获取当前分类属性值统计
		$tmp_data = array(
			'cat_id' => $data['cat_id'],
		);
		list(,$cur_stat)    = 
			$this->get_category_attr_val_stat(json_encode($tmp_data));
			
		$cur_stat_list = explode(',', $cur_stat);
		$result_list = array();
		
		for($i=0; $i< count($cur_stat_list); $i++)
		{
			$result_list[] = $optioin_list[$i] 
			               + $cur_stat_list[$i];
		}
		
		$result = implode(',', $result_list);
	    $where = array(
			'id'=> $data['cat_id'],
	    );
	    $tmp_data['goods_attr_val_ids'] = $result;
	    if(M('Category')->where($where)->save($tmp_data))
	    {
			return array(
				200,
				array(
					'is_success'=>0,
					'message'   =>urlencode('操作成功'),
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
