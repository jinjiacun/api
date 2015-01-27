<?php
namespace Soapi\Controller;
use Soapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--企业新闻评论管理--
------------------------------------------------------------
function of api:


#添加企业新闻评论
public function add
@@input
@param $user_id;     #会员id
@param $nickname     #会员昵称
@param $company_id;  #企业id
@param $news_id;     #企业新闻id
@param $content;     #评论内容
@@output
@param $is_success 0-成功操作,-1-操作失败,-2-不允许操作
##--------------------------------------------------------##
public function get_list
##--------------------------------------------------------##
*/
class ComnewsController extends BaseController {
		/**
		* sql script:
		* create table so_com_news(id int primary key auto_increment,
	                              user_id int not null default 0 comment '用户id',
	                              company_id int not null default 0 comment '企业id',
	                              news_id int not null default 0 comment '企业新闻id',
	                              content text comment '评论内容',
	                              is_validate int not null default 0 comment '是否审核',
	                              validate_time int not null default 0  comment '审核时间',
	                              assist_num int not null default 0 comment '点赞数',
	                              add_time int not null default 0 comment '添加日期'
	                             )charset=utf8;
	 * */
	 
	 protected $_module_name = 'Com_news';
	 protected $id;             
	 protected $user_id;     #会员id
	 protected $company_id;  #企业id
	 protected $news_id;     #企业新闻id
	 protected $content;     #评论内容
	 protected $is_validate; #是否审核(0-未审核,1-已审核)
	 protected $assist_num;  #点赞数目
	 protected $add_time;    #添加日期
	 
	 
	 #添加企业新闻评论
	public function add($content)
	/*
	@@input
	@param $user_id;     #会员id
	@param $company_id;  #企业id
	@param $news_id;     #企业新闻id
	@param $content;     #评论内容
	@@output
	@param $is_success 0-成功操作,-1-操作失败,-2-不允许操作
	*/
	{
		$data = $this->fill($content);
		if(!isset($data['user_id'])
		|| !isset($data['company_id'])
		|| !isset($data['news_id'])
		|| !isset($data['content'])
		)
		{
			return C('param_err');
		}
		
		$data['user_id']    = intval($data['user_id']);
		$data['company_id'] = intval($data['company_id']);
		$data['news_id']    = intval($data['news_id']);
		$data['content']    = htmlspecialchars(trim($data['content']));

		if(0>= $data['user_id']
		|| 0>= $data['company_id']
		|| 0>= $data['news_id']
		|| '' == $data['content']
		)
		{
			return C('param_fmt_err');
		}
		
		/*
		if(!$this->__check(array('news_id'   => $data['news_id'],
								'company_id'=> $data['company_id'],
								'user_id'   => $data['user_id']
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
		*/
		
		$data['add_time'] = time();
		
		if(M($this->_module_name)->add($data))
		{
			return array(
				200,
				array(
					'is_success'=>0,
					'message'=>C('option_ok')
				),
			);
		}
		
		return array(
				200,
				array(
					'is_success'=>-1,
					'message'=>C('option_fail')
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
						'id'          => intval($v['id']),
						'user_id'     => intval($v['user_id']),
						'nickname'    => $this->_get_nickname($v['user_id']),
						'company_id'  => intval($v['company_id']),
						'news_id'     => intval($v['news_id']),
						'content'     => urlencode($v['content']),
						'is_validate' => intval($v['is_validate']),
						'assist_num'  => intval($v['assist_num']),
						'add_time'    => intval($v['add_time']),
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
}
