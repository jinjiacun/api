<?php
namespace Azureapi\Controller;
use Think\Controller;
/**
--基础类--
------------------------------------------------------------
public function add           			  添加数据
public function add_mul                   批量添加
##--------------------------------------------------------##
public function get_list      			  查询数据列表
@@input
@param $page_index   当前满足条件的第几页(可选)
@param $page_size    当前请求的页面数(可选)
@param $where        当前查询条件(可选)
@@output
@param 返回对应的列表，具体内容看具体的对应的模块
##--------------------------------------------------------##
public function get_row                   查询一行
##--------------------------------------------------------##
public function get_info
@@input
@param $id
@@output
##--------------------------------------------------------##
public function update                    更新
@@input
@param $where 条件
@param $data  要更新的数据
@@output
@param $is_success 0-成功操作，-1-操作失败
##--------------------------------------------------------##
public function delete                    删除
@@input
@param $content 条件
@@output
@param $is_success 0-成功操作,-1-操作失败
##--------------------------------------------------------##
public function send_mobile_validate_code 发送手机验证码
##--------------------------------------------------------##
public function get_mobile_validate_code  查询手机验证码
##--------------------------------------------------------##
public function send_email                发送邮件
##--------------------------------------------------------##
public function get_real_ip               获取当前访问的ip地址
------------------------------------------------------------
*/
class BaseController extends Controller {

	protected $_module_name = '';
	protected $_key = '';

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

		/*
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
		*/
		return $format_params;
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

	#批量添加
	public function add_mul($content)
	{
		$data = $this->fill($content);
		$obj = M($this->_module_name);
		if($obj->addAll($data))
		{
			return array(
				200,
				array(
					'is_success' => 0,
					'message'    => urlencode('成功操作'),
				),
			);
		}

		return array(
			200,
			array(
				'is_success' => -1,
				'message'    => urlencode('操作失败'),
			),
		);
	}

	#查询列表
	public function get_list($content)
	/*
	@@input
	@param $page_index   当前满足条件的第几页(可选)
	@param $page_size    当前请求的页面数(可选)
	@param $where        当前查询条件(可选)
	@@output
	@param 返回对应的列表，具体内容看具体的对应的模块
	*/
	{
		$data = $this->fill($content);
		$data['where'] = isset($data['where'])?$data['where']:array();
		$data['page_index'] = isset($data['page_index'])?intval($data['page_index']):1;
		$data['page_size']  = isset($data['page_size'])?intval($data['page_size']):10;
		$data['order']      = isset($data['order'])?$data['order']:array($this->_key=>'desc');
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
		$list = $obj->page($page_index, $page_size)
		            ->where($data['where'])
		            ->order($data['order'])
		            ->select();
		#
		$record_count = 0;
		$record_count = $obj->where($data['where'])->count();
		return array(
					$list,
					$record_count
		);
	}

	#查询单个
	public function get_row($content){

	}

	#通过关键字通过基本信息
	public function get_info($content)
	/*
	@@input
	@param $id
	@@output
	*/
	{
		$data = $this->fill($content);

		if(!isset($data['id']))
		{
			return C('param_err');
		}

		$data['id'] = intval($data['id']);

		if(0>= $data['id'])
		{
			return C('param_fmt_err');
		}

		$tmp_one = M($this->_module_name)->find($data['id']);
		if($tmp_one)
		{
			return array(
				200,
				$tmp_one
			);
		}

		return array(
			200,
			array(
			),
		);
	}

	#修改
	public function update($content)
	/**
	@@input
	@param $where 条件
	@param $data  要更新的数据
	@@output
	@param $is_success 0-成功操作，-1-操作失败
	*/
	{
		$data = $this->fill($content);
		$obj  = M($this->_module_name);
		if($obj->where($data['where'])->save($data['data']))
		{
			return array(
				200,
				array(
				'is_success'=>0,
				'message'   => urlencode('操作成功')
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

	#删除
	public function delete($content)
	/*
	@@input
	@param $content 条件
	@@output
	@param $is_success 0-成功操作,-1-操作失败
	*/
	{
		$data = $this->fill($content);
		$obj  = M($this->_module_name);
		if($obj->where($data)->delete())
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

	#获取当前访问的ip地址
	public function get_real_ip($content){
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
