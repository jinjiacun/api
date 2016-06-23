<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');

class RoomliveController extends BaseController
{
    /**
       sql script:
       create table roomlive(LiveID char(50) primary key,
       AdminId int,
       AdminName varchar(50),
       AdminAvartar text,
       LiveContent text,
       LiveQuote text,
       LiveTime timestamp,
       LivevirGrade int,
       LiveTop int,
       RoomId int,
       LiveType int,
       LiveState int,
       LiveUpdate timestamp,
       ComId int
       )charset=utf8;
     */

    protected $_module_name = 'roomlive';
    protected $_key = 'LiveID';

    protected $LiveID;
    protected $AdminId;
    protected $AdminName;
    protected $AdminAvartar;
    protected $LiveContent;
    protected $LiveQuote;
    protected $LiveTime;
    protected $LiveVipGrade;
    protected $LiveTop;
    protected $RoomId;
    protected $LiveType;
    protected $LiveState;
    protected $LiveUpdate;
    protected $ComId;

    public function get_list($content)
    {
        list($data, $record_count) = parent::get_list($conent);
        $list = array();
        if($data)
            {
                foreach($data as $v)
                    {
                        $list[] = array(
                            'LiveID' => $v['LiveID'],
                            'AdminId' => $v['AdminId'],
                            'AdminName' => $v['AdminName'],
                            'AdminAvartar' => $v['AdminAvartar'],
                            'LiveContent' => $v['LiveContent'],
                            'LiveQuote' => $v['LiveQuote'],
                            'LiveTime' => $v['LiveTime'],
                            'LiveVipGrade' => $v['LiveVipGrade'],
                            'LiveTop' => $v['LiveTop'],
                            'RoomId' => $v['RoomId'],
                            'LiveType' => $v['LiveType'],
                            'LiveState' => $v['LiveState'],
                            'LiveUpdate' => $v['LiveUpdate'],
                            'ComId' => $v['ComId'],
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