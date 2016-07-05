<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
   --管理员-角色-权限
   --功能:新增
   public function add
   --功能:批量新增
   public function add_all
   --功能:列表查询
   public function get_list
   --功能:查询单条
   public function get_info
   --功能:通过关键字查询单条
   public function get_info_by_key
*/
class ComRoMoController extends BaseController
{
    /**
       sql script:
       create table sp_com_ro_mo(RoMoId int primary key auto_increment,
       RoleId int,
       AMId int,
       Creatime timestamp
       )charset=utf8;
     */
    
    protected $_module_name = 'Com_ro_mo';
    protected $_key = 'RoMoId';
    
    protected $RoMoId;
    protected $RoleId;
    protected $AMId;
    protected $Creatime;
    
    /**
       功能:新增
       
       参数:
       @@input
       @param $RoleId int 角色id
       @param $AMId int 管理员id
     */
    public function add($content){
        $data = $this->fill($content);

        if(!isset($data['RoleId'])
        || !isset($data['AMId'])){
            return C('param_err');
        }

        $data['RoleId'] = intval($data['RoleId']);
        $data['AMId'] = intval($data['AMId']);

        if(0 >= $data['RoleId']
        || 0 >= $data['AMId']){
            return C('param_fmt_err');
        }
        
        if(False !== M($this->_module_name)->add($data)){
            return array(200,
            array(
                'is_success' => 0,
                'message' => C('option_ok'),
                'id' => M()->getLastInsID()
            ));
        }

        return array(200,
        array(
            'is_success' => 1,
            'message' => urlencode('添加失败'))
        );
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
                'message'    => C('option_ok'))
            );
        }
        
        return array(200,
        array(
            'is_success' => 1,
            'message' => urlencode('添加失败'))
        );
    }

    
    
    public function get_list($content)
    {
        list($data, $record_count) = parent::get_list($content);
        $list = array();
        if($data)
            {
                foreach($data as $v)
                    {
                        $list[] = array(
                            'RoMoId' => $v['RoMoId'],
                            'RoleId' => $v['RoleId'],
                            'AMId' => $v['AMId'],
                            'Creatime' => $v['Creatime']
                        );
                    }
            }
        
        return array(200,
        array(
            'list' => $list,
            'record_count'=>$record_count
        )
        );
    }
}