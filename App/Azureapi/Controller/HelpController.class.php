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

}