<?php
namespace api\Controller;
use Api\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--会员登录日志管理--
*/
class UserloginlogController extends BaseController {
	
	protected $_module_name = 'user';
	protected $id;                      #id
	protected $user_id;                 #用户id
	protected $user_name;               #用户名
	protected $login_time;              #登录时间
	protected $login_ip;                #登陆ip

	
}