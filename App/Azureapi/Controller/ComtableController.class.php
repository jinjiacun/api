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
class ComtableController extends BaseController {
  /**
 * sql script:
  create table comtable(ComId int primary key auto_increment,
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

 protected $_module_name = 'comtable';
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

    return array(
      200,
      $list
    );
  }

  /**
     功能:审核通过
     
     参数:
     @param $ComId int 机构编号
     @param $ComTag string 机构tag
   */
  public function check($content)
  {
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
