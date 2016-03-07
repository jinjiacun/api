<?php
namespace api\Controller;
use Think\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
class NewsController extends BaseController {

	protected $_module_name = 'news';
	protected $id;
	protected $cat_id;       #分类id
	protected $title;        #标题
	protected $source;       #来源
	protected $author;       #作者
	protected $intro;        #简介
	protected $content;      #内容
	protected $add_time;     #添加日期
	
	/**
	 * create table yms_news(id int, 
	 *                       cat_id int comment '新闻分类id', 
	 *                       title varchar(255) comment '新闻标题', 
	 *                       source varchar(100) comment '来源',
	 *                       author varchar(100) comment '作者',
	 *                       intro  varchar(50)  comment '简介',
	 *                       content text        comment '内容',
	 *                       add_time int        comment '添加日期'
	 *                      )charset=utf8;
	 * */
}
