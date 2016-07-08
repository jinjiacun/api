<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--机构会员关系管理--
------------------------------------------------------------
function of api:
--功能:添加
public function add
--功能:列表查询
public function get_list
--功能:通过查询一条信息
public function get_info
--功能:通过关键字查询一条信息
------------------------------------------------------------
*/
class ComUserController extends BaseController
{
    /**
       sql script:
       create table sp_com_user(ComUserId bigint(19) primary key auto_increment,
       User_Id bigint(19),
       ComId int,
       ComTag varchar(50),
       UState int,
       VipLevel int,
       ComTime timestamp,
       AgreeTime timestamp
       )charset=utf8;
     */
    
    protected $_module_name = 'com_user';
    protected $_key = 'ComUserId';
    
    protected $ComUserId;
    protected $User_Id;
    protected $ComId;
    protected $ComTag;
    protected $UState;
    protected $VipLevel;
    protected $ComTime;
    protected $AgreeTime;

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
                            'ComUserId' => urlencode($v['ComUserId']),
                            'User_Id'   => urlencode($v['User_Id']),
                            'ComId'     => urlencode($v['ComId']),
                            'ComTag'    => urlencode($v['ComTag']),
                            'UState'    => urlencode($v['UState']),
                            'VipLevel'  => urlencode($v['VipLevel']),
                            'ComTime'   => urlencode($v['ComTime']),
                            'AgreeTime' => urlencode($v['AgreeTime']),
                        );
                    }
            }

        return array(200,
        array(
            'list' => $list,
            'record_count' => $record_count)
        );
    }

    /**
       功能:查询单条
     */
    public function get_info($content){
        $data = $this->fill($content);
        
        if(count($data) <= 0){
            return C('param_err');
        }
        
        $list = array();
        $tmp_one = M($this->_module_name)->where($data)->find();
        if($tmp_one){
            $list = array(
                            'ComUserId' => urlencode($tmp_one['ComUserId']),
                            'User_Id'   => urlencode($tmp_one['User_Id']),
                            'ComId'     => urlencode($tmp_one['ComId']),
                            'ComTag'    => urlencode($tmp_one['ComTag']),
                            'UState'    => urlencode($tmp_one['UState']),
                            'vipLevel'  => urlencode($tmp_one['VipLevel']),
                            'ComTime'   => urlencode($tmp_one['ComTime']),
                            'AgreeTime' => urlencode($tmp_one['AgreeTime']),
                        );
        }
        
        return array(200, $list);
    }

    /**
       功能:通过关键字查询单条
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
                            'ComUserId' => urlencode($tmp_one['ComUserId']),
                            'User_Id'   => urlencode($tmp_one['User_Id']),
                            'ComId'     => urlencode($tmp_one['ComId']),
                            'ComTag'    => urlencode($tmp_one['ComTag']),
                            'UState'    => urlencode($tmp_one['UState']),
                            'vipLevel'  => urlencode($tmp_one['VipLevel']),
                            'ComTime'   => urlencode($tmp_one['ComTime']),
                            'AgreeTime' => urlencode($tmp_one['AgreeTime']),
                        );
        }
        
        return array(200, $list);
    }
}