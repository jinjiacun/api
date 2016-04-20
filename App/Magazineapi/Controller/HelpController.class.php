<?php
namespace Magazineapi\Controller;
use Magazineapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--辅助管理--
------------------------------------------------------------
function of api:
##----首页----
####首页切换图片(5张图片)
public function home_five_img_url
####下拉文章(最多十个文章)
public function home_ten_article

##--------------------------------------------------------##
*/
class Helpcontroller extends BaseController {
	/**
	 * sql script:
	 * create table so_user(id int primary key auto_increment,
	                         name varchar(255) comment '名称',
	                         passwd varchar(255) comment '密码',
	                         nickname varchar(255) comment '昵称',
	                         sex int not null default 0 comment '性别(0-男,1-女)',
	                         last_time int not null default 0 comment '最后登录日期',
	                         last_ip varchar(255) comment '最后登录ip',
	                         add_time int not null default 0 comment '添加日期'
	                         )charset=utf8;
	 * */
	 
	 protected $_module_name = 'user';
	 protected $id;

	public function home_five_img_url($content)
	{
		$list = array(
			'list'=>array(
				C('media_url_pre')."media/ad/item01.png",
				C('media_url_pre')."media/ad/item02.png",
				C('media_url_pre')."media/ad/item03.png",
				C('media_url_pre')."media/ad/item04.png",
				C('media_url_pre')."media/ad/item05.png",
				),
			'record_count'=>5,
			);
		return array(
				200,
				$list
			);
	}

	public function home_ten_article($content)
	{
		$list = array(
			'list'=>array(
				array(
					'title'=>'1月26日',
					'content'=>'一绘视频|发现身边被忽略的美',
					'p1'=>C('media_url_pre').'media/article/p1.png',
					'p2'=>C('media_url_pre').'media/article/p1_1.png',
					),
				array(
					'title'=>'1月25日',
					'content'=>'精选|爱她就为她打造一座爱的城堡',
					'p1'=>C('media_url_pre').'media/article/p2.png',
					'p2'=>C('media_url_pre').'media/article/p2_1.png',
					),
				),
			'record_count'=>2,
		);
		return array(
				200,
				$list
			);
	}
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
}
