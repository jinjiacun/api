<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--日志管理--
------------------------------------------------------------
function of api:
--功能:新增
--功能:列表查询
--功能:查询一条信息
--功能:通过关键字查询一条信息
------------------------------------------------------------
*/
class OperatelogController extends BaseController {
/**
 * sql script:
  create table operatelog(LogId int primary key auto_increment,
                      LogType int,
                      AdminId int,
                      AdminName varchar(50),
                      BusiName varchar(50),
                      OperateIntro text,
                      LogTime timestamp,
                      ComId int
               )charset=utf8;
 * */

 protected $_module_name = 'allmessage';
 protected $_key = 'LogId';

 protected $LogId;
 protected $LogType;
 protected $AdminId;
 protected $AdminName;
 protected $BusiName;
 protected $OperateIntro;
 protected $LogTime;
 protected $ComId;

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
         'LogId'           =>$v['LogId'],
         'LogType'         =>$v['LogType'],
         'AdminId'         =>$v['AdminId'],
         'AdminName'       =>$v['AdminName'],
         'BusiName'        =>$v['BusiName'],
         'OperateIntro'    =>$v['OperateIntro'],
         'LogTime'         =>$v['LogTime'],
         'ComId'           =>$v['ComId'],
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
 public function get_info_by_key($conent){
     $list = array();
     return array(200,
     $list);
 }
 }
