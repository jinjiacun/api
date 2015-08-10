<?php
namespace Soapi\Controller;
use  Soapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--推送信息管理--
------------------------------------------------------------
function of api:

#添加
public function add
@@input
@param $title   标题
@param $content 内容
@param $event   触发类型
@param $type    推送类型(详细见字典说明)
@param $token   当是点推时，对应的ios设备;否则为null
@param $user_id 当是点推时，用户id;否则为0
@param $user_id_device_token 当推送类型为全推或者组推时，要推动的设备信息相关信息
@param $rule    附带规则信息
@@output
@param $is_success 0-操作成功,-1-操作失败
##--------------------------------------------------------##
#推送操作
private function done
@@input 
@param $id 要推动消息的id
@@output
@param $is_success 0-操作成功,-1-操作失败
##--------------------------------------------------------##
#android push
private function push_android
@@input
@param $title   标题
@param $content 内容
@param $user_ids 用户id(空为全播;之间用逗号隔开是组播;单个是点播)
@param $user_id_len 要推动的用户数(0-全推;1-点推;n-组推)
@@output
@param $is_success 0-操作成功,-1-操作失败
##--------------------------------------------------------##
#ios push
private function push_ios
@@input
@param $title   标题
@param $content 内容
@param $tokens  要推动的所有token，之间用逗号隔开
@@output
@param $is_success 0-操作成功,-1-操作失败
##--------------------------------------------------------##
#触发推送事件
public function push_event
@@input
@param $event_type  事件类型
@param $cause_param 事件源参数 
@@output
@param $push_ids
##--------------------------------------------------------##
public function get_list 
##--------------------------------------------------------##
*/
class PushmessageController extends BaseController {
	/**
	 * sql script:
	 * create table so_push_message(id int primary key auto_increment,
								  title   varchar(255) comment '标题',
								  content varchar(255) comment '内容',
								  src_event_param varchar(255) comment '事件源参数',
								  des_event_param varchar(255) comment '事件结果参数',
								  event   varchar(255) comment '触发类型',								  
                                  type varchar(255) default '009001' comment '推动类型',
	                              token varchar(255),
	                              user_id int not null default 0 comment '用户id',
	                              user_id_device_token text comment '用户id和设备类型及其对应的token,之间用下划线连接，每个组之间用逗号隔开',
	                              add_time int not null default 0 comment '添加日期'
	                             )charset=utf8;
	 * */
	 
	 protected $_module_name = 'Push_message';
	 protected $id;
	 protected $title;               #标题
	 protected $content;             #内容
	 protected $src_event_param;     #事件源参数
	 protected $des_event_param;     #事件结果参数
     protected $type;                #设备类型
     protected $token;               #token值
     protected $user_id;             #用户id
	 protected $user_id_device_token;#推送设备相关信息
     protected $add_time;            #添加日期
		 
