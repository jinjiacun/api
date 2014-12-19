<?php
namespace api\Controller;
use Think\Controller;
/**
--基础类--
*/
class BaseController extends Controller {

	protected $_module_name = '';

	public function __set($property_name, $value)
	{
		if(isset($this->$property_name))
		{
			$this->$property_name = $value;
		}
	}

	public function __get($property_name)
	{
		if(isset($this->$property_name))
		{
			return $this->$property_name;
		}
	}

	#填充参数
	protected function fill($content)
	{
		//反解析
    	#格式化并检查参数
		$format_params = json_decode($content, true);
		$key_list      = array_keys($format_params);
		extract($format_params);

		$data = array();
		if($key_list
		&& 0<count($key_list))
		{
			foreach($key_list as $v)
			{
				$data[$v] = ${$v}; 
			}
			unset($v);
		}

		return $data;
	}

	#添加
	public function add($content){
		$data = $this->fill($content);
		$obj  = M($this->_module_name);
		if($obj->add($data))
		{
			return array(
					200,
					array('is_success'=>0)
				);
		}

		return array(
				 200,
				 array('is_success'=>-1)
				 );
	}

	#查询列表
	public function get_list($content){
		$data = $this->fill($content);
		$obj  = M($this->_module_name);
		$list = $obj->where($data)->select();
		print_r($obj->getLastSql);
		return $list;
	}

	#查询单个
	public function get_row($content){

	}

	#修改
	public function update($content)
	{
		$data = $this->fill($content);
		$obj  = M($this->_module_name);
		if($obj->update($data))
		{
			return true;
		}

		return false;
	}

	#删除
	public function delete($content, $status, $content)
	{
		$data = $this->fill($content);
		$obj  = M($this->_module_name);
		if($obj->where($data)->delete())
		{
			return true;
		}
		return false;
	}
}