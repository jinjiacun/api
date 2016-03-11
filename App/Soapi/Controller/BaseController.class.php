<?php
namespace Soapi\Controller;
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
------------------------------------------------------------
##--------------------------------------------------------##
public function filter_sensitive 过滤敏感词
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
		$data['order']      = isset($data['order'])?$data['order']:array('id'=>'desc');
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
				$list = $obj->page($page_index, $page_size)
					->group($data['group'])
		            ->where($data['where'])
		            ->order($data['order'])
		            ->select();
		}
		else
		{
			$list = $obj->page($page_index, $page_size)
				->where($data['where'])
				->order($data['order'])
				->select();
		}           
		            
		//echo M()->getlastSql();
		//die;
		#
		$record_count = 0;
		$record_count = $obj->where($data['where'])->count();
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
		if(false !== $obj->where($data['where'])->save($data['data']))
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
		curl_setopt($ch, CURLOPT_TIMEOUT, 5); 
		if($params !== false){
		 	curl_setopt($ch, CURLOPT_POSTFIELDS , $params);
		} 
		curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 5.1; rv:21.0) Gecko/20100101 Firefox/21.0'); 
		curl_setopt($ch, CURLOPT_URL,$url); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
		$result = curl_exec($ch); 
		$curl_errno = curl_errno($ch); 
        $curl_error = curl_error($ch);
		curl_close($ch); 
		if($curl_errno >0){ 
                echo "cURL Error ($curl_errno): $curl_error\n"; 
                $result = array();
        }
        /*
        else{ 
                echo "Data received: $data\n"; 
        }*/  


		return $result; 
	}
	
	public function get($mobile='', $uname='', $my_url='', $preurl='', $agent='', $screen='', $remark='')
	{
		$url = C('resource_url');
		$url .= 'mobile='.$mobile
		        .'&uname='.$uname
		        .'&url='.urlencode($my_url)
		        .'&preurl='.urlencode($preurl)
		        .'&agent='.urlencode($agent)
		        .'&screen='.$screen
		        .'&remark='.$remark;
		#$url = sprintf($url,$mobile, $uname, $url, $preurl, $agent, $screen, $remark);
		#print_r($url);
		$ch = curl_init($url) ;  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回  
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回  
		$output = curl_exec($ch) ;  
			
		return $output;  
			/* 写入文件 */  
			/*
			$fh = fopen("out.html", 'w') ;  
			fwrite($fh, $output) ;  
			fclose($fh) ;   
			*/
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
		f(in_array($this->get_real_ip(),array('192.168.1.113','192.168.1.65')))
		file_put_contents(__PUBLIC__.'log/debug_'.date('Y-m-d').'.log', $str, FILE_APPEND);
	}
	
	#过滤敏感词
	/*
	$hei=array(
		'中国',
		'日本'
		);
		$blacklist="/".implode("|",$hei)."/i";
		$str="中国一是一个很好的国家";
		if(preg_match($blacklist, $str, $matches)){
		    print "found:". $matches[0];
		  } else {
		    print "not found.";
		  }
	*/
	public function filter_sensitive ($source_word)
	{
		$is_ok = false;
		$badword = array (
  		0 => '阿扁推翻', 1 => '阿宾',2 => '阿賓',  3 => '挨了一炮',  4 => '爱液横流',  5 => '安街逆',  6 => '安局办公楼',
  		7 => '安局豪华',  8 => '安门事',  9 => '安眠藥',  10 => '案的准确',  11 => '八九民',  12 => '八九学',  13 => '八九政治',
  		14 => '把病人整',  15 => '把邓小平',  16 => '把学生整',  17 => '罢工门',  18 => '白黄牙签',  19 => '败培训',  20 => '办本科',
  		21 => '办理本科',  22 => '办理各种',  23 => '办理票据',  24 => '办理文凭',  25 => '办理真实',  26 => '办理证书',  27 => '办理资格',
  		28 => '办文凭',  29 => '办怔',  30 => '办证',  31 => '半刺刀',  32 => '辦毕业',  33 => '辦證',  34 => '谤罪获刑',  35 => '磅解码器',
  		36 => '磅遥控器',  37 => '宝在甘肃修',  38 => '保过答案',  39 => '报复执法',  40 => '爆发骚',  41 => '北省委门',  42 => '被打死',
  		43 => '被指抄袭',  44 => '被中共',  45 => '本公司担',  46 => '本无码',  47 => '毕业證',  48 => '变牌绝',  49 => '辩词与梦',
  		50 => '冰毒',  51 => '冰火毒',  52 => '冰火佳',  53 => '冰火九重',  54 => '冰火漫',  55 => '冰淫传',  56 => '冰在火上',  57 => '波推龙',
  		58 => '博彩娱',  59 => '博会暂停',  60 => '博园区伪',  61 => '不查都',  62 => '不查全',  63 => '不思四化',  64 => '布卖淫女',
  		65 => '部忙组阁',  66 => '部是这样',  67 => '才知道只生',  68 => '财众科技',  69 => '采花堂',  70 => '踩踏事',  71 => '苍山兰', 
  		72 => '苍蝇水',  73 => '藏春阁',  74 => '藏獨',  75 => '操了嫂',  76 => '操嫂子',  77 => '策没有不',  78 => '插屁屁',  79 => '察象蚂',
  		80 => '拆迁灭',  81 => '车牌隐',  82 => '成人电',  83 => '成人卡通',  84 => '成人聊',  85 => '成人片',  86 => '成人视',  87 => '成人图',
  		88 => '成人文',  89 => '成人小',  90 => '城管灭',  91 => '惩公安',  92 => '惩贪难',  93 => '充气娃',  94 => '冲凉死',  95 => '抽着大中',
  		96 => '抽着芙蓉',  97 => '出成绩付',  98 => '出售发票',  99 => '出售军',  100 => '穿透仪器',  101 => '春水横溢',  102 => '纯度白',
  		103 => '纯度黄',  104 => '次通过考',  105 => '催眠水',  106 => '催情粉',  107 => '催情药',  108 => '催情藥',  109 => '挫仑',
  		110 => '达毕业证',  111 => '答案包',  112 => '答案提供',  113 => '打标语',  114 => '打错门',  115 => '打飞机专',  116 => '打死经过',
  		117 => '打死人',  118 => '打砸办公',  119 => '大鸡巴',  120 => '大雞巴',  121 => '大纪元',  122 => '大揭露',  123 => '大奶子',
  		124 => '大批贪官',  125 => '大肉棒',  126 => '大嘴歌',  127 => '代办发票',  128 => '代办各',  129 => '代办文',  130 => '代办学',
  		131 => '代办制',  132 => '代辦',  133 => '代表烦',  134 => '代開',  135 => '代考',  136 => '代理发票',  137 => '代理票据',
  		138 => '代您考',  139 => '代您考',  140 => '代写毕',  141 => '代写论',  142 => '代孕',  143 => '贷办',  144 => '贷借款',
  		145 => '贷开',  146 => '戴海静',  147 => '当代七整',  148 => '当官要精',  149 => '当官在于',  150 => '党的官',  151 => '党后萎',
  		152 => '党前干劲',  153 => '刀架保安',  154 => '导的情人',  155 => '导叫失',  156 => '导人的最',  157 => '导人最',  158 => '导小商',
  		159 => '到花心',  160 => '得财兼',  161 => '的同修',  162 => '灯草和',  163 => '等级證',  164 => '等屁民',  165 => '等人老百',
  		166 => '等人是老',  167 => '等人手术',  168 => '邓爷爷转',  169 => '邓玉娇',  170 => '地产之歌',  171 => '地下先烈',  
  		172 => '地震哥',  173 => '帝国之梦',  174 => '递纸死',  175 => '点数优惠',  176 => '电狗',  177 => '电话监',  178 => '电鸡',
  		  179 => '甸果敢',  180 => '蝶舞按',  181 => '丁香社',  182 => '丁子霖',  183 => '顶花心',  184 => '东北独立',  185 => '东复活',
  		    186 => '东京热',  187 => '東京熱',  188 => '洞小口紧',  189 => '都当警',  190 => '都当小姐',  191 => '都进中央',  192 => '毒蛇钻',
  		193 => '独立台湾',  194 => '赌球网',  195 => '短信截',  196 => '对日强硬',  197 => '多美康',  198 => '躲猫猫',  199 => '俄羅斯',
  		200 => '恶势力操',  201 => '恶势力插',  202 => '恩氟烷',  203 => '儿园惨',  204 => '儿园砍',  205 => '儿园杀',  206 => '儿园凶',
  		207 => '二奶大',  208 => '发牌绝',  209 => '发票出',  210 => '发票代',  211 => '发票销',  212 => '發票',  213 => '法车仑',
  		 214 => '法伦功',  215 => '法轮',  216 => '法轮佛',  217 => '法维权',  218 => '法一轮',  219 => '法院给废',  220 => '法正乾',
  		 221 => '反测速雷',  222 => '反雷达测',  223 => '反屏蔽',  224 => '范燕琼',  225 => '方迷香',  226 => '防电子眼',  227 => '防身药水',
  		 228 => '房贷给废',  229 => '仿真枪',  230 => '仿真证',  231 => '诽谤罪',  232 => '费私服',  233 => '封锁消',  234 => '佛同修',
  		 235 => '夫妻交换',  236 => '福尔马林',  237 => '福娃的預',  238 => '福娃頭上',  239 => '福香巴',  240 => '府包庇',  241 => '府集中领',
  		 242 => '妇销魂',  243 => '附送枪',  244 => '复印件生',  245 => '复印件制',  246 => '富民穷',  247 => '富婆给废',  248 => '改号软件',
  		 249 => '感扑克',  250 => '冈本真',  251 => '肛交',  252 => '肛门是邻',  253 => '岡本真',  254 => '钢针狗',  255 => '钢珠枪',  256 => '港澳博球',
  		 257 => '港馬會',  258 => '港鑫華',  259 => '高就在政',  260 => '高考黑',  261 => '高莺莺',  262 => '搞媛交',  263 => '告长期',  264 => '告洋状',
  265 => '格证考试',
  266 => '各类考试',
  267 => '各类文凭',
  268 => '跟踪器',
  269 => '工程吞得',
  270 => '工力人',
  271 => '公安错打',
  272 => '公安网监',
  273 => '公开小姐',
  274 => '攻官小姐',
  275 => '共狗',
  276 => '共王储',
  277 => '狗粮',
  278 => '狗屁专家',
  279 => '鼓动一些',
  280 => '乖乖粉',
  281 => '官商勾',
  282 => '官也不容',
  283 => '官因发帖',
  284 => '光学真题',
  285 => '跪真相',
  286 => '滚圆大乳',
  287 => '国际投注',
  288 => '国家妓',
  289 => '国家软弱',
  290 => '国家吞得',
  291 => '国库折',
  292 => '国一九五七',
  293 => '國內美',
  294 => '哈药直销',
  295 => '海访民',
  296 => '豪圈钱',
  297 => '号屏蔽器',
  298 => '和狗交',
  299 => '和狗性',
  300 => '和狗做',
  301 => '黑火药的',
  302 => '红色恐怖',
  303 => '红外透视',
  304 => '紅色恐',
  305 => '胡江内斗',
  306 => '胡紧套',
  307 => '胡錦\濤',
  308 => '胡适眼',
  309 => '胡耀邦',
  310 => '湖淫娘',
  311 => '虎头猎',
  312 => '华国锋',
  313 => '华门开',
  314 => '化学扫盲',
  315 => '划老公',
  316 => '还会吹萧',
  317 => '还看锦涛',
  318 => '环球证件',
  319 => '换妻',
  320 => '皇冠投注',
  321 => '黄冰',
  322 => '浑圆豪乳',
  323 => '活不起',
  324 => '火车也疯',
  325 => '机定位器',
  326 => '机号定',
  327 => '机号卫',
  328 => '机卡密',
  329 => '机屏蔽器',
  330 => '基本靠吼',
  331 => '绩过后付',
  332 => '激情电',
  333 => '激情短',
  334 => '激情妹',
  335 => '激情炮',
  336 => '级办理',
  337 => '级答案',
  338 => '急需嫖',
  339 => '集体打砸',
  340 => '集体腐',
  341 => '挤乳汁',
  342 => '擠乳汁',
  343 => '佳静安定',
  344 => '家一样饱',
  345 => '家属被打',
  346 => '甲虫跳',
  347 => '甲流了',
  348 => '奸成瘾',
  349 => '兼职上门',
  350 => '监听器',
  351 => '监听王',
  352 => '简易炸',
  353 => '江胡内斗',
  354 => '江太上',
  355 => '江系人',
  356 => '江贼民',
  357 => '疆獨',
  358 => '蒋彦永',
  359 => '叫自慰',
  360 => '揭贪难',
  361 => '姐包夜',
  362 => '姐服务',
  363 => '姐兼职',
  364 => '姐上门',
  365 => '金扎金',
  366 => '金钟气',
  367 => '津大地震',
  368 => '津地震',
  369 => '进来的罪',
  370 => '京地震',
  371 => '京要地震',
  372 => '经典谎言',
  373 => '精子射在',
  374 => '警察被',
  375 => '警察的幌',
  376 => '警察殴打',
  377 => '警察说保',
  378 => '警车雷达',
  379 => '警方包庇',
  380 => '警用品',
  381 => '径步枪',
  382 => '敬请忍',
  383 => '究生答案',
  384 => '九龙论坛',
  385 => '九评共',
  386 => '酒象喝汤',
  387 => '酒像喝汤',
  388 => '就爱插',
  389 => '就要色',
  390 => '举国体',
  391 => '巨乳',
  392 => '据说全民',
  393 => '绝食声',
  394 => '军长发威',
  395 => '军刺',
  396 => '军品特',
  397 => '军用手',
  398 => '开邓选',
  399 => '开锁工具',
  400 => '開碼',
  401 => '開票',
  402 => '砍杀幼',
  403 => '砍伤儿',
  404 => '康没有不',
  405 => '康跳楼',
  406 => '考答案',
  407 => '考后付款',
  408 => '考机构',
  409 => '考考邓',
  410 => '考联盟',
  411 => '考前答',
  412 => '考前答案',
  413 => '考前付',
  414 => '考设备',
  415 => '考试包过',
  416 => '考试保',
  417 => '考试答案',
  418 => '考试机构',
  419 => '考试联盟',
  420 => '考试枪',
  421 => '考研考中',
  422 => '考中答案',
  423 => '磕彰',
  424 => '克分析',
  425 => '克千术',
  426 => '克透视',
  427 => '空和雅典',
  428 => '孔摄像',
  429 => '控诉世博',
  430 => '控制媒',
  431 => '口手枪',
  432 => '骷髅死',
  433 => '快速办',
  434 => '矿难不公',
  435 => '拉登说',
  436 => '拉开水晶',
  437 => '来福猎',
  438 => '拦截器',
  439 => '狼全部跪',
  440 => '浪穴',
  441 => '老虎机',
  442 => '雷人女官',
  443 => '类准确答',
  444 => '黎阳平',
  445 => '李洪志',
  446 => '李咏曰',
  447 => '理各种证',
  448 => '理是影帝',
  449 => '理证件',
  450 => '理做帐报',
  451 => '力骗中央',
  452 => '力月西',
  453 => '丽媛离',
  454 => '利他林',
  455 => '连发手',
  456 => '聯繫電',
  457 => '炼大法',
  458 => '两岸才子',
  459 => '两会代',
  460 => '两会又三',
  461 => '聊视频',
  462 => '聊斋艳',
  463 => '了件渔袍',
  464 => '猎好帮手',
  465 => '猎枪销',
  466 => '猎槍',
  467 => '獵槍',
  468 => '领土拿',
  469 => '流血事',
  470 => '六合彩',
  471 => '六死',
  472 => '六四事',
  473 => '六月联盟',
  474 => '龙湾事件',
  475 => '隆手指',
  476 => '陆封锁',
  477 => '陆同修',
  478 => '氯胺酮',
  479 => '乱奸',
  480 => '乱伦类',
  481 => '乱伦小',
  482 => '亂倫',
  483 => '伦理大',
  484 => '伦理电影',
  485 => '伦理毛',
  486 => '伦理片',
  487 => '轮功',
  488 => '轮手枪',
  489 => '论文代',
  490 => '罗斯小姐',
  491 => '裸聊网',
  492 => '裸舞视',
  493 => '落霞缀',
  494 => '麻古',
  495 => '麻果配',
  496 => '麻果丸',
  497 => '麻将透',
  498 => '麻醉狗',
  499 => '麻醉枪',
  500 => '麻醉槍',
  501 => '麻醉藥',
  502 => '蟆叫专家',
  503 => '卖地财政',
  504 => '卖发票',  
  505 => '卖银行卡',
  506 => '卖自考',
  507 => '漫步丝',
  508 => '忙爱国',
  509 => '猫眼工具',
  510 => '毛一鲜',
  511 => '媒体封锁',
  512 => '每周一死',
  513 => '美艳少妇',
  514 => '妹按摩',
  515 => '妹上门',
  516 => '门按摩',
  517 => '门保健',
  518 => '門服務',
  519 => '氓培训',
  520 => '蒙汗药',
  521 => '迷幻型',
  522 => '迷幻药',
  523 => '迷幻藥',
  524 => '迷昏口',
  525 => '迷昏药',
  526 => '迷昏藥',
  527 => '迷魂香',
  528 => '迷魂药',
  529 => '迷魂藥',
  530 => '迷奸药',
  531 => '迷情水',
  532 => '迷情药',
  533 => '迷藥',
  534 => '谜奸药',
  535 => '蜜穴',
  536 => '灭绝罪',
  537 => '民储害',
  538 => '民九亿商',
  539 => '民抗议',
  540 => '明慧网',
  541 => '铭记印尼',
  542 => '摩小姐',
  543 => '母乳家',
  544 => '木齐针',
  545 => '幕没有不',
  546 => '幕前戲',
  547 => '内射',
  548 => '南充针',
  549 => '嫩穴',
  550 => '嫩阴',
  551 => '泥马之歌',
  552 => '你的西域',
  553 => '拟涛哥',
  554 => '娘两腿之间',
  555 => '妞上门',
  556 => '浓精',
  557 => '怒的志愿',
  558 => '女被人家搞',
  559 => '女激情',
  560 => '女技师',
  561 => '女人和狗',
  562 => '女任职名',
  563 => '女上门',  
  564 => '女優',
  565 => '鸥之歌',
  566 => '拍肩神药',
  567 => '
拍肩型',
  568 => '
牌分析',
  569 => '
牌技网',
  570 => '
炮的小蜜',
  571 => '
陪考枪',
  572 => '
配有消',
  573 => '
喷尿',
  574 => '
嫖俄罗',
  575 => '
嫖鸡',
  576 => '
平惨案',
  577 => '
平叫到床',
  578 => '
仆不怕饮',
  579 => '
普通嘌',
  580 => '
期货配',
  581 => '
奇迹的黄',
  582 => '
奇淫散',
  583 => '
骑单车出',
  584 => '
气狗',
  585 => '
气枪',
  586 => '
汽狗',
  587 => '
汽枪',
  588 => '
氣槍',
  589 => '
铅弹',
  590 => '
钱三字经',
  591 => '
枪出售',
  592 => '
枪的参',
  593 => '
枪的分',
  594 => '
枪的结',
  595 => '
枪的制',
  596 => '
枪货到',
  597 => '
枪决女犯',
  598 => '
枪决现场',
  599 => '
枪模',
  600 => '
枪手队',
  601 => '
枪手网',
  602 => '
枪销售',
  603 => '
枪械制',
  604 => '
枪子弹',
  605 => '
强权政府',
  606 => '
强硬发言',
  607 => '
抢其火炬',
  608 => '
切听器',
  609 => '
窃听器',
  610 => '
禽流感了',
  611 => '
勤捞致',
  612 => '
氢弹手',
  613 => '
清除负面',
  614 => '
清純壆',
  615 => '
情聊天室',
  616 => '
情妹妹',
  617 => '
情视频',
  618 => '
情自拍',
  619 => '
氰化钾',
  620 => '
氰化钠',
  621 => '
请集会',
  622 => '
请示威',
  623 => '
请愿',
  624 => '
琼花问',
  625 => '
区的雷人',
  626 => '
娶韩国',
  627 => '
全真证',
  628 => '
群奸暴',
  629 => '
群起抗暴',
  630 => '
群体性事',
  631 => '
绕过封锁',
  632 => '
惹的国',
  633 => '
人权律',
  634 => '
人体艺',
  635 => '
人游行',
  636 => '
人在云上',
  637 => '
人真钱',
  638 => '
认牌绝',
  639 => '
任于斯国',
  640 => '
柔胸粉',
  641 => '
肉洞',
  642 => '
肉棍',
  643 => '
如厕死',
  644 => '
乳交',
  645 => '
软弱的国',
  646 => '
赛后骚',
  647 => '
三挫',
  648 => '
三级片',
  649 => '
三秒倒',
  650 => '
三网友',
  651 => '
三唑',
  652 => '
骚妇',
  653 => '
骚浪',
  654 => '
骚穴',
  655 => '
骚嘴',
  656 => '
扫了爷爷',
  657 => '
色电影',
  658 => '
色妹妹',
  659 => '
色视频',
  660 => '
色小说',
  661 => '
杀指南',
  662 => '
山涉黑',
  663 => '
煽动不明',
  664 => '
煽动群众',
  665 => '
上门激',
  666 => '
烧公安局',
  667 => '
烧瓶的',
  668 => '
韶关斗',
  669 => '
韶关玩',
  670 => '
韶关旭',
  671 => '
射网枪',
  672 => '
涉嫌抄袭',
  673 => '
深喉冰',
  674 => '
神七假',
  675 => '
神韵艺术',
  676 => '
生被砍',
  677 => '
生踩踏',
  678 => '
生肖中特',
  679 => '
圣战不息',
  680 => '
盛行在舞',
  681 => '
尸博',
  682 => '
失身水',
  683 => '
失意药',
  684 => '
狮子旗',
  685 => '
十八等',
  686 => '
十大谎',
  687 => '
十大禁',
  688 => '
十个预言',
  689 => '
十类人不',
  690 => '
十七大幕',
  691 => '
实毕业证',
  692 => '
实体娃',
  693 => '
实学历文',
  694 => '
士康事件',
  695 => '
式粉推',
  696 => '
视解密',
  697 => '
是躲猫',
  698 => '
手变牌',
  699 => '
手答案',
  700 => '
手狗',
  701 => '
手机跟',
  702 => '
手机监',
  703 => '
手机窃',
  704 => '
手机追',
  705 => '
手拉鸡',
  706 => '
手木仓',
  707 => '
手槍',
  708 => '
守所死法',
  709 => '
兽交',
  710 => '
售步枪',
  711 => '
售纯度',
  712 => '
售单管',
  713 => '
售弹簧刀',
  714 => '
售防身',
  715 => '
售狗子',
  716 => '
售虎头',
  717 => '
售火药',
  718 => '
售假币',
  719 => '
售健卫',
  720 => '
售军用',
  721 => '
售猎枪',
  722 => '
售氯胺',
  723 => '
售麻醉',
  724 => '
售冒名',
  725 => '
售枪支',
  726 => '
售热武',
  727 => '
售三棱',
  728 => '
售手枪',
  729 => '
售五四',
  730 => '
售信用',
  731 => '
售一元硬',
  732 => '
售子弹',
  733 => '
售左轮',
  734 => '
书办理',
  735 => '
熟妇',
  736 => '
术牌具',
  737 => '
双管立',
  738 => '
双管平',
  739 => '
水阎王',
  740 => '
丝护士',
  741 => '
丝情侣',
  742 => '
丝袜保',
  743 => '
丝袜恋',
  744 => '
丝袜美',
  745 => '
丝袜妹',
  746 => '
丝袜网',
  747 => '
丝足按',
  748 => '
司长期有',
  749 => '
司法黑',
  750 => '
私房写真',
  751 => '
死法分布',
  752 => '
死要见毛',
  753 => '
四博会',
  754 => '
四大扯|1',
  755 => '
四小码',
  756 => '
苏家屯集',
  757 => '
诉讼集团',
  758 => '
素女心',
  759 => '
速代办',
  760 => '
速取证',
  761 => '
酸羟亚胺',
  762 => '
蹋纳税',
  763 => '
太王四神',
  764 => '
泰兴幼',
  765 => '
泰兴镇中',
  766 => '
泰州幼',
  767 => '
贪官也辛',
  768 => '
探测狗',
  769 => '
涛共产',
  770 => '
涛一样胡',
  771 => '
特工资',
  772 => '
特码',
  773 => '
特上门',
  774 => '
体透视镜',
  775 => '
替考',
  776 => '
替人体',
  777 => '
天朝特',
  778 => '
天鹅之旅',
  779 => '
天推广歌',
  780 => '
田罢工',
  781 => '
田田桑',
  782 => '
田停工',
  783 => '
庭保养',
  784 => '
庭审直播',
  785 => '
通钢总经',
  786 => '
偷電器',
  787 => '
偷肃贪',
  788 => '
偷听器',
  789 => '
偷偷贪',
  790 => '
头双管',
  791 => '
透视功能',
  792 => '
透视镜',
  793 => '
透视扑',
  794 => '
透视器',
  795 => '
透视眼镜',
  796 => '
透视药',
  797 => '
透视仪',
  798 => '
秃鹰汽',
  799 => '
突破封锁',
  800 => '
突破网路',
  801 => '
推油按',
  802 => '
脱衣艳',
  803 => '
瓦斯手',
  804 => '
袜按摩',
  805 => '
外透视镜',
  806 => '
外围赌球',
  807 => '
湾版假',
  808 => '
万能钥匙',
  809 => '
万人骚动',
  810 => '
王立军',
  811 => '
王益案',
  812 => '
网民案',
  813 => '
网民获刑',
  814 => '
网民诬',
  815 => '
微型摄像',
  816 => '
围攻警',
  817 => '
围攻上海',
  818 => '
维汉员',
  819 => '
维权基',
  820 => '
维权人',
  821 => '
维权谈',
  822 => '
委坐船',
  823 => '
谓的和谐',
  824 => '
温家堡',
  825 => '
温切斯特',
  826 => '
温影帝',
  827 => '
溫家寶',
  828 => '
瘟加饱',
  829 => '
瘟假饱',
  830 => '
文凭证',
  831 => '
文强',
  832 => '
纹了毛',
  833 => '
闻被控制',
  834 => '
闻封锁',
  835 => '
瓮安',
  836 => '
我的西域',
  837 => '
我搞台独',
  838 => '
乌蝇水',
  839 => '
无耻语录',
  840 => '
无码专',
  841 => '
五套功',
  842 => '
五月天',
  843 => '
午夜电',
  844 => '
午夜极',
  845 => '
武警暴',
  846 => '
武警殴',
  847 => '
武警已增',
  848 => '
务员答案',
  849 => '
务员考试',
  850 => '
雾型迷',
  851 => '
西藏限',
  852 => '
西服进去',
  853 => '
希脏',
  854 => '
习进平',
  855 => '
习晋平',
  856 => '
席复活',
  857 => '
席临终前',
  858 => '
席指着护',
  859 => '
洗澡死',
  860 => '
喜贪赃',
  861 => '
先烈纷纷',
  862 => '
现大地震',
  863 => '
现金投注',
  864 => '
线透视镜',
  865 => '
限制言',
  866 => '
陷害案',
  867 => '
陷害罪',
  868 => '
相自首',
  869 => '
香港论坛',
  870 => '
香港马会',
  871 => '
香港一类',
  872 => '
香港总彩',
  873 => '
硝化甘',
  874 => '
小穴',
  875 => '
校骚乱',
  876 => '
协晃悠',
  877 => '
写两会',
  878 => '
泄漏的内',
  879 => '
新建户',
  880 => '
新疆叛',
  881 => '
新疆限',
  882 => '
新金瓶',
  883 => '
新唐人',
  884 => '
信访专班',
  885 => '
信接收器',
  886 => '
兴中心幼',
  887 => '
星上门',
  888 => '
行长王益',
  889 => '
形透视镜',
  890 => '
型手枪',
  891 => '
姓忽悠',
  892 => '
幸运码',
  893 => '
性爱日',
  894 => '
性福情',
  895 => '
性感少',
  896 => '
性推广歌',
  897 => '
胸主席',
  898 => '
徐玉元',
  899 => '
学骚乱',
  900 => '
学位證',
  901 => '
學生妹',
  902 => '
丫与王益',
  903 => '
烟感器',
  904 => '
严晓玲',
  905 => '
言被劳教',
  906 => '
言论罪',
  907 => '
盐酸曲',
  908 => '
颜射',
  909 => '
恙虫病',
  910 => '
姚明进去',
  911 => '
要人权',
  912 => '
要射精了',
  913 => '
要射了',
  914 => '
要泄了',
  915 => '
夜激情',
  916 => '
液体炸',
  917 => '
一小撮别',
  918 => '
遗情书',
  919 => '
蚁力神',
  920 => '
益关注组',
  921 => '
益受贿',
  922 => '
阴间来电',
  923 => '
陰唇',
  924 => '
陰道',
  925 => '
陰戶',
  926 => '
淫魔舞',
  927 => '
淫情女',
  928 => '
淫肉',
  929 => '
淫騷妹',
  930 => '
淫兽',
  931 => '
淫兽学',
  932 => '
淫水',
  933 => '
淫穴',
  934 => '
隐形耳',
  935 => '
隐形喷剂',
  936 => '
应子弹',
  937 => '
婴儿命',
  938 => '
咏妓',
  939 => '
用手枪',
  940 => '
幽谷三',
  941 => '
游精佑',
  942 => '
有奶不一',
  943 => '
右转是政',
  944 => '
幼齿类',
  945 => '
娱乐透视',
  946 => '
愚民同',
  947 => '
愚民政',
  948 => '
与狗性',
  949 => '
玉蒲团',
  950 => '
育部女官',
  951 => '
冤民大',
  952 => '
鸳鸯洗',
  953 => '
园惨案',
  954 => '
园发生砍',
  955 => '
园砍杀',
  956 => '
园凶杀',
  957 => '
园血案',
  958 => '
原一九五七',
  959 => '
原装弹',
  960 => '
袁腾飞',
  961 => '
晕倒型',
  962 => '
韵徐娘',
  963 => '
遭便衣',
  964 => '
遭到警',
  965 => '
遭警察',
  966 => '
遭武警',
  967 => '
择油录',
  968 => '
曾道人',
  969 => '
炸弹教',
  970 => '
炸弹遥控',
  971 => '
炸广州',
  972 => '
炸立交',
  973 => '
炸药的制',
  974 => '
炸药配',
  975 => '
炸药制',
  976 => '
张春桥',
  977 => '
找枪手',
  978 => '
找援交',
  979 => '
找政法委副',
  980 => '
赵紫阳',
  981 => '
针刺案',
  982 => '
针刺伤',
  983 => '
针刺事',
  984 => '
针刺死',
  985 => '
侦探设备',
  986 => '
真钱斗地',
  987 => '
真钱投',
  988 => '
真善忍',
  989 => '
真实文凭',
  990 => '
真实资格',
  991 => '
震惊一个民',
  992 => '
震其国土',
  993 => '
证到付款',
  994 => '
证件办',
  995 => '
证件集团',
  996 => '
证生成器',
  997 => '
证书办',
  998 => '
证一次性',
  999 => '
政府操',
  1000 => '
政论区',
  1001 => '
證件',
  1002 => '
植物冰',
  1003 => '
殖器护',
  1004 => '
指纹考勤',
  1005 => '
指纹膜',
  1006 => '
指纹套',
  1007 => '
至国家高',
  1008 => '
志不愿跟',
  1009 => '
制服诱',
  1010 => '
制手枪',
  1011 => '
制证定金',
  1012 => '
制作证件',
  1013 => '
中的班禅',
  1014 => '
中共黑',
  1015 => '
中国不强',
  1016 => '
种公务员',
  1017 => '
种学历证',
  1018 => '
众像羔',
  1019 => '
州惨案',
  1020 => '
州大批贪',
  1021 => '
州三箭',
  1022 => '
宙最高法',
  1023 => '
昼将近',
  1024 => '
主席忏',
  1025 => '
住英国房',
  1026 => '
助考',
  1027 => '
助考网',
  1028 => '
专业办理',
  1029 => '
专业代',
  1030 => '
专业代写',
  1031 => '
专业助',
  1032 => '
转是政府',
  1033 => '
赚钱资料',
  1034 => '
装弹甲',
  1035 => '
装枪套',
  1036 => '
装消音',
  1037 => '
着护士的胸',
  1038 => '
着涛哥',
  1039 => '
姿不对死',
  1040 => '
资格證',
  1041 => '
资料泄',
  1042 => '
梓健特药',
  1043 => '
字牌汽',
  1044 => '
自己找枪',
  1045 => '
自慰用',
  1046 => '
自由圣',
  1047 => '
自由亚',
  1048 => '
总会美女',
  1049 => '
足球玩法',
  1050 => '
最牛公安',
  1051 => '
醉钢枪',
  1052 => '
醉迷药',
  1053 => '
醉乙醚',
  1054 => '
尊爵粉',
  1055 => '
左转是政',
  1056 => '
作弊器',
  1057 => '
作各种证',
  1058 => '
作硝化甘',
  1059 => '
唑仑',
  1060 => '
做爱小',
  1061 => '
做原子弹',
  1062 => '
做证件',
);

		
		$badword1 = array_combine($badword,array_fill(0,count($badword),'*'));
		//$bb = '我今天开着张三丰田上班';
		$bb = $source_word;
		$str = strtr($bb, $badword1);
		//echo $str;
		if(strlen($str) == strlen($bb))
			return false;
		return true;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}
