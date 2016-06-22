<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--机构管理--
------------------------------------------------------------
function of api:
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
                        ComPhoto varchar(50),
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

 protected $ComId;
 protected $ComTag;
 protected $ComName;
 protected $ComAllName;
 protected $ComLogo;
 protected $ComPhoto;
 protected $ComEmail;
 protected $LoginBack;
 protected $ComMin;
 protected $ComSLogo;
 protected $ComBanner;
 protected $ComBanLink;
 protected $ComState;
 protected $ComFlag;
 protected $ComUrl;
 protected $ComLine;
 protected $ComMob;
 protected $ComAddress;
 protected $CreateTime;
 protected $UpTime;
 protected $ExpTime;
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
          'ComId'       =>$v['ComId'],
          'ComTag'      =>$v['ComTag'],
          'ComName'     =>$v['ComName'],
          'ComAllName'  =>$v['ComAllName'],
          'ComLogo'     =>$v['ComLogo'],
          'ComPhoto'    =>$v['ComPhoto'],
          'ComEmail'    =>$v['ComEmail'],
          'LoginBack'   =>$v['LoginBack'],
          'ComMin'      =>$v['ComMin'],
          'ComSLogo'    =>$v['ComSLogo'],
          'ComBanner'   =>$v['ComBanner'],
          'ComBanLink'  =>$v['ComBanLink'],
          'ComState'    =>$v['ComState'],
          'ComFlag'     =>$v['ComFlag'],
          'ComUrl'      =>$v['ComUrl'],
          'ComLine'     =>$v['ComLine'],
          'ComMob'      =>$v['ComMob'],
          'ComAddress'  =>$v['ComAddress'],
          'CreateTime'  =>$v['CreateTime'],
          'UpTime'      =>$v['UpTime'],
          'ExpTime'     =>$v['ExpTime'],
          'AppId'       =>$v['AppId'],
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
