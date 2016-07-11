<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--机构管理--
------------------------------------------------------------
function of api:
--功能:添加
public function add
--功能:列表查询
public function get_list
--功能:通过查询一条信息
public function get_info
--功能:通过关键字查询一条信息
public function get_info_by_key
--功能:审核通过
public function check
--功能:重置机构管理员密码
public function reset_admin_password
------------------------------------------------------------
*/
class ComTableController extends BaseController {
  /**
 * sql script:
  create table sp_com_table(ComId int primary key auto_increment,
                        ComTag varchar(100),
                        ComName varchar(100),
                        ComAllName varchar(200),
                        ComLogo text,
                        ComPhone varchar(50),
                        ComEmail varchar(200),
                        LoginBack text,
                        ComMin int,
                        ComLogin int,
                        ComSLogo text,
                        ComBanner text,
                        ComBanLink text,
                        ComState int,
                        ComFlag int,
                        ComUrl text,
                        ComLine varchar(50),
                        ComMob varchar(50),
                        ComMail varchar(100),
                        ComAddress text,
                        CreateTime timestamp,
                        UpTime timestamp,
                        ExpTime timestamp,
                        AppId int
               )charset=utf8;
 * */

 protected $_module_name = 'com_table';
 protected $_key = 'ComId';

 protected $ComId;      //机构id
 protected $ComTag;     //机构tag(url参数)
 protected $ComName;    //机构名称
 protected $ComAllName; //机构全称
 protected $ComLogo;    //机构logo
 protected $ComPhone;   //热线电话
 protected $ComEmail;   //企业邮箱
 protected $LoginBack;
 protected $ComMin;
 protected $ComSLogo;
 protected $ComBanner;
 protected $ComBanLink;
 protected $ComState;   //状态
 protected $ComFlag;    
 protected $ComUrl;     //公司网站
 protected $ComLine;    //联系人
 protected $ComMob;     //联系电话
 protected $ComMail;    //联系邮箱
 protected $ComAddress; //地址
 protected $CreateTime;
 protected $UpTime;
 protected $ExpTime;    //过期时间
 protected $AppId;

