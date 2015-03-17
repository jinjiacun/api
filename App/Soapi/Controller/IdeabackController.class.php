<?php
namespace Soapi\Controller;
use Soapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--意见反馈管理--
------------------------------------------------------------
function of api:

public function add
*/
class IdeabackController extends BaseController {
	/**
	 * sql script:
	 * create table so_idea_back(id int primary key auto_increment,
	                         content text comment '内容',
	                         pic int not null default 0 comment '图片',
	                         contact varchar(255) comment '联系方式',
	                         user_agent varchar(255) comment '来源',
	                         userip varchar(255) comment '用户ip',
	                         add_time int not null default 0 comment '添加日期'
	                         )charset=utf8;
	 * */
	 
	 protected $_module_name = 'Idea_back';
	 protected $id;
	 protected $content;//200字
	 protected $pic;
	 protected $contact;//联系方式
	 protected $user_agent;//来源
	 protected $userip;    //用户ip
	 
	 
	 public function add($contact)
	 /*
	  @input
	  @param $content 内容
	  @param $pic     图片
	  @param $contact 联系方式
	  @param $userip  用户ip
	  @output
	  @param is_success 0-成功,-1-失败
	  * */
	 {
		$data = $this->fill($contact); 
		if(!isset($data['content'])
		|| !isset($data['pic'])
		|| !isset($data['contact'])
		|| !isset($data['userip'])
		)
		{
			return C('param_err');
		}
		
		$data['content'] = htmlspecialchars(trim($data['content']));
		$data['contact'] = htmlspecialchars(trim($data['contact']));
		
		if('' == $data['content']
		|| '' == $data['contact']
		)
		{
			return C('param_fmt_err');
		}
		
		$data['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
		$data['add_time'] = time();
		$data['userip'] = '' == $data['userip']?$this->get_real_ip():$data['userip'];
		
		if(M($this->_module_name)->add($data))
		{
			return array(
				200,
				array(
					'is_success'=>0,
					'message'=>C('option_ok'),
				),
			);
		}
		
		return array(
				200,
				array(
					'is_success'=>-1,
					'message'=>C('option_fail'),
				),
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
						'content'     => urlencode($v['content']),
						'contact'     => $v['contact'],
						'pic'         => $v['pic'],
						'pic_url'     => $this->get_pic_url($v['pic']),
						'user_agent'  => $v['user_agent'],
						'userip'      => $v['userip'],
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
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
}
