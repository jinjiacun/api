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
class AModuleController extends BaseController {
/**
 * sql script:
  create table sp_amodule(AMoId int primary key auto_increment,
                      AMoName varchar(50),
                      AMoPId int,
                      AdminId int,
                      AMoUrl varchar(100),
                      AMoType int,
                      AMoState int,
                      AMTime timestamp,
                      AMPath varchar(100),
                      AMUpTime timestamp
               )charset=utf8;
 * */

 protected $_module_name = 'amodule';
 protected $_key = 'AMoId';

 protected $AMoId;
 protected $AmoName;//模块名称
 protected $AMoPId;//模块父级id,0为顶级模块
 protected $AdminId;//增加此模块的管理员
 protected $AMoUrl;//模块url
 protected $AMoType;//模块类型
 protected $AMoState;//状态:0-禁用,1-启用
 protected $AMTime;//创建时间
 protected $AMPath;//模块路径
 protected $AMUpTime;//更新时间


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
         'AMoId'       =>$v['AMoId'],
         'AMoName'     =>$v['AMoName'],
         'AMoPId'      =>$v['AMoPId'],
         'AdminId'     =>$v['AdminId'],
         'AMoUrl'      =>$v['AMoUrl'],
         'AMoType'     =>$v['AMoType'],
         'AMoState'    =>$v['AMoState'],
         'AMTime'      =>$v['AMTime'],
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
