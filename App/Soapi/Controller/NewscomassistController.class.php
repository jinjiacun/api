<?php
namespace Soapi\Controller;
use Soapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
function of api:
 

--新闻评论点赞管理--
------------------------------------------------------------
#添加新闻评论点咱
public function add
@@input
@param $news_id      新闻id
@param $comment_id   企业新闻评论id
@param $user_id      用户id
@@output
@param $is_success 0-成功，-1-失败
##--------------------------------------------------------##
#检查
public function check
@@input
@param $news_id
@@output
@param true-允许, false-不允许
##--------------------------------------------------------##
*/
class NewscomassistController extends BaseController {
	/**
	 * sql script:
	 * create table so_news_com_assist(id int primary key auto_increment,
	                              news_id int not null default 0 comment '新闻id',
	                              comment_id int not null default 0 comment '新闻评论id',
	                              user_id int not null default 0 comment '用户id',
	                              add_time int not null default 0 comment '添加日期'
	                             )charset=utf8;
	 * */
	 
	 
}
