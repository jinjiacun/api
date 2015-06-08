<?php
namespace Soapi\Controller;
use  Soapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--曝光评论顶纪录管理--
------------------------------------------------------------
function of api:

#添加
public function add
@@input
@param $user_id     用户id
@param $exposal_id  入库企业id
@@output
@param $is_success 0-操作成功,-1-操作失败,-2-不允许操作
##--------------------------------------------------------##
public function get_list 
##--------------------------------------------------------##
*/
class ExposaltopController extends BaseController {
	/**
	 * sql script:
	 * create table so_exposal_top(id int primary key auto_increment,
	                              user_id int not null default 0 comment '用户id',
	                              exposal_id int not null default 0 comment '入库企业id',
	                              add_time int not null default 0 comment '添加日期'
	                             )charset=utf8;
	 * */
	 
	 protected $_module_name = 'Exposal_top';
	 protected $id;
	 protected $user_id;    #用户id
	 protected $exposal_id; #入库企业id
	 protected $add_time;
	 
	#添加
	public function add($content)
	/*
	@@input
	@param $user_id     用户id
	@param $exposal_id  入库企业id
	@@output
	@param $is_success 0-操作成功,-1-操作失败,-2-不允许操作
	*/
	{
		$data = $this->fill($content);
		if(!isset($data['user_id'])
		|| !isset($data['exposal_id'])
		)
		{
			return C('param_err');
		}
		
		$data['user_id'] = intval($data['user_id']);
		$data['exposal_id'] = intval($data['exposal_id']);
		
		if(0>= $data['user_id']
		|| 0>= $data['exposal_id']
		)
		{
			return C('param_fmt_err');
		}
		
		if(!$this->__check(array('exposal_id'=>$data['exposal_id'],
		                         'user_id'=>$data['user_id'])))
		{
			return array(
				200,
				array(
					'is_success'=>-2,
					'message'=>C('option_no_allow'),
				)
			);
		}
		
		//检查曝光是否存在
		if(!M('In_exposal')->find($data['exposal_id']))
		{
			return array(
				200,
				array(
					'is_success'=>-3,
					'message'=>urlencode('此曝光不存在'),
				)
			);
		}
		
		//检查曝光是否删除
		if(M('In_exposal')->where(array(
								'id' => $data['exposal_id'],
								'is_delete' => 1,
								))
		                  ->find())
		{
			return array(
				200,
				array(
					'is_success'=>-4,
					'message'=>urlencode('此曝光已删除'),
				)
			);
		}
		
		$tmp_info = M('In_exposal')->filed('company_id')
		                           ->find($data['exposal_id']);
		$company_id = $tmp_info['company_id'];
		//检查曝光企业是否存在
		if(!M('Company')->find($company_id))
		{
			return array(
				200,
				array(
					'is_success'=>-5,
					'message'=>urlencode('此企业不存在'),
				)
			);
		}
		
		$data['add_time'] = time();
		
		if(M($this->_module_name)->add($data))
		{
			$com_exposal = A('Soapi/Inexposal');
			if($com_exposal->__top(array(
								'id'=>$data['exposal_id']
									),'top_num'))
			{
				$tmp_param = array(
					'id'=>$data['exposal_id'],
				);
				list(,$tmp_content) = A('Soapi/Inexposal')->get_info(json_encode($tmp_param));
				return array(
					200,
					array(
						'is_success'=>0,
						'message'=>C('option_ok'),
						'amount'=>$tmp_content['top_num'],
					),
				);
			}
		}
		
		return array(
				200,
				array(
					'is_success'=>-1,
					'message'=>C('option_fail'),
				),
			);
	}
}
