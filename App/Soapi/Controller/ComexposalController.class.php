<?php
namespace Soapi\Controller;
use  Soapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--曝光评论管理--
------------------------------------------------------------
function of api:

#添加
public function add
@@input
@param $user_id     会员id
@param $exposal_id  企业入库id
@param $parent_id   父类id(默认为0,当盖楼时为当前楼的评论id)
@param $content     内容
@@output
@param $is_success 0-操作成功,-1-操作失败
##--------------------------------------------------------##
public function get_list 
##--------------------------------------------------------##
*/
class ComexposalController extends BaseController {
	/**
	 * sql script:
	 * create table so_com_exposal(id int primary key auto_increment,
	                              user_id int not null default 0 comment '用户id',
	                              exposal_id int not null default 0 comment '入库企业id',
	                              parent_id int not null default 0 comment '是否盖楼',
	                              content text comment '内容',
	                              is_validate int not null default 0 comment '是否审核',
	                              validate_time int not null default 0  comment '审核时间',
	                              is_anonymous int not null default 0 comment '是否匿名(0-不匿名,1-匿名,默认为0)',
	                              pic_1 int not null default 0 comment '图片id',
	                              pic_2 int not null default 0 comment '图片id',
	                              pic_3 int not null default 0 comment '图片id',
	                              pic_4 int not null default 0 comment '图片id',
	                              pic_5 int not null default 0 comment '图片id',
	                              type varchar(255) comment '评论类型', 
	                              is_depth int not null default 0 comment '是否超过第二层(0-没,1-超过)',
	                              is_delete int not null default 0 comment '删除',
	                              top_num int not null default 0 comment '顶数',
	                              has_child int not null default 0 comment '审核通过的回复数量',
	                              childs int not null default 0 comment '未审核的回复数量',
                                  last_child_time int not null default 0 comment '最近回复时间',                                   
                                  last_time int not null default 0 comment '最新回复或者再回复时间或者当前时间',
								  last_user_id int not null default 0 comment '最新回复或者再回复用户或者当前用户id',
	                              add_time int not null default 0 comment '添加日期'
	                             )charset=utf8;
	 * */
	 
	 protected $_module_name = 'Com_exposal';
	 protected $id;
	 protected $user_id;       #会员id
	 protected $exposal_id;    #入库id
	 protected $parent_id;     #父类id(默认为0,当盖楼时为当前楼的评论id)
	 protected $content;       #内容
	 protected $is_validate;   #是否审核(0-未审核,1-审核)
	 protected $validate_time; #审核日期
	 protected $top_num;       #顶的数目
	 protected $is_anonymous;  #是否匿名(0-不匿名,1-匿名,默认为0)
	 protected $pic_1;         #图片id
	 protected $pic_2;         #图片id
	 protected $pic_3;         #图片id
	 protected $pic_4;         #图片id
	 protected $pic_5;         #图片id
	 protected $type;          #评论类型
	 protected $has_child;     #审核评论回复数
     protected $last_child_time; #最近回复时间
	 protected $add_time;      #添加日期
	 
