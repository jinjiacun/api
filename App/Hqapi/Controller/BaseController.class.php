<?php
namespace Hqapi\Controller;
use Think\Controller;
set_time_limit(0);
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
@@inputr
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
##--------------------------------------------------------##
protected function __exists				  判定是否存在
##--------------------------------------------------------##
public function down_net_pic              从网络下载图片
@@input
@param $net_pic_url
@@output
@param $local_pic_url
##--------------------------------------------------------##
public function rtx_push                  rxt消息推送
@@input
@param $rtx_nickname
@param $rtx_no
@param $message_title
@param $message_content
@@output
@param $is_success 0-成功,1-失败
#
------------------------------------------------------------
*/
class BaseController extends Controller {

	protected $_module_name = '';
	protected $_key         = 'id';

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
		$data['add_time'] = time();
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
	public function get_list_ex($content)
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
		if(isset($data['where']['sign']))
		{
			if(0<$data['where']['sign'])
			{
				$data['where']['sign'] = 1;
			}
		}
		$data['page_index'] = isset($data['page_index'])?intval($data['page_index']):1;
		$data['page_size']  = isset($data['page_size'])?intval($data['page_size']):10;
		if($this->key){
			$data['order']      = isset($data['order'])?$data['order']:array($this->key=>'desc');
		}else{
			$data['order']      = isset($data['order'])?$data['order']:null;
		}
		$obj  = M($this->_module_name);
		if(isset($data['page_index']))
			$page_index = $data['page_index'];
		else
			$page_index = 1;
			
		if(0>= $page_index)
		{
			$page_index = 1;
		}
		
		if(isset($data['page_size']))
			$page_size  = $data['page_size'];
		else
			$page_size  = 10;
			
		if(30<= $page_size)
		{
			$page_size = 30;
		}
		//$page_index = 1;
		//$page_size  = 10;
		if(isset($data['group']))
		{
				$list = $obj->distinct(true)->field('cmd,pname')
				    ->page($page_index, $page_size)
					->group($data['group'])
		            ->where($data['where'])
		            ->order($data['order'])
		            ->select();
		}
		else if(isset($data['order']))
		{
			$list = $obj->distinct(true)->field('cmd,pname')
			    ->page($page_index, $page_size)
				->where($data['where'])
				->order($data['order'])
				->select();
		}
		else{
			$list = $obj->distinct(true)->field('cmd,pname')
			    ->page($page_index, $page_size)
				->where($data['where'])
				->select();
		}
		            
		//echo M()->getlastSql();
		//die;
		#
		$record_count = 0;
		$record_count = $obj->where($data['where'])		                    
			                ->count("distinct cmd");
	    /*
		if(isset($data['group'])){
			$record_count = $obj->group($data['group'])
			                     ->where($data['where'])
			                     ->count();	
		}
		else{
			$record_count = $obj->where($data['where'])
			                     ->count();		
		}
		*/
		