	#添加
	public function add($content)
	/*
	@@input
	@param $title                标题
	@param $content              内容
	@param $src_event_param      事件源参数
	@param $des_event_param      事件结果参数
	@param $event                事件触发类型
	@param $type                 推送类型(默认点推，详细查看字典信息)
	@param $token                设备对应的token
	@param $user_id              用户id
	@param $user_id_device_token 推送设备相关信息
	@@output
	@param $is_success 0-操作成功,-1-操作失败
	*/
	{
		$data = $this->fill($content);
		if(!isset($data['type'])
		|| !isset($data['title'])
		|| !isset($data['content'])
		|| !isset($data['event'])
		|| !isset($data['src_event_param'])
		|| !isset($data['des_event_param'])
		)
		{
			return C('param_err');
		}
		
		$data['type']              = htmlspecialchars(trim($data['type']));
		$data['title']             = htmlspecialchars(trim($data['title']));
		$data['content']           = htmlspecialchars(trim($data['content']));
		$data['event']             = htmlspecialchars(trim($data['event']));
		$data['src_event_param']   = htmlspecialchars(trim($data['src_event_param']));
		$data['des_event_param']   = htmlspecialchars(trim($data['des_event_param']));
		
			
		if('' == $data['type']
		|| '' == $data['title']
		|| '' == $data['content']
		|| '' == $data['event']
		|| '' == $data['src_event_param']
		|| '' == $data['des_event_param']
		)
		{
			return C('param_fmt_err');
		}
         
        $now = time();
        
	    $data['add_time'] = $now;
		
		if(M($this->_module_name)->add($data))
		{
				return array(
					200,
					array(
						'is_success'=>0,
						'message'=>C('option_ok'),
						'id'=> M()->getLastInsID(),
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
	
	#推送操作
	private function done($id)
	/*
	@@input 
	@param $id 要推动消息的id
	@@output
	@param $is_success 0-操作成功,-1-操作失败
	*/
	{
		if(0>= $id)return;
		
		$user_id_len = 0;
		$user_ids    = '';
		
		//查看推送信息，分点推、组推、全推不同处理
		$push_info = M($this->_module_name)->find($id);
		if(!$push_info)
		{
			return array(
				500,
				urlencode('此条推送消息不存在'),
			);
		}
		
		switch($push_info['type'])
		{
			case '009001'://点推处理
				{
					$user_id_device_token = $push_info['user_id_device_token'];					
					$user_id_device_token_l = explode(',', $user_id_device_token);
					$user_id_len = count($user_id_device_token_l);
					$device  = '';
					$token   = '';
					$user_id = '';
					$rule_message = '';
					if(0< $user_id_len)
					{
						foreach($user_id_device_token_l as $v)
						{
							$tmp_info = explode('_', $v);
							#获取设备
							$user_id      = $tmp_info[0];
							$device       = $tmp_info[1];
							$token        = $tmp_info[2];
							$rule_message = $push_info['des_event_param'];
							switch($device)
							{
								case '008001'://ios
									{											
										$this->push_ios($push_info['title'],
															$push_info['content'],
															$token,
															1,
															$rule_message);
									}
									break;
								case '008002'://android
									{
										$this->push_android($push_info['title'],
															$push_info['content'],
															$token,
															1,
															$rule_message;
									}
									break;
								default:
									break;
							}
						}
						unset($user_id_device_token_l, $v);
					}
				}
				break;
			case '009002'://组推处理
				{
					##更新user_id_device_token
					//??	
				}
				break;
			case '009003'://全推处理
				{
					##user_id_device_token
					###查询ios要推动的token，其他android直接推送
					//todo
				}
				break;
			default:
				break;
		}
	}
		
		#android push
		private function push_android($title, $content, $tokens, $user_id_len,$rule_message)
		/*
		@@input
		@param $title   标题
		@param $content 内容
		@param $user_ids 用户id(空为全播;之间用逗号隔开是组播;单个是点播)
		@param $user_id_len 要推动的用户数(0-全推;1-点推;n-组推)
		@@output
		@param $is_success 0-操作成功,-1-操作失败
		*/
		{
			if(0 > $user_id_len)
				return ;
			if(0 == $user_id_len)
			{
				//::todo
			}
			else
			{
				$token_list = explode(',', $tokens);
				foreach($token_list as $v)
				{
					$this->post('http://192.168.1.131/phpmquttclient/send_mqtt.php', 
					            array('target'=>'souhei/'.$v,
					                  'message'=>$rule_message,
					                  )
					);
				}
				unset($token_list, $v);
			}
		}

		#ios push
		private function push_ios($title, $content, $tokens, $token_len, $rule_message)
		/*
		@@input
		@param $title   标题
		@param $content 内容
		@param $tokens  要推动的所有token，之间用逗号隔开
		@@output
		@param $is_success 0-操作成功,-1-操作失败 
		*/
		{
			$token_list = explode(',', $tokens);
			$is_success = false;
			foreach($token_list as $v)
			{
				$deviceToken = $v;
				//'aeca10b5ca2a68f071299dc1bd0cdeebb3b5038ba78e1b1dd2e5d6db8137cf43';
				//推送方式，包含内容和声音
				$body = array("aps" => array("alert" => $content,
											 "badge" => 1,
											 "sound"=>'default'),
							  "comment" => $rule_message,
						);
				//创建数据流上下文对象
				$ctx = stream_context_create();
				//设置pem格式文件
				$pem = __PUBLIC__."ssl/apns-dev.pem";
				//$pem = "apns-dev.pem";      
				//设置数据流上下文的本地认证证书      
				stream_context_set_option($ctx,"ssl","local_cert", $pem);       
				$pass = "123";      
				//设置数据流上下文的密码      
				stream_context_set_option($ctx, "ssl", "passphrase", $pass);      
				//产品发布APNS服务器，gateway.push.apple.com       
				//测试APNS服务器，gateway.sandbox.push.apple.com       
				//socket通讯
				$fp = stream_socket_client("ssl://gateway.sandbox.push.apple.com:2195", $err, $errstr, 60, STREAM_CLIENT_CONNECT, $ctx);
				if (!$fp) {
					//echo "连接失败.";
					return;
				} 
				//print "连接OK\n";
				//载荷信息，JSON编码      
				$payload = json_encode($body);
				//构建发送的二进制信息      
				$msg = chr(0) . pack("n",32) . pack("H*", str_replace(' ', '', $deviceToken))      
				.pack("n",strlen($payload)) . $payload;
				//echo "发送消息:" . $payload ."\n";      
				$result = fwrite($fp, $msg);
				if (!$result){  
					$is_success = false;
					echo 'Message not delivered' . PHP_EOL;  
				}else{
					$is_success = true;
					//echo 'Message successfully delivered' . PHP_EOL;  
				}
				fclose($fp);				
			}
			return $is_success;
		}		
		 
		 
		#触发推送事件
		public function push_event($event_type, $src_event_param, $content)
		{
			$collect_param = array(
				'title'                => '',
				'event'                => '',
				'src_event_param'      => '',
				'content'              => '',
				'des_event_param'      => '',
				'type'                 => '',
				'user_id'              => '',
				'token'                => '',
				'user_id_device_token' => '',
			);
			$message_id = 0;
			$_event_template = C('push_event_type');
			$_current_template = '';
			$is_validate = true;//是否有效
			switch($event_type)
			{
				case '010001':
					{
						#采集信息并检测是否符合推送
						//::todo
						$collect_param['title']    = '回复主贴';
						
						$collect_param['content']  = $content;
						
						$collect_param['event']    = '010001';
						
						$collect_param['src_event_param'] = $src_event_param;
						
						preg_match_all('#(\w+):(\w+)#', $src_event_param ,$_tmp_set);
						$comment_id = $_tmp_set[2][1];
						unset($_tmp_list);
						
						$_current_template = $_event_template['comment_master']['des_event_param'];
						$des_event_param = str_replace('<COMMENT_ID>', $comment_id, $_current_template);
						
						$collect_param['des_event_param'] = $des_event_param;
						
						$collect_param['type']            = '009001';
						
						$comment_info = M('Comment')->field('user_id')->find($comment_id);
						
						$collect_param['user_id']         = $comment_info['user_id'];
						
						$token_info = M('Token')->field('token,type')
						                        ->where(array('user_id'=>$collect_param['user_id']))
						                        ->find();
						
						$collect_param['token']           = $token_info['token'];
						
						$collect_param['user_id_device_token'] = sprintf("%s-%s-%s", $collect_param['user_id'], 
						                                                             $token_info['type'],
						                                                             $token_info['token']);
					}
					break;
				case '010002':
					{
						#采集信息并检测是否符合推送
						//::todo
						$collect_param['title']    = '回复跟帖';
						
						$collect_param['content']  = $content;
						
						$collect_param['event']    = '010002';
						
						$collect_param['src_event_param'] = $src_event_param;
						
						preg_match_all('#(\w+):(\w+)#', $src_event_param ,$_tmp_set);
						$comment_id = $_tmp_set[2][1];
						unset($_tmp_list);
						
						$_current_template = $_event_template['comment_master']['des_event_param'];
						$des_event_param = str_replace('<COMMENT_ID>', $comment_id, $_current_template);
						
						$collect_param['des_event_param'] = $des_event_param;
						
						$collect_param['type']            = '009001';
						
						$comment_info = M('Comment')->field('user_id')->find($comment_id);
						
						$collect_param['user_id']         = $comment_info['user_id'];
						
						$token_info = M('Token')->field('token,type')
						                        ->where(array('user_id'=>$collect_param['user_id']))
						                        ->find();
						
						$collect_param['token']           = $token_info['token'];
						
						$collect_param['user_id_device_token'] = sprintf("%s-%s-%s", $collect_param['user_id'], 
						                                                             $token_info['type'],
						                                                             $token_info['token']);
					}
					break;
				case '010003':#企业评级改变
					{
						#采集信息并检测是否符合推送
						//::todo
						$collect_param['title']    = '评级改变';
						
						$collect_param['content']  = $content;
						
						$collect_param['event']    = '010003';
						
						$collect_param['src_event_param'] = $src_event_param;
						
						preg_match_all('#(\w+):(\w+)#', $src_event_param ,$_tmp_set);
						$company_id = $_tmp_set[2][1];
						$nature     = $_tmp_set[2][2];
						$auth_level = $_tmp_set[2][3];
						unset($_tmp_list);
						
						$_current_template = $_event_template['comment_master']['des_event_param'];
						$des_event_param = str_replace('<COMPANY_ID>', $comment_id, $_current_template);
						$des_event_param = str_replace('<NATURE>',     $nature,     $_current_template);
						$des_event_param = str_replace('<AUTH_LEVEL>', $auth_level, $_current_template);
						
						$collect_param['des_event_param'] = $des_event_param;
						
						$collect_param['type']            = '009001';
						
						$user_list = M('Attention')->field('user_id')
						                           ->where(array('company_id'=>$company_id))
						                           ->select();
						if(0>= count($user_list))
						{
							$is_validate = false;
						}
						else
						{
							$user_id_list = array();
							foreach($user_list as $v)
							{
								$user_id_list[] = $v['user_id'];
							}
							unset($user_list, $v);
							if(1 == count($user_id_list))
							{
								$collect_param['user_id']         = $user_id_list[0];
						
								$token_info = M('Token')->field('token,type')
						                            ->where(array('user_id'=>$collect_param['user_id']))
						                            ->find();
						
								$collect_param['token']           = $token_info['token'];
						
								$collect_param['user_id_device_token'] = sprintf("%s-%s-%s", $collect_param['user_id'], 
						                                                             $token_info['type'],
						                                                             $token_info['token']);
						    }
						    else
						    {
								$collect_param['user_id']         = 0;
								
								$user_ids = implode(',', $user_id_list);
						
								$token_list = M('Token')->field('token,type,user_id')
						                            ->where(array('user_id'=>array('in',$user_ids)))
						                            ->select();
						                            
						        $collect_param['token']           = '';
						        foreach($token_list as $v)
						        {
									if(isset($collect_param['user_id_device_token']))
									{
										$collect_param['user_id_device_token'] = $collect_param['user_id_device_token'].','.sprintf("%s-%s-%s", $v['user_id'], 
						                                                             $v['type'],
						                                                             $v['token']);
									}
									else
									{
									
						
											$collect_param['user_id_device_token'] = sprintf("%s-%s-%s", $v['user_id'], 
						                                                             $v['type'],
						                                                             $v['token']);
									}
								}
								unset($token_list, $v);								
							}
						}
					}
					break;
				case '010004':#负面新闻
					{
						#采集信息并检测是否符合推送
						//::todo
						$collect_param['title']    = '负面新闻';
						
						$collect_param['content']  = $content;
						
						$collect_param['event']    = '010004';
						
						$collect_param['src_event_param'] = $src_event_param;
						
						preg_match_all('#(\w+):(\w+)#', $src_event_param ,$_tmp_set);
						$news_id    = $_tmp_set[2][0];
						$comment_id = $_tmp_set[2][1];
						unset($_tmp_list);
						
						$_current_template = $_event_template['comment_master']['des_event_param'];
						$des_event_param = str_replace('<NEWS_ID>', $news_id, $_current_template);
						
						$collect_param['des_event_param'] = $des_event_param;
						
						$collect_param['type']            = '009001';
						
						$user_list = M('Attention')->field('user_id')
						                           ->where(array('company_id'=>$company_id))
						                           ->select();
						if(0>= count($user_list))
						{
							$is_validate = false;
						}
						else
						{
							$user_id_list = array();
							foreach($user_list as $v)
							{
								$user_id_list[] = $v['user_id'];
							}
							unset($user_list, $v);
							if(1 == count($user_id_list))
							{
								$collect_param['user_id']         = $user_id_list[0];
						
								$token_info = M('Token')->field('token,type')
														->where(array('user_id'=>$collect_param['user_id']))
														->find();
						
								$collect_param['token']           = $token_info['token'];
						
								$collect_param['user_id_device_token'] = sprintf("%s-%s-%s", $collect_param['user_id'], 
																							 $token_info['type'],
																							 $token_info['token']);
							}
							else
							{
								$user_ids = implode(',', $user_id_list);
								$token_list = M('Token')->field('token,type,user_id')
														->where(array('user_id'=>array('in', $user_ids)))
														->select();
						
								$collect_param['token']           = '';
						
								foreach($token_list as $v)
								{
									if(isset($collect_param['user_id_device_token']))
									{
										$collect_param['user_id_device_token'] = 
										 $collect_param['user_id_device_token'].','.sprintf("%s-%s-%s", $v['user_id'], 
						                                                             $v['type'],
						                                                             $v['token']);
									}
									else
									{
										$collect_param['user_id_device_token'] = sprintf("%s-%s-%s", $v['user_id'], 
																							 $v['type'],
																							 $v['token']);
									}
								}
								unset($token_list, $v);
							}							
						}						
					}
					break;		
			}
			
			if($is_validate)
			{
				#写入推动消息
				list(,$info) = $this->add(json_encode($collect_param));
				$message_id = $info['id'];
				#推动执行
				$this->done($message_id);
			}
		}

}
