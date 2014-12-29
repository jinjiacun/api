<?php
namespace api\Controller;
use Api\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--分类属性值管理--
------------------------------------------------------------
function of api:

##--------------------------------------------------------## 
public function del_by_ids   批量删除分类属性值映射关系
@@input
@param $ids  分类属性值映射id
@@output
@param $is_success 0-成功操作,-1-操作失败 
##--------------------------------------------------------##  
*/
class CatattrvalController extends BaseController {
	protected $_module_name = 'cat_attr_val';
	protected $id;
	protected $cat_id;
	protected $attr_id;
	protected $attr_val_id;
	
	public function get_list($content)
	{
		list($data, $record_count) = parent::get_list($content);

		$list = array();
		if($data)
		{
			foreach($data as $v)
			{
				$list[] = array(
						'id'         =>intval($v['id']),
						'cat_id'     =>intval($v['cat_id']),
						'attr_id'    =>intval($v['attr_id']),
						'attr_val_id'=>intval($v['attr_val_id']),
						'goods_stat' =>intval($v['goods_stat']),
					);	
			}
			unset($data, $v);
		}

		return array(200, array(
							'list'=>$list,
							'record_count'=>$record_count)
					);
	}

	#批量删除分类属性值映射关系
	public function del_by_ids($content)
	/*
	@@input
	@param $ids  分类属性值映射id
	@@output
	@param $is_success 0-成功操作,-1-操作失败
	*/
	{
		$data = $this->fill($content);
		if(!isset($data['ids']))
		{
			return C('param_err');
		}
		
		$data['ids'] = htmlspecialchars(trim($data['ids']));
		
		if('' == $data['ids'])
		{
			return C('param_fmt_err');
		}
		
		if(M($this->_module_name)->delete($data['ids']))
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
				'is_success'=> -1,
				'message'   => urlencode('操作失败'),
			),
		);
	}
}
