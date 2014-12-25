<?php
namespace api\Controller;
use Think\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
class NewscatController extends BaseController {

	protected $_module_name = 'news_cat';
	protected $id;
	protected $parent_id;
	protected $is_show;   #是否显示
	protected $sort;      #排序
	protected $name;      #名称
	protected $add_time;  #添加日期
	
	/**
	 * create table yms_new_cat(id int, 
	 *                          parent_id int comment '父类id', 
	 *                          is_show int comment '是否显示',
	 *                          sort int comment '排序',
	 *                          name varchar(255) comment '名称',
	 *                          add_time int comment '添加日期'
	 *                         );
	 * */
}
