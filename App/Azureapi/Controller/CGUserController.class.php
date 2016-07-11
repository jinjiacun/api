<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--用户管理--
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
------------------------------------------------------------
*/
class CGUserController extends BaseController {
  /**
   * sql script:
    create table sp_cguser(User_Id int primary key auto_increment,
                        UserNickName varchar(50),
                        UserName varchar(50),
                        UserPsw varchar(50),
                        UserState int,
                        UserAvatar text,
                        User_Sex int,
                        UserBirthDay timestamp,
                        UserJob varchar(100),
                        UserFlag int,
                        UserIntro text,
                        UserPayPsw varchar(50),
                        VipGrade int,
                        UserCGold float,
                        UserAllCGold float,
                        LoginIp varchar(50),
                        LoginTime timestamp,
                        RegisterTime timestamp,
                        Address text,
                        RegisterIp varchar(50),
                        UserUpTime timestamp,
                        AdminID int,
                        AdminName varchar(50),
                        PswMa varchar(200),
                        PswAn varchar(200),
                        LoginKey varchar(20),
                        UserBak1 int,
                        UserBak2 int,
                        UserStrBak1 char(10),
                        UserStrBak2 char(10)
                 )charset=utf8;
   * */

   protected $_module_name = 'cguser';
   protected $_key = 'User_Id';

   protected $User_Id;
   protected $UserNickName;
   protected $UserName;
   protected $UserPsw;
   protected $UserState;
   protected $UserAvatar;
   protected $User_Sex;
   protected $UserBirthDay;
   protected $UserJobj;
   protected $UserFlag;
   protected $UserIntro;
   protected $UserPayPsw;
   protected $VipGrade;
   protected $UserCGold;
   protected $UserAllCGold;
   protected $LoginIp;
   protected $LoginTime;
   protected $RegisterTime;
   protected $Address;
   protected $RegisterIp;
   protected $UserUpTime;
   protected $AdminID;
   protected $AdminName;
   protected $PswMa;
   protected $PswAn;
   protected $LoginKey;
   protected $UserBak1;
   protected $UserBak2;
   protected $UserStrBak1;
   protected $UserStrBak2;

   /**
      功能:列表查询
    */
   public function get_list($content)
   {
     list($data, $record_count) = parent::get_list($content);
     $list = array();
     if($data)
     {
       foreach($data as $v)
       {
         $list[] = array(
             'User_Id'      => urlencode($v['User_Id']),
             'UserNickName' => urlencode($v['UserNickName']),
             'UserName'     => urlencode($v['UserName']),
             'UserPsw'      => urlencode($v['UserPsw']),
             'UserState'    => urlencode($v['UserState']),
             'UserAvatar'   => urlencode($v['UserAvatar']),
             'User_Sex'     => urlencode($v['User_Sex']),
             'UserBirthDay' => urlencode($v['UserBirthDay']),
             'UserJobj'     => urlencode($v['UserJobj']),
             'UserFlag'     => urlencode($v['UserFlag']),
             'UserIntro'    => urlencode($v['UserIntro']),
             'UserPayPsw'   => urlencode($v['UserPayPsw']),
             'VipGrade'     => urlencode($v['VipGrade']),
             'UserCGold'    => urlencode($v['UserCGold']),
             'UserAllCGold' => urlencode($v['UserAllCGold']),
             'LoginIp'      => urlencode($v['LoginIp']),
             'LoginTime'    => urlencode($v['LoginTime']),
             'RegisterTime' => urlencode($v['RegisterTime']),
             'Address'      => urlencode($v['Address']),
             'RegisterIp'   => urlencode($v['RegisterIp']),
             'UserUpTime'   => urlencode($v['UserUpTime']),
             'AdminID'      => urlencode($v['AdminID']),
             'AdminName'    => urlencode($v['AdminName']),
             'PswMa'        => urlencode($v['PswMa']),
             'PswAn'        => urlencode($v['PswAn']),
             'LoginKey'     => urlencode($v['LoginKey']),
             'UserBak1'     => urlencode($v['UserBak1']),
             'UserBak2'     => urlencode($v['UserBak2']),
             'UserStrBak1'  => urlencode($v['UserStrBak1']),
             'UserStrBak2'  => urlencode($v['UserStrBak2']),
           );
       }
     }

     return array(200,
         array(
           'list'=>$list,
           'record_count'=> $record_count)
         );
   }

