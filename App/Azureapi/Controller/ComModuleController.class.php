<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--前台模块管理--
------------------------------------------------------------
function of api:
--功能:新增
--功能:列表查询
--功能:查询一条信息
--功能:通过关键字查询一条信息
------------------------------------------------------------
*/
class ComModuleController extends BaseController {
/**
 * sql script:
  create table sp_com_module(MoId int primary key auto_increment,
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

 protected $_module_name = 'com_module';
 protected $_key = 'MoId';

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
 protected $AdminId;

 /**
    功能:新增
  */
 public function add($content){
     return array(200,
     array(
         'is_success'=>1,
         'message'=>'错误'));
 }

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

 /**
    功能:查询一条信息
  */
 public function get_info($content){
     $list = array();
     return array(200,
     $list);
 }

 /**
    功能:通过关键字查询一条信息
  */
 public function get_info_by_key($content){
     $list = array();
     return array(200,
     $list);
 }

 }
