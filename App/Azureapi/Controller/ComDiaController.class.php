<?php
namespace Azureadmin\Controller;
use Azureadmin\Controller;
include_once(dirname(__FILE__)."/BaseController.class.php");

class ComdiaController extends BaseController
{
    /**
       --管理--
       ----------------------------------------------------------
       --功能:新增
       --功能:列表查询
       --功能:查询单条信息
       --功能:通过关键字查询单条
     */
  
    /**
       sql script:
       create table comdia(DiaId int primary key auto_increment,
       UserId int,
       UserOffer varchar(50),
       AdminId int,
       AdminName varchar(50),
       ComId int,
       DiaState int,
       DiaPath varchar(200),
       DiaCon text,
       DiaTime timestamp,
       DiaFinTime timestamp
       )charset=utf8;
     */
    
    protected $_module_name = 'comdia';
    protected $_key = 'DiaId';

    protected $DiaId;
    protected $UserId;//用户id
    protected $UserOffer;//用户实盘
    protected $AdminId;//分析师id
    protected $AdminName;//分析师名称
    protected $ComId;//机构公司名称
    protected $DiaState;//诊断状态
    protected $DiaPath;//上传文件路径
    protected $DiaCon;//诊断内容
    protected $DiaTime;//提交时间
    protected $DiaFinTime;//诊断完成时间

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
    public function get_list($content){
        list($data, $record_count) = parent::get_list($content);
        $list = array();
        if($data){
            foreach($data as $v){
                $list[] = array(
                    'DiaId' => $v['DiaId'],
                    'UserId' => $v['UserId'],
                    'UserOffer' => $v['UserOffer'],
                    'AdminId' => $v['AdminId'],
                    'AdminName' => $v['AdminName'],
                    'ComId' => $v['ComId'],
                    'DiaState' => $v['DiaState'],
                    'DiaPath' => $v['DiaPath'],
                    'DiaCon' => $v['DiaCon'],
                    'DiaTime' => $v['DiaTime'],
                    'DiaFinTime' => $v['DiaFinTime']
                );
            }
        }
        
        return array(200,
        array(
            'list'=>$list,
            'record_count'=>$record_count));            
    }

 
    /**
       功能:查询单条
     */
    

    /**
       功能:通过关键字查询单条
     */
}
