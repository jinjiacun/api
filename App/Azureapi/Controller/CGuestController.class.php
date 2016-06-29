<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--管理--
------------------------------------------------------------
function of api:
------------------------------------------------------------
*/
class CguserController extends BaseController {
  /**
   * sql script:
    create table cguser(User_Id int primary key auto_increment,
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

   public function get_list($content)
   {
     list($data, $record_count) = parent::get_list($content);
     $list = array();
     if($data)
     {
       foreach($data as $v)
       {
         $list[] = array(
           'User_Id' => $v['User_Id'],
           'UserNickName'=> $v['UserNickName'],
           'UserName' => $v['UserName'],
           'UserPsw' =>$v['UserPsw'],
           'UserState'=>$v['UserState']
           'UserAvatar'=>$v['UserAvatar'],
           'User_Sex' => $v['User_Sex'],
           'UserBirthDay' => $v['UserBirthDay'],
           'UserJobj' => $v['UserJobj'],
           'UserFlag' => $v['UserFlag'],
           'UserIntro' => $v['UserIntro'],
           'UserPayPsw' => $v['UserPayPsw'],
           'VipGrade' => $v['VipGrade'],
           'UserCGold' => $v['UserCGold'],
           'UserAllCGold' => $v['UserAllCGold'],
           'LoginIp' => $v['LoginIp'],
           'LoginTime' => $v['LoginTime'],
           'RegisterTime' => $v['RegisterTime'];
           'Address' => $v['Address'],
           'RegisterIp' => $v['RegisterIp'],
           'UserUpTime' => $v['UserUpTime'],
           'AdminID' => $v['AdminID'],
           'AdminName' => $v['AdminName'],
           'PswMa' => $v['PswMa'],
           'PswAn' => $v['PswAn'],
           'LoginKey' => $v['LoginKey'],
           'UserBak1' => $v['UserBak1'],
           'UserBak2' => $v['UserBak2'],
           'UserStrBak1' => $v['UserStrBak1'],
           'UserStrBak2' => $v['UserStrBak2'],
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
 }
