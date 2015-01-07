<?php
namespace Soapi\Controller;
use Api\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--评价管理--
------------------------------------------------------------
function of api:
 

#添加评价
public function add
@@input
@param $company_id; //企业id*
@param $parent_id;  //盖楼评论(默认0,盖楼为基层的id)
@param $type;       //评论类型(点赞、提问、加黑)*
@param $content;    //评论内容*
@param $expression; //评论表情
@param $pic_1;      //图片5张
@param $pic_2;
@param $pic_3;
@param $pic_4;
@param $pic_5;
@param $is_validate //是否审核
@param $add_time;   //添加日期
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
*/
class CommentController extends BaseController {
	/**
	 * sql script:
	 * create table so_comment(id int primary key auto_increment,
	                              user_id int not null default 0 comment '用户id',
	                              type    varchar(10) comment '类别',
	                              nature  varchar(10) comment '企业性质',
	                              trade   varchar(10) comment '所属行业',
	                              company_name varchar(255) comment '企业名称',
	                              corporation  varchar(255) comment '企业简介',
	  							  reg_address varchar(255) comment '注册地址',
	                              company_type varchar(255) comment '公司类型',
	                              busin_license int not null default 0 comment '营业执照',
	     						  code_certificate int not null default 0 comment '机构代码证',
	                              telephone varchar(255) comment '联系电话',
	                              amount varchar(255) comment '涉及金额',
	                              website varchar(255) comment '公司网址',
	                              record varchar(255) comment '官网备案',
	                              content text comment '曝光内容',
	                              pic_1 int not null default 0 comment '图片1',
	                              pic_2 int not null default 0 comment '图片2',
	                              pic_3 int not null default 0 comment '图片3',
	                              pic_4 int not null default 0 comment '图片4',
	                              pic_5 int not null default 0 comment '图片5',
	                              agent_platform varchar(255) comment '代理平台',
	                              mem_sn varchar(255) comment '会员编号',
	                              certificate int not null default 0 comment '资质证明',
	                              find_website varchar(255) comment 'find_website',
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
	
	
}
?>
