<?php
namespace Soapi\Controller;
use Soapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--广告管理--
------------------------------------------------------------
function of api:
 

#添加广告
public function add
@@input
@param $title
@param $url
@param $pic
@@output
@param $is_success 0-操作成功，-1-操作失败
##--------------------------------------------------------##
public function get_list
*/
class AdController extends BaseController {
	/**
	 * sql script:
	 * create table so_ad(id int primary key auto_increment,
	                      title varchar(255) comment '标题',
                          url varchar(255) comment '链接',
                          pic int not null default 0 comment '图片',
						  intro varchar(255) comment '简介',
	                      add_time int not null default 0 comment '添加日期'
	                      )charset=utf8;
	 * */
	protected $_module_name = "ad";
	protected $id;
	protected $title;
	protected $url;
	protected $pic;
	protected $intro;
	protected $add_time;   #添加日期
	
	#添加加黑
	public function add($content)
	/*
	@@input
	@param $title
	@param $url
	@param $pic
	@param $intro
	@@output
	@param $is_success 0-操作成功，-1-操作失败，-2-此图片不存在
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['title'])
		|| !isset($data['pic'])
		|| !isset($data['intro']) 
		)
		{
			return C('param_err');
		}
		
	    $data['title'] = htmlspecialchars(trim($data['title']));
		$data['pic']   = intval($data['pic']);
        $data['intro'] = htmlspecialchars(trim($data['intro']));
		
		if('' == $data['title']
		|| 0>= $data['pic']
        || '' == $data['intro']
		)
		{
			return C('param_fmt_err');
		}
		
		//检查图片是否存在
		if(0<$data['pic'])
		{
			if(!M('Media')->find($data['pic']))
			{
				return array(
					200,
					array(
						'is_success' => -2,
						'message'    => urlencode('此图片不存在'),
					),
				);
			}
		}
			
		$data['add_time'] = time();
		
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
						'id'         => intval($v['id']),
					    'title'      => urlencode($v['title']),
	                    'url'        => urlencode($v['url']),
						'pic'        => intval($v['pic']),
                        'pic_url'    => $this->get_pic_url($v['pic']),
                        'intro'      => urlencode($v['intro']),
						'add_time'   => intval($v['add_time']),
						
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

	#通过id查询一条信息
	public function get_info($content)
	/*
	@@input
	@param $id
	@@output
	@param $id
	@param $title
	@param $url
	@param $pic
	@param $intro
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
				'title'       => urlencode($tmp_one['title']),
				'url'         => urlencode($tmp_one['url']),
				'pic'         => intval($tmp_one['pic']),
				'pic_url'     => $this->get_pic_url($tmp_one['pic']),
                'intro'       => urlencode($tmp_one['intro']),
				'add_time'    => intval($tmp_one['add_time']), 
			);
		}
		
		return array(
			200,
			$list
		);
	}

}
