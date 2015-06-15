<?php
namespace Soapi\Controller;
use  Soapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--曝光评论顶管理--
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
class ComexposaltopController extends BaseController {
	/**
	 * sql script:
	 * create table so_com_exposal_top(id int primary key auto_increment,
	                                   company_id int not null default 0 comment '新闻id',
	                                   exposal_id int not null default 0 comment '曝光id',
	                                   comment_id int not null default 0 comment '曝光评论id',
	                                   user_id int not null default 0 comment '用户id',	                                   
	                                   add_time int not null default 0 comment '添加日期'
	                                   )charset=utf8;
	 * */
	 protected $_module_name = 'Com_exposal_top';
	 protected $exposal_id;       #曝光id
	 protected $company_id;       #企业id
	 protected $user_id;          #用户id
	 
	 #添加
	 public function add($content)
	 /*
		@@input
		@param $company_id 企业id
		@param $exposal_id 曝光id
		@param $comment_id 曝光回复id
		@param $user_id 用户id
		@@output
		@param $is_success 0-操作成功，-1-操作失败,-2-不允许操作,-3-此曝光不存在 ,-4-此曝光已删除 ,-5-此企业不存在 ,-6-此回复不存在 ,-7-此回复已删除
	 */
	 {
		 $data = $this->fill($content);
		 
		 if(!isset($data['company_id'])
		 || !isset($data['exposal_id'])
		 || !isset($data['comment_id'])
		 || !isset($data['user_id']))
		 {
			 return C('param_err');
		 }
		 
		 $data['company_id'] = intval($data['company_id']);
		 $data['exposal_id'] = intval($data['exposal_id']);
		 $data['comment_id'] = intval($data['comment_id']);
		 $data['user_id'] = intval($data['user_id']);
		 
		 if(0>= $data['company_id']
		 || 0>= $data['exposal_id']
		 || 0>= $data['user_id'])
		 {
			 return C('param_fmt_err');
		 }
		 
		 
		 if(!$this->__check(array('company_id'=>$data['company_id'],
		                          'exposal_id'=>$data['exposal_id'],
		                          'comment_id'=>$data['comment_id'],
		                         'user_id'=>$data['user_id'])))
		 {
			 return array(
				200,
				array(
					'is_success'=>-2,
					'message'=>urlencode('不允许操作'),
				),
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
				),
			 );
		 }
		 
		 //检查曝光是否删除
		 if(M('In_exposal')->where(array(
									'id'       => $data['exposal_id'],
									'is_delete'=>1,
								))
		                    ->find())
		 {
			 return array(
				200,
				array(
					'is_success'=>-4,
					'message'=>urlencode('此曝光已删除'),
				),
			 );
		 }
		 
		 //检查企业
		 if(!M('Company')->find($data['company_id']))
		 {
			 return array(
				200,
				array(
					'is_success'=>-5,
					'message'=>urlencode('此企业不存在'),
				),
			 );
		 }
		 
		 //检查评论是否存在
		 if(!M('Com_exposal')->find($data['comment_id']))
		 {
			 return array(
				200,
				array(
					'is_success'=>-6,
					'message'=>urlencode('此回复不存在'),
				),
			 );
		 }
		 
		 //检查评论是否删除
		 if(M('Com_exposal')->where(array(
									'id'=>$data['comment_id'],
									'is_delete'=>1,
								))
		                    ->find())
		 {
			 return array(
				200,
				array(
					'is_success'=>-7,
					'message'=>urlencode('此回复已删除'),
				),
			 );
		 }
		 
		 
		 $data['add_time'] = time();
		 
		 if(M($this->_module_name)->add($data))
		 {
			 //自动增加统计
			 $news_obj = A('Soapi/Comexposal');
			 if($news_obj->__assist(array('id'=>$data['comment_id']),'top_num'))
			 {
				$tmp_param = array(
					'id'=> $data['comment_id'],
				);
				list(,$tmp_content) = A('Soapi/Comexposal')->get_info(json_encode($tmp_param));
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
	 
	 
	 #获取顶总数
	public function amount($content)
	/*
	@@input
	@param $comment_id 曝光评论id
	@@output
	@param $is_success 0-成功,-1-失败
	@param $amount  总数
	*/
	{
		$data = $this->fill($content);
			
		if(!isset($data['exposal_id']))
		{
			return C('param_err');
		}
		
		$data['exposal_id'] = intval($data['exposal_id']);
		
		if(0>= $data['exposal_id'])
		{
			return C('param_fmt_err');
		}
		
		$amount = 0;
		
		$amount = M($this->_module_name)->where($data)->count();
		
		return array(
			200,
			array(
				'is_success'=>0,
				'message'=>C('option_ok'),
				'amount'=>$amount,
			)
		);
	}
}