   /**
      功能:查询单条
    */
   public function get_info($content){
       $data = $this->fill($content);

       if(count($data) <= 0){
           return C("param_fmt_err");
       }

       $list = array();
       $tmp_one = M($this->_module_name)->where($data)->find();
       if($tmp_one){
           $list[] = array(
             'User_Id'      => urlencode($tmp_one['User_Id']),
             'UserNickName' => urlencode($tmp_one['UserNickName']),
             'UserName'     => urlencode($tmp_one['UserName']),
             'UserPsw'      => urlencode($tmp_one['UserPsw']),
             'UserState'    => urlencode($tmp_one['UserState']),
             'UserAvatar'   => urlencode($tmp_one['UserAvatar']),
             'User_Sex'     => urlencode($tmp_one['User_Sex']),
             'UserBirthDay' => urlencode($tmp_one['UserBirthDay']),
             'UserJobj'     => urlencode($tmp_one['UserJobj']),
             'UserFlag'     => urlencode($tmp_one['UserFlag']),
             'UserIntro'    => urlencode($tmp_one['UserIntro']),
             'UserPayPsw'   => urlencode($tmp_one['UserPayPsw']),
             'VipGrade'     => urlencode($tmp_one['VipGrade']),
             'UserCGold'    => urlencode($tmp_one['UserCGold']),
             'UserAllCGold' => urlencode($tmp_one['UserAllCGold']),
             'LoginIp'      => urlencode($tmp_one['LoginIp']),
             'LoginTime'    => urlencode($tmp_one['LoginTime']),
             'RegisterTime' => urlencode($tmp_one['RegisterTime']),
             'Address'      => urlencode($tmp_one['Address']),
             'RegisterIp'   => urlencode($tmp_one['RegisterIp']),
             'UserUpTime'   => urlencode($tmp_one['UserUpTime']),
             'AdminID'      => urlencode($tmp_one['AdminID']),
             'AdminName'    => urlencode($tmp_one['AdminName']),
             'PswMa'        => urlencode($tmp_one['PswMa']),
             'PswAn'        => urlencode($tmp_one['PswAn']),
             'LoginKey'     => urlencode($tmp_one['LoginKey']),
             'UserBak1'     => urlencode($tmp_one['UserBak1']),
             'UserBak2'     => urlencode($tmp_one['UserBak2']),
             'UserStrBak1'  => urlencode($tmp_one['UserStrBak1']),
             'UserStrBak2'  => urlencode($tmp_one['UserStrBak2']),
           );
       }

       return array(200, $list);
   }

   /**
      功能:通过关键字查询单条
    */
   public function get_info_by_key($content){
       $data = $this->fill($content);

       if(!isset($data[$this->_key])){
           return C("param_err");
       }

       $list = array();
       $tmp_one = M($this->_module_name)->find($data[$this->_key]);
       if($tmp_one){
           $list[] = array(
             'User_Id'      => urlencode($tmp_one['User_Id']),
             'UserNickName' => urlencode($tmp_one['UserNickName']),
             'UserName'     => urlencode($tmp_one['UserName']),
             'UserPsw'      => urlencode($tmp_one['UserPsw']),
             'UserState'    => urlencode($tmp_one['UserState']),
             'UserAvatar'   => urlencode($tmp_one['UserAvatar']),
             'User_Sex'     => urlencode($tmp_one['User_Sex']),
             'UserBirthDay' => urlencode($tmp_one['UserBirthDay']),
             'UserJobj'     => urlencode($tmp_one['UserJobj']),
             'UserFlag'     => urlencode($tmp_one['UserFlag']),
             'UserIntro'    => urlencode($tmp_one['UserIntro']),
             'UserPayPsw'   => urlencode($tmp_one['UserPayPsw']),
             'VipGrade'     => urlencode($tmp_one['VipGrade']),
             'UserCGold'    => urlencode($tmp_one['UserCGold']),
             'UserAllCGold' => urlencode($tmp_one['UserAllCGold']),
             'LoginIp'      => urlencode($tmp_one['LoginIp']),
             'LoginTime'    => urlencode($tmp_one['LoginTime']),
             'RegisterTime' => urlencode($tmp_one['RegisterTime']),
             'Address'      => urlencode($tmp_one['Address']),
             'RegisterIp'   => urlencode($tmp_one['RegisterIp']),
             'UserUpTime'   => urlencode($tmp_one['UserUpTime']),
             'AdminID'      => urlencode($tmp_one['AdminID']),
             'AdminName'    => urlencode($tmp_one['AdminName']),
             'PswMa'        => urlencode($tmp_one['PswMa']),
             'PswAn'        => urlencode($tmp_one['PswAn']),
             'LoginKey'     => urlencode($tmp_one['LoginKey']),
             'UserBak1'     => urlencode($tmp_one['UserBak1']),
             'UserBak2'     => urlencode($tmp_one['UserBak2']),
             'UserStrBak1'  => urlencode($tmp_one['UserStrBak1']),
             'UserStrBak2'  => urlencode($tmp_one['UserStrBak2']),
           );
       }

       return array(200, $list);
   }
 }
