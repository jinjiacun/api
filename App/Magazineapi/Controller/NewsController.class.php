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
	                              sign int not null default 0 comment '正负面新闻(0-正面)',
	                              title varchar(255) comment '标题',
	                              source varchar(255) comment '来源',
	                              author varchar(255) comment '作者',
	                              content text comment '内容',
	                              pic int not null default 0 comment '配图(pc)',
	                              assist_num int not null default 0 comment '点赞数', 
	                              show_time int not null default 0 comment '显示日期',
	                              add_time int not null default 0 comment '添加日期'
	                             )charset=utf8;
	 * */

	protected $_module_name = 'news';
	protected $id;
	protected $company_id;
	protected $sign;
	protected $title;
	protected $source;
	protected $author;
	protected $content;
	protected $pic;
	protected $assist_num;
	protected $show_time;
	protected $add_time;

	#添加
	public function add($content)
	/*
	@@input
	@param $company_id  企业id
	@param $sign        正负面
	@param $title       标题
	@param $source      来源
	@param $author      作者
	@param $resume      摘要
	@param $content     内容
	@param $pic         图片
	@@output
	@param $is_success 0-操作成功，-1-操作失败
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['title'])
		//|| !isset($data['source'])
		//|| !isset($data['author'])
		|| !isset($data['content'])
		|| !isset($data['pic'])
		|| !isset($data['show_time'])
		)
		{
			return C('param_err');
		}
		
		$data['title']   = htmlspecialchars(trim($data['title']));
		//$data['source']  = htmlspecialchars(trim($data['source']));
		//$data['author']  = htmlspecialchars(trim($data['author']));
		$data['content'] = htmlspecialchars(trim($data['content']));
		$data['pic']     = intval($data['pic']);
		$data['show_time'] = intval($data['show_time']);
		
		if('' == $data['title']
		//|| '' == $data['source']
		//|| '' == $data['author']
		|| '' == $data['content']
		|| 0  >= $data['pic']
		|| 0  >= $data['show_time']
		)
		{
			return C('param_fmt_err');
		}
		
		$data['add_time'] = time();
		
		#判定是否负面新闻:begin
		$_push_has_validate = false;
		if(0< $data['company_id']
		&& 1 == $data['sign'])
		{
			$_push_has_validate = true;
		}
		#判定是否负面新闻:end
		
		if(M($this->_module_name)->add($data))
		{
			$id = M()->getLastInsID();
			
			if($_push_has_validate)
			{
				$_tempalte_param = C('push_event_type');
					$src_event_param = $_tempalte_param['company_news']['src_event_param'];
					$src_event_param = str_replace("<COMPANY_ID>", $data['company_id'], $src_event_param);
					$src_event_param = str_replace("<NEWS_ID>", $id, $src_event_param);
					A('Soapi/Pushmessage')->push_event('010004', $src_event_param, sprintf("负面新闻 %s", $data['title']));
			}
			
			return array(
				200,
				array(
					'is_success'=>0,
					'message'=>C('option_ok'),
					'id'=> $id,
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
						'sign'        => intval($v['sign']),
						'title'       => urlencode($v['title']),
						'source'      => urlencode($v['source']),
						'author'      => urlencode($v['author']),
						'content'     => urlencode($v['content']),
						'pic'         => intval($v['pic']),
						'pic_url'     => $this->get_pic_url($v['pic']),
						'assist_num'  => intval($v['assist_num']),
						'show_time'   => intval($v['show_time']),
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
	@param $company_id 企业id
	@param $sign     正负面
	@param $title    标题
	@param $source   来源
	@param $author   作者
	@param $resume   摘要
	@param $content  内容
	@param $pic      图片
	@param $assist_num 点赞数
	@param $show_time 显示时间
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
				'sign'        => intval($tmp_one['sign']),
				'title'       => urlencode($tmp_one['title']),
				'source'      => urlencode($tmp_one['source']),
				'author'      => urlencode($tmp_one['author']),
				//'content'  => stripslashes(htmlspecialchars_decode(urlencode($tmp_one['content']))),
				'content'     => urlencode(htmlspecialchars_decode($tmp_one['content'])),
				'pic'         => intval($tmp_one['pic']),
				'pic_url'     => $this->get_pic_url($tmp_one['pic']),
				'assist_num'  => intval($tmp_one['assist_num']),
				'show_time'   => intval($tmp_one['show_time']),
				'add_time'    => intval($tmp_one['add_time']), 
			);
		}
		
		return array(
			200,
			$list
		);
	}

	#修改	
		public function update($content)
		/**
		@@input
		@param $where 条件
		@param $data  要更新的数据
		@@output
		@param $is_success 0-成功操作，-1-操作失败
		*/
		{
			$data = $this->fill($content);

			$content = json_encode($data);
			#判定是否从正面:begin
			$has_validate = false;
			if(1 == $data['data']['sign'])
			{
					$tmp_info = M($this->_module_name)->field('sign')->find($data['where']['id']);
					if(0 == $tmp_info['sign'])
					{
						$has_validate = true;
					}
			}
			
			
			#判定是否从正面:end			
			list($status_code,$r_content) = parent::update($content);
			$data = $this->fill($content);
			if(500 == $status_code)
			{
				return array(
					$status_code,
					$r_content
				);
			}
			
			
			if(200 == $status_code
			&& 0 == $r_content['is_success'])
			{
				#新闻从正面到负面:begin
				if($_push_has_validate)
				{
						$_tempalte_param = C('push_event_type');
						$src_event_param = $_tempalte_param['company_news']['src_event_param'];
						$src_event_param = str_replace("<COMPANY_ID>", $data['data']['company_id'], $src_event_param);
						$src_event_param = str_replace("<NEWS_ID>", $data['where']['id'], $src_event_param);
						A('Soapi/Pushmessage')->push_event('010004', $src_event_param, sprintf("负面新闻 %s", $data['title']));
				}
				#新闻从正面到负面:end
					
				return array(
					$status_code,
					$r_content
				);
			}
			
			return array(
					$status_code,
					$r_content
			);
		}



























}
