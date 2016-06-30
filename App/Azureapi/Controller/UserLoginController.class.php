<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');

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

    public function get_list($content)
    {
        list($data, $record_count) = parent::get_list($content);
        $list = array();
        if($data)
            {
                foreach($data as $v)
                    {
                        $list[] = array(
                            'ULId' => $v['ULId'],
                            'LoginId' => $v['LoginId'],
                            'LoginType' => $v['LoginType'],
                            'TypeName' => $v['TypeName'],
                            'VFlag' => $v['VFlag'],
                            'PswFlag' => $v['PswFlag'],
                            'User_Id' => $v['User_Id'],
                            'TLExtend' => $v['TLExtend'],
                            'Createtime' => $v['Createtime'],
                            'LoginTime' => $v['LoginTime'],
                            'LoginIp' => $v['LoginIp'],
                        );
                    }
            }
        
        return array(200,
        array(
            'list'=>$list,
            'record_count'=>$record_count
        )
        );
    }
}