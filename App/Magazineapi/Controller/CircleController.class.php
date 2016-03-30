<?php
namespace Magazineapi\Controller;
use Magazineapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
-- 圈子管理--
------------------------------------------------------------
function of api:
*/
class CircleController extends BaseController {
	/**
	 * sql script:
	 * create table so_circle(id int primary key auto_increment,
	                         ico int not null default 0 comment '图标',
	                         title varchar(255) comment '标题',
	                         content varchar(255) comment '内容',
	                         image1 int not null default 0 comment '图片1',
	                         image2 int not null default 0 comment '图片2',
	                         image3 int not null default 0 comment '图片3',
	                         image4 int not null default 0 comment '图片4',
	                         message_number int not null default 0 comment '消息数',
	                         add_time int not null default 0 comment '添加日期'
	                         )charset=utf8;
	 * */
	 
	 protected $_module_name = 'circle;
	 protected $id;  
	 protected $ico;       
	 protected $title;
	 protected $content;
	 protected $image1;
	 protected $image2;
	 protected $image3;
	 protected $image4;
	 protected $message_number;
	 protected $add_time;
	 
	 #添加
	 public function add($content)
	 /*
	 @@input
     	@param string $ico
     	@param string $title
     	@param string $content
     	@param string $image1
     	@param string $image2
     	@param string $image3
     	@param string $image4
     	@param int      $message_number
	 @@output
	 @param $is_success 0-成功,-1-失败
	 */
	 {
		$data = $this->fill($content);
		
		if(!isset($data['ico'])
		|| !isset($data['title'])
		|| !isset($data['content'])
		|| !isset($data['image1'])
		|| !isset($data['image2'])
		|| !isset($data['image3'])
		|| !isset($data['image4'])
		|| !isset($data['message_number'])
		)
		{
			return C('param_err');
		}
	
		if(0 > $data['ico']
		|| '' == $data['title']		
		|| '' == $data['content']
		|| 0 > $data['image1']
		|| 0 > $data['image2']
		|| 0 > $data['image3']
		|| 0 > $data['image4']
		|| '' == $data['message_number]
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
		@param $id    		   id
		@@output
		@param $id                          	id
	    	@param $ico                        	图标
		@param $title                      	标题
		@param $content              	简要描述
		@param $image1                	图片1
		@param $image2                	图片2
		@param $image3                	图片3
		@param $image4                          	图片4
		@param $message_number	消息数目
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
						'id'                    		=> intval($tmp_one['id']),
					  	 'ico'	          		=> intval($tmp_one['ico']),
						 'title'	          		=> intval($tmp_one['title']),
						 'content'        		=> intval($tmp_one['content']),
						 'image1'         		=> intval($tmp_one['image1']),
						 'image2'         		=> intval($tmp_one['image2']),
						 'image3'         		=> intval($tmp_one['image3']),
						 'image4'        		=> intval($tmp_one['image4']),
						 'message_number'	=> intval($tmp_one['meaage_number']),
						'add_time'      		=> intval($tmp_one['add_time']),
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
							'id'                	  	=> intval($v['id']),
							 'ico'	          		=> intval($v['ico']),
							 'title'	          		=> intval($v['title']),
							 'content'        		=> intval($v['content']),
							 'image1'         		=> intval($v['image1']),
							 'image2'         		=> intval($v['image2']),
							 'image3'         		=> intval($v['image3']),
							 'image4'        		=> intval($v['image4']),
							 'message_number'	=> intval($v['meaage_number']),
							'add_time'      		=> intval($v['add_time']),							
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

