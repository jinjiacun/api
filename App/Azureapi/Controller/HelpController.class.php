<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
class HelpController extends BaseController
{
    public function get_dictionary(){
        $_list = array();
        //管理员id及其名称
        $list = M('com_admin')->field("AdminId,AdminName")->select();
        if($list){
            foreach($list as $v){
                $_list['admin'][intval($v['AdminId'])] = urlencode($v['AdminName']);
            }
        }
        
        //角色id及其名称
        unset($list);
        $list = M('com_role')->field("RoleId,RoleName")->select();
        if($list){
            foreach($list as $v){
                $_list['role'][intval($v['RoleId'])] = urlencode($v['RoleName']);
            }
        }

        //机构id及其名称
        unset($list);
        $list = M('com_table')->field("ComId,ComName")->select();
        if($list){
            foreach($list as $v){
                $_list['com'][intval($v['ComId'])] = urlencode($v['ComName']);
            }
        }
        
        return array(200,
        $_list);
    }

    /**
       功能:通过sql查询信息
     */
    private function get_by_sql($sql){
        //$data = $this->fill($content);
        $result = M()->query($sql);
        return array(200,
        $result
        );
    }

    /**
       功能:通过模板查询统计

       参数:
       @@input
       @param $ComId int 机构id
       @param $template_name string 模板名称
    */
    public function stat_by_template($content){
        $data = $this->fill($content);
        if(!isset($data['ComId'])){
            return C('param_err');
        }
        $_template_sql_list = C('TEMPLATE_SQL');
        
        $sql = $_template_sql_list[$data['template_name']];
        $sql = str_replace('<ComId>', $data['ComId'], $sql);
        $sql = str_replace("\r\n", '', $sql);
        return $this->get_by_sql($sql);
    }
    
}