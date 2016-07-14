<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--用户诊断管理--
------------------------------------------------------------
function of api:
------------------------------------------------------------
*/
class ComAdminLTEController extends BaseController {
  /**
   * sql script:
    create table sp_com_admin_lte(ComAdminId int primary key auto_increment,
                            ComTLevel varchar(50),
                            ComTStyle varchar(200),
                            ComTIntro text,
                            ComLiveNum int,
                            ComFanNum int,
                            ComInterNum int
                 )charset=utf8;
   * */

   protected $_module_name = 'com_admin_lte';
   protected $_key = 'ComAdminId';

   protected $ComAdminId;
   protected $ComTLevel;
   protected $ComTStyle;
   protected $ComTIntro;
   protected $ComLiveNum;
   protected $ComFanNum;
   protected $ComInterNum;

   public function get_list()
   {
       list($data, $record_count) = parent::get_list($content);
       $list = array();
       if($data)
       {
           foreach($data as $v){
               $list[] = array(
                   'ComAdminId'  => urlencode($v['ComAdminId']),
                   'ComTLevel'   => urlencode($v['ComTLevel']),
                   'ComTStyle'   => urlencode($v['ComTStyle']),
                   'ComTIntro'   => urlencode($v['ComTIntro']),
                   'ComLiveNum'  => urlencode($v['ComLiveNum']),
                   'ComFanNum'   => urlencode($v['ComFanNum']),
                   'ComInterNum' => urlencode($v['ComInterNum']),
               );
           }
       }

       return array(200,
       array(
           'list'=>$list,
           'record_count'=>$record_count,
       )
       );
   }

   public function get_info($content){
       $data = $this->fill($content);
       
       if(count($data) <= 0){
           return C("param_fmt_err");
       }

       $list = array();
       $tmp_one = M($this->_module_name)->where($data)->find();
       if($tmp_one){
           $list = array(
               'ComAdminId'  => urlencode($tmp_one['ComAdminId']),
               'ComTLevel'   => urlencode($tmp_one['ComTLevel']),
               'ComTStyle'   => urlencode($tmp_one['ComTStyle']),
               'ComTIntro'   => urlencode($tmp_one['ComTIntro']),
               'ComLiveNum'  => urlencode($tmp_one['ComLiveNum']),
               'ComFanNum'   => urlencode($tmp_one['ComFanNum']),
               'ComInterNum' => urlencode($tmp_one['ComInterNum']),
           );
       }

       return array(200,
       $list);
   }
   
   //通过关键字ComAdminId
   public function get_info_by_key($content){
       $data = $this->fill($content);
       
       if(!isset($data[$this->_key])){
           return C("param_err");
       }

       $list = array();
       $tmp_one = M($this->_module_name)->find($data[$this->_key]);
       if($tmp_one){
           $list = array(
               'ComAdminId'  => urlencode($tmp_one['ComAdminId']),
               'ComTLevel'   => urlencode($tmp_one['ComTLevel']),
               'ComTStyle'   => urlencode($tmp_one['ComTStyle']),
               'ComTIntro'   => urlencode($tmp_one['ComTIntro']),
               'ComLiveNum'  => urlencode($tmp_one['ComLiveNum']),
               'ComFanNum'   => urlencode($tmp_one['ComFanNum']),
               'ComInterNum' => urlencode($tmp_one['ComInterNum']),
           );
       }

       return array(200,
       $list);
   }

}