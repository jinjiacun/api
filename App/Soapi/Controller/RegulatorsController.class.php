<?php
namespace Soapi\Controller;
use Soapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--监管机构管理--
------------------------------------------------------------
function of api:

#添加监管机构
public function add
@@input
@param $type     类型编号
@param $title    标题
@param $website  官网
@param $pic      图片
@param $content  内容
@@output
@param $is_success 0-操作成功,-1-操作失败
@param $id
##--------------------------------------------------------##
#按照id查询一条信息
public function get_info
@@input
@param $id
@@output
@param $id
@param $type     类型编号
@param $title    标题
@param $website  官网
@param $pic      图片
@param $content  内容
@param $add_time 添加日期
##--------------------------------------------------------##
#查询监管机构
public function get_list
@@input
@param $page_index         //当前页数(默认1)
@param $page_size          //页面大小(默认10)
@param $where              //里面是需要查询的条件(默认无条件)
@param $order              //里面需要排序的字段(默认id倒排序)
@@output
@param $id
@param $type     类型编号
@param $title    标题
@param $website  官网
@param $pic      图片
@param $content  内容
@param $add_time 添加日期
##--------------------------------------------------------##
*/
class RegulatorsController extends BaseController {
	/**
	 * sql script:
	 * create table so_regulators(id int primary key auto_increment,
	                              type int not null default 0 comment '类别(贵金属监管、外汇监管)',
	                              title varchar(255) comment '标题',
	                              website varchar(255) comment '官网',
	                              pic int not null default 0 comment '图片',
	                              content text comment '内容',
	                              add_time int comment '添加日期'
	                             )charset=utf8;
	 * */
	 protected $_module_name = 'regulators';
	 protected $id;
	 protected $type;     #类型
	 protected $title;    #标题
	 protected $website;  #官网
	 protected $pic;      #图片
	 protected $content;  #内容
	 protected $add_time; #添加日期
	 
	 #添加监管机构
	public function add($content)
	/*
	@@input
	@param $type     类型编号
	@param $title    标题
	@param $website  官网
	@param $pic      图片
	@param $content  内容
	@@output
	@param $is_success 0-操作成功,-1-操作失败
	@param $id
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['type'])
		|| !isset($data['title'])
		|| !isset($data['website'])
		|| !isset($data['pic'])
		|| !isset($data['content'])
		)
		{
			return C('param_err');
		}
		
		$data['type']    = htmlspecialchars(trim($data['type']));
		$data['title']   = htmlspecialchars(trim($data['title']));
		$data['website'] = htmlspecialchars(trim($data['website']));
		$data['pic']     = intval($data['pic']);
		$data['content'] = htmlspecialchars(trim($data['content']));
		
		if('' == $data['type']
		|| '' == $data['title']
		|| '' == $data['website']
		|| 0 >= $data['pic']
		|| '' == $data['content']
		)
		{
			return C('param_fmt_err');
		}
		
		$data['add_time'] = time();
		
		if(M('regulators')->add($data))
		{
			return array(
				200,
				array(
					'is_success'=>0,
					'message'   => C('option_ok'),
					'id'        => M()->getLastInsID(),
				),
			);
		}
		
		return array(
			200,
			array(
				'is_success'=>-1,
				'message'   => C('option_fail'),
			),
		);
	}
	
	#按照id查询一条信息
	public function get_info($content)
	/*
	@@input
	@param $id
	@@output
	@param $id
	@param $type     类型编号
	@param $title    标题
	@param $website  官网
	@param $pic      图片
	@param $content  内容
	@param $add_time 添加日期
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['id'])
		)
		{
			return C('param_err');
		}
		
		$data['id'] = intval($data['id']);
		
		if(0>= $data['id']
		)
		{
			return C('param_fmt_err');
		}
		
		$list = array();
		$tmp_one = M($this->_module_name)->find($data['id']);
		if($tmp_one)
		{
			$list = array(
			'id'      => intval($tmp_one['id']),
			'type'    => urlencode($tmp_one['type']),
			'title'   => urlencode($tmp_one['title']),
			'website' => $tmp_one['website'],
			'pic'     => intval($tmp_one['pic']),
			'content' => urlencode($tmp_one['content']),
			'add_time'=> intval($tmp_one['add_time']), 
			);
		}
		
		return array(
			200,
			$list
		);
	}
	
	#查询监管机构
	public function get_list($content)
	/*
	@@input
	@param $page_index         //当前页数(默认1)
	@param $page_size          //页面大小(默认10)
	@param $where              //里面是需要查询的条件(默认无条件)
	@param $order              //里面需要排序的字段(默认id倒排序)
	@@output
	@param $id
	@param $type     类型编号
	@param $title    标题
	@param $website  官网
	@param $pic      图片
	@param $content  内容
	@param $add_time 添加日期
	*/
	{
		 list($data, $record_count) = parent::get_list($content);
        
        $list = array();
        if($data)
        {
            foreach($data as $v)
            {
                $list[] = array(
                        'id'      => intval($v['id']),
						'type'    => urlencode($v['type']),
						'title'   => urlencode($v['title']),
						'website' => $v['website'],
						'pic'     => intval($v['pic']),
						'content' => urlencode($v['content']),
						'add_time'=> intval($v['add_time']), 
                    );  
            }
            unset($v);
        }

        return array(200, array('list'=>$list, 
                                'record_count'=>$record_count));
	}
}
