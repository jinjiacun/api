<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
class HelpController extends BaseController
{
    public function get_dictionary(){
        $_list = array();
        //管理员id及其名称
        $list = M('comadmin')->field("ComId,AdminName")->select();
        if($list){
            foreach($list as $v){
                $_list['admin'][intval($v['ComId'])] = urlencode($v['AdminName']);
            }
        }
        
        //角色id及其名称
        unset($list);
        $list = M('comrole')->field("RoleId,RoleName")->select();
        if($list){
            foreach($list as $v){
                $_list['role'][intval($v['RoleId'])] = urlencode($v['RoleName']);
            }
        }
        
        return array(200,
        $_list);
    }

}