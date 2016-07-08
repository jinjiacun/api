<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--会员登陆--
------------------------------------------------------------
function of api:
--功能:添加
public function add
--功能:列表查询
public function get_list
--功能:通过查询一条信息
public function get_info
--功能:通过关键字查询一条信息
public function get_info_by_key
------------------------------------------------------------
*/
class UserLoginController extends BaseController
{
    /**
       sql script:
       create table sp_user_login(ULId bigint(19) primary key,
       LoginId varchar(100),
       LoginType int,
       TypeName varchar(50),
       VFlag int,
       PswFlag int,
       User_Id bigint(19),
       LTExtend varchar(50),
       Createtime timestamp,
       LoginTime timestamp,
       LoginIp varchar(50)
       )charset=utf8;
     */
    
    protected $_module_name = 'user_login';
    protected $_key = 'ULId';
    
    protected $ULId;
    protected $LoginId;
    protected $LoginType;
    protected $TypeName;
    protected $VFlag;
    protected $PswFlag;
    protected $User_Id;
    protected $LTExtend;
    protected $Createtime;
    protected $LoginTime;
    protected $LoginIp;

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
                            'ULId'       => urlencode($v['ULId']),
                            'LoginId'    => urlencode($v['LoginId']),
                            'LoginType'  => urlencode($v['LoginType']),
                            'TypeName'   => urlencode($v['TypeName']),
                            'VFlag'      => urlencode($v['VFlag']),
                            'PswFlag'    => urlencode($v['PswFlag']),
                            'User_Id'    => urlencode($v['User_Id']),
                            'LTExtend'   => urlencode($v['LTExtend']),
                            'Createtime' => urlencode($v['Createtime']),
                            'LoginTime'  => urlencode($v['LoginTime']),
                            'LoginIp'    => urlencode($v['LoginIp']),
                        );
                    }
            }
        
        return array(200,
        array(
            'list'=>$list,
            'record_count'=>$record_count)
        );
    }

    /**
       功能:查询单条
     */
    public function get_info($content){
        $data = $this->fill($content);
        
        if(count($data) <= 0){
            return C('param_fmt_err');
        }

        $list = array();
        $tmp_one = M($this->_module_name)->where($data)->find();
        if($tmp_one){
            $list = array(
                            'ULId'       => urlencode($tmp_one['ULId']),
                            'LoginId'    => urlencode($tmp_one['LoginId']),
                            'LoginType'  => urlencode($tmp_one['LoginType']),
                            'TypeName'   => urlencode($tmp_one['TypeName']),
                            'VFlag'      => urlencode($tmp_one['VFlag']),
                            'PswFlag'    => urlencode($tmp_one['PswFlag']),
                            'User_Id'    => urlencode($tmp_one['User_Id']),
                            'LTExtend'   => urlencode($tmp_one['LTExtend']),
                            'Createtime' => urlencode($tmp_one['Createtime']),
                            'LoginTime'  => urlencode($tmp_one['LoginTime']),
                            'LoginIp'    => urlencode($tmp_one['LoginIp']),
                        );
        }
        

        return array(200, $list);
    }

    /**
       功能:通过关键字查询单条
     */
    public function get_info_by_key($content){
        $data = $this->fill($content);
        
        if(!isset($data[$this->_key])){
            return C('param_fmt_err');
        }

        $list = array();
        $tmp_one = M($this->_module_name)->find($data[$this->_key]);
        if($tmp_one){
            $list = array(
                            'ULId'       => urlencode($tmp_one['ULId']),
                            'LoginId'    => urlencode($tmp_one['LoginId']),
                            'LoginType'  => urlencode($tmp_one['LoginType']),
                            'TypeName'   => urlencode($tmp_one['TypeName']),
                            'VFlag'      => urlencode($tmp_one['VFlag']),
                            'PswFlag'    => urlencode($tmp_one['PswFlag']),
                            'User_Id'    => urlencode($tmp_one['User_Id']),
                            'LTExtend'   => urlencode($tmp_one['LTExtend']),
                            'Createtime' => urlencode($tmp_one['Createtime']),
                            'LoginTime'  => urlencode($tmp_one['LoginTime']),
                            'LoginIp'    => urlencode($tmp_one['LoginIp']),
                        );
        }
        

        return array(200, $list);
    }
}