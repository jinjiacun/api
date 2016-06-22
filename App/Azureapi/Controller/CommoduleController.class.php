<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--前台模块管理--
------------------------------------------------------------
function of api:
------------------------------------------------------------
*/
class CommoduleController extends BaseController {
/**
 * sql script:
  create table commodule(MoId int primary key auto_increment,
                      MoName   varchar(50),
                      MoIntro text,
                      MoUrl varchar(200),
                      MoNeed int,
                      MoType int,
                      MoPid int,
                      MoIime timestamp,
                      MoUpTime timestamp,
                      MoState int,
                      AdminId int
               )charset=utf8;
 * */

 protected $_module_name = 'commodule';

 protected $MoId;
 protected $MoName;
 protected $MoIntro;
 protected $MoUrl;
 protected $MoNeed;
 protected $MoType;
 protected $MoPid;
 protected $MoTime;
 protected $MoUpTime;
 protected $MoState;
 Protected $AdminId;

 public function get_list($content)
 {
   list($data, $record_count) = parent::get_list($content);
   $list = array();
   if($data)
   {
     foreach($data as $v)
     {
       $list[] = array(
         'MoId'           =>$v['MoId'],
         'MoName'         =>$v['MoName'],
         'MoIntro'        =>$v['MoIntro'],
         'MoNeed'         =>$v['MoNeed'],
         'ModType'        =>$v['ModType'],
         'MoPid'          =>$v['MoPid'],
         'MoTime'         =>$v['MoTime'],
         'MoUpTime'       =>$v['MoUpTime'],
         'MoState'        =>$v['MoState'],
         'AdminId'        =>$v['AdminId'],
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
