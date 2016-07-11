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
       ComIntro_Introduce text comment '公司简介',
       ComIntro_Download text comment '软件下载',
       ComIntro_Contact text comment '联系我们',
       ComSafe_Invest text comment '投资安全',
       ComSafe_Guarantee text comment '平台保障',
       ComSafe_Suggest text comment '安全建议',
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
    protected $ComIntro_Introduce;
    protected $ComIntro_Download;
    protected $ComIntro_Contact;
    protected $ComSafe_Invest;
    protected $ComSafe_Guarantee;
    protected $ComSafe_Suggest;
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

        if(False !== M($this->_module_name)->add($data)){
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
                            'ComId'        => urlencode($v["ComId"]),
                            'ComAdmin'     => urlencode($v['ComAdmin']),
                            'ComAdminRole' => urlencode($v['ComAdminRole']),
                            'ComAnaId'     => urlencode($v['ComAnaId']),
                            'ComAnaRole'   => urlencode($v['ComAnaRole']),
                            'ComShowSpan'  => urlencode($v['ComShowSpan']),
                            'ComShowState' => urlencode($v['ComShowState']),
                            'ComShowAsc'   => urlencode($v['ComShowAsc']),
                            'ComLinkType'  => urlencode($v['ComLinkType']),
                            'ResType'      => urlencode($v['ResType']),
                            'ShowType'     => urlencode($v['ShowType']),
                            'ThemeId'      => urlencode($v['ThemeId']),
                            'ComIntro_Introduce' => urlencode($v['ComIntro_Introduce']),
                            'ComIntro_Download'  => urlencode($v['ComIntro_Download']),
                            'ComIntro_Contact'   => urlencode($v['ComIntro_Contact']),
                            'ComSafe_Invest'     => urlencode($v['ComSafe_Invest']),
                            'ComSafe_Guarantee'  => urlencode($v['ComSafe_Guarantee']),
                            'ComSafe_Suggest'    => urlencode($v['ComSafe_Suggest']),                     
                            'ComSafe'      => urlencode($v['ComSafe']),
                            'CeTime'       => urlencode($v['CeTime']),
                            'CeUpTime'     => urlencode($v['CeUpTime']),
                        );
                    }
            }
        
        return array(200,
        array(
            'list'=> $list,
            'record_count'=> $record_count)
        );
    }

    /**
       功能:查询单条
     */
    public function get_info($content){
        $data = $this->fill($content);

        if(0 >= count($data)){
            return C('param_err');
        }
        
        $tmp_one = M($this->_module_name)->where($data)->find();
        $list = array();
        if($tmp_one){
            $list = array(
                'ComId'        => urlencode($tmp_one['ComId']),
                'ComAdmin'     => urlencode($tmp_one['ComAdmin']),
                'ComAdminRole' => urlencode($tmp_one['ComAdminRole']),
                'ComAnaId'     => urlencode($tmp_one['ComAnaId']),
                'ComAnaRole'   => urlencode($tmp_one['ComAnaRole']),
                'ComShowSpan'  => urlencode($tmp_one['ComShowSpan']),
                'ComShowState' => urlencode($tmp_one['ComShowState']),
                'ComShowAsc'   => urlencode($tmp_one['ComShowAsc']),
                'ComLinkType'  => urlencode($tmp_one['ComLinkType']),
                'ResType'      => urlencode($tmp_one['ResType']),
                'ShowType'     => urlencode($tmp_one['ShowType']),
                'ThemeId'      => urlencode($tmp_one['ThemeId']),   
                'ComIntro_Introduce' => urlencode($tmp_one['ComIntro_Introduce']),
                'ComIntro_Download'  => urlencode($tmp_one['ComIntro_Download']),
                'ComIntro_Contact'   => urlencode($tmp_one['ComIntro_Contact']),
                'ComSafe_Invest'     => urlencode($tmp_one['ComSafe_Invest']),
                'ComSafe_Guarantee'  => urlencode($tmp_one['ComSafe_Guarantee']),
                'ComSafe_Suggest'    => urlencode($tmp_one['ComSafe_Suggest']),  
                'CeTime'       => urlencode($tmp_one['CeTime']),
                'CeUpTime'     => urlencode($tmp_one['CeUpTime'])
            );
        }

        return array(200,
            $list);
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
            $list = array(
                'ComId'        => urlencode($tmp_one['ComId']),
                'ComAdmin'     => urlencode($tmp_one['ComAdmin']),
                'ComAdminRole' => urlencode($tmp_one['ComAdminRole']),
                'ComAnaId'     => urlencode($tmp_one['ComAnaId']),
                'ComAnaRole'   => urlencode($tmp_one['ComAnaRole']),
                'ComShowSpan'  => urlencode($tmp_one['ComShowSpan']),
                'ComShowState' => urlencode($tmp_one['ComShowState']),
                'ComShowAsc'   => urlencode($tmp_one['ComShowAsc']),
                'ComLinkType'  => urlencode($tmp_one['ComLinkType']),
                'ResType'      => urlencode($tmp_one['ResType']),
                'ShowType'     => urlencode($tmp_one['ShowType']),
                'ThemeId'      => urlencode($tmp_one['ThemeId']),
                'ComIntro_Introduce' => urlencode($tmp_one['ComIntro_Introduce']),
                'ComIntro_Download'  => urlencode($tmp_one['ComIntro_Download']),
                'ComIntro_Contact'   => urlencode($tmp_one['ComIntro_Contact']),
                'ComSafe_Invest'     => urlencode($tmp_one['ComSafe_Invest']),
                'ComSafe_Guarantee'  => urlencode($tmp_one['ComSafe_Guarantee']),
                'ComSafe_Suggest'    => urlencode($tmp_one['ComSafe_Suggest']),
                'CeTime'       => urlencode($tmp_one['CeTime']),
                'CeUpTime'     => urlencode($tmp_one['CeUpTime'])
            );
        }

        return array(200,$list);
    }
}