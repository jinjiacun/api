<?php
namespace Azureadmin\Controller;
use Azureadmin\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
   --管理--
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
}