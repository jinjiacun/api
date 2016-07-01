<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--栏目模块管理--
------------------------------------------------------------
function of api:
--功能:新增
public function add
--功能:列表查询
public function get_list
--功能:查询单条
public function get_info
--功能:通过关键字查询
public function get_info_by_keyxx
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
    功能:新增
    
    参数:
    @@input
    @param $AMoPId int 父栏目编号
    @param $AMoName string 栏目名称
    @param $AMoUrl string 栏目地址
    @param $AMoType int 栏目类型
    @param $AdminId int 创建者
    @param $AMoState int 状态
  */
 public function add($content){
     $data = $this->fill($content);
     
     if(!isset($data['AMoPId'])
     || !isset($data['AMoName'])
     || !isset($data['AMoUrl'])
     || !isset($data['AMoType'])
     || !isset($data['AdminId'])
     || !isset($data['AMoState'])){
         return C('param_err');
     }

     $data['AMoPId'] = intval($data['AMoPId']);
     $data['AMoName'] = htmlspecialchars(trim($data['AMoName']));
     $data['AMoUrl'] = htmlspecialchars(trim($data['AMoUrl']));
     $data['AMoType'] = intval($data['AMoType']);
     $data['AdminId'] = intval($data['AdminId']);
     $data['AMoState'] = intval($data['AMoState']);

     if(0 > $data['AMoPId']
     || '' ==  $data['AMoName']
     || 0 > $data['AMoType']
     || 0 > $data['AdminId']
     || 0 > $data['AMoState']){
         return C('param_fmt_err');
     }

     $data['AMTime'] = date('Y-m-d H:i:s');
     $data['AMUpTime'] = date('Y-m-d H:i:s');

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
         'message'=>urlencode('错误')
     ));
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

 /**
    功能:查询单条
  */
 public function get_info($conent){
     $data = $this->fill($content);
     
     $list = array();

     if(count($data) > 0){
         $tmp_one = M($this->_module_name)->where($data)->find();
         if($tmp_one
         && count($tmp_one) > 0){
             $list = array(
                 'AMoId'       => $tmp_one['AMoId'],
                 'AMoName'     => $tmp_one['AMoName'],
                 'AMoPId'      => $tmp_one['AMoPId'],
                 'AdminId'     => $tmp_one['AdminId'],
                 'AMoUrl'      => $tmp_one['AMoUrl'],
                 'AMoType'     => $tmp_one['AMoType'],
                 'AMoState'    => $tmp_one['AMoState'],
                 'AMTime'      => $tmp_one['AMTime'],
                 'AMPath'      => $tmp_one['AMPath'],
                 'AMUpTime'    => $tmp_one['AMUpTime'],
             );
         }
     }

     return array(200, $list);         
 }

 /**
    功能:通过关键字查询
  */
 public function get_info_by_key($content){
     $data = $this->fill($content);
     
     if(!isset($data[$this->_key])){
         return C('param_err');
     }
     
     $list = array();

     if(count($data) > 0){
         $tmp_one = M($this->_module_name)->find($data[$this->_key]);
         if($tmp_one
         && count($tmp_one) > 0){
             $list = array(
                 'AMoId'       => $tmp_one['AMoId'],
                 'AMoName'     => $tmp_one['AMoName'],
                 'AMoPId'      => $tmp_one['AMoPId'],
                 'AdminId'     => $tmp_one['AdminId'],
                 'AMoUrl'      => $tmp_one['AMoUrl'],
                 'AMoType'     => $tmp_one['AMoType'],
                 'AMoState'    => $tmp_one['AMoState'],
                 'AMTime'      => $tmp_one['AMTime'],
                 'AMPath'      => $tmp_one['AMPath'],
                 'AMUpTime'    => $tmp_one['AMUpTime'],
             );
         }
     }

     return array(200, $list);         
 }

}
