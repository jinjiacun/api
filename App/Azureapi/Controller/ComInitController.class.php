<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--初始人员管理--
------------------------------------------------------------
--功能:新增
--功能:查询列表
--功能:查询单条
--功能:通过关键字查询单条
*/
class ComInitController extends BaseController
{
    /**
       sql script:
       create table sp_com_init(ComId int primary key auto_increment,
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
    
    protected $_module_name = 'com_init';
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
       
       参数:
       @@input
       @param $ComAdmin int 机构管理员id
       @param $AdminPWD string 机构管理员密码
       @param $ComAdminRole int 机构管理员角色
       @param $ComAdaId int 机构分析师id
       @param $AdaPWD string 机构分析师密码
       @param $ComAdaRole int 机构分析师角色
       @param $ThemeId int 前台模板id
       @param $ComIntro string 机构介绍
       @param $ComId int 机构id
       @param $ComSafe string 机构安全
       @param $CeTime string 创建时间
       @param $CeUpTime string 更新时间
       @param $ResType int 资源类型
     */
    public function add($content){
        $data = $this->fill($content);
        if(!isset($data['ComAdmin'])
        || !isset($data['AdminPWD'])
        || !isset($data['ComAdminRole'])
        || !isset($data['ComAdaId'])
        || !isset($data['AdaPWD'])
        || !isset($data['ComAdaRole'])
        || !isset($data['ThemeId'])
        || !isset($data['ComId'])
        || !isset($data['ResType'])){
            return C('param_err');
        }
        
        $data['ComAdmin'] = intval($data['ComAdmin']);
        $data['AdminPWD'] = htmlspecialchars(trim($data['AdminPWD']));
        $data['ComAdminRole'] = intval($data['ComAdminRole']);
        $data['ComAdaId'] = intval($data['ComAdaId']);
        $data['AdaPWD'] = htmlspecialchars(trim($data['AdaPWD']));
        $data['ComAdaRole'] = intval($data['ComAdaRole']);
        $data['ThemeId'] = intval($data['ThemeId']);
        $data['ComId'] = intval($data['ComId']);
        $data['ResType'] = intval($data['ResType']);
        
        if(0 >= $data['ComAdmin']
        || '' == $data['AdminPWD']
        || 0 >= $data['ComAdminRole']
        || 0 >= $data['ComAdaId']
        || '' == $data['AdaPWD']
        || 0 >= $data['ComRoleId']
        || 0 >= $data['ThemeId']
        || 0 >= $data['ComId']
        || 0 >= $data['ResType']){
            return C('param_fmt_err');
        }
        
        $data['CeTime'] = date('Y-m-d H:i:s');
        $data['CeUpTime'] = date('Y-m-d H:i:s');

        if(False != M($this->_module_name)->add($data)){
            return array(200,
            array(
                'is_success'=>0,
                'message'=>C('option_ok'),
                'id' => M()->getLastInsID())   
            );
        }

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