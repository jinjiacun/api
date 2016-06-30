<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');

class ComUserController extends BaseController
{
    /**
       sql script:
       create table sp_com_user(ComUserId bigint(19) primary key auto_increment,
       User_Id bigint(19),
       ComId int,
       ComTag varchar(50),
       UState int,
       VipLevel int,
       ComTime timestamp,
       AgreeTime timestamp
       )charset=utf8;
     */
    
    protected $_module_name = 'com_user';
    protected $_key = 'ComUserId';
    
    protected $ComUserId;
    protected $User_Id;
    protected $ComId;
    protected $ComTag;
    protected $UState;
    protected $VipLevel;
    protected $ComTime;
    protected $AgreeTime;

    public function get_list($content)
    {
        list($data, $record_count) = parent::get_list($content);
        $list = array();
        if($data)
            {
                foreach($data as $v)
                    {
                        $list[] = array(
                            'ComUserId' => $v['ComUserId'],
                            'User_Id' => $v['User_Id'],
                            'ComId' => $v['ComId'],
                            'ComTag' => $v['ComTag'],
                            'UState' => $v['UState'],
                            'VipLevel' => $v['VipLevel'],
                            'ComTime' => $v['ComTime'],
                            'AgreeTime' => $v['AgreeTime'],
                        );
                    }
            }

        return array(200,
        array(
            'list' => $list,
            'record_count' => $record_count)
        );
    }
}