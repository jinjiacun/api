<?php
namespace Soapi\Controller;
use Api\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--评论管理--
------------------------------------------------------------
function of api:
 

#添加评价
public function add
@@input
@param $company_id //企业id*
@param $parent_id  //盖楼评论(默认0,盖楼为基层的id)
@param $type       //评论类型(点赞、提问、加黑)*
@param $content    //评论内容*
@param $expression //评论表情
@param $pic_1      //图片5张
@param $pic_2
@param $pic_3
@param $pic_4
@param $pic_5
@param $add_time   //添加日期
@@output
@param $is_success 0-成功操作,-1-操作失败
##--------------------------------------------------------##
#查询评价
public function get_list
@@input
@param $page_index         //当前页数(默认1)
@param $page_size          //页面大小(默认10)
@param $where              //里面是需要查询的条件(默认无条件)
@param $order              //里面需要排序的字段(默认id倒排序)
@@output
@param $company_id; //企业id
@param $parent_id;  //盖楼评论
@param $type;       //评论类型(点赞、提问、加黑)
@param $content;    //评论内容
@param protected $expression; //评论表情
@param protected $pic_1;      //图片5张
@param protected $pic_2;
@param protected $pic_3;
@param protected $pic_4;
@param protected $pic_5;
@param $is_validate           //是否审核
@param protected $add_time;   //添加日期
##--------------------------------------------------------##
#企业评论人数统计
public function stat_user_all_amount
@@input
@param $company_id 企业名称
@@output
@param $content 人数
##--------------------------------------------------------##
#审核评论
public function check
@@input
@param $id
@param $is_validate 审核结果(0-未通过,1-已通过)
@param $validate_time 审核时间
@@output
@param $is_success 0-操作成功,-1-操作失败
##--------------------------------------------------------##
*/
class CommentController extends BaseController {
	/**
	 * sql script:
	 * create table so_comment(id int primary key auto_increment,
	                              user_id int not null default 0 comment '用户id',
	                              company_id int not null default 0 comment '企业id',
	                              parent_id int not null default 0 comment '是否盖楼',
	                              type varchar(255) comment '评论类型',
	                              content text comment '评论内容',
	                              expression varchar(10) comment '评论表情',
	                              pic_1 int not null default 0 comment '图片1',
	                              pic_2 int not null default 0 comment '图片2',
	                              pic_3 int not null default 0 comment '图片3',
	                              pic_4 int not null default 0 comment '图片4',
	                              pic_5 int not null default 0 comment '图片5',
	                              is_validate int not null default 0 comment '是否审核',
	                              validate_time int not null default 0  comment '审核时间',
	                              add_time int not null default 0 comment '添加日期'
	                             )charset=utf8;
	 * */
	
	protected $_module_name = 'comment';
	
	protected $id;
	protected $user_id;
	protected $company_id; //企业id
	protected $parent_id;  //盖楼评论
	protected $type;       //评论类型(点赞、提问、加黑)
	protected $content;    //评论内容
	protected $expression; //评论表情
	protected $pic_1;      //图片5张
	protected $pic_2;
	protected $pic_3;
	protected $pic_4;
	protected $pic_5;
	protected $is_validate; //是否审核
	protected $add_time;    //添加日期
	
	#添加评价
	public function add($content)
	/*
	@@input
	@param $company_id //企业id*
	@param $parent_id  //盖楼评论(默认0,盖楼为基层的id)
	@param $type       //*评论类型(点赞、提问、加黑)*
	@param $content    //评论内容*
	@param $expression //评论表情
	@param $pic_1      //*图片5张
	@param $pic_2
	@param $pic_3
	@param $pic_4
	@param $pic_5
	@param $add_time   //添加日期
	@@output
	@param $is_success 0-成功操作,-1-操作失败
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['company_id'])
		|| !isset($data['type'])
		|| !isset($data['content'])
		|| !isset($data['pic_1'])
		)
		{
			return C('param_err');
		}
		
		$data['company_id'] = intval($data['company_id']);
		$data['type']       = htmlspecialchars(trim(($data['type']));
		$data['content']    = htmlspecialchars(trim(($data['content']));
		$data['pic_1']      = intval($data['pic_1']);
		
		if(0>= $data['company_id']
		|| ''==$data['type']
		|| ''==$data['content']
		|| 0>= $data['pic_1']
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
					'message'=> C('option_ok')
				),
			);
		}
		
		return array(
				200,
				array(
					'is_success'=>-1,
					'message'=> C('option_fail')
				),
			);
	}
	
	#企业评论人数统计
	public function stat_user_all_amount($content)
	/*
	@@input
	@param $company_id 企业名称
	@@output
	@param $content 人数
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['company_id']))
		{
			return C('param_err');
		}
		
		$data['company_id'] = intval($data['company_id']);
		
		if(0>= $data['company_id'])
		{
			return C('param_fmt_err');
		}
		
		$content = array(
			'company_id' => $data['company_id']
		);
		
		$re_count = 0;
		$re_count = M($this->_module_name)->where($content)->count();
		
		return array(
			200
		    $re_count
		);
	}
	
	#审核评论
	public function check($conte)
	/*
	@@input
	@param $id
	@param $is_validate 审核结果(0-未通过,1-已通过)
	@@output
	@param $is_success 0-操作成功,-1-操作失败
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['id'])
		|| !isset($data['is_validate'])
		)
		{
			return C('param_err');
		}
		
		$data['id'] = intval($data['id']);
		
		if(0>= $data['id']
		|| !in_array($data['is_validate'], array(0, 1))
		)
		{
			return C('param_fmt_err');
		}
		
		$content = array(
			'is_validate'   => $data['is_validate'],
			'validate_time' => time(),
		); 
		$where = array(
			'id' => $data['id']
		);
		
		if(M($this->_module_name)->where($where)->save($content))
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
					'message'=>C('option_ok'),
				)
			);
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}
?>
