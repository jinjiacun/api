<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--栏目模块管理--
------------------------------------------------------------
function of api:
------------------------------------------------------------
*/
class AmoduleController extends BaseController {
/**
 * sql script:
  create table amodule(AMoId int primary key auto_increment,
                      AMoName varchar(50),
                      AMoPId int,
                      AdminId int,
                      AMoUrl varchar(100),
                      AMoType int,
                      AMoState int,
                      AMoTime timestamp,
                      AMPath varchar(100),
                      AMUpTime timestamp
               )charset=utf8;
 * */

 protected $_module_name = 'amodule';

 protected $AMoId;
 protected $AmoName;
 protected $AMoPId;
 protected $AdminId;
 protected $AMoUrl;
 protected $AMoType;
 protected $AMoState;
 protected $AMoTime;
 protected $AMPath;
 protected $AMUpTime;

 public function get_list($content)
 {
   list($data, $record_count) = parent::get_list($content);
   $list = array();
   if($data)
   {
     foreach($data as $v)
     {
       $list[] = array(
         'AMoId'       =>$v['AMoId'],
         'AMoName'     =>$v['AMoName'],
         'AMoPId'      =>$v['AMoPId'],
         'AdminId'     =>$v['AdminId'],
         'AMoUrl'      =>$v['AMoUrl'],
         'AMoType'     =>$v['AMoType'],
         'AMoState'    =>$v['AMoState'],
         'AMoTime'     =>$v['AMoTime'],
         'AMPath'      =>$v['AMPath'],
         'AMUpTime'    =>$v['AMUpTime'],
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
