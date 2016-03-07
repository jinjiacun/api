<?php
namespace Magazineapi\Controller;
use Magazineapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--文章管理--
------------------------------------------------------------
function of api:
*/
class ArticleController extends BaseController {
	/**
	 * sql script:
	 * create table so_article(id int primary key auto_increment,
	                         title varchar(255) comment '标题',
	                         author varchar(255) comment '作者',
	                         description varchar(255) comment '摘要',
	                         content text comment '内容',
	                         add_time int not null default 0 comment '添加日期'
	                         )charset=utf8;
	 * */
	 
	 protected $_module_name = 'article';
	 protected $id;         
	 protected $title;
	 protected $author;
	 protected $description;
	 protected $content;
	 protected $add_time;
	 
	 #添加
	 public function add($content)
	 /*
	 @@input
     @param string $title
     @param string $author
     @param string $description
     @param string $content
	 @@output
	 @param $is_success 0-成功,-1-失败
	 */
	 {
		$data = $this->fill($content);
		
		if(!isset($data['title'])
		|| !isset($data['author'])
		|| !isset($data['description'])
		|| !isset($data['content'])
		)
		{
			return C('param_err');
		}
	
		if('' == $data['title']
		|| '' == $data['author']
		|| '' == $data['description']
		|| '' == $data['content']
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
				)
			);
		}
	
		return array(
				200,
				array(
					'is_success'=>-1,
					'message'=>C('option_fail'),
				)
			);
	 }
	 
		#通过id查询单条
		public function get_info($content)
		/*
		@@input
		@param $id    文章id
		@@output
		@param $id;                          文章id
		@param $title;                      标题
		@param $author;                作者
		@param $description;	   摘要
	    @param  $content;             内容
		@param  $add_time          	添加日期
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
						'id'                    => intval($tmp_one['id']),
					    'title'                => urlencode($tmp_one['title']),
						'author'          => urlencode($tmp_one['author']),
						'description'  => urlencode($tmp_one['description']),
						'content'        => urlencode($tmp_one['content']),
						'add_time'      => intval($tmp_one['add_time']),
				);
			}
		
			return array(
				200,
				$list
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
							'id'                	  => intval($v['id']),
							'title'                => urlencode($v['title']),
							'author'          => urlencode($v['author']),
							'description'  => urlencode($v['description']),
							'content'        => urlencode($v['content']),
							'add_time'      => intval($v['add_time']),							
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

