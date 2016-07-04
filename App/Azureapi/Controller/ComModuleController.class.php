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

    参数:
    @@input
    @param $MoName string 模块名称
    @param $AdminId int 创建者
    @param $MoState int 状态
    @param $MoUrl string url
    @param $MoIntro string 介绍 (非必须)
    @param $MoPid int 父节点id
    @param $MoType int 类型
    @param $MoNeed int 是否必要
  */
 public function add($content){
     $data = $this->fill($content);

     if(!isset($data['MoName'])
     || !isset($data['AdminId'])
     || !isset($data['MoState'])
     || !isset($data['MoUrl'])
     || !isset($data['MoPid'])
     || !isset($data['MoType'])
     || !isset($data['MoNeed'])){
         return C('param_err');
     }

     $data['MoName']  = htmlspecialchars(trim($data['MoName']));
     $data['AdminId'] = intval($data['AdminId']);
     $data['MoState'] = intval($data['MoState']);
     $data['MoUrl'] = htmlspecialchars($data['MoUrl']);
     $data['MoPid'] = intval($data['MoPid']);
     $data['MoType'] = intval($data['MoType']);
     $data['MoNeed'] = intval($data['MoNeed']);

     if('' == $data['MoName']
     || 0 > $data['AdminId']
     || 0 > $data['MoState']
     || 0 > $data['MoUrl']
     || 0 > $data['MoPid']
     || 0 > $data['MoType']
     || 0 > $data['MoNeed']){
         return C('param_fmt_err');
     }
     
     $data['MoTime'] = date('Y-m-d H:i:s');
     $data['MoUpTime'] = date('Y-m-d H:i:s');

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
         'message'=>C('option_fail')));
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
         'MoUrl'          =>$v['MoUrl'],
         'MoNeed'         =>$v['MoNeed'],
         'MoType'         =>$v['MoType'],
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
     $data = $this->fill($content);
     
     if(!isset($data)){
         return C('param_err');
     }

     if(cout($data) > 0){
         return C('param_fmt_err');
     }
     
     $list = array();
     $tmp_one = M($this->_module_name)-where($data)->find();
     if($tmp_one){
         $list = array(
             'MoId'           =>intval($tmp_one['MoId']),
             'MoName'         =>urlencode($tmp_one['MoName']),
             'MoIntro'        =>urlencode($tmp_one['MoIntro']),
             'MoUrl'          =>$tmp_one['MoUrl'],
             'MoNeed'         =>intval($tmp_one['MoNeed']),
             'MoType'         =>intval($tmp_one['MoType']),
             'MoPid'          =>intval($tmp_one['MoPid']),
             'MoTime'         =>$tmp_one['MoTime'],
             'MoUpTime'       =>$tmp_one['MoUpTime'],
             'MoState'        =>intval($tmp_one['MoState']),
             'AdminId'        =>intval($tmp_one['AdminId']),
         );
     }
     
     return array(200,
     $list);
 }

 /**
    功能:通过关键字查询一条信息
  */
 public function get_info_by_key($content){
     $data = $this->fill($content);
     
     if(!isset($data[$this->_key])){
         return C('param_err');
     }

     $list = array();
     $tmp_one = M($this->_module_name)->find($data[$this->_key]);
     if($tmp_one){
         $list = array(
             'MoId'           =>intval($tmp_one['MoId']),
             'MoName'         =>urlencode($tmp_one['MoName']),
             'MoIntro'        =>urlencode($tmp_one['MoIntro']),
             'MoUrl'          =>$tmp_one['MoUrl'],
             'MoNeed'         =>intval($tmp_one['MoNeed']),
             'MoType'         =>intval($tmp_one['MoType']),
             'MoPid'          =>intval($tmp_one['MoPid']),
             'MoTime'         =>$tmp_one['MoTime'],
             'MoUpTime'       =>$tmp_one['MoUpTime'],
             'MoState'        =>intval($tmp_one['MoState']),
             'AdminId'        =>intval($tmp_one['AdminId']),
         );
     }
     
     return array(200,
     $list);
 }

 }
