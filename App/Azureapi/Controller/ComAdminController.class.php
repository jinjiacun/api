<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--管理员管理--
------------------------------------------------------------
function of api:
--功能:新增
public funtion add
--功能:批量新增
public function add_all
--功能:列表查询
public function get_list
--功能:查询一条信息
public function get_info
--功能:通过关键字查询一条信息
public function get_info_by_key
------------------------------------------------------------
*/
class ComAdminController extends BaseController {
  /**
   * sql script:
    create table sp_com_admin(AdminId int primary key auto_increment,
                        AdminName varchar(50) comment '用户名',
                        Password text comment '密码',
                        AdminUserName varchar(100) comment '账号',
                        ComId int comment '机构id',
                        RoleId int comment '角色id',
                        AuthNo varchar(100) comment '分析师编号',
                        Adavatar text comment '头像',
                        CreatTime timestamp comment '创建日期',
                        UpTime timestamp comment '更新日期',
                        AdminState int comment '状态',
                        LoginIp varchar(20) comment '登陆ip',
                        LoginTime timestamp comment '登陆日期',
                        CreateAdminId int comment '创建人(0为系统)'
                 )charset=utf8;
   * */

   protected $_module_name = 'Com_admin';
   protected $_key = 'AdminId';

   protected $AdminId;//机构管理id
   protected $AdminName;//管理员名称
   protected $Password;//密码
   protected $AdminUserName;
   protected $ComId;//机构公司id
   protected $RoleId;//角色id
   protected $AuthNo;//分析师认证编号
   protected $Adavatar;//头像
   protected $CreatTime;//创建时间
   protected $UpTime;//更新时间
   protected $AdminState;//状态:0-禁用,1-启用
   protected $LoginIp;
   protected $LoginTime;
   protected $CreateAdminId;

   /**
      功能:新增
      
      参数:
      @@input
      @param $AdminName string 用户名
      @param $AdminUserName string 账号
      @param $Password string 密码
      @param $ComId int 机构id
      @param $RoleId int 角色id
      @param $AuthNo string 分析师编号
      @param $Adavatar string 头像
      @param $Creatime string 创建日期
      @param $UpTime string 更新日期
      @param $AdminState int 状态
      @param $LoginIp string 登陆ip
      @param $LoginTime string 登陆时间
      @param $CreateAdminId int 创建人
    */
   public function add($content)
   {
       $data = $this->fill($content);
       if(!isset($data['AdminName'])
       || !isset($data['AdminUserName'])
       || !isset($data['Password'])
       || !isset($data['ComId'])
       || !isset($data['RoleId'])){
           return C('param_err');
       }

       $data['AdminName'] = htmlspecialchars(trim($data['AdminName']));
       $data['AdminUserName'] = htmlspecialchars(trim($data['AdminUserName']));
       $data['Password'] = htmlspecialchars(trim($data['Password']));
       $data['ComId'] = intval($data['ComId']);
       $data['RoleId'] = intval($data['RoleId']);

       if('' == $data['AdminName']
       || '' == $data['AdminUserName']
       || '' == $data['Password']
       || 0 > $data['ComId']
       || 0 >= $data['RoleId']){
           return C('param_fmt_err');
       }
       
       if(False !== M($this->_module_name)->add($data)){
           return array(200,
           array(
               'is_success'=>0,
               'message'=>C('option_ok'))
           );
       }
       
       return array(200,
       array(
           'is_success'=>1,
           'message'=>'错误'));
   }

   /**
      功能:批量新增
    */
   public function add_all($content){
       $data = $this->fill($content);

       if(False !== M($this->_module_name)->addAll($data)){
           return array(200,
           array(
               'is_success'=>0,
               'message'=>C('option_ok'))
           );
       }

       return array(200,
       array(
           'is_success'=>1,
           'message'=>urlencode('错误'))
       );
   }

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
    $tmp_one = M($this->_module_name)->where($where)->find();
    if($tmp_one)
    {
        /*$user_info = $this->do_getuserinfo_by_username($data['user_name']);
      session('user_name',   $data['user_name']);
      session('user_id',     $user_info['id']);
      session('nick_name',   $user_info['nick_name']);
      session('user_mobile', $user_info['user_mobile']);
        */
        
      return array(
        200,
        array(
          'is_success' => 0,
          'message' => urlencode('成功登录'),
          'AdminId' => $tmp_one['AdminId'],
          'AdminName' => $tmp_one['AdminName'],
          'RoleId' => $tmp_one['RoleId'],
          'ComId' => $tmp_one['ComId'],
          'Adavatar' => $tmp_one['Adavatar'],
          ),
      );
    }

