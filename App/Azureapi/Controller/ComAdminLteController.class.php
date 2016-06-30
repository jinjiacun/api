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
class ComAdminLteController extends BaseController {
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
           $list[] = array(
               'ComAdminId' => $v['ComAdminId'],
               'ComTLevel' => $v['ComTLevel'],
               'ComTStyle' => $v['ComTStyle'],
               'ComTIntro' => $v['ComTINtro'],
               'ComLiveNum' => $v['ComLiveNum'],
               'ComFanNum' => $v['ComFanNum'],
               'ComIntroNum' => $v['ComIntroNum'],
           );
       }

       return array(200,
       array(
           'list'=>$list,
           'record_count'=>$record_count,
       )
       );
   }
   
   //通过关键字ComAdminId
   public function get_info()