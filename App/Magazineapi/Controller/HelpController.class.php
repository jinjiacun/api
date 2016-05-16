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
####杂志列表
public function magazine_list
####我的杂志列表
public function mine_magazine_list
####我的文章列表
public function mine_article_list
####我的资讯列表
public function mine_info_list
####我的帖子列表
public function mine_letter_list
##--------------------------------------------------------##
*/
class Helpcontroller extends BaseController {
	/**
	 * sql script:
	 * 
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


	 ####杂志列表
	 public function magazine_list()
	 {
	 	$list = array(
			'list'=>array(
				array(
					'year'=>2016,
					'title'=>'1月刊',
					'img'=>C('media_url_pre').'media/article/p1.png',
					),
				array(
					'year'=>2016,
					'title'=>'2月刊',
					'img'=>C('media_url_pre').'media/article/p1.png',
					),
				array(
					'year'=>2016,
					'title'=>'3月刊',
					'img'=>C('media_url_pre').'media/article/p1.png',
					),
				array(
					'year'=>2016,
					'title'=>'4月刊',
					'img'=>C('media_url_pre').'media/article/p1.png',
					),
				array(
					'year'=>2016,
					'title'=>'5月刊',
					'img'=>C('media_url_pre').'media/article/p1.png',
					),
				array(
					'year'=>2016,
					'title'=>'6月刊',
					'img'=>C('media_url_pre').'media/article/p1.png',
					),
				array(
					'year'=>2016,
					'title'=>'2月刊',
					'img'=>C('media_url_pre').'media/article/p1.png',
					),
				array(
					'year'=>2016,
					'title'=>'2月刊',
					'img'=>C('media_url_pre').'media/article/p1.png',
					),
				),
			'record_count'=>8,
		);
		return array(
				200,
				$list
		);
	 }
	 
	 ####我的杂志列表
	public function mine_magazine_list()
	{
		$list = array(
			'list'=>array(
				array(
					'year'=>2016,
					'title'=>'1月刊',
					'img'=>C('media_url_pre').'media/article/p1.png',
					),
				array(
					'year'=>2016,
					'title'=>'2月刊',
					'img'=>C('media_url_pre').'media/article/p1.png',
					),
				array(
					'year'=>2016,
					'title'=>'3月刊',
					'img'=>C('media_url_pre').'media/article/p1.png',
					),
				array(
					'year'=>2016,
					'title'=>'4月刊',
					'img'=>C('media_url_pre').'media/article/p1.png',
					),
				),
			'record_count'=>4,
		);
		return array(
				200,
				$list
		);
	}

	####我的文章列表
	public function mine_article_list()
	{
		$list = array(
			'list'=>array(
				array(
					'title'=>'文章1',
					'img'=>C('media_url_pre').'media/article/p1.png',
					),
				array(
					'title'=>'文章2',
					'img'=>C('media_url_pre').'media/article/p1.png',
					),
				array(
					'title'=>'文章3',
					'img'=>C('media_url_pre').'media/article/p1.png',
					),
				array(
					'title'=>'文章4',
					'img'=>C('media_url_pre').'media/article/p1.png',
					),
				),
			'record_count'=>4,
		);
		return array(
				200,
				$list
		);
	}

	####我的资讯列表
	public function mine_info_list()
	{
		'list'=>array(
				array(
					'title'=>'资讯1',
					'img'=>C('media_url_pre').'media/article/p1.png',
					),
				array(
					'title'=>'资讯2',
					'img'=>C('media_url_pre').'media/article/p1.png',
					),
				array(
					'title'=>'资讯3',
					'img'=>C('media_url_pre').'media/article/p1.png',
					),
				array(
					'title'=>'资讯4',
					'img'=>C('media_url_pre').'media/article/p1.png',
					),
				),
			'record_count'=>4,
	}

	####我的帖子列表
	public function mine_letter_list()
	{

	}
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
}
