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
#统计未审核数目
public function stat_no_validate
@@input
@@output
@param $amount
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
#批量删除
public function delete_mul
@@input
@param $id 数组
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
#查询是否有回复
private function has_child
@@input
@param $id
@@output
@param 0 -有 ，1-没有
##--------------------------------------------------------##
#触发评论中是否有未审核的回复
public function stat_re_comment
@@output
@param $is_success 0-操作成功,-1-操作失败
##--------------------------------------------------------##
#判定是否有祖父,存在返回对应的id
private function check_exists_pparent_id
@@input
@param $parent_id 当前父类id
@@output
@param $pprent_id 祖父id
##--------------------------------------------------------##
#恢复
public function recover
@@input
@param $id 当前id
@@output
@param $is_success 0-操作成功,-1-操作失败
##--------------------------------------------------------##
#统计审核通过的回复数量
public function update_re_child_amount
@@input
@param $id 评论id
@@output
@param $is_success 0-操作成功，-1-操作失败
##--------------------------------------------------------##
#更新最新审核通过的评论的最新时间和用户id
public function update_v_last
@@input
@param $id 评论id
*/
class CommentController extends BaseController {
	/**
	 * sql script:
	 * create table so_comment(id int primary key auto_increment,
	                              user_id int not null default 0 comment '用户id',
	                              company_id int not null default 0 comment '企业id',
								  auth_level varchar(10) comment '企业认证等级',
	                              parent_id int not null default 0 comment '是否盖楼',
	                              pparent_id int not null default 0 comment '祖父id',
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
	                              childs int not null default 0 comment '未审核的回复数量',
	                              has_child int not null default 0 comment '已审核的子回复数',
                                  last_child_time int not null default 0 comment '最新回复评论时间',
                                  last_cchild_time int not null default 0 comment '最新再回复时间',
                                  last_time int not null default 0 comment '最新回复或者再回复时间或者当前时间',
								  last_user_id int not null default 0 comment '最新回复或者再回复用户或者当前用户id'
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
		
	    $now = time();		
        $data['last_time'] = $now;
        $user_id = $data['user_id'];
		$data['last_user_id'] = $user_id;
		$data['add_time'] = $now;
		$data['ip']       = $this->get_real_ip();
		$data['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
		#查询企业等级
		$tmp_info = M('Company')->field('auth_level')->find($data['company_id']);
		$data['auth_level'] = $tmp_info['auth_level'];
		unset($tmp_info);
		/*
		$parent_id = intval($data['parent_id']);
		if(0== $parent_id)
		{
			//累计评论数
			if(A('Soapi/Company')->__top(array('id'=>$data['company_id']), 
											'com_amount'));
		}
		*/
		
		//判定是否是三级分类
		$pparent_id = 0;
		if(0< $data['parent_id'])
		{
			$pparent_id = $this->check_exists_pparent_id($data['parent_id']);
		}
		$data['pparent_id'] = $pparent_id;
							
