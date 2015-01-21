<?php
namespace Soapi\Controller;
use Soapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--新闻点赞管理--
------------------------------------------------------------
function of api:
 

#添加
public function add
@@input
@param $news_id 新闻id
@param $user_id 用户id
@@output
@param $is_success 0-操作成功，-1-操作失败,-2-不允许操作
##--------------------------------------------------------##
*/
class NewsassistController extends BaseController {
	/**
	 * sql script:
	 * create table so_news_assist(id int primary key auto_increment,
	                              news_id int not null default 0 comment '新闻id',
	                              user_id int not null default 0 comment '用户id',
	                              add_time int not null default 0 comment '添加日期'
	                             )charset=utf8;
	 * */
	 
	 protected $_module_name = 'news_assist';
	 protected $news_id;       #新闻id
	 protected $user_id;       #用户id
	 
	 #添加
	 public function add($content)
	 /*
		@@input
		@param $news_id 新闻id
		@param $user_id 用户id
		@@output
		@param $is_success 0-操作成功，-1-操作失败,-2-不允许操作
	 */
	 {
		 $data = $this->fill($content);
		 
		 if(!isset($data['news_id'])
		 || !isset($data['user_id']))
		 {
			 return C('param_err');
		 }
		 
		 $data['news_id'] = intval($data['news_id']);
		 $data['user_id'] = intval($data['user_id']);
		 
		 if(0>= $data['news_id']
		 || 0>= $data['user_id'])
		 {
			 return C('param_fmt_err');
		 }
		 
		 
		 if(!$this->__check(array('news_id'=>$data['news_id'],
		                         'user_id'=>$data['user_id'])))
		 {
			 return array(
				200,
				array(
					'is_success'=>-2,
					'message'=>urlencode('不允许操作'),
				),
			 );
		 }
		 
		 $data['add_time'] = time();
		 
		 if(M($this->_module_name)->add($data))
		 {
			 //自动增加统计
			 $news_obj = A('Soapi/News');
			 if($news_obj->__assist(array('id'=>$data['news_id']),'assist_num'))
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
