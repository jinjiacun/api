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
@param $is_delete             //是否删除(0-未删除,1-已删除)
@param $ip                    //ip地址
@param protected $add_time;   //添加日期
##--------------------------------------------------------##
#查询评价(带两条回复)
public function get_list_ex
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
@param $is_delete             //是否删除(0-未删除,1-已删除)
@param $ip                    //ip地址
@param protected $add_time;   //添加日期
##--------------------------------------------------------##
#企业评论人数统计
public function stat_user_all_amount
@@input
@param $company_id 企业名称
@@output
@param $content 人数
##--------------------------------------------------------##
#通过id获取单条信息
public function get_info
@@input
@param $id
@@output
##--------------------------------------------------------##
#查询主评论
private function get_parent_content
@@input
@id
@@output
@content
##--------------------------------------------------------##
#审核
public function validate
@@input
@param $id
@@output
@param $is_success 0-成功操作,-1-操作失败
##--------------------------------------------------------##
#删除
public function delete
@@input
@param $id          
@param $company_id  企业id
@@output
@param $is_success 0-成功操作,-1-操作失败
##--------------------------------------------------------##
#更新评论人数统计
private function set_com_amount
@@input
@param $company_id
@@output
$param true, false
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
	                              is_delete int not null default 0 comment '0-未删除,1-已删除',
	                              ip varchar(255) comment 'ip地址',
	                              user_agent varchar(255) comment '来源',
	                              add_time int not null default 0 comment '添加日期'
	                             )charset=utf8;
	 * */
	
	protected $_module_name = 'Comment';
	
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
	protected $is_delete;
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
		$data['ip']       = $this->get_real_ip();
		$data['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
		/*
		$parent_id = intval($data['parent_id']);
		if(0== $parent_id)
		{
			//累计评论数
			if(A('Soapi/Company')->__top(array('id'=>$data['company_id']), 
											'com_amount'));
		}
		*/
								
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
	@param $is_delete             //是否删除(0-未删除,1-已删除)
	@param $ip                    //ip地址
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
						'parent_content' => urlencode($this->get_parent_content($v['parent_id'])),
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
						'validate_time'=> intval($v['validate_time']),
						'is_anonymous' => intval($v['is_anonymous']),
						'top_num'      => intval($v['top_num']),
						'is_delete'    => intval($v['is_delete']),
						'ip'           => $v['ip'],
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
	
	#查询评价(带两条回复)
	public function get_list_ex($content)
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
	@param protected $pic_1;      //图片5张
	@param protected $pic_2;
	@param protected $pic_3;
	@param protected $pic_4;
	@param protected $pic_5;
	@param $is_validate           //是否审核
	@param $is_anonymous          //是否匿名
	@param $top_num               //顶数
	@param $is_delete             //是否删除(0-未删除,1-已删除)
	@param $ip                    //ip地址
	@param protected $add_time;   //添加日期
	*/
	{
		$list         = array();
		$record_count = 0;
		
		list(,$old_list) = $this->get_list($content);
		$list = $old_list['list'];
		$record_count = $old_list['record_count'];
		
		$data = $this->fill($content);
		
		
		foreach($list as $k=> $v)
		{
			$data['where']['parent_id'] = intval($v['id']);
			$data['page_size'] = 2;
			$data['page_index'] = 1;
			list(, $sub) = $this->get_list(json_encode($data));
			$list[$k]['re_sub'] = array(
				'list'=>$sub['list'],
				'record_count'=>$sub['record_count']
			);
		}
		unset($k, $v);
		return array(
			200,
			array(
				'list'         =>$list,
				'record_count' =>$record_count
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
	
	#通过id获取单条信息
	public function get_info($content)
	/*
	@@input
	@param $id
	@@output
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
		$v = M($this->_module_name)->where($data)->find();
		if($v)
		{
			$list = array(
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
						'is_delete'    => intval($v['is_delete']),
						'ip'           => $v['ip'],
						'add_time'     => intval($v['add_time']),
					);	
		}
		
		return array(
			200,
			$list
		);
	}
	
	
	#审核
	public function validate($content)
	/*
	@@input
	@param id
	@param $company_id
	@@output
	@param $is_success 0-成功操作,-1-操作失败
	*/
	{
		$data = $this->fill($content);
		unset($content);
		if(!isset($data['company_id'])
		|| !isset($data['id'])
		)
		{
			return C('param_err');
		}
		
		$data['id'] = intval($data['id']);
		$data['company_id'] = intval($data['company_id']);
		
		if(0>= $data['id']
		|| 0>= $data['company_id']
		)
		{
			return C('param_fmt_err');
		}
		
		$content = array(
			'id'=>$data['id']
		);
		$company_id = $data['company_id'];
		unset($data);
		$data = array(
			'is_validate'=>1,
			'validate_time'=>time(),
		);
		if(M($this->_module_name)->where($content)->save($data))
		{
				//总数累计
				$this->set_com_amount($company_id);
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
	
	#审核
	public function validate_mul($content)
	/*
	@@input
	@param $id 数组
	@param $company_id 企业数组
	@@output
	@param $is_success 0-成功操作,-1-操作失败
	*/
	{
		$data = $this->fill($content);
		unset($content);
		if(!isset($data['id']))
		{
			return C('param_err');
		}
		
		$data['id'] = $data['id'];
		
		if(!is_array($data['id']))
		{
			return C('param_fmt_err');
		}
		
		$content = array(
			'id'=>array('in',implode(',',$data['id']))
		);
		$company_id = $data['company_id'];
		unset($data);
		$data = array(
			'is_validate'=>1,
			'validate_time'=>time(),
		);
		if(M($this->_module_name)->where($content)->save($data))
		{
			foreach($company_id as $v)
			{
				//审核评论时，记数
				$this->set_com_amount($v);
			}
			
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
	
	#删除
	public function delete($content)
	/*
	@@input
	@param $id
	@param $company_id
	@param $parent_id
	@param $is_validte
	@@output
	@param $is_success 0-成功操作,-1-操作失败
	*/
	{
		$data = $this->fill($content);
		if(!isset($data['id'])
		|| !isset($data['company_id'])
		|| !isset($data['is_validate'])
		)
		{
			return C('param_err');
		}
		
		$data['id'] = intval($data['id']);
		$data['company_id'] = intval($data['company_id']);
		$data['is_validate'] = intval($data['is_validate']);
		$data['parent_id']   = intval($data['parent_id']);
		
		if(0>= $data['id']
		|| 0>= $data['company_id']
		)
		{
			return C('param_fmt_err');
		}
		
		if(false !== M($this->_module_name)->where(array('id'=>$data['id']))
		                                   ->save(array('is_delete'=>1)))
		{
			//更新评论统计
			$this->set_com_amount($data['company_id'],-1, $data['is_validate']);
			//删除为主评论，还需删除回复
			if(0 < $data['parent_id'])
			{
				$list = M()->query("select id 
				                    from so_comment 
				                    where parent_id=$data[parent_id] 
				                    and company_id=$data[company_id]
				                    and is_delete=0
				                    ");
				if($list
				&& 0<count($list))
				{
					foreach($list as $v)
					{
						$this->set_com_amount($data['company_id'],-1, $data['is_validate']);
					}
				}
				//删除所有回复评论
				M($this->_module_name)->where(array('parent_id'=>$data['parent_id'],
				                                    'is_delete'=>0))->delete();
			}
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
	
	#更新评论人数统计
	private function set_com_amount($company_id, $sign=0, $is_validate=0)
	/*
	@@input
	@param $company_id
	@param $sign 默认0，-1为降
	@@output
	$param true, false
	*/
	{
		if(0>= $company_id)
			return false;
			
		//审核评论时，记数
		
		if(0 == $sign)
		{
			A('Soapi/Company')->__top(array('id'=>$company_id),'com_amount');
												
		}
		else if(-1== $sign && 1 == $is_validate)
		{
			A('Soapi/Company')->__down(array('id'=>$company_id),'com_amount');
		}										
		return true;
	}
	
	
	
	
	
	
	
	
	
	
	
	
}
?>
