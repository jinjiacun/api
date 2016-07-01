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
class AllMessageController extends BaseController {
/**
 * sql script:
  create table sp_all_message(FMId int primary key auto_increment,
                      FMTitle varchar(100),
                      FMCon text,
                      FMState int,
                      FMFlag int,
                      FMTime timestamp,
                      FMUpTime timestamp,
                      FMComId text
               )charset=utf8;
 * */

 protected $_module_name = 'All_message';
 protected $_key = 'FMId';

 protected $FMId;
 protected $FMTitle;//消息标题
 protected $FMCon;//消息内容
 protected $FMState;//消息状态
 protected $FMFlag;//同步标识
 protected $FMTime;//消息时间
 protected $FMUpTime;//更新时间
 protected $FMComId;//发送机构id,多个以|分割,0代表全部

 /**
    功能:新增
    
    参数:
    @@input
    @param $FMTitle 机构编号
    @param $FMCon   内容
    @param $FMComId 标题
    @param $FMState 状态    
  */
 public function add($content){
     $data = $this->fill($content);

     if(!isset($data['FMTitle'])
     || !isset($data['FMCon'])
     || !isset($data['FMComId'])
     || !isset($data['FMState'])){
         return C('param_err');
     }

     $data['FMTitle'] = htmlspecialchars(trim($data['FMTitle']));
     $data['FMCon'] = htmlspecialchars(trim($data['FMCon']));
     $data['FMComId'] = htmlspecialchars(trim($data['FMComId']));
     $data['FMState'] = intval($data['FMState']);

     if('FMTitle' == $data['FMTitle']
     || 'FMCon' == $data['FMCon']
     || 'FMComId' == $data['FMComId']
     || 0 > $data['FMStatex']){
         return C('param_fmt_err');
     }    

     $data['FMTime'] = date('Y-m-d H:i:s');
     $data['FMUpTime'] = date('Y-m-d H:i:s');
     
     if(False !== M($this->_module_name)->add($data)){
         return array(200,
         array(
             'is_success'=>0,
             'message'=>C('option_ok'),
             'id'=>M()->getLastInsID())
         );
     }
     
     return array(200,
     array(
         'is_success'=>1,
         'message'=>urlencode('错误'))
     );
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
         'FMId'           =>$v['FMId'],
         'FMTitle'        =>$v['FMTitle'],
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
