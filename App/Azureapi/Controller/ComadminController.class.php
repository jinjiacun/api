<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--管理员管理--
------------------------------------------------------------
function of api:
------------------------------------------------------------
*/
class ComadminController extends BaseController {
  /**
   * sql script:
    create table comadmin(AdminId int primary key auto_increment,
                        AdminName   varchar(50),
                        Password text,
                        AdminUserName varchar(100),
                        ComId int,
                        RoleId int,
                        AuthNo varchar(100),
                        Adavatar text,
                        CreatTime timestamp,
                        UpTime timestamp,
                        LoginIp varchar(20),
                        LoginTime timestamp,
                        CreateAdminId int
                 )charset=utf8;
   * */

   protected $_module_name = 'comadmin';

   protected $AdminId;
   protected $Password;
   protected $AdminUserName;
   protected $ComId;
   protected $RoleId;
   protected $AuthNo;
   protected $Adavatar;
   protected $CreatTime;
   protected $UpTime;
   protected $LoginIp;
   protected $LoginTime;
   protected $CreateAdminId;

  #登录
  /**
  *@@input
  *@param $AdminName  用户名/昵称/手机号码
  *@param $Password   密码
  */
  public function login($content)
  {
    $data = $this->fill($content);
    if(!isset($data['AdminName'])
    || !isset($data['Password'])
    )
    {
      return C('param_err');
    }

    $data['AdminName'] = htmlspecialchars(trim($data['AdminName']));
    $data['Password']  = htmlspecialchars(trim($data['Password']));

    if('' == $data['AdminName']
    || '' == $data['Password']
    )
    {
      return C('param_fmt_err');
    }

    $where = array(
          'AdminName'=>$data['AdminName'],
          'Password'=>md5($data['Password'])
      );
    $tmp_one = M('Comadmin')->where($where)->find();
    if($tmp_one)
    {
      $user_info = $this->do_getuserinfo_by_username($data['user_name']);
      session('user_name',   $data['user_name']);
      session('user_id',     $user_info['id']);
      session('nick_name',   $user_info['nick_name']);
      session('user_mobile', $user_info['user_mobile']);
      return array(
        200,
        array(
          'is_success'=>0,
                    'message'=>urlencode('成功登录'),
          ),
      );
    }

    #昵称登录
    $where = array(
        'nick_name' => $data['user_name'],
        'password'  => md5($data['password'])
    );
    $tmp_one = M('User')->where($where)->find();
    if($tmp_one)
    {
      $user_info = $this->do_getuserinfo_by_nickname($data['user_name']);
      session('user_name',   $data['user_name']);
      session('user_id',     $user_info['id']);
      session('nick_name',   $user_info['nick_name']);
      session('user_mobile', $user_info['user_mobile']);
      return array(
        200,
        array(
          'is_success'=>0,
                    'message'=>urlencode('成功登录'),
          ),
      );
    }

    #手机号码登录
    $where = array(
        'mobile'   => $data['user_name'],
        'password' => md5($data['password'])
    );
    $tmp_one = M('User')->where($where)->find();
    if($tmp_one)
    {
      $user_info = $this->do_getuserinfo_by_mobile($data['user_name']);
      session('AdminName',   $data['AdminName']);
      session('AdminId',     $user_info['AdminId']);
      session('nick_name',   $user_info['nick_name']);
      session('user_mobile', $user_info['user_mobile']);
      return array(
        200,
        array(
          'is_success'=>0,
                    'message'=>urlencode('成功登录'),
          ),
      );
    }

    return array(
        200,
        array(
          'is_success'=>-1,
          'message'=>urlencode('登录失败'),
          ),
      );
  }

  public function do_getuserinfo_by_username($content)
  {

  }

}