		if(M($this->_module_name)->add($data))
		{
			//评论的回复，则改变父评论未审核childs数为1
			if(0< $data['parent_id'])
			{
				M($this->_module_name)->where(array('id'=>$data['parent_id']))->setInc('childs', 1);
                //更新父评论里面最新的子评论时间
                M($this->_module_name)->where(array('id'=>$data['parent_id']))->save(array('last_child_time'=>$now));
                M($this->_module_name)->where(array('id'=>$data['parent_id']))->save(array('last_time'=>$now));
                M($this->_module_name)->where(array('id'=>$data['parent_id']))->save(array('last_user_id'=>$user_id));
				//判定是否第三层
				if(0< $data['pparent_id'])
				{
					M($this->_module_name)->where(array('id'=>$data['pparent_id']))->setInc('childs', 1);
                    //更新祖父评论里面最新评论的再回复时间
                    M($this->_module_name)->where(array('id'=>$data['pparent_id']))->save(array('last_cchild_time'=>$now));
                    M($this->_module_name)->where(array('id'=>$data['pparent_id']))->save(array('last_time'=>$now));
                    M($this->_module_name)->where(array('id'=>$data['pparent_id']))->save(array('last_user_id'=>$user_id));  
				}
			}
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
						'company_name' => A('Soapi/Company')->get_name_by_id($v['company_id']),
						'parent_id'    => intval($v['parent_id']),
                                                'pparent_id'   => intval($v['pparent_id']),
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
						'has_child_ex' => $this->has_child($v['id']),
						'has_child'    => intval($v['has_child']),
						'last_time'    => intval($v['last_time']),
						'last_user_id' => intval($v['last_user_id']),
						'last_nickname'=> $this->_get_nickname($v['last_user_id']),
						'v_last_time'    => intval($v['v_last_time']),
						'v_last_user_id' => intval($v['v_last_user_id']),
						'v_last_nickname'=> $this->_get_nickname($v['v_last_user_id']),
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
		$is_validate = $data['where']['_complex']['is_validate'];
		
        if(!isset($data['where_ex']))
		{
			if(0 == $data['where']['parent_id'])
			{
				//登录-全部
				if(0 < $data['user_id']
				&& isset($data['where']['_string']))
				{
					$param_user_id = '';
					$param_is_validate = '';
					$param_type = '';
					$_string = $data['where']['_string'];			
					$tmp_str = explode('or', $_string);
					$param_user_id = trim($tmp_str[0]);
					$tmp_str = explode('and', $tmp_str[1]);
					$param_is_validate = str_replace('(','',$tmp_str[0]);
					$param_type = trim($tmp_str[1]);
					if(isset($param_type)&& '' != $param_type)
					{
						$data['where']['_string'] = $param_user_id.' or '.$param_is_validate.'';
					}
				}
				else
				{
					if(isset($data['where']['_string']))
						unset($data['where']['_string']);
				}
			}
		}
		else
		{
			$data['where'] = $data['where_ex'];
		}
		
		if(!isset($data['where_ex']))
		{
			if(isset($data['where']['has_child'])) unset($data['where']['has_child']);
        	if(isset($data['where']['type'])) unset($data['where']['type']);
		}		

		foreach($list as $k=> $v)
		{
			if(!isset($data['where_ex']))
			{
				if(isset($data['where']['_complex'])) 
				{
					    //if(-10000 == $data['user_id'])
				   		//{
							$data['page_size'] = 10;
							$data['page_index'] = 1;
							if(0 == $is_validate)
							{
								//$data['where']['is_validate'] = $data['where']['_complex']['is_validate'];					
								$where['is_validate'] = 0;
								$where['childs']  = array('gt', 0);
								$where['_logic'] = 'or';
								$data['where']['_complex'] = $where;
							}
							else
							{
								$data['where']['is_validate'] = $data['where']['_complex']['is_validate'];
							}
						/*
						$data['where']['is_validate'] = $data['where']['_complex']['is_validate'];					
						$where['is_validate'] = 0;
						$where['childs']  = array('gt', 0);
						//$where['pparent_id'] = array('gt', 0);
						*/
						
						//unset($data['where']['_complex']);
					//}
					//else
					//{
						
					//}
				}
				else
				{
						$data['page_size'] = 2;
						$data['page_index'] = 1;
						if(isset($data['where']['pic_1']))unset($data['where']['pic_1']);					
				}
				$data['where']['parent_id'] = intval($v['id']);
			}
			else
			{
						$data['where']['parent_id']	= intval($v['id']);
						$data['page_size'] = 2;
						$data['page_index'] = 1;
			}
			
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
	
	#统计未审核数目
	public function stat_no_validate($content)
	/*
	@@input
	@@output
	@param $amount
	*/
	{
		$amount = 0;
		$where = array(
			'is_validate'=>0,
			'is_delete'=>0,
		);
		
		$amount = M($this->_module_name)->where($where)->count();
		$amount = intval($amount);
		return array(
			200,
			array(
				'amount'=>$amount,
			)
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
	@param $is_success 0-成功操作,-1-操作失败,-2-不允许审核(父级还未审核)
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
		$id = $data['id'];
		unset($data);
		$data = array(
			'is_validate'=>1,
			'validate_time'=>time(),
		);
		
		//检查父类是否审核
		$t_one = M($this->_module_name)
		         ->field('pparent_id,parent_id')
		         ->where(array('id'=>$id))->find();
		if(0< $t_one['pparent_id'])
		{
			if(!$this->__check_exists(array(
									'id'=>$t_one['pparent_id'],
									'is_validate'=>0)
									))
			{
				return array(
					200,
					array(
						'is_success'=>-2,
						'message'=>urlencode('不允许审核(父父级未审核)'),
					)
				);
			}			
		}
		if(0< $t_one['parent_id'])
		{
			if(!$this->__check_exists(array(
									'id'=>$t_one['parent_id'],
									'is_validate'=>0)
									))
			{
				return array(
					200,
					array(
						'is_success'=>-2,
						'message'=>urlencode('不允许审核(父级未审核)'),
					)
				);
			}			
		}
		
		
		
		//检查这条信息是否审核
		if(!$this->__check_exists(array(
		                              "id"=>$id,
		                              'is_validate'=>1)
		                              ))
		{
			return array(
					200,
					array(
						'is_success'=>0,
						'message'=>C('option_ok'),
					)
				);
		}
		if(false !== M($this->_module_name)->where($content)->save($data))
		{
				//总数累计
				$this->set_com_amount($company_id);
				//统计子回复总数
				$this->update_re_child_amount(json_encode(array('id'=>$id)));
				$tmp_content = M($this->_module_name)->field('pparent_id,parent_id')->where(array('id'=>$id))->find();
				//$this->get_info(json_encode(array('id'=>$id)));
				//统计父评论数
				if(0< $tmp_content['parent_id'])
				{
					$this->update_re_child_amount(json_encode(array('id'=>$tmp_content['parent_id'])));
					//减少父评论未审核子回复数
					M($this->_module_name)->where(array('id'=>$tmp_content['parent_id']))->setDec("childs", 1);
					//减少第一层未审核回复数
					if(0 < $tmp_content['pparent_id'])
					{
						M($this->_module_name)->where(array('id'=>$tmp_content['pparent_id']))->setDec("childs", 1);
					}
				}	
				#统计最新审核的评论时间和user_id(当前或者回复或者再回复的)
				$this->update_v_last($id);
				
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
		$company_id = array_unique($data['company_id']);
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
			unset($v);
			
			foreach($data['id'] as $v)
			{
				//统计子回复总数
				$this->update_re_child_amount(json_encode(array('id'=>$v)));
				list(,$tmp_content) = $this->get_info(json_encode(array('id'=>$v)));
				//统计父评论数
				if(0< $tmp_content['parent_id'])
					$this->update_re_child_amount(json_encode(array('id'=>$tmp_content['parent_id'])));
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
	@param $is_validate
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
			//总数累计
			$this->set_com_amount($data['company_id']);
			//统计子回复总数
			$this->update_re_child_amount(json_encode(array('id'=>$data['id'])));
			list(,$tmp_content) = $this->get_info(json_encode(array('id'=>$data['id'])));
			//统计父评论数
			if(0< $tmp_content['parent_id'])
				$this->update_re_child_amount(json_encode(array('id'=>$tmp_content['parent_id'])));
			//删除为主评论，还需删除回复
			/*
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
						//删除三级回复
						M($this->_module_name)
						->where(array('parent_id'=>$v['id']))
						->save(array('is_delete'=>0));
					}
				}
				//删除所有回复评论
				M($this->_module_name)
				->where(array('parent_id'=>$data['parent_id']))
				->save(array('is_delete'=>0));
				//统计评论
				$this->set_com_amount($data['company_id'],-1, $data['is_validate']);
			}
			*/
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
	
	#批量删除
	public function delete_mul($content)
	/*
	@@input
	@param $id 数组
	@@output
	@param $is_success 0-成功操作,-1-操作失败
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['id']))
		{
			return C('param_err');
		}
		
		if(!is_array($data['id']))
		{
			return C('param_fmt_err');
		}
		
		foreach($data['id'] as $v)
		{
			#查询一条信息
			$param = array(
				'id'=>$v
			);
			list(,$info) = $this->get_info(json_encode($param));
			unset($param);
			if(0<count($info))
			{
				$param = array(
					'id'         => $info['id'],
					'company_id' => $info['company_id'],
					'parent_id'  => $info['parent_id'],
					'is_validate' => $info['is_validate'],
				);
				list(,$content) = $this->delete(json_encode($param));
				unset($param);
				if(-1 == $content['is_success'])
				{
					return array(
							200,
							array(
								'is_success'=>0,
								'message'=>C('option_ok'),
							),
					);
				}
			}
			unset($info);
		}
		unset($v, $data['id']);
		
		return array(
			200,
			array(
				'is_success'=>0,
				'message'=>C('option_ok'),
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
		
		//动态统计评论数
		$comment_amount = M($this->_module_name)
		                  ->where(array(
								'company_id'=>$company_id,
								'is_delete'=>0,
								'is_validate'=>1,
		                  ))
		                  ->count();
		if(false == M('Company')
		            ->where(array('id'=>$company_id))
		            ->save(array('com_amount'=>$comment_amount)))
		{
			return false;
		}
		
		/*
		if(0 == $sign)
		{
			//A('Soapi/Company')->__top(array('id'=>$company_id),'com_amount');
												
		}
		else if(-1== $sign && 1 == $is_validate)
		{
			//A('Soapi/Company')->__down(array('id'=>$company_id),'com_amount');
		}
		*/										
		return true;
	}
	
	
	#查询是否有回复
	private function has_child($id)
	/*
	@@input
	@param $id
	@@output
	@param 0 -有 ，1-没有
	*/
	{
		if(0>= $id)
			return -1;
			
		$where['parent_id'] = $id;
		
		$tmp_amount = M($this->_module_name)->where($where)->count();		
		if(0 == $tmp_amount)
			return 1;
		
		return 0;
	}
	
	#触发评论中是否有未审核的回复
	public function stat_re_comment($content)
	/*
	@@output
	@param $is_success 0-操作成功,-1-操作失败
	*/
	{
		/*
		//查询所有的主评论
		$tmp_list = M($this->_module_name)
		            ->field("id")
		            ->where(array("parent_id"=>0,
		                          "is_delete"=>0))
		            ->select();
		if($tmp_list
		&& 0<count($tmp_list))
		{
			foreach($tmp_list as $v)
			{
				$tmp_count = 0;
				$tmp_count = M($this->_module_name)
				->where(array('parent_id'=>$v['id'],
						      'is_validate'=>0,
				             ))->count();
				$tmp_count = intval($tmp_count);
				M($this->_module_name)->where(array("id"=>$v["id"]))->save(array("childs"=>$tmp_count));
			}
			unset($v, $tmp_list);
		}
		
		
		
		
		return array(
			200,
			array(
				'is_success'=>0,
				'message'=>C('option_ok'),
			)
		);
		*/
	}
	
	#判定是否有祖父,存在返回对应的id
	private function check_exists_pparent_id($parent_id)
	/*
	@@input
	@param $parent_id 当前父类id
	@@output
	@param 祖父id
	*/
	{
		if(0>= $parent_id)
		{
			return 0;
		}
		
		$param = array(
			'id'=>$parent_id
		);
		$re_back = M($this->_module_name)
		              ->field("parent_id")
		              ->where($param)->find();
		return $re_back['parent_id'];
	}	
	
	
	#恢复
	public function recover($content)
	/*
	@@input
	@param $id 当前id
	@@output
	@param $is_success 0-操作成功,-1-操作失败
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
		
		$data['is_delete'] = 0;
		if(false !== M($this->_module_name)->where(array('id'=>$data['id']))->save(array('is_delete'=>0)))
		{
			list(,$tmp_content) = $this->get_info(json_encode(array('id'=>$data['id'])));			
			//总数累计
			$this->set_com_amount($tmp_content['company_id']);
			//统计子回复总数
			$this->update_re_child_amount(json_encode(array('id'=>$data['id'])));			
			//统计父评论数
			if(0< $tmp_content['parent_id'])
				$this->update_re_child_amount(json_encode(array('id'=>$tmp_content['parent_id'])));
				
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
					'is_success'=>0,
					'message'=>C('option_fail'),
				)
		);
	}

	#统计审核通过的回复数量
	public function update_re_child_amount($content)
	/*
	@@input
	@param $id 评论id
	@@output
	@param $is_success 0-操作成功，-1-操作失败
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
		
		$amount = 0;
		$param = array(
			'parent_id'=>$data['id'],
			'is_delete'=>0,
			'is_validate'=>1
		);
		$amount = M($this->_module_name)->where($param)->count();
		unset($param);
		$amount = intval($amount);
		
		$param = array(
			'has_child'=>$amount
		);		
		if(false !== M($this->_module_name)
		             ->where(array('id'=>$data['id']))
		             ->save($param))
		{
			return array(
				200,
				array(
					'is_success'=>0,
					'message'=>C('option_ok')
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

	#更新最新审核通过的评论的最新时间和用户id
	private function update_v_last($id)
	{
		#查询当前评论
		$comment_info = M($this->_module_name)->find($id);
		$v_last_time = $v_last_user_id = 0;
		$mast_comment_id = 0;
		

		#如果是主评论		
		if(0 == $comment_info['parent_id'])
		{
			$mast_comment_id = $comment_info['id'];
			$v_last_time     = $comment_info['add_time'];
			$v_last_user_id  = $comment_info['user_id'];
		}
		#如果是回复
		elseif(0 == $comment_info['pparent_id']
		&& 0 != $comment_info['parent_id'])
		{
			$mast_comment_id = $comment_info['parent_id'];
			$tmp_param = array(
				'is_validate'=>1,
				'is_delete'=>0,
				'_string'=>"parent_id=$mast_comment_id or pparent_id=$mast_comment_id",
			);
			$tmp_info = M($this->_module_name)->where($tmp_param)->order(array('add_time'=>'desc'))->find();
			$v_last_time    = $tmp_info['add_time'];
			$v_last_user_id = $tmp_info['user_id']; 
		}
		#如果是再回复
		elseif(0 != $comment_info['parent_id']
		&& 0 != $comment_info['pparent_id'])
		{
			$mast_comment_id = $comment_info['pparent_id'];
			$tmp_param = array(
				'is_validate'=>1,
				'is_delete'=>0,
				'_string'=>"parent_id=$mast_comment_id or pparent_id=$mast_comment_id",
			);
			$tmp_info = M($this->_module_name)->where($tmp_param)->order(array('add_time'=>'desc'))->find();
			$v_last_time    = $tmp_info['add_time'];
			$v_last_user_id = $tmp_info['user_id']; 
		}

		#更新主评论最新审核的时间和用户id(主评论或者回复或者再回复)
		if(false !== M($this->_module_name)->where(array('id'=>$mast_comment_id))
		                                   ->save(array('v_last_time'=>$v_last_time,'v_last_user_id'=>$v_last_user_id)))
		{
			return true;
		}
		
		return false;
	}




























}
?>
