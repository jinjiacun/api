<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--公告管理--
------------------------------------------------------------
function of api:
--功能:新增
--功能:列表查询
--功能:查询一条信息
--功能:通过关键字查询一条信息
------------------------------------------------------------
*/
class AllmessageController extends BaseController {
/**
 * sql script:
  create table allmessage(FMId int primary key auto_increment,
                      FMTitle varchar(100),
                      FMCon text,
                      FMState int,
                      FMFlag int,
                      FMTime timestamp,
                      FMUpTime timestamp,
                      FMComId text
               )charset=utf8;
 * */

 protected $_module_name = 'allmessage';
 protected $_key = 'FMId';

 protected $FMId;
 protected $FMTitle;
 protected $FMCon;
 protected $FMState;
 protected $FMFlag;
 protected $FMTime;
 protected $FMUpTime;
 protected $FMComId;

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
         'FMId'           =>$v['FmId'],
         'FMTitle'        =>$v['FmTitle'],
         'FMCon'          =>$v['FMCon'],
         'FMState'        =>$v['FMState'],
         'FMFlag'         =>$v['FMFlag'],
         'FMTime'         =>$v['FMTime'],
         'FMUpTime'       =>$v['FMUpTime'],
         'FMComId'        =>$v['FMComId'],
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
