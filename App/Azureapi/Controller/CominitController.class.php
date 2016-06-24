<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--管理--
------------------------------------------------------------
--功能:新增
--功能:查询列表
--功能:查询单条
--功能:通过关键字查询单条
*/
class CominitController extends BaseController
{
    /**
       sql script:
       create table cominit(ComId int primary key auto_increment,
       ComAdmin int,
       AdminPWD varchar(50),
       ComAdminRole int,
       ComAnaid int,
       AnaPWD varchar(50),
       ComAnaRole int,
       ComShowSpan int,
       ComShowState int,
       ComShowAsc int,
       ComLinkType int,
       ResType int,
       ShowType int,
       ThemeId int,
       ComIntro text,
       ComSafe text,
       CeTime timestamp,
       CeUpTime timestamp
       )charset=utf8;
     */
    
    protected $_module_name = 'cominit';
    protected $_key = 'ComId';
    
    protected $ComId;
    protected $ComAdmin;
    protected $AdminPWD;
    protected $ComAdminRole;
    protected $ComAnaId;
    protected $AnaPWD;
    protected $ComAnaRole;
    protected $ComShowSpan;
    protected $ComShowState;
    protected $ComShowAsc;
    protected $ComLinkType;
    protected $ResType;
    protected $ThemeId;
    protected $ComIntro;
    protected $ComSafe;
    protected $CeTime;
    protected $CeUpTime;

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
                            'ComId' => $v["ComId"],
                            'ComAdmin' => $v['ComAdmin'],
                            'AdminPWD' => $v['AdminPWD'],
                            'ComAdminRole' => $v['ComAdminRole'],
                            'ComAnaId' => $v['ComAnaId'],
                            'AnaPWD' => $v['AnaPWD'],
                            'ComAnaRole' => $v['ComAnaRole'],
                            'ComShowSpan' => $v['ComShowSpan'],
                            'ComShowState' => $v['ComShowState'],
                            'ComShowAsc' => $v['ComShowAsc'],
                            'ComLinkType' => $v['ComLinkType'],
                            'ResType' => $v['ResType'],
                            'ShowType' => $v['ShowType'],
                            'ThemeId' => $v['ThemeId'],
                            'ComIntro' => $v['ComIntro'],
                            'ComSafe' => $v['ComSafe'],
                            'CeTime' => $v['CeTime'],
                            'CeUpTime' => $v['CeUpTime'],
                        );
                    }
            }
        
        return array(200,
        array(
            'list'=> $list,
            'record_count'=> $record_count
        )
        );
    }

    /**
       功能:查询单条
     */
    public function get_info($content){
        $data = $this->fill($content);
        if(!$data
        || 0>=count($data)){
            return C('param_err');
        }
        
        $tmp_one = M($this->_module_name)->where($data)->find();
        $list = array();
        if($tmp_one){
            $list[] = array(
                'ComId' => $tmp_one['ComId'],
                'ComAdmin' => $tmp_one['ComAdmin'],
                'AdminPWD' => $tmp_one['AdminPWD'],
                'ComAdminRole' => $tmp_one['ComAdminRole'],
                'ComAnaId' => $tmp_one['ComAnaId'],
                'AnaPWD' => $tmp_one['AnaPWD'],
                'ComAnaRole' => $tmp_one['ComAnaRole'],
                'ComShowSpan' => $tmp_one['ComShowSpan'],
                'ComShowState' => $tmp_one['ComShowState'],
                'ComShowAsc' => $tmp_one['ComShowAsc'],
                'ComLinkType' => $tmp_one['ComLinkType'],
                'ResType' => $tmp_one['ResType'],
                'ShowType' => $tmp_one['ShowType'],
                'ThemeId' => $tmp_one['ThemeId'],
                'ComIntro' => $tmp_one['ComIntro'],
                'ComSafe' => $tmp_one['ComSafe'],
                'CeTime' => $tmp_one['CeTime'],
                'CeUpTime' => $tmp_one['CeUpTime']);
        }

        return array(200,
        array(
            'list' => $list,
            'record_count'=> $record_count));
    }

    /**
       功能:通过关键字查询单条
     */
    public function get_info_by_key($content){
        $data = $this->fill($content);
        if(!isset($data[$this->_key])){
            return C('param_err');
        }
        
        $tmp_one = M($this->_module_name)->find($data[$this->_key]);
        $list = array();
        if($tmp_one){
            $list[] = array(
                'ComId' => $tmp_one['ComId'],
                'ComAdmin' => $tmp_one['ComAdmin'],
                'AdminPWD' => $tmp_one['AdminPWD'],
                'ComAdminRole' => $tmp_one['ComAdminRole'],
                'ComAnaId' => $tmp_one['ComAnaId'],
                'AnaPWD' => $tmp_one['AnaPWD'],
                'ComAnaRole' => $tmp_one['ComAnaRole'],
                'ComShowSpan' => $tmp_one['ComShowSpan'],
                'ComShowState' => $tmp_one['ComShowState'],
                'ComShowAsc' => $tmp_one['ComShowAsc'],
                'ComLinkType' => $tmp_one['ComLinkType'],
                'ResType' => $tmp_one['ResType'],
                'ShowType' => $tmp_one['ShowType'],
                'ThemeId' => $tmp_one['ThemeId'],
                'ComIntro' => $tmp_one['ComIntro'],
                'ComSafe' => $tmp_one['ComSafe'],
                'CeTime' => $tmp_one['CeTime'],
                'CeUpTime' => $tmp_one['CeUpTime']);
        }

        return array(200,
        array(
            'list' => $list,
            'record_count'=> $record_count));
    }
}