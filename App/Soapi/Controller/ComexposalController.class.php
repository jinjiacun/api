<?php
namespace Soapi\Controller;
use  Soapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--曝光评论管理--
------------------------------------------------------------
function of api:

#添加
public function add
@@input
@param $user_id     会员id
@param $exposal_id  企业入库id
@param $parent_id   父类id(默认为0,当盖楼时为当前楼的评论id)
@param $content     内容
@@output
@param $is_success 0-操作成功,-1-操作失败
##--------------------------------------------------------##
public function get_list 
##--------------------------------------------------------##
*/
class ComexposalController extends BaseController {
	/**
	 * sql script:
	 * create table so_com_exposal(id int primary key auto_increment,
	                              user_id int not null default 0 comment '用户id',
	                              exposal_id int not null default 0 comment '入库企业id',
	                              parent_id int not null default 0 comment '是否盖楼',
	                              content text comment '内容',
	                              is_validate int not null default 0 comment '是否审核',
	                              validate_time int not null default 0  comment '审核时间',
	                              add_time int not null default 0 comment '添加日期'
	                             )charset=utf8;
	 * */
	 
	 protected $_module_name = 'Com_exposal';
	 protected $id;
	 protected $user_id;       #会员id
	 protected $exposal_id;    #入库id
	 protected $parent_id;     #父类id(默认为0,当盖楼时为当前楼的评论id)
	 protected $content;       #内容
	 protected $is_validate;   #是否审核(0-未审核,1-审核)
	 protected $validate_time; #审核日期
	 protected $top_num;       #顶的数目
	 protected $add_time;      #添加日期
	 
	 #添加
	public function add($content)
	/*
	@@input
	@param $user_id     会员id
	@param $exposal_id  企业入库id
	@param $parent_id   父类id(默认为0,当盖楼时为当前楼的评论id)
	@param $content     内容 
	@@output
	@param $is_success 0-操作成功,-1-操作失败,-2-不允许操作
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['user_id'])
		|| !isset($data['exposal_id'])
		|| !isset($data['content'])
		)
		{
			return C('param_err');
		}
		
		$data['user_id'] = intval($data['user_id']);
		$data['exposal_id'] = intval($data['exposal_id']);
		$data['content'] = htmlspecialchars(trim($data['content']));
		
		if(0>= $data['user_id']
		|| 0>= $data['exposal_id']
		|| '' == $data['content']
		)
		{
			return C('param_fmt_err');
		}
		
		/*
		if(!$this->__check(array('user_id'=>$data['user_id'],
		                         'exposal_id'=>$data['exposal_id'])))
		{
			return array(
				200,
				array(
					'is_success'=>-2,
					'message'=>C('option_no_allow'),
				)
			);
		}
		*/
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
						'id'           => intval($v['id']),
						'user_id'      => intval($v['user_id']),
						'nickname'     => $this->_get_nickname($v['user_id']),
						'exposal_id'   => intval($v['exposal_id']),
						'parent_id'    => intval($v['parent_id']),
						'parent_content' => urlencode($this->get_parent_content($v['parent_id'])),
						'content'      => urlencode($v['content']),
						'is_validate'  => intval($v['is_validate']),
						'validate_time'=> intval($v['validate_time']),
						'top_num'      => intval($v['top_num']),
						'add_time'     => intval($v['add_time']),
						
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
