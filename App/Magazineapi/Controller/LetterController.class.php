<?php
namespace Magazineapi\Controller;
use Magazineapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--帖子管理--
------------------------------------------------------------
function of api:
*/
class LetterController extends BaseController {
	/**
	 * sql script:
	 * create table so_letter(id int primary key auto_increment,
	                         article_id int not null default 0 comment '文章id',
	                         title varchar(255) comment '标题',	
	                         content text comment '内容',
	                         `create` int not null default 0 comment '提交者',
	                         add_time int not null default 0 comment '添加日期'
	                         )charset=utf8;
	 * */
	 
	 protected $_module_name = 'letter';
	 protected $id;
	 protected $article_id;
	 protected $title;
	 protected $content;
	 protected $create;
	 protected $add_time;
	 
	 #添加
	 public function add($content)
	 /*
	 @@input
	 @param int       $article_id
     @param string $title
     @param string $content
     @param int       $create
	 @@output
	 @param $is_success 0-成功,-1-失败
	 */
	 {
		$data = $this->fill($content);
		
		if(!isset($data['article_id'])
		|| !isset($data['title'])
		|| !isset($data['content'])
		|| !isset($data['create'])
		)
		{
			return C('param_err');
		}
	
		if(0> $data['article_id']
		|| '' == $data['title']
		|| '' == $data['content']
		|| 0> $data['create']
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
		@param $id    帖子id
		@@output
		@param $id;                          帖子id
		@param $article_id;            文章id
		@param $title;                      标题
		@param $content;              内容
		@param $create;                  作者
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
					   'article_id'       => intval($tmp_one['article_id']), 
						'title'               =>  urlencode($tmp_one['title']),
						'content'       =>  urlencode($tmp_one['content']),
						'create'           => intval($tmp_one['create']),
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
							'article_id'       => intval($v['article_id']), 
							'title'               =>  urlencode($v['title']),
							'content'       =>  urlencode($v['content']),
							'create'           => intval($v['create']),
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


