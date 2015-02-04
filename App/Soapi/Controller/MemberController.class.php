<?php
namespace Soapi\Controller;
use Soapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--会员管理--
------------------------------------------------------------
function of api:

public function add
@@input
@param $user_id 会员id
@param $keyword 查询关键字
@@output
@param $is_success 0-操作成功,-1-操作失败
*/
class MemberController extends BaseController {
	/**
	 * sql script:
	 * create table so_member(id int primary key auto_increment,
	                             uid int not null default 0 comment '用户id',
	                             state int not null default 1 comment '1-未限制,0-关闭',
	                             ip varchar(255) comment '限制ip,空白',
	                             add_time int not null default 0 comment '添加日期'
	                             )charset=utf8;
	 * */
}
