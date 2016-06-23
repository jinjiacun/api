<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');

class ComroomController extends BaseController
{
    /**
       sql script:
       create table comroom(RoomId int primary key auto_increment,
       RoomName varchar(50),
       RoomTitle varchar(200),
       RoomIntro text,
       RoomTeacher text,
       RoomMEtip int,
       RoomPopSet int,
       RoomHisPop int,
       ComId int,
       RoomLivetime varchar(200),
       RoomMaximage varchar(200),
       RoomMinimage varchar(200),
       RoomLiveLimit int,
       RoomLivehisLimit int,
       RoomVipType int,
       RoomInterLimit int,
       LiveNum int,
       InterNum int,
       RoomEnable int,
       RoomLiveState int,
       RoomAddTime timestamp,
       RoomAddAdmin int,
       RoomUpdateTime timestamp,
       RoomUpdateAdmin int,
       RoomType int
       )charset=utf8;
     */
    
    protected $_module_name = 'comroom';
    protected $_key = 'RoomId';

    protected $RoomId;
    protected $RoomName;
    protected $RoomTitle;
    protected $RoomIntro;
    protected $RoomTeacher;
    protected $RoomMEtip;
    protected $RoomPopSet;
    protected $RoomHisPop;
    protected $ComId;
    protected $RoomLivetime;
    protected $RoomMaximage;
    protected $RoomMinimage;
    protected $RoomLiveLimit;
    protected $RoomLivehisLimit;
    protected $RoomVipType;
    protected $RoomInterLimit;
    protected $LiveNum;
    protected $InterNum;
    protected $RoomEnable;
    protected $RoomLiveState;
    protected $RoomAddTime;
    protected $RoomAddAdmin;
    protected $RoomUpdateTime;
    protected $RoomUpdateAdmin;
    protected $RoomType;
    
    public function get_list($content)
    {
        list($data, $record_count) = parent::get_list($content);
        $list = array();
        if($data)
            {
                foreach($data as $v)
                    {
                        $list[] = array(
                            'RoomId' => $v['RoomId'],
                            'RoomName' => $v['RoomName'],
                            'RoomTitle' => $v['RoomTitle'],
                            'RoomIntro' => $v['RoomIntro'],
                            'RoomTeacher' => $v['RoomTeacher'],
                            'RoomMEtip' => $v['RoomMEtip'],
                            'RoomPopSet' => $v['RoomPopSet'],
                            'RoomHisPop' => $v['RoomHisPop'],
                            'ComId' => $v['ComId'],
                            'RoomLivetime' => $v['RoomLivetime'],
                            'RoomMaximage' => $v['RoomMaximage'],
                            'RoomMinimage' => $v['RoomMinimage'],
                            'RoomLiveLimit' => $v['RoomLiveLimit'],
                            'RoomLivehisLimit' => $v['RoomLivehisLimit'],
                            'RoomVipType' => $v['RoomVipType'],
                            'RoomInterLimit' => $v['RoomInterLimit'],
                            'LiveNum' => $v['LiveNum'],
                            'InterNum' => $v['InterNum'],
                            'RoomEnable' => $v['RoomEnable'],
                            'RoomLiveState' => $v['RoomLiveState'],
                            'RoomAddTime' => $v['RoomAddTime'],
                            'RoomUpdateTime' => $v['RoomUpdateTime'],
                            'RoomUpdateAdmin' => $v['RoomUpdateAdmin'],
                            'RoomType' => $v['RoomType'] 
                        );
                    }   
            }

        return array(200,
        array(
            'list'=> $list,
            'record_count'=> $record_count
        )
        );
    }
}