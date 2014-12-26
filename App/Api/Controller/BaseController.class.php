<?php
namespace api\Controller;
use Think\Controller;
/**
--基础类--
------------------------------------------------------------
public function add           			  添加数据
public function get_list      			  查询数据列表
public function get_row                   查询一行
public function update                    更新
public function delete                    删除
public function send_mobile_validate_code 发送手机验证码
public function get_mobile_validate_code  查询手机验证码
public function send_email                发送邮件
------------------------------------------------------------
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
					array('is_success'=>0,
						  'id'=>$obj->getLastInsID()
						  )
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
		$data['where'] = isset($data['where'])?$data['where']:array();
		$data['page_index'] = isset($data['page_index'])?intval($data['page_index']):1;
		$data['page_size']  = isset($data['page_size'])?intval($data['page_size']):10;
		$obj  = M($this->_module_name);
		if(isset($data['page_index']))
			$page_index = $data['page_index'];
		else
			$page_index = 1;
		if(isset($data['page_size']))
			$page_size  = $data['page_size'];
		else
			$page_size  = 10;
		//$page_index = 1;
		//$page_size  = 10;
		$list = $obj->page($page_index, $page_size)->where($data['where'])->select();
		#
		$record_count = 0;
		$record_count = $obj->where($data['where'])->count();
		return array(
					$list,
					$record_count,
					);
	}

	#查询单个
	public function get_row($content){

	}

	#修改
	public function update($content)
	{
		$data = $this->fill($content);
		$obj  = M($this->_module_name);
		if($obj->where($data['where'])->update($data['data']))
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


	public function send_mobile_validate_code($content)
	{

	}

	#查询手机验证码
	public function get_mobile_validate_code($content)  
	{

	}

	#发送邮件
	public function send_email($content)                
	{

	}

	function get_real_ip(){
		$ip=false;
		if(!empty($_SERVER["HTTP_CLIENT_IP"])){
			$ip = $_SERVER["HTTP_CLIENT_IP"];
		}
		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ips = explode (", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
		if ($ip) 
			{
				array_unshift($ips, $ip); $ip = FALSE; 
			}
			for ($i = 0; $i < count($ips); $i++) {
				if (!eregi ("^(10|172\.16|192\.168)\.", $ips[$i])) {
				$ip = $ips[$i];
				break;
				}
			}
		}
		return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
	}
}
