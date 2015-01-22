<?php
namespace Soapi\Controller;
use  Soapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--评论管理--
------------------------------------------------------------
function of api:


#添加评价
public function add
@@input
@param $user_id         //会员id
@param $company_id      //企业id*
@param $parent_id       //盖楼评论(默认0,盖楼为基层的id)
@param $type            //评论类型(点赞、提问、加黑)*
@param $content         //评论内容*
@param $pic_1           //图片5张
@param $pic_2
@param $pic_3
@param $pic_4
@param $pic_5
@param $is_anonymous   //是否匿名
@param $add_time       //添加日期
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
@param $id
@param $user_id     //会员id
@param $nickname    //会员昵称
@param $company_id; //企业id
@param $parent_id;  //盖楼评论
@param $type;       //评论类型(点赞、提问、加黑)
@param $content;    //评论内容
@param protected $pic_1;      //图片5张
@param protected $pic_2;
@param protected $pic_3;
@param protected $pic_4;
@param protected $pic_5;
@param $is_validate           //是否审核
@param $is_anonymous          //是否匿名
@param $top_num               //顶数
@param protected $add_time;   //添加日期
##--------------------------------------------------------##
#企业评论人数统计
public function stat_user_all_amount
@@input
@param $company_id 企业名称
@@output
@param $content 人数
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
	                              pic_1 int not null default 0 comment '图片1',
	                              pic_2 int not null default 0 comment '图片2',
	                              pic_3 int not null default 0 comment '图片3',
	                              pic_4 int not null default 0 comment '图片4',
	                              pic_5 int not null default 0 comment '图片5',
	                              is_validate int not null default 0 comment '是否审核',
	                              validate_time int not null default 0  comment '审核时间',
	                              is_anonymous int not null default 0 comment '是否匿名,默认0,不匿名',
	                              top_num int not null default 0 comment '顶的数目',
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
	@param $user_id    //会员id
	@param $company_id //企业id*
	@param $parent_id  //盖楼评论(默认0,盖楼为基层的id)
	@param $type       //*评论类型(点赞、提问、加黑)*
	@param $content    //评论内容*
	@param $pic_1      //*图片5张
	@param $pic_2
	@param $pic_3
	@param $pic_4
	@param $pic_5
	@param $is_anonymous   //*是否匿名
	@param $add_time   //添加日期
	@@output
	@param $is_success 0-成功操作,-1-操作失败
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['user_id'])
		|| !isset($data['company_id'])
		|| !isset($data['type'])
		|| !isset($data['content'])
		)
		{
			return C('param_err');
		}
		
		$data['user_id']      = intval($data['user_id']);
		$data['company_id']   = intval($data['company_id']);
		$data['type']         = htmlspecialchars(trim($data['type']));
		$data['content']      = htmlspecialchars(trim($data['content']));
		$data['is_anonymous'] = intval($data['is_anonymous']);
		
		
		if(0>= $data['user_id']
		|| 0>= $data['company_id']
		|| ''==$data['type']
		|| ''==$data['content']
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
					'message'=> C('option_ok'),
					'id'=> M()->getLastInsID(),
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
	
	#查询评价
	public function get_list($content)
	/*
	@@input
	@param $page_index         //当前页数(默认1)
	@param $page_size          //页面大小(默认10)
	@param $where              //里面是需要查询的条件(默认无条件)
	@param $order              //里面需要排序的字段(默认id倒排序)
	@@output
	@param $id
	@param $user_id     //会员id
	@param $nickname    //会员昵称
	@param $company_id; //企业id
	@param $parent_id;  //盖楼评论
	@param $type;       //评论类型(点赞、提问、加黑)
	@param $content;    //评论内容
	@param $pic_1;      //图片5张
	@param $pic_2;
	@param $pic_3;
	@param $pic_4;
	@param $pic_5;
	@param $is_validate           //是否审核
	@param $is_anonymous          //是否匿名
	@param $top_num               //顶数
	@param $add_time;   //添加日期
	*/
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
						'company_id'   => intval($v['company_id']),
						'parent_id'    => intval($v['parent_id']),
						'type'         => $v['type'],
						'content'      => urlencode($v['content']),
						'pic_1'        => intval($v['pic_1']),
						'pic_1_url'    => $this->get_pic_url($v['pic_1']),
						'pic_2'        => intval($v['pic_2']),
						'pic_2_url'    => $this->get_pic_url($v['pic_2']),
						'pic_3'        => intval($v['pic_3']),
						'pic_3_url'    => $this->get_pic_url($v['pic_3']),
						'pic_4'        => intval($v['pic_4']),
						'pic_4_url'    => $this->get_pic_url($v['pic_4']),
						'pic_5'        => intval($v['pic_5']),
						'pic_5_url'    => $this->get_pic_url($v['pic_5']),
						'is_validate'  => intval($v['is_validate']),
						'is_anonymous' => intval($v['is_anonymous']),
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
		$re_count = M($this->_module_name)->distinct('user_id')
		                                  ->where($content)
		                                  ->count();
		
		return array(
			200,
		    $re_count
		);
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}
?>