  public function get_list($content)
  {
    list($data, $record_count) = parent::get_list($content);
    $list = array();
    if($data)
    {
      foreach($data as $v)
      {
        $list[] = array(
            'ComId'       => intval($v['ComId']),
            'ComTag'      => urlencode($v['ComTag']),
            'ComName'     => urlencode($v['ComName']),
            'ComAllName'  => urlencode($v['ComAllName']),
            'ComLogo'     => urlencode($v['ComLogo']),
            'ComPhone'    => urlencode($v['ComPhone']),
            'ComEmail'    => urlencode($v['ComEmail']),
            'LoginBack'   => urlencode($v['LoginBack']),
            'ComMin'      => urlencode($v['ComMin']),
            'ComSLogo'    => urlencode($v['ComSLogo']),
            'ComBanner'   => urlencode($v['ComBanner']),
            'ComBanLink'  => urlencode($v['ComBanLink']),
            'ComState'    => intval($v['ComState']),
            'ComFlag'     => urlencode($v['ComFlag']),
            'ComUrl'      => urlencode($v['ComUrl']),
            'ComLine'     => urlencode($v['ComLine']),
            'ComMob'      => urlencode($v['ComMob']),
            'ComMail'     => urlencode($v['ComMail']),
            'ComAddress'  => urlencode($v['ComAddress']),
            'CreateTime'  => urlencode($v['CreateTime']),
            'UpTime'      => urlencode($v['UpTime']),
            'ExpTime'     => urlencode($v['ExpTime']),
            'AppId'       => intval($v['AppId']),
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

  /**
     功能:查询单条信息
   */
  public function get_info($content){
      $data = $this->fill($content);

      if(count($data) <=0){
          return C('param_fmt_err');
      }

      $list = array();
      $tmp_one = M($this->_module_name)->where($data)->find();
      if($tmp_one){
          if(count($tmp_one) >0){
              $list = array(
        'ComId'       =>$tmp_one['ComId'],
        'ComTag'      =>$tmp_one['ComTag'],
        'ComName'     =>$tmp_one['ComName'],
        'ComAllName'  =>$tmp_one['ComAllName'],
        'ComLogo'     =>$tmp_one['ComLogo'],
        'ComPhone'    =>$tmp_one['ComPhone'],
        'ComEmail'    =>$tmp_one['ComEmail'],
        'LoginBack'   =>$tmp_one['LoginBack'],
        'ComMin'      =>$tmp_one['ComMin'],
        'ComSLogo'    =>$tmp_one['ComSLogo'],
        'ComBanner'   =>$tmp_one['ComBanner'],
        'ComBanLink'  =>$tmp_one['ComBanLink'],
        'ComState'    =>$tmp_one['ComState'],
        'ComFlag'     =>$tmp_one['ComFlag'],
        'ComUrl'      =>$tmp_one['ComUrl'],
        'ComLine'     =>$tmp_one['ComLine'],
        'ComMob'      =>$tmp_one['ComMob'],
        'ComMail'     =>$tmp_one['ComMail'],
        'ComAddress'  =>$tmp_one['ComAddress'],
        'CreateTime'  =>$tmp_one['CreateTime'],
        'UpTime'      =>$tmp_one['UpTime'],
        'ExpTime'     =>$tmp_one['ExpTime'],
        'AppId'       =>$tmp_one['AppId'],
      );
          }
      }

      return array(200, $list);
  }

  #通过关键字查询单条
  public function get_info_by_key($content)
  {
    $data = $this->fill($content);

    if(!isset($data[$this->_key]))
    {
      return C('param_err');
    }

    $data[$this->_key] = intval($data[$this->_key]);

    if(0>= $data[$this->_key])
    {
      return C('param_fmt_err');
    }

    $list = array();
    $tmp_one = M($this->_module_name)->find($data[$this->_key]);
    if($tmp_one)
    {
      $list = array(
          'ComId'       => urlencode($tmp_one['ComId']),
          'ComTag'      => urlencode($tmp_one['ComTag']),
          'ComName'     => urlencode($tmp_one['ComName']),
          'ComAllName'  => urlencode($tmp_one['ComAllName']),
          'ComLogo'     => urlencode($tmp_one['ComLogo']),
          'ComPhone'    => urlencode($tmp_one['ComPhone']),
          'ComEmail'    => urlencode($tmp_one['ComEmail']),
          'LoginBack'   => urlencode($tmp_one['LoginBack']),
          'ComMin'      => urlencode($tmp_one['ComMin']),
          'ComSLogo'    => urlencode($tmp_one['ComSLogo']),
          'ComBanner'   => urlencode($tmp_one['ComBanner']),
          'ComBanLink'  => urlencode($tmp_one['ComBanLink']),
          'ComState'    => urlencode($tmp_one['ComState']),
          'ComFlag'     => urlencode($tmp_one['ComFlag']),
          'ComUrl'      => urlencode($tmp_one['ComUrl']),
          'ComLine'     => urlencode($tmp_one['ComLine']),
          'ComMob'      => urlencode($tmp_one['ComMob']),
          'ComMail'     => urlencode($tmp_one['ComMail']),
          'ComAddress'  => urlencode($tmp_one['ComAddress']),
          'CreateTime'  => urlencode($tmp_one['CreateTime']),
          'UpTime'      => urlencode($tmp_one['UpTime']),
          'ExpTime'     => urlencode($tmp_one['ExpTime']),
          'AppId'       => urlencode($tmp_one['AppId']),
      );
    }

    return array(
      200,
      $list
    );
  }

  /**
     功能:审核通过
     
     参数:
     @@input
     @param $ComId int 机构编号
     @param $ComTag string 机构tag
     @@out
     @param $is_success int 
     (1:机构标签已存在,
      2:更新机构信息错误,
      3:新增管理员角色失败,
      4:新增分析师角色失败,
      5:更新管理员和分析师默认权限失败,
      6:新增机构超级管理失败,
      7:新增机构分析师失败,
      8:新增初始人员失败,
      9:新增vip等级错误,
      10:新增VIP权限错误,
      11:新增直播室失败,
      12:新增默认直播室内容失败,
      13:审核同步失败
      )
   */

  public function check($content)
  {
      $data = $this->fill($content);
      
      if(!isset($data['ComId'])
      || !isset($data['ComTag'])){
          return C('param_err');
      }

      $data['ComId'] = intval($data['ComId']);
      $data['ComTag'] = htmlspecialchars(trim($data['ComTag']));

      #检查机构标签是否存在
      $where['ComTag'] = $data['ComTag'];
      $_tmp = M($this->_module_name)->field("AppId")->where($where)->find();
      unset($where);
      if(!$_tmp
      || 0 == count($_tmp)){
          return array(200,
          array(
              'is_success'=>1,
              'message'=>urlencode('标签已存在'))
          );
      }
      $app_id = $_tmp['AppId'];
      unset($_tmp);

      #更新机构信息
      $content = array(
          'where' => array(
              'ComId' => $data['ComId']
          ),
          'data' => array(
              'UpTime' => date('Y-m-d H:i:s'),
              'ExpTime' =>date('Y-m-d H:i:s', strtotime('+1 day +1 year')),#默认新增一年和一天
              'ComState' => 1,
              'ComTag' => strtolower($data['ComTag'])
          )
      );
      $_tmp = M($this->_module_name)->where($content['where'])->update($content['data']);
      unset($content);
      if(False === $_tmp){
          return array(200,
          array(
              'is_success' => 2,
              'message' => urlencode('更新机构信息错误'))
          );
      }
      unset($_tmp);

      #增加角色-管理员
      $admin_role_id = 0;
      $in_content = array(
          'RoleName'  => '超级管理员',
          'ComId'     => $data['ComId'],
          'RoleState' => 1,
          'AdminId'   => 0);
      list($status_code, $content) = A('Azureapi/Comrole')->add(json_encode($in_content));
      unset($in_content);
      if($status_code != 200
      || $content['is_success'] != 0){
          return array(200,
          array(
              'is_success' => 3,
              'message' => urlencode('新增管理员角色失败'))
          );
      }
      $admin_role_id = $content['id'];
      unset($status_code, $content);
      
      #新增角色-分析师
      $analyst_role_id = 0;
      $in_content = array(
          'RoleName'  => '分析师',
          'ComId'     => $data['ComId'],
          'RoleState' => 1,
          'AdminId'   => 0);
      list($status_code, $content) = A('Azureapi/Comrole')->add(json_encode($in_content));
      unset($in_conetnt);
      if($status_code != 200
      || $content['is_success'] != 0){
          return array(200,
          array(
              'is_success' => 4,
              'message' => urlencode('新增分析师角色失败'))
          );
      }
      $analyst_role_id = $content['id'];
      unset($status_code, $content);
      
      #设置角色权限
      $in_content = array();
      $admin_right_str = C('AdminInitCol');
      $analyst_right_str = C('TeacherInitCol');
      $admin_right_list = explode(',', $admin_right_str);
      $analyst_right_list = explode(',', $analyst_right_str);
      unset($admin_right_str, $analyst_right_str);
      foreach($admin_right_list as $v){
      $in_content[] = array(
          'RoleId' => $admin_role_id,
          'AMId'   => intval($v),
          'Creatime' => date('Y-m-d H:i:s')
       );
       }
      unset($v);
      foreach($analyst_right_list as $v){
      $in_content[] = array(
      'RoleId' => $analyst_role_id,
          'AMId' => intval($v),
          'CreateTime' => date('Y-m-d H:i:s')
          );
      }
      list($status_code, $content) = A('Azureapi/Comromo')->add_all(json_encode($in_content));
      unset($in_content);
      if($status_code != 200
          || $content['is_success'] != 0){
      return array(200,
          array(
      'is_success'=>5,
          'message'=>urlencode('更新管理员和分析师默认权限失'))
          );
      }
      unset($status_code, $content);
      
      #增加机构管理员
      $admin_id = 0;
      $in_content =  array(
      'AdminName' => 'admin_'.$data['ComTag'],
          'AdminUserName' => 'admin_'.$data['ComTag'],
          'Password' => md5('123456'),
          'ComId' => $data['ComId'],
          'RoleId'=> $admin_role_id,
          'AuthNo' => '',
          'Adavatar' => '',
          'Creatime' => date('Y-m-d H:i:s'),
          'UpTime' => date('Y-m-d H:i:s'),
          'AdminState' => 1,
          'LoginIp' => '',
          'LoginTime' => '',
          'CreateAdminId' => 0          
          );
      list($status_code, $content) = A('Azureapi/Comadmin')->add($in_content);
      if($status_code != 200 || $content['is_success'] != 0){
      return array(200,
          array(
      'is_success'=>6,
          'message' => urlencode('新增机构超级管理失败'))
          );
  }
      $admin_id = $content['id'];
      unset($status_code, $content);
      
      $in_content = #新增分析师
      array(
      'AdminName' => '定制分析师',
          'AdminUserName' => 'teacher_'.$data['ComTag'],
          'Password' => md5('123456'),
          'ComId' => $data['ComId'],
          'RoleId' => $analyst_role_id,
          'AuthNo' => '',
          'Adavatar' => 'http://zy.cngold.com.cn/image/teacherdefault.png',
          'Creatime' => date('Y-m-d H:i:s'),
          'UpTime' => date('Y-m-d H:i:s'),
          'AdminState' => 1,
          'LoginIp' => '',
          'LoginTime' => '',
          'CreateAdminId' => 0
          );
      list($status_code, $content) = A('Azureapi/Comadmin')->add(json_encode($in_content));
      unset($in_content);
      if($status_code != 200 || $content['is_success'] != 0){
          return array(200,
          array(
              'is_success'=> 7,
              'message' => urlencode('新增机构分析师失败')),
          );
      }
          $analyst_id = $content['id'];
          unset($status_code, $content);

      #增加初始人员
      $in_content = array(
          'ComAdmin' => $admin_id,
          'AdminPWD' => md5('123456'),
          'ComAdminRole' => $admin_role_id,
          'ComAnaId' => $analyst_id,
          'AnaPWD' => md5('123456'),
          'ComAnaRole' => $analyst_role_id,
          'ThemeId' => 1,
          'ComIntro' => '',
          'ComId' => $data['ComId'],
          'ComSafe' => '',
          'CeTime' => date('Y-m-d H:i:s'),
          'CeUpTime' => date('Y-m-d H:i:s'),
          'ResType' => 2
          );    
      list($status_code, $content) = A('Azureapi/Cominit')->add($content);
      unset($in_content);
      if($status_code != 200 || $content['is_success'] != 0){
          return array(200,
          array(
              'is_success'=>8,
              'message'=>urlencode('新增初始人员失败'))
          );
      }
      unset($status_code, $content);

      $vip_module_str = C('VIPInitCol');
      $vip_module_list = explode('|', $vip_module_str);
      #增加vip等级及权限
      for($i=0; $i<6; $i++){
      $vip_id = 0;
      $vip_state = 0;
      if($i< count($vip_module_list))
          $vip_state = 1;
      $in_content = array(
      'VipLevel'=>0,
          'VipName'=>'VIP '.$i,
          'VipState' =>1,
          'CVipIntro' => '',
          'AdminId' => 0,
          'ComId' => $data['ComId'],
          'CVipTime' => date('Y-m-d H:i:s'),
          'UpTime' => date('Y-m-d H:i:s')
          );
      list($status_code, $content) = A('Azureapi/Comvip')->add(in_content);
      unset($in_content);
      if($status_code != 200 || $content['is_success'] != 0){
          return array(200,
          array(
              'is_success'=>9,
              'message'=>'新增vip等级错误')
          );
      }
      $vip_id = $content['id'];
      unset($status_code, $content);

      #增加VIP权限
      if($i< count($vip_module_list)){
      $vip_init_module_list = $vip_module_list[$i];
      foreach($vip_init_module_list as $v)
      {
         $in_content[] = array(
              'ComId' => $data['ComId'],
              'ComTag' => $data['ComTag'],
              'CVipId' => $vip_id,
              'VipLevel' => $i,
                  'MoId' => $v,
              'MVTime' => date('Y-m-d H:i:s'),
              'UpTime' => date('Y-m-d H:i:s'),
              'VipState' => $vip_state
              );        
      }
      list($status_code, $content) = A('Azureapi/Commovip')->add_all($in_content);
      unset($in_content);
      if($status_code != 200 || $content['is_success'] != 0){
      return array(200,
          array(
            'is_success'=>10,
                'message'=>urlencode('新增VIP权限错误'))      
      );
      }
      
      }
      
  } 
      
      #增加默认直播室
      $in_content = array(
      'RoomAddAdmin' => 0,
          'RoomUpdateAdmin' => 0,
          'ComId' => $data['ComId'],
          'RoomEnable' => 1,
          'RoomHisPop' => 1,
          'RoomInterLimit' => 1,
          'RoomLiveLimit' => 1,
          'RoomTitle' => C('DefaultRoomTitle'),
          'RoomTeacher' => $analyst_id,
          'RoomMaximage' => C('DefaultRoomMaximage'),
          'RoomLivehisLimit' => 1,
          'RoomLivetime' => '||',
          'RoomMEtip' => 0,
          'RoomMinimage' => C('DefaultRoomMinimage'),
          'RoomName' => C('DefaultRoomName'),
          'RoomPopSet' =>1);
      list($status_code, $content) = A('Azureapi/ComRoom')->add($in_content);
      unset($in_content);
      if($status_code != 200 || $content['is_success'] != 0){
      return array(200,
          array(
      'is_success'=>11,
          'message'=>urlencode('新增直播室失败')));
       }
      $room_id = $content['id'];
      unset($status_code, $content);

      #增加默认直播内容
      $in_content = array(
          'AdminAvatar'=> C('DefaultAdavatar'),
          'AdminId' => $analyst_id,
          'AdminName' => C('AnalystPrefixName').$data['ComTag'],
          'ComId' => $data['ComId'],
          'LiveContent' => C('DefaultLiveContent'),
          'LiveQuote' => '',
          'LiveState' => 1,
          'LiveTop' => 0,
          'LiveType' => 0,
          'LiveVipGrade' => 0,
          'RoomId' => $room_id);
      list($status_code, $content) = A('Azureapi/RoomLive')->add($in_content);
      unset($in_content);
      if($status_code != 200 || $content['is_success'] != 0){
          return array(200,
          array(
              'is_success'=>12,
              'message' => urlencode('新增默认直播室内容失')));
      }
      unset($status_code, $content);          

      #审核通过数据同步
      $template_data = 'appid=%d&pw=%s&state=1';
      $data = sprintf($template_data, $app_id, md5($app_id.'cngold'.date('Ymd')));
      $result = $this->curl_get(C('PassUrl'), $data);
      if(strtolower($result) != 'true'){
      return array(200,
          array(
          'is_success'=>13,
          'message'=>urlencode('审核同步失败')));
      }
      

      return array(200,
      array(
          'is_success'=>0,
          'message'=>'错误'));
  }


  /**
     功能:重置机构管理员密码
     
     参数:
     @param $ComId int 机构编号
     @param $AdminId int 管理员id
   */
  public function reset_admin_password($content)
  {
      return array(200,
      array(
          'is_success'=>0,
          'message'=>'错误'));
  }
}
