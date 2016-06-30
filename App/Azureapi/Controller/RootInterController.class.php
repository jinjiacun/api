<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');

class RoomInterController extends BaseController
{
    /**
       sql script:
       create table sp_room_inter(InterId char(50) primary key,
       UserId int,
       UserNickName varchar(50),
       ReUserId bigint(19),
       InterContent text,
       InterTime timestamp,
       InterCheck int,
       InterReturn text,
       InterState int,
       RoomId int,
       ComId int,
       Intertype int
       )charset=utf8;
     */

    protected $_module_name = 'room_inter';
    protected $_key = 'InterId';
    
    protected $InterId;
    protected $UserId;
    protected $UserNickName;
    protected $ReUserId;
    protected $InterContent;
    protected $InterTime;
    protected $InterCheck;
    protected $InterReturn;
    protected $InterState;
    protected $RoomId;
    protected $ComId;
    protected $InterType;

    public function get_list($content)
    {
        list($data, $record_count) = parent::get_list($content);
        $list = array();
        if($data)
            {
                foreach($data as $v)
                    {
                        $list[] = array(
                            'InterId' => $v['InterId'],
                            'UserId' => $v['UserId'],
                            'UserNickName' => $v['UserNickName'],
                            'ReUserId' => $v['ReUserId'],
                            'InterContent' => $v['InterContent'],
                            'InterTime' => $v['InterTime'],
                            'InterCheck' => $v['InterCheck'],
                            'InterReturn' => $v['InterReturn'],
                            'InterState' => $v['InterState'],
                            'RoomId' => $v['RoomId'],
                            'ComId' => $v['ComId'],
                            'InterType' => $v['InterType']
                        );
                    }
            }
        
        return array(200,
        array(
            'list' => $list,
            'record_count'=>$record_count
        ),
        );
    }
}