	 #添加
	public function add($content)
	/*
	@@input
	@param $user_id      会员id
	@param $exposal_id   企业入库id
	@param $parent_id    父类id(默认为0,当盖楼时为当前楼的评论id)
	@param $content      内容
	@param $is_anonymous 是否匿名(0-不匿名,1-匿名,默认为0)
	@param $type         评论类型
	@param $is_depth     是否超过第二层(0-没,1-超过)
	@@output
	@param $is_success 0-操作成功,-1-操作失败,-2-此条曝光不存在,-3-此条曝光已删除 ,-4-此企业不存在 ,-5-上级评论不存在 ,-6-父评论已删除
	*/
	{
		$data = $this->fill($content);
		if(!isset($data['user_id'])
		|| !isset($data['exposal_id'])
		|| !isset($data['content'])
		|| !isset($data['is_anonymous'])
		|| !isset($data['type'])
		)
		{
			return C('param_err');
		}
		
		$data['user_id']      = intval($data['user_id']);
		$data['exposal_id']   = intval($data['exposal_id']);
		$data['content']      = htmlspecialchars(trim($data['content']));
		$data['is_anonymous'] = intval($data['is_anonymous']);
		$data['type']         = htmlspecialchars(trim($data['type']));
		
		if(0>= $data['user_id']
		|| 0>= $data['exposal_id']
		|| '' == $data['content']
		|| (0 != $data['is_anonymous'] && 1 != $data['is_anonymous'])
		|| '' == $data['type']
		)
		{
			return C('param_fmt_err');
		}
		
		/*
		if(!$this->__check(array('user_id'=>$data['user_id'],
		                         'exposal_id'=>$data['exposal_id'])))
		{
			return array(
				200,
				array(
					'is_success'=>-2,
					'message'=>C('option_no_allow'),
				)
			);
		}
		*/
		
		//检查曝光是否存在
		if(!M('In_exposal')->find($data['exposal_id']))
		{
			return array(
				200,
				array(
					'is_success' => -2,
					'message'    => urlencode('此条曝光不存在'),
				),
			);
		}
		
		//曝光是否删除
		if(M('In_exposal')->where(array(
										'id'=>$data['exposal_id'],
										'is_delete'=>1,
		                                ))
		                  ->find())
		{
			return array(
				200,
				array(
					'is_success' => -3,
					'message'    => urlencode('此条曝光已删除'),
				),
			);
		}
		
		$tmp_info = M('In_exposal')->field('company_id')
		                           ->find($data['exposal_id']);
		$company_id = $tmp_info['company_id'];
		//检查企业是否存在
		if(!M('Company')->find($data['company_id']))
		{
			return array(
				200,
				array(
					'is_success' => -4,
					'message'    => urlencode('此企业不存在'),
				),
			);
		}
		
		//检查父评论是否存在
		if(0< $data['parent_id'])
		{
			if(!M('Com_exposal')->find($data['parent_id']))
			{
				return array(
					200,
					array(
						'is_success' => -5,
						'message'    => urlencode('上级评论不存在'),
					),
				);
			}
		}
		
		//检查父评论是否删除
		if(M('Com_exposal')->where(array(
									'id'        => $data['parent_id'],
									'is_delete' =>1,
								))
		                    ->find())
		{
			return array(
				200,
				array(
					'is_success' => -6,
					'message'    => urlencode('父评论已删除'),
				),
			);
		}
		
        		$now = time();
        		$data['last_time'] = $now;
		$user_id = $data['user_id'];
	    	$data['add_time'] = $now;
	    	#更新曝光回复时间和用户id
		M('In_exposal')->where(array('id'=>$data['exposal_id']))->save(array('last_time'=>$now,'last_user_id'=>$user_id));
	
		$is_validate = true;
		#检查是否有敏感词
		if($this->filter_sensitive($data['content']))
		{
			$is_validate = false;
		}		

		if(M($this->_module_name)->add($data))
		{
			//评论的回复，则改变父评论未审核childs数为1
			if(0< $data['parent_id'])
			{
				M($this->_module_name)->where(array('id'=>$data['parent_id']))->setInc('childs', 1);
                			M($this->_module_name)->where(array('id'=>$data['parent_id']))->save(array('last_child_time'=>$now));
                			M($this->_module_name)->where(array('id'=>$data['parent_id']))->save(array('last_time'=>$now));
				M($this->_module_name)->where(array('id'=>$data['parent_id']))->save(array('last_user_id'=>$user_id));
			}
			$id = M()->getLastInsID();
			if($is_validate)
			{
				$this->validate(json_encode(array('id'=>$id));
			}
			return array(
				200,
				array(
					'is_success'=>0,
					'message'=>C('option_ok'),
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
	
	public function get_list($content)
	{
		list($data, $record_count) = parent::get_list($content);

		$list = array();
		if($data)
		{
			foreach($data as $v)
			{
				$list[] = array(
						'id'           => intval($v['id']),
						'user_id'      => intval($v['user_id']),
						'nickname'     => $this->_get_nickname($v['user_id']),
						'exposal_id'   => intval($v['exposal_id']),
						'parent_id'    => intval($v['parent_id']),
						'parent_content' => urlencode($this->get_parent_content($v['parent_id'])),
						'content'      => urlencode($v['content']),
						'is_validate'  => intval($v['is_validate']),
						'validate_time'=> intval($v['validate_time']),
						'is_anonymous' => intval($v['is_anonymous']),
						'top_num'      => intval($v['top_num']),
						'pic_1'        => intval($v['pic_1']),
						'pic_1_url'    => $this->get_pic_url($v['pic_1']),
						'pic_2'        => intval($v['pic_2']),
						'pic_2_url'    => $this->get_pic_url($v['pic_2']),
						'pic_3'        => intval($v['pic_3']),
						'pic_3_url'    => $this->get_pic_url($v['pic_3']),
						'pic_4'        => intval($v['pic_4']),
						'pic_4_url'    => $this->get_pic_url($v['pic_4']),
						'pic_5'        => intval($v['pic_5']),
						'pic_5_url'    => $this->get_pic_url($v['pic_5']),
						'type'         => urlencode($v['type']),
						'top_num'      => intval($v['top_num']),
						'has_child'    => intval($v['has_child']),
						'last_time'    => intval($v['last_time']),
						'last_user_id' => intval($v['last_user_id']),
						'last_nickname'=> $this->_get_nickname($v['last_user_id']),
						'v_last_time'    => intval($v['v_last_time']),
						'v_last_user_id' => intval($v['v_last_user_id']),
						'v_last_nickname'=> $this->_get_nickname($v['v_last_user_id']),
						'v_last_is_anonymous'=> intval($v['v_last_is_anonymous']),
						'add_time'     => intval($v['add_time']),
						
					);	
			}
		}

		return array(200, 
				array(
					'list'=>$list,
					'record_count'=> $record_count,
					)
				);
	}
	
	#通过id查询一条信息
	public function get_info($content)
	/*
	@@input
	@param $id
	@@output
	@param $id
	@param $title
	@param $url
	@param $pic
	@param $intro
	@param $add_time 
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
		
		$list = array();
		$v = M($this->_module_name)->find($data['id']);
		if($v)
		{
			$list = array(
						'id'           => intval($v['id']),
						'user_id'      => intval($v['user_id']),
						'nickname'     => $this->_get_nickname($v['user_id']),
						'exposal_id'   => intval($v['exposal_id']),
						'parent_id'    => intval($v['parent_id']),
						'parent_content' => urlencode($this->get_parent_content($v['parent_id'])),
						'content'      => urlencode($v['content']),
						'is_validate'  => intval($v['is_validate']),
						'validate_time'=> intval($v['validate_time']),
						'is_anonymous' => intval($v['is_anonymous']),
						'top_num'      => intval($v['top_num']),
						'pic_1'        => intval($v['pic_1']),
						'pic_1_url'    => $this->get_pic_url($v['pic_1']),
						'pic_2'        => intval($v['pic_2']),
						'pic_2_url'    => $this->get_pic_url($v['pic_2']),
						'pic_3'        => intval($v['pic_3']),
						'pic_3_url'    => $this->get_pic_url($v['pic_3']),
						'pic_4'        => intval($v['pic_4']),
						'pic_4_url'    => $this->get_pic_url($v['pic_4']),
						'pic_5'        => intval($v['pic_5']),
						'pic_5_url'    => $this->get_pic_url($v['pic_5']),
						'type'         => urlencode($v['type']),
						'top_num'      => intval($v['top_num']),
						'has_child'    => intval($v['has_child']),
						'last_time'    => intval($v['last_time']),
						'last_user_id' => intval($v['last_user_id']),
						'last_nickname'=> $this->_get_nickname($v['last_user_id']),
						'v_last_time'    => intval($v['v_last_time']),
						'v_last_user_id' => intval($v['v_last_user_id']),
						'v_last_nickname'=> $this->_get_nickname($v['v_last_user_id']),
						'v_last_is_anonymous'=> intval($v['v_last_is_anonymous']),
						'add_time'     => intval($v['add_time']),			);
		}
		
		return array(
			200,
			$list
		);
	}
	
	public function get_list_com_ex($content)
	{
		$list         = array();
		$record_count = 0;
		
		list(,$old_list) = $this->get_list($content);
		$list = $old_list['list'];
		$record_count = $old_list['record_count'];
		
		$data = $this->fill($content);
		
		$user_id = intval($data['user_id']);
		if(!isset($data['where_ex'])) 
		{
			if(isset($data['where']['has_child']))  unset($data['where']['has_child']);		
		}
		else
		{
			$data['where'] = $data['where_ex'];
		}
		//if(isset($data['where']))unset($data['where']);		
		foreach($list as $k=> $v)
		{
			$data['where']['parent_id'] =  intval($v['id']);
			if(!isset($data['where_ex']))
			{
				if(!isset($data['where']['_complex']))
				{
					if(0< $user_id)
						$data['where']['_string'] = "user_id=$user_id or is_validate=1";
					elseif(0 == $user_id)
						$data['where']['is_validate'] = 1;
				}	
			
		    	if(isset($data['where']['pic_1']))
		    	{
					unset($data['where']['pic_1']);
				}
			
				if(!isset($data['where']['_complex']))
				{	
					if(0 > $user_id)
					{
						$data['page_size'] = 10;
						$data['page_index'] = 1;
						$data['where']['is_validate'] = $data['where']['_complex']['is_validate'];
						unset($data['where']['_complex']);
					}
					else
					{
						$data['page_size'] = 2;
						$data['page_index'] = 1;
					}
				}
				else
				{
					if(-10000 == $user_id)
					{
						
					}
					else
					{
						$data['page_size'] = 2;
						$data['page_index'] = 1;
					}
				}
			}
			else
			{
				$data['page_size'] = 2;
				$data['page_index'] = 1;
			}
			list(, $sub) = $this->get_list(json_encode($data));
			$list[$k]['sub'] = array(
				'list'=>$sub['list'],
				'record_count'=>intval($sub['record_count'])
			);
		}
		unset($k, $v);
		return array(
			200,
			array(
				'list'         =>$list,
				'record_count' =>$record_count
			)
		);
	}
	
	#审核
	public function validate($content)
	/*
	@@input
	@param id
	@@output
	@param $is_success 0-成功操作,-1-操作失败,-2-不允许审核(父类未审核)
	*/
	{
		$data = $this->fill($content);
		unset($content);
		if(!isset($data['id'])
		)
		{
			return C('param_err');
		}
		
		$data['id'] = intval($data['id']);
		
		if(0>= $data['id']
	//	|| 0>= $data['company_id']
		)
		{
			return C('param_fmt_err');
		}
		
		$content = array(
			'id'=>$data['id']
		);
		$id = $data['id'];
		unset($data);
		$data = array(
			'is_validate'=>1,
			'validate_time'=>time(),
		);
		
		//检查父类是否审核
		$t_one = M($this->_module_name)->field('parent_id')
		                               ->where(array('id'=>$id))
		                               ->find();
		if(0 < $t_one['parent_id'])
		{
			if(!$this->__check_exists(array('id'=>$t_one['parent_id'],
			                                'is_validate'=>0)))
			{
				return array(
					200,
					array(
						'is_success'=>-2,
						'message'=>urlencode('不允许审核(父类未审核)'),
					)
				);
			}
		}
		//检查此是否审核
		if(!$this->__check_exists(array(
									'id'=>$id,
									'is_validate'=>1)
										))
		{
			return array(
					200,
					array(
						'is_success'=>0,
						'message'=>C('option_ok'),
					)
				);
		}
		
		
		if(M($this->_module_name)->where($content)->save($data))
		{
				//统计子回复总数
				$this->update_re_child_amount(json_encode(array('id'=>$id)));
				//list(,$tmp_content) = $this->get_info(json_encode(array('id'=>$id)));
				$tmp_content = M($this->_module_name)->field('is_depth, parent_id,exposal_id')->find($id);
				//统计父评论数
				if(0< $tmp_content['parent_id'])
				{
					$this->update_re_child_amount(json_encode(array('id'=>$tmp_content['parent_id'])));
					//减少父评论未审核子回复数
					M($this->_module_name)->where(array('id'=>$tmp_content['parent_id']))->setDec("childs", 1);
				}
				#统计最新审核的评论时间和user_id(当前或者回复)
				$this->update_v_last($id);
				
				#推送:begin
				if(0 == $tmp_content['is_depth'])
				{
					$_template_push = C('push_event_type');
					if(0== $tmp_content['parent_id'])//推送曝光回复
					{
						##判定不是同一个用户
						$user_id_list = M($this->_module_name)->field('user_id, content')
										->find($id);
						//if($user_id_list[0]['user_id'] != $user_id_list[1]['user_id'])
						//{
							$user_nickname = $this->_get_nickname($user_id_list['user_id']);
							$content = sprintf("%s 回复了您的曝光评论：%s", $user_nickname,$user_id_list['content']);
							$param_template  = C('push_event_type');
							$src_event_param = $param_template['exposal_re']['src_event_param'];
							$src_event_param = str_replace("<COMMENT_ID>", $id,                        $src_event_param);
							$src_event_param = str_replace("<EXPOSAL_ID>", $tmp_content['exposal_id'], $src_event_param);
							$src_event_param = str_replace("<USER_ID>",    $user_id_list['user_id'],   $src_event_param);
							$src_event_param = str_replace("<CONTENT>",    $content,                   $src_event_param);
							$this->__debug(sprintf("src_event_param:%s\n", $src_event_param));
							A('Soapi/Pushmessage')->push_event('010005', $src_event_param,             $content);
						//}
					}
					else//推送曝光回复的回复
					{
						##判定不是同一个用户
						$user_id_list = M($this->_module_name)->field('user_id, content')
										->where(array('id'=>array("in",$id.",".$tmp_content['parent_id'])))
										->order(array('id'=>'desc'))
										->select();
						if($user_id_list[0]['user_id'] != $user_id_list[1]['user_id'])
						{
							$user_nickname = $this->_get_nickname($user_id_list[0]['user_id']);
							$content = sprintf("%s 回复了您的曝光评论：%s", $user_nickname,$user_id_list[0]['content']);
							$param_template  = C('push_event_type');
							$src_event_param = $param_template['exposal_rre']['src_event_param'];
							$src_event_param = str_replace("<COMMENT_ID>", $id,                        $src_event_param);
							$src_event_param = str_replace("<EXPOSAL_ID>", $tmp_content['exposal_id'], $src_event_param);
							$src_event_param = str_replace("<PARENT_ID>",  $tmp_content['parent_id'],  $src_event_param);
							$src_event_param = str_replace("<CONTENT>",    $content,                   $src_event_param);
							$this->__debug(sprintf("src_event_param:%s\n", $src_event_param));
							A('Soapi/Pushmessage')->push_event('010006',   $src_event_param ,$content);
						}
					}
				}				
				#推送:end
				
				return array(
					200,
					array(
						'is_success'=>0,
						'message'=>C('option_ok'),
					)
				);
		}
		
		return array(
					200,
					array(
						'is_success'=>-1,
						'message'=>C('option_fail'),
					)
		);
	}
	
	
	#统计审核通过的回复数量
	public function update_re_child_amount($content)
	/*
	@@input
	@param $id 评论id
	@@output
	@param $is_success 0-操作成功，-1-操作失败
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
		
		$amount = 0;
		$param = array(
			'parent_id'=>$data['id'],
			'is_delete'=>0,
			'is_validate'=>1
		);
		$amount = M($this->_module_name)->where($param)->count();
		unset($param);
		$amount = intval($amount);
		
		$param = array(
			'has_child'=>$amount
		);		
		if(false !== M($this->_module_name)
		             ->where(array('id'=>$data['id']))
		             ->save($param))
		{
			return array(
				200,
				array(
					'is_success'=>0,
					'message'=>C('option_ok')
				)
			);
		}
		
		return array(
				200,
				array(
					'is_success'=>-1,
					'message'=>C('option_fail')
				)
			);
	}
	
	#触发评论中是否有未审核的回复
	public function stat_re_comment($content)
	/*
	@@output
	@param $is_success 0-操作成功,-1-操作失败
	*/
	{
		/*
		//查询所有的主评论
		$tmp_list = M($this->_module_name)
		            ->field("id")
		            ->where(array("parent_id"=>0,
		                          "is_delete"=>0,))
		            ->select();
		if($tmp_list
		&& 0<count($tmp_list))
		{
			foreach($tmp_list as $v)
			{
				$tmp_count = M($this->_module_name)
				->where(array('parent_id'=>$v['id'],
						      'is_validate'=>0,
				             ))->count();
				//if(0< $tmp_count)
				//{
					$tmp_data = array("childs"=>$tmp_count);
					M($this->_module_name)->where(array("id"=>$v["id"]))->save($tmp_data);
					unset($tmp_data);
				//}
			}
			unset($v, $tmp_list);
		}
		
		
		
		
		return array(
			200,
			array(
				'is_success'=>0,
				'message'=>C('option_ok'),
			)
		);
		*/
	}
		
	#更新最新审核通过的评论的最新时间和用户id
	private function update_v_last($id)
	{
		#查询当前评论
		$comment_info = M($this->_module_name)->find($id);
		$exposal_id      = $comment_info['exposal_id'];
		$v_last_time = $v_last_user_id = $v_last_is_anonymous = 0;
		//$mast_comment_id = 0;
		
		#如果是主回复
		/*		
		if(0 == $comment_info['parent_id'])
		{
			$mast_comment_id = $comment_info['id'];
			$v_last_time     = $comment_info['add_time'];
			$v_last_user_id  = $comment_info['user_id'];
			$v_last_is_anonymous = $comment_info['is_anonymous'];
		}
		*/
		#如果再回复
		//if(0 != $comment_info['parent_id'])
		//{
			//$mast_comment_id = $comment_info['parent_id'];
			$tmp_param = array(
				'is_validate' =>1,
				'is_delete'   =>0,
				'exposal_id'  =>$exposal_id,
				//'_string'     => "id=$id or parent_id=$id",
				//'parent_id'=>$mast_comment_id,
			);
			$tmp_info = M($this->_module_name)->where($tmp_param)->order(array('add_time'=>'desc'))->find();
			$v_last_time    = $tmp_info['add_time'];
			$v_last_user_id = $tmp_info['user_id']; 
			$v_last_is_anonymous = $comment_info['is_anonymous'];
		//}
		
		#更新主评论最新审核的时间和用户id(主评论或者回复或者再回复)
		if(false !== M('In_exposal')->where(array('id'=>$exposal_id))
		                                   ->save(array('v_last_time'=>$v_last_time,
		                                                'v_last_user_id'=>$v_last_user_id,
		                                                'v_last_is_anonymous' =>$v_last_is_anonymous,
		                                                )))
		{
			return true;
		}
		
		return false;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}