		//echo M()->getlastSql();
		return array(
					$list, 
					$record_count
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
		if(isset($data['where']['sign']))
		{
			if(0<$data['where']['sign'])
			{
				$data['where']['sign'] = 1;
			}
		}
		$data['page_index'] = isset($data['page_index'])?intval($data['page_index']):1;
		$data['page_size']  = isset($data['page_size'])?intval($data['page_size']):10;
		if($this->key){
			$data['order']      = isset($data['order'])?$data['order']:array($this->key=>'desc');
		}else{
			$data['order']      = isset($data['order'])?$data['order']:null;
		}
		$obj  = M($this->_module_name);
		if(isset($data['page_index']))
			$page_index = $data['page_index'];
		else
			$page_index = 1;
			
		if(0>= $page_index)
		{
			$page_index = 1;
		}
		
		if(isset($data['page_size']))
			$page_size  = $data['page_size'];
		else
			$page_size  = 10;
			
		if(30<= $page_size)
		{
			$page_size = 30;
		}
		//$page_index = 1;
		//$page_size  = 10;
		if(isset($data['group']))
		{
				$list = $obj->distinct(true)->field($data['fields'])
				    ->page($page_index, $page_size)
					->group($data['group'])
		            ->where($data['where'])
		            ->order($data['order'])
		            ->select();
		}
		else if(isset($data['order']))
		{
			$list = $obj->page($page_index, $page_size)
				->where($data['where'])
				->order($data['order'])
				->select();
		}
		else{
			$list = $obj->page($page_index, $page_size)
				->where($data['where'])
				->select();
		}
		            
		//echo M()->getlastSql();
		//die;
		#
		$record_count = 0;
		$record_count = $obj->where($data['where'])
			                     ->count();
	    /*
		if(isset($data['group'])){
			$record_count = $obj->group($data['group'])
			                     ->where($data['where'])
			                     ->count();	
		}
		else{
			$record_count = $obj->where($data['where'])
			                     ->count();		
		}
		*/
		
		//echo M()->getlastSql();
		return array(
					$list, 
					$record_count
		);
	}

	
	#通过id查询
	public function get_list_by_mul_ids($content)
	{
		$data  = $this->fill($content);		
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
		$old_id = 0;
		#原来受理人bug修改
		if('Bug' == $this->_module_name)
		{
			if(isset($data['where']['id'])
			&& isset($data['data']['get_member']))
			{
				$tmp_one = M('Bug')->field('get_member')->find($data['where']['id']);
				if($tmp_one)
				    $old_id = $tmp_one['get_member'];
					#$this->_mosquitto_push($tmp_one['get_member']);
			}
		}
		if(false !== $obj->where($data['where'])->save($data['data']))
		{
			#查询是否是bug修改
			if('Bug' == $this->_module_name)
			{
				if(isset($data['where']['id'])
				&& isset($data['data']['get_member']))
				{
					$this->_mosquitto_push($data['data']['get_member']);
					if(0 < $old_id)
						$this->_mosquitto_push($old_id);
				}
			}
			
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
		if(false !== $obj->where($data)->delete())
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
	public function get_real_ip($content=''){
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
	
	//生成safekey
	public function mk_passwd($params,$seg_index=0)
	{
		$seg_list = array(
			"<nickname>|<mobile>|<validated>|<pswd>|<userip>|souhei975427",               #(0)注册
			"<mobile>|<smscode>|<new_pswd>|<userip>|souhei975427",                        #(1)忘记密码
			"<uid>|<userip>|<yyyyMMdd>|souhei975427",                                     #(2)更新用户信息
			"<uid>|<yyyyMMdd>|souhei975427",                                              #(3)查询用户信息
			"<loginname>|<logintype>|<yyyyMMdd>|souhei975427",                            #(4)检查手机号码|用户名|手机号|邮箱
			                                                                              #|QQ号|微信OpenId|QQOpenId|微博OpenId
			"<mobile>|<imagecode>|souhei975427",                                          #(5)获取手机短信验证码
			"<uid>|<state>|souhei975427",                                                 #(6)更新用户状态
			"<uid>|<blackip>|souhei975427",                                               #(7)更新用户登录IP黑名单
			"<yyyyMMdd>|souhei975427",                                                    #(8)统计用户信息
			"<nickname>|<openid>|<userip>|souhei975427",                                  #(9)威信注册
			"<ui_id>|<openid>|<userip>|souhei975427",                                     #(10)绑定微信
			"<ui_id>|<logintype>|<loginname>|souhei975427",					              #(11)取消绑定
		);
		$str = $seg_list[$seg_index];
		switch($seg_index)
		{
			case 0:
				{
					$str = str_replace("<nickname>",  $params['nickname'], $str);
					$str = str_replace("<mobile>",    $params['mobile']  , $str);
					$str = str_replace("<validated>", $params['validated'], $str);
					$str = str_replace("<pswd>",      $params['pswd'], $str);
					$str = str_replace("<userip>",    $params['userip'], $str);
				}
			break;
			case 1:
				{
					$str = str_replace("<mobile>",    $params['mobile']  , $str);
					$str = str_replace("<smscode>",   $params['smscode']  ,$str);
					$str = str_replace("<new_pswd>",  $params['new_pswd'], $str);
					$str = str_replace("<userip>",    $params['userip'],   $str);
				}
			break;
			case 2:
				{
					$str = str_replace("<uid>",       $params['uid']  ,    $str);
					$str = str_replace("<userip>",    $params['userip'],   $str);
					$str = str_replace("<yyyyMMdd>",  $params['yyyyMMdd'], $str);
				}
			break;
			case 3:
				{
					$str = str_replace("<uid>",       $params['uid']  ,    $str);					
					$str = str_replace("<yyyyMMdd>",  $params['yyyyMMdd'], $str);
				}
			break;
			case 4:
				{
					$str = str_replace("<loginname>",    $params['loginname']  ,    $str);	
					$str = str_replace("<logintype>",    $params['logintype']  ,    $str);	
					$str = str_replace("<yyyyMMdd>",     $params['yyyyMMdd']   ,    $str);
				}
			break;
			case 5:
				{
					$str = str_replace("<mobile>",    $params['mobile']  ,    $str);
					$str = str_replace("<imagecode>", $params['imagecode'],   $str);
				}
			break;
			case 6:
				{
					$str = str_replace("<uid>",       $params['uid']  ,      $str);
					$str = str_replace("<state>",     $params['state']  ,    $str);
				}
			break;
			case 7:
				{
					$str = str_replace("<uid>",       $params['uid']  ,       $str);
					$str = str_replace("<blackip>",   $params['blackip']  ,   $str);
				}
			break;
			case 8:
				{
					$str = str_replace("<yyyyMMdd>",       $params['yyyyMMdd']  ,       $str);
				}
			break;
			case 9:
				{
					$str = str_replace("<nickname>", $params['nickname'], $str);
					$str = str_replace("<openid>", $params['openid'], $str);
					$str = str_replace("<userip>", $params['userip'], $str);
				}
			break;
			case 10:
				{
					$str = str_replace("<ui_id>", $params["ui_id"], $str);
					$str = str_replace("<openid>", $params["openid"], $str);
					$str = str_replace("<userip>", $params["userip"], $str);
				}
			break;
			case 11:
				{
					$str = str_replace("<ui_id>",     $params["ui_id"], $str);
					$str = str_replace("<logintype>", $params["logintype"], $str);
					$str = str_replace("<loginname>", $params["loginname"], $str);
				}
			break;
		}
		//var_dump($str);
		$re_str = $this->md5_16($str, true);		
		return $re_str;
	}
	
	public function md5_16($str){
              return substr(md5($str),8,16);
	}
	
	//动态生成昵称
	public function make_nickname($len=6)
	{
		/*
		$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZ
			abcdefghijklmnopqrstuvwxyz0123456789-@#~'; 
		// characters to build the password from 
		mt_srand((double)microtime()*1000000*getmypid()); 
		// seed the random number generater (must be done) 
		$password=''; 
		while(strlen($password)<$len) 
			$password.=substr($chars,(mt_rand()%strlen($chars)),1); 
		return $password; 
		*/
		$randStr = str_shuffle('1234567890');
		$rand = substr($randStr,0,6);
		return 'SOUHEI_'.$rand;
	}
	
	public function post($url, $params = false, $header = array()){
		//$cookie_file = tempnam(dirname(__FILE__),'cookie');
		$cookie_file = __PUBLIC__.'cookies.tmp';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_POST, 1); 
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60); 
		//curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file); 
		//curl_setopt($ch, CURLOPT_COOKIEFILE,$cookieFile); 
		curl_setopt($ch, CURLOPT_COOKIEFILE,$cookie_file); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE); 
		curl_setopt($ch, CURLOPT_HTTPGET, true); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 30); 
		if($params !== false){
		 	curl_setopt($ch, CURLOPT_POSTFIELDS , $params);
		} 
		curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 5.1; rv:21.0) Gecko/20100101 Firefox/21.0'); 
		curl_setopt($ch, CURLOPT_URL,$url); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
		$result = curl_exec($ch); 
		curl_close($ch); 
		 
		return $result; 
	}
	
	
	//通过id获取图片地址
	protected function get_pic_url($id)
	{
		if(0 == $id) return '';
		$pc_cache_pic_info = S('pc_cache_pic_info');
		$app_cache_pic_info = S('app_cache_pic_info');
		if(!empty($pc_cache_pic_info))
		{
			if(isset($pc_cache_pic_info[$id])
			&& !is_mobile_request()
			)
			{	
				return C('media_url_pre').$pc_cache_pic_info[$id];
			}
			elseif(isset($app_cache_pic_info[$id]))
			{
				return C('media_url_pre').$app_cache_pic_info[$id];
			}
		}
		$pic_info = M('Media')->find($id);
		if($pic_info)
		{
			//判定是手机来源
            if(is_mobile_request())
            {
				//判定新闻
            	if('001006' == $pic_info['dict_sn'])
            	{					
					//$pic_info['media_url']= 'media/news_pc/2015-02-07/1423303605_1282.jpg';
					$pic_pc = __PUBLIC__.$pic_info['media_url'];//目标app路径
                    $type = substr($pic_info['media_url'],-4);
                    $pic_app = str_replace($type,'',$pic_info['media_url']).'_app'.$type;
					if(!file_exists(__PUBLIC__.$pic_app))
					{						
						//$aa=getimagesize($pic_pc);
						//var_dump($aa);
						//$width = $aa[0];
						//$height = $aa[1];
						//$width = $width *0.5;
						//$height = $height * 0.5;
						//$width = intval($width);
						//$height = intval($height);
						//$width = intval($width);
						//$height = intval($height);
						$width = 160;
						$height = 120;
						//var_dump($width);
						//var_dump($height);
						//生成手机缩略图
                        $res = img2thumb($pic_pc, __PUBLIC__.$pic_app, $width, $height);
                        if($res)
                        {
							$app_cache_pic_info[$id] = $pic_app;
							S('app_cache_pic_info', $app_cache_pic_info);
							return C('media_url_pre').$pic_app;
						}
					}	
					else{
						$app_cache_pic_info[$id] = $pic_app;
						S('app_cache_pic_info', $app_cache_pic_info);
						return C('media_url_pre').$pic_app;
					}
				}
			}
			$pc_cache_pic_info[$id] = $pic_info['media_url'];
			S('pc_cache_pic_info', $pc_cache_pic_info);
			return C('media_url_pre').$pic_info['media_url'];
		}
	}
	
	#检查是否允许操作
	protected function __check($content)
	{
		$star_time = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
		$end_time  = mktime(23, 59, 59, date('m'), date('d'), date('Y'));
		$content['add_time'] = array(array('gt', $star_time),
		                             array('lt', $end_time));
		if(M($this->_module_name)->where($content)->find())
		{
			return false;
		}
		
		return true;
	}
	
	#检查是否允许操作，是否存在
	protected function __check_exists($content)
	{	                          
		if(M($this->_module_name)->where($content)->find())
		{
			return false;
		}
		return true;
	}
	
	#顶
	public function __top($content, $field_name)
	/*
	@@input
	@param $content    条件
	@param $field_name 目标字段名称
	@@output
	@param true-成功, false-失败
	*/
	{	
		if(M($this->_module_name)->where($content)->setInc($field_name, 1))
		{
			return true;
		}
		
		return false;
	}
	
	#赞
	public function __assist($content, $field_name)
	{
		return $this->__top($content, $field_name);
	}
	
	#降
	public function __down($content, $field_name)
	/*
	@@input
	@param $content    条件
	@param $field_name 目标字段名称
	@@output
	@param true-成功, false-失败
	*/
	{
		if(M($this->_module_name)->where($content)->setDec($field_name, 1))
		{
			return true;
		}
		
		return false;
	}
	
	#审核
	public function validate($content)
	{
		$data = $this->fill($content);
		unset($content);
		if(!isset($data['id']))
		{
			return C('param_err');
		}
		
		$data['id'] = intval($data['id']);
		
		if(0>= $data['id'])
		{
			return C('param_fmt_err');
		}
		
		$content = array(
			'id'=>$data['id']
		);
		unset($data);
		$data = array(
			'is_validate'=>1,
			'validate_time'=>time(),
		);
		if(M($this->_module_name)->where($content)->save($data))
		{
				//return true;
				return array(
					200,
					array(
						'is_success'=>0,
						'message'=>C('option_ok'),
					)
				);
		}
		
		//return false;
		return array(
					200,
					array(
						'is_success'=>-1,
						'message'=>C('option_fail'),
					)
				);
	}
	
	#判定是否存在
	protected function __exists($field_name, $field_value)
	{
		$content = array(
			$field_name  => $field_value,
		);
		
		$tmp_one = M($this->_module_name)->where($content)->find();
		if($tmp_one)
		{
			return true;
		}
		
		return false;
	}
	
	#修改判定存在
	protected function __exists_ex($field_name, $field_value, $field_key_val)
	{
		$content[$field_name] =  $field_value;
		$content['id']        = array('neq', $field_key_val);
		$tmp_one = M($this->_module_name)->where($content)->find();
		if($tmp_one)
		{
			return true;
		}
		
		return false;
	}
	
	#通过用户id获取昵称
	protected function _get_nickname($user_id)
	{
		if(0>= $user_id)
			return '';
		$content = array(
			'uid'=>$user_id
		);
		$nickname = '';
		
		#检查本地nickname是否存在		
		$tmp_param = array(
			'user_id'=>$user_id
		);
		list(,$tmp_info) = A('Soapi/Usernickname')->get_nickname_by_id(json_encode($tmp_param));
		unset($tmp_param);
		if($tmp_info)
		{
				$nickname = $tmp_info['nickname'];
		}
		else
		{
			#不存在查询远程数据
			list(,$list) = A('Soapi/User')->get_info(json_encode($content));
			if(isset($list[0]['UI_NickName']))
			{
				$nickname = $list[0]['UI_NickName'];
				$tmp_param = array(
					'user_id'  =>$user_id,
					'nickname' =>$nickname
				);	
				A('Soapi/Usernickname')->add(json_encode($tmp_param));
				unset($tmp_param);
			}
		}
		
		return $nickname;
	}
	
	#最大值
	public function __get_Max($field_name, $where)
	{
		$list = array();
		$max_val = 0;
		$max_val = M($this->_module_name)->where($where)->max($field_name);
		$where[$field_name] = $max_val;
		$list = M($this->_module_name)->where($where)->find();
		return $list;
	}
		
	#查询主评论
	protected function get_parent_content($id)
	/*
	@@input
	@id
	@@output
	@content
	*/
	{
		if(0 >= $id) return '';
		
		$content = '';
		$content = M($this->_module_name)->field('content')
		                                 ->find($id);
		return $content['content'];
	}
	
	#从网络下载图片
	public function down_net_pic($content)
	/*
	@@input
	@param $net_pic_url
	@@output
	@param $local_pic_url
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['net_pic_url']))
		{
			return C('param_err');
		}
		
		$data['net_pic_url'] = htmlspecialchars(trim($data['net_pic_url']));
		
		if('' == $data['net_pic_url'])
		{
			return C('param_fmt_err');
		}
		
		$pic_path = __PUBLIC__."/tmp/tmp.jpg";
		if(file_exists($pic_path))
		{
			@unlink($pic_path); 
		}
		
		
		if(@copy($data['net_pic_url'], $pic_path))
		{
				return array(
						200,
						array(
							'is_success'=>0,
							'message'=>C('option_ok'),
							'local_pic_url'=>$pic_path
						),
				);
		}
		
		return array(
			200,
			array(
				'is_success'=>-1,
				'message'=>C('option_fail'),
			),
		);
	}
	
	
	public function _amount()
	{
		$record_count = 0;

		$record_count = M($this->_module_name)->count();
		return array(
				200,
				array(
					'record_count'=>$record_count
				),
		);
	}
	
	
	
	public function __debug($str)
	{
		#file_put_contents(__PUBLIC__.'log/debug_'.date('Y-m-d').'.log', $str, FILE_APPEND);
	}
	
	
	public function _mosquitto_push($admin_id)
	{
		$admin_name = '';
		$message = 0;
		
		#查询用户名称
		$tmp_one = M('Admin')->field('admin_name')->find($admin_id);
		if($tmp_one)
			$admin_name = $tmp_one['admin_name'];
			
		
		$result = A('Bugapi/bug')->get_self_bug(json_encode(array('admin_id'=>$admin_id)));
		
		if(200 == $result[0] #严重bug
		&& 0 == $result[1]['is_success'])
		{
			$message = 1;
		}
		elseif(200 == $result[0]
		&& 1 == $result[1]['is_success'])
		{
			$message = 2;
		}
		
		$topic_prefix = 'bug/';
		if(1 == C('IS_DEBUG'))
		{
			$topic_prefix = 'debug_bug/';
		}

		#推送
		$this->post(C('mosquitto_server_url'),
					array(
						'target'=>$topic_prefix.$admin_name,
						'message'=>$message
					));
		
	}

	public function rtx_push($rtx_nickname, $rtx_no, $message_title, $message_content)
	{
		$time = 0;
	    $send_to = $rtx_nickname;
		$title = $message_title;
		$body = $message_content;

		if($this->send_msg($send_to, $title, $time, $body))
			return true;

		return false;	
	}

	private function send_msg($send_to, $title, $time, $body)
	{
		$RootObj = new COM("RTXSAPIRootObj.RTXSAPIRootObj");
		$RootObj->ServerIP = C('RTX_SERVER');
		$RootObj->ServerPort = C('RTX_PORT');
		try{
			$RootObj->SendNotify($send_to, $title, $time, $body);
		}
		catch(Exception $e)
		{
			return false;
		}
		return true;
	}

	function curl_file_get_contents($durl){
	  $ch = curl_init();
	  curl_setopt($ch, CURLOPT_URL, $durl);
	  curl_setopt($ch, CURLOPT_TIMEOUT, 5);
	  curl_setopt($ch, CURLOPT_USERAGENT, _USERAGENT_);
	  curl_setopt($ch, CURLOPT_REFERER,_REFERER_);
	  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	  $r = curl_exec($ch);
	  curl_close($ch);
	   return $r;
	}
}
