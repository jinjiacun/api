<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
   --角色管理--
--功能:新增
--功能:批量新增
--功能:查询列表
--功能:查询一条信息
--功能:通过关键字查询一条信息
*/
class ComRoleController extends BaseController
{
    /**
       sql script:
       create table sp_com_role(RoleId int primary key auto_increment,
       RoleName varchar(50),
       ComId int,
       RoleState int,
       Creatime timestamp,
       UpTime timestamp,
       AdminId int
       )charset=utf8;
     */
    
    protected $_module_name = 'Com_role';
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
       
       参数:
       @@input
       @param $RoleName string 角色名称
       @param $ComId int 机构id
       @param $AdminId int 创建人
       @@out
       @param $is_success int
       (0-成功,1-失败)
     */
    public function add($content){
        $data = $this->fill($content);
        
        if(!isset($data['RoleName'])
        || !isset($data['ComId'])
        || !isset($data['AdminId'])){
            return C('param_err');
        }

        $data['RoleName'] = htmlspecialchars(trim($data['RoleName']));
        $data['ComId'] = intval(trim($data['ComId']));
        $data['AdminId'] = intval(trim($data['AdminId']));

        if('' == $data['RoleName']
        || 0 >= $data['ComId']
        || 0 > $data['AdminId']){
            return C('param_fmt_err');
        }
        
        $data['Creatime'] = date('Y-m-d H:i:s');
        $data['UpTime'] = date('Y-m-d H:i:s');
        
        if(False !== M($this->_module_name)->add($data)){
            return array(200,
            array(
                'is_success' => 0,
                'message' => C('option_ok'),
                'id' => M()->getLastInsID())
            );
        }

        return array(200,
        array(
            'is_success'=>1,
            'message'=>'错误'));
    }

    /**
       功能:批量新增
     */
    public function add_all($content){
        $data = $this->fill($content);
        
        if(False !== M($this->_module_name)->addAll($data)){
            return array(200,
            array(
                'is_success' => 0,
                'message' => C('option_ok'))
            );
        }
        
        return array(200,
        array(
            'is_success' =>1,
            'message' => urlencode('失败'))
        );
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
                            'RoleId' => intval($v['RoleId']),
                            'RoleName' => urlencode($v['RoleName']),
                            'ComId' => intval($v['ComId']),
                            'RoleState' => intval($v['RoleState']),
                            'Creatime' => $v['Creatime'],
                            'UpTime' => $v['UpTime'],
                            'AdminId' => intval($v['AdminId']),
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