    #昵称登录
    /*
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
    */

    #手机号码登录
    /*
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
    */

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

  /**
     功能:列表查询
   */
  public function get_list($content){
      list($data, $record_count) = parent::get_list($content);
      $list = array();
      if($data){
          foreach($data as $v){
              $list[] = array(
                  'AdminId'       => urlencode($v['AdminId']),
                  'AdminName'     => urlencode($v['AdminName']),
                  'AdminUserName' => urlencode($v['AdminUserName']),
                  'ComId'         => urlencode($v['ComId']),
                  'RoleId'        => urlencode($v['RoleId']),
                  'AuthNo'        => urlencode($v['AuthNo']),
                  'Adavatar'      => urlencode($v['Adavatar']),
                  'Creatime'      => urlencode($v['Creatime']),
                  'UpTime'        => urlencode($v['UpTime']),
                  'AdminState'    => urlencode($v['AdminState']),
                  'LoginIp'       => urlencode($v['LoginIp']),
                  'LoginTime'     => urlencode($v['LoginTime']),
                  'CreateAdminId' => urlencode($v['CreateAdminId'])
              );
          }
      }

      return array(200,
      array(
          'list'=>$list,
          'record_count'=>$record_count));
  }

  /**
     功能:通过条件查询一条信息
   */
  public function get_info($content)
  {
      $data = $this->fill($content);
      
      if(!$data 
      || 0<count($data)){
          return C('param_err');
      }

      $list = array();
      $tmp_one = M($this->_module_name)->where($data)                                           
                                       ->find();
      if($tmp_one){
          $list = array(
              'AdminId'       => urlencode($tmp_one['AdminId']),
              'AdminName'     => urlencode($tmp_one['AdminName']),
              'AdminUserName' => urlencode($tmp_one['AdminUserName']),
              'ComId'         => urlencode($tmp_one['ComId']),
              'RoleId'        => urlencode($tmp_one['RoleId']),
              'AuthNo'        => urlencode($tmp_one['AuthNo']),
              'Adavatar'      => urlencode($tmp_one['Adavatar']),
              'Creatime'      => urlencode($tmp_one['Creatime']),
              'UpTime'        => urlencode($tmp_one['UpTime']),
              'AdminState'    => urlencode($tmp_one['AdminState']),
              'LoginIp'       => urlencode($tmp_one['LoginIp']),
              'LoginTime'     => urlencode($tmp_one['LoginTime']),
              'CreateAdminId' => urlencode($tmp_one['CreateAdminId'])
          );
      }
          
          return array(200,
          $list);
  }

  //通过关键字查询一条信息
  public function get_info_by_key($content)
  {
      $data = $this->fill($content);
      
      if(!$data[$this->_key]){
          return C('param_err');
      }

      $list = array();
      $tmp_one = M($this->_module_name)->find($data[$this->_key]);
      if($tmp_one){
          $list = array(
              'AdminId'       => urlencode($tmp_one['AdminId']),
              'AdminName'     => urlencode($tmp_one['AdminName']),
              'AdminUserName' => urlencode($tmp_one['AdminUserName']),
              'ComId'         => urlencode($tmp_one['ComId']),
              'RoleId'        => urlencode($tmp_one['RoleId']),
              'AuthNo'        => urlencode($tmp_one['AuthNo']),
              'Creatime'      => urlencode($tmp_one['Creatime']),
              'UpTime'        => urlencode($tmp_one['UpTime']),
              'AdminState'    => urlencode($tmp_one['AdminState']),
              'LoginIp'       => urlencode($tmp_one['LoginIp']),
              'LoginTime'     => urlencode($tmp_one['LoginTime']),
              'CreateAdminId' => urlencode($tmp_one['CreateAdminId'])
              );
      }

      return array(200,
      $list);
  }
}
