<?php
namespace Soapi\Controller;
use Soapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--企业新闻评论点赞管理--
------------------------------------------------------------
function of api:

#添加
public function add
@@input
@param $user_id     用户id
@param $company_id  企业id
@param $news_id     新闻id
@param $comment_id  企业新闻评论id
@@output
@param $is_success 0-成功操作,-1-操作失败,-2-不允许操作
##--------------------------------------------------------##
*/
class ComnewsassistController extends BaseController {
		/**
		* sql script:
		* create table so_com_news_assist(id int primary key auto_increment,
	                              user_id int not null default 0 comment '用户id',
	                              company_id int not null default 0 comment '企业id',
	                              news_id int not null default 0 comment '企业新闻id',
	                              comment_id int not null default 0 comment '企业新闻评论id',
	                              add_time int not null default 0 comment '添加日期'
	                             )charset=utf8;
	   * */
	
	   protected $_module_name = 'Com_news_assist';
	   protected $id;
	   protected $user_id;
	   protected $company_id;
	   protected $news_id;
	   protected $comment_id;
	   protected $add_time;
	   
	    #添加
		public function add($content)
		/*
		@@input
		@param $user_id
		@param $company_id
		@param $news_id
		@param $comment_id
		@@output
		@param $is_success 0-成功操作,-1-操作失败
		*/
		{
			$data = $this->fill($content);
			if(!isset($data['user_id'])
			|| !isset($data['company_id'])
			|| !isset($data['news_id'])
			|| !isset($data['comment_id'])
			)
			{
				return C('param_err');
			}
			
			$data['user_id']    = intval($data['user_id']);
			$data['company_id'] = intval($data['company_id']);
			$data['news_id']    = intval($data['news_id']);
			$data['comment_id'] = intval($data['comment_id']);
			
			if(0>= $data['user_id']
			|| 0>= $data['company_id']
			|| 0>= $data['news_id']
			|| 0>= $data['comment_id']
			)
			{
				return C('param_fmt_err');
			}
			
			if(!$this->__check(array(
								'id'=> $data['comment_id']
			)))
			{
				return array(
				200,
				array(
					'is_success'=>-2,
					'message'=>C('option_no_allow'),
				),
			 );
			}
			
			if(M($this->_module_name)->add($data))
			{
				$com_news = A('Soapi/Comnews');
				if($com_news->__assist(array('id'=>$data['comment_id']),
				                      'assist_num'))
				{
					return array(
						200,
						array(
							'is_success'=>0,
							'message'=>C('option_ok'),
						),
					);
				}
			}
			
			return array(
						200,
						array(
							'is_success'=>-1,
							'message'=>C('option_fail'),
						),
					);
		}
}
