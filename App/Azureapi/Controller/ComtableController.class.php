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

 protected $ComId;      //机构id
 protected $ComTag;     //机构tag(url参数)
 protected $ComName;    //机构名称
 protected $ComAllName; //机构全称
 protected $ComLogo;    //机构logo
 protected $ComPhoto;   //
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

  #通过ComId查询单条
  public function get_info($content)
  /*
  @@input
  @param $ComId 机构id
  @@output
  @param  $ComId
  */
  {
    $data = $this->fill($content);

    if(!isset($data['ComId']))
    {
      return C('param_err');
    }

    $data['ComId'] = intval($data['ComId']);

    if(0>= $data['ComId'])
    {
      return C('param_fmt_err');
    }

    $list = array();
    $tmp_one = M($this->_module_name)->find($data['ComId']);
    if($tmp_one)
    {
      $list = array(
        'ComId'       =>$tmp_one['ComId'],
        'ComTag'      =>$tmp_one['ComTag'],
        'ComName'     =>$tmp_one['ComName'],
        'ComAllName'  =>$tmp_one['ComAllName'],
        'ComLogo'     =>$tmp_one['ComLogo'],
        'ComPhoto'    =>$tmp_one['ComPhoto'],
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

}
