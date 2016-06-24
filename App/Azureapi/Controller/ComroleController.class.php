<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
   --角色管理--
--功能:新增
--功能:查询列表
--功能:查询一条信息
--功能:通过关键字查询一条信息
*/
class ComroleController extends BaseController
{
    /**
       sql script:
       create table comrole(RoleId int primary key auto_increment,
       RoleName varchar(50),
       ComId int,
       RoleState int,
       Creatime timestamp,
       UpTime timestamp,
       AdminId int
       )charset=utf8;
     */
    
    protected $_module_name = 'comrole';
    protected $_key = 'RoleId';

    protected $RoleId;
    protected $RoleName;
    protected $ComId;
    protected $RoleState;
    protected $Creatime;
    protected $UpTime;
    protected $AdminId;

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
       功能:查询列表
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
                            'RoleId' => $v['RoleId'],
                            'RoleName' => $v['RoleName'],
                            'ComId' => $v['ComId'],
                            'RoleState' => $v['RoleState'],
                            'Creatime' => $v['Creatime'],
                            'UpTime' => $v['UpTime'],
                            'AdminId' => $v['AdminId'],
                        );
                    }
            }

        return array(200,
        array(
            'list' => $list,
            'record_count' => $record_count
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