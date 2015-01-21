<?php
namespace Soapi\Controller;
use Soapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--新闻管理--
------------------------------------------------------------
function of api:
 

#添加
public function add
@@input
@param $title   标题
@param $source  来源
@param $author  作者
@param $content 内容
@param $pic     图片
@param $assist_num 点赞数
@@output
@param $is_success 0-操作成功，-1-操作失败
##--------------------------------------------------------##
#查询
public function get_list
##--------------------------------------------------------##
#查询企业新闻映射
public function get_id_name_map
@@input
@@output
格式[{'id':'name'},...,{}]
##--------------------------------------------------------##
#通过id查询一条信息
public function get_info
@@input
@param $id
@@output
@param $id
@param $title      标题
@param $source     来源
@param $author     作者
@param $content    内容
@param $pic        图片
@param $assist_num 点赞数
@param $add_time
##--------------------------------------------------------##
#新闻点赞
public function __assist
@@input
@news_id
@@output
@param true-成功, false-失败
##--------------------------------------------------------##
*/
class NewsController extends BaseController {
	/**
	 * sql script:
	 * create table so_news(id int primary key auto_increment,
	                              company_id int not null default 0 comment '企业id(为0时，是系统新闻)',
	                              title varchar(255) comment '标题',
	                              source varchar(255) comment '来源',
	                              author varchar(255) comment '作者',
	                              content text comment '内容',
	                              pic int not null default 0 comment '配图(pc)',
	                              pic_app int not null default 0 comment '配图(app)',
	                              assist_num int not null default 0 comment '点赞数', 
	                              add_time int not null default 0 comment '添加日期'
	                             )charset=utf8;
	 * */

	protected $_module_name = 'news';
	protected $id;
	protected $company_id;
	protected $title;
	protected $source;
	protected $author;
	protected $content;
	protected $pic;
	protected $pic_app;
	protected $assist_num;
	protected $add_time;

	#添加
	public function add($content)
	/*
	@@input
	@param $company_id  企业id
	@param $title       标题
	@param $source      来源
	@param $author      作者
	@param $content     内容
	@param $pic         图片
	@@output
	@param $is_success 0-操作成功，-1-操作失败
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['title'])
		|| !isset($data['source'])
		|| !isset($data['author'])
		|| !isset($data['content'])
		|| !isset($data['pic'])
		)
		{
			return C('param_err');
		}
		
		$data['title']   = htmlspecialchars(trim($data['title']));
		$data['source']  = htmlspecialchars(trim($data['source']));
		$data['author']  = htmlspecialchars(trim($data['author']));
		$data['content'] = htmlspecialchars(trim($data['content']));
		$data['pic']     = intval($data['pic']);
		
		if('' == $data['title']
		|| '' == $data['source']
		|| '' == $data['author']
		|| '' == $data['content']
		|| 0  >= $data['pic']
		)
		{
			return C('param_fmt_err');
		}
		
		$data['add_time'] = time();
		
		if(M($this->_module_name)->add($data))
		{
			return array(
				200,
				array(
					'is_success'=>0,
					'message'=>C('option_ok'),
					'id'=> M()->getLastInsID(),
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
						'company_id'  => intval($v['company_id']),
						'title'       => urlencode($v['title']),
						'source'      => urlencode($v['source']),
						'author'      => urlencode($v['author']),
						'content'     => urlencode($v['content']),
						'pic'         => intval($v['pic']),
						'pic_url'     => $this->get_pic_url($v['pic']),
						'pic_app'     => intval($v['pic_app']),
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
	
	#查询企业新闻映射
	public function get_id_name_map($content)
	/*
	@@input
	@@output
	*/
	{
		$list = array();
		
		unset($content);
		
		$content['company_id'] = array('neq',0);
		$tmp_list = M($this->_module_name)->field("id,title")
		                   ->where($content)->select();
		if($tmp_list
		&& 0< count($tmp_list))
		{
			foreach($tmp_list as $v)
			{
				$list[intval($v['id'])] = urlencode($v['title']);
			}
		}
		
		return array(
			200,
			$list
		);
	}

	#通过id查询一条信息
	public function get_info($content)
	/*
	@@input
	@param $id
	@@output
	@param $id
	@param $title    标题
	@param $source   来源
	@param $author   作者
	@param $content  内容
	@param $pic      图片
	@param $assist_num 点赞数
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
		$tmp_one = M($this->_module_name)->find($data['id']);
		if($tmp_one)
		{
			$list = array(
				'id'          => intval($tmp_one['id']),
				'company_id'  => intval($tmp_one['company_id']),
				'title'       => urlencode($tmp_one['title']),
				'source'      => urlencode($tmp_one['source']),
				'author'      => urlencode($tmp_one['author']), 
				//'content'  => stripslashes(htmlspecialchars_decode(urlencode($tmp_one['content']))),
				'content'     => urlencode(htmlspecialchars_decode($tmp_one['content'])),
				'pic'         => intval($tmp_one['pic']),
				'pic_url'     => $this->get_pic_url($tmp_one['pic']),
				'pic_app'     => intval($tmp_one['pic_app']),
				'assist_num'  => intval($tmp_one['assist_num']),
				'add_time'    => intval($tmp_one['add_time']), 
			);
		}
		
		return array(
			200,
			$list
		);
	}





























}
