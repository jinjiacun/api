<?php
namespace Soapi\Controller;
use Soapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--企业评论的顶纪录管理--
------------------------------------------------------------
function of api:


#添加
public function add
@@input
@param $user_id
@param $company_id
@param $comment_id
@@output
@param $is_success 0-成功操作,-1-操作失败,-2-不允许添加
##--------------------------------------------------------##
*/
class ComtopController extends BaseController {
	/**
		* sql script:
		* create table so_com_top(id int primary key auto_increment,
	                              user_id int not null default 0 comment '用户id',
	                              company_id int not null default 0 comment '企业id',
	                              comment_id int not null default 0 comment '企业评论id',
	                              add_time int not null default 0 comment '添加日期'
	                             )charset=utf8;
	*/
	protected $_module_name = 'Com_top';
	protected $id;        
	protected $user_id;    #用户id
	protected $company_id; #企业id
	protected $comment_id;     #企业评论id
	protected $add_time;   #添加日期
	
	public function add($content)
	/*
	@@input
	@param $user_id
	@param $company_id
	@param $comment_id
	@@output
	@param $is_success 0-成功操作,-1-操作失败,-2-不允许操作 ,-3-此评论已删除 ,-4-此评论不存在 ,-5-此评论的企业不存在或者已删除
	*/
	{
		$data = $this->fill($content);
		if(!isset($data['user_id'])
		|| !isset($data['company_id'])
		|| !isset($data['comment_id'])
		)
		{
			return C('param_err');
		}
		
		$data['user_id'] = intval($data['user_id']);
		$data['company_id'] = intval($data['company_id']);
		$data['comment_id'] = intval($data['comment_id']);
		
		if(0>= $data['user_id']
		|| 0>= $data['company_id']
		|| 0>= $data['comment_id']
		)
		{
			return C('param_fmt_err');
		}
		
		if(!$this->__check(array('comment_id'=>$data['comment_id'],
		                         'user_id'=>$data['user_id'])))
		{
			return array(
				200,
				array(
					'is_success'=>-2,
					'message'=>urlencode('不允许操作'),
				)
			);
		}
		
		//检查评论是否被删除
		if(M('Comment')->where(array(
								'id'=>$data['comment_id'],
								'is_delete'=>1,
								))
		               ->find())
		{
			return array(
				200,
				array(
					'is_success'=>-3,
					'message'=>urlencode('此评论已删除'),
				)
			);
		}
		
		//检查评论不存在
		if(!M('Comment')->find($data['comment_id']))
		{
			return array(
				200,
				array(
					'is_success'=>-4,
					'message'=>urlencode('此评论不存在'),
				)
			);
		}
		
		//检查企业是否存在
		if(!M($this->_module_name)->where(array(
											'id'=>$data['company_id']
											))
									->find())
		{
			return array(
				200,
				array(
					'is_success'=>-5,
					'message'=>urlencode('此评论的企业不存在或者已删除'),
				),
			);
		}
		
		
		$data['add_time'] = time();
		if(M($this->_module_name)->add($data))
		{
			//增加对应的评论总数目
			$comment_obj = A('Soapi/Comment');
			if(isset($content)) unset($content);
			$field_name = 'top_num';
			$content = array(
				'id'=>$data['comment_id']
			);
			if($comment_obj->__top($content, $field_name))
			{
				$tmp_param = array(
					'id'=>$data['comment_id']
				);
				list(,$tmp_content) = A('Soapi/Comment')->get_info(json_encode($tmp_param));
				return array(
					200,
					array(
						'is_success'=>0,
						'message'=>C('option_ok'),
						'amount'=>$tmp_content['top_num'],
					),
				);
			}			
		}
		
		return array(
				200,
				array(
					'is_success'=>0,
					'message'=>C('option_fail')
				),
			);
	}
}
