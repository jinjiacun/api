<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');

/**
   --直播室内容
*/
class RoomLiveController extends BaseController
{
    /**
       sql script:
       create table sp_room_live(LiveID char(50) primary key,
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

    protected $_module_name = 'room_live';
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

    /**
       
     */
    public function add($content){
        $data = $this->fill($content);
        
        if(!isset($data['AdminAvatar'])
        || !isset($data['AdminId'])
        || !isset($data['AdminName'])
        || !isset($data['ComId'])
        || !isset($data['LiveContent'])
        || !isset($data['LiveState'])
        || !isset($data['RoomId'])){
            return C('param_err');
        }
        
        $data['AdminAvatar'] = htmlspecialchars(trim($data['AdaminAvatar']));
        $data['AdminId'] = intval($data['AdminId']);
        $data['AdminName'] = htmlspecialchars(trim($data['Adminname']));
        $data['ComId'] = intval($data['ComId']);
        $data['LiveContent'] = htmlspecialchars(trim($data['LiveContent']));
        $data['LiveState'] = intval($data['LiveState']);
        $data['RoomId'] = intval($data['RoomId']);

        if('' == $data['AdminAvatar']
        || 0 >= $data['AdminId']
        || '' == $data['AdminName']
        || 0 >= $data['ComId']
        || '' == $data['LiveContent']
        || 0 > $data['LiveState']
        || 0 >= $data['RoomId']){
            return C('param_fmt_err');
        }
        
        $data['LiveTime'] = date('Y-m-d H:i:s');
        $data['LiveUpdate'] = date('Y-m-d H:i:s');

        if(False !== M($this->_module_name)->add($data)){
            return array(200,
            array(
                'is_success'=>0,
                'message'=>C('option_ok'),
                'id' => M()->getLastInsID()
            ));
        }

        return array(200,
        array(
            'is_success'=>1,
            'message'=>urlencode('错误')));
    }
    
    public function get_list($content)
    {
        list($data, $record_count) = parent::get_list($content);
        $list = array();
        if($data){
            foreach($data as $v){
                $list[] = array(
                    'LiveID'       => urlencode($v['LiveID']),
                    'AdminId'      => urlencode($v['AdminId']),
                    'AdminName'    => urlencode($v['AdminName']),
                    'AdminAvartar' => urlencode($v['AdminAvartar']),
                    'LiveContent'  => urlencode($v['LiveContent']),
                    'LiveQuote'    => urlencode($v['LiveQuote']),
                    'LiveTime'     => urlencode($v['LiveTime']),
                    'LiveVipGrade' => urlencode($v['LiveVipGrade']),
                    'LiveTop'      => urlencode($v['LiveTop']),
                    'RoomId'       => urlencode($v['RoomId']),
                    'LiveType'     => urlencode($v['LiveType']),
                    'LiveState'    => urlencode($v['LiveState']),
                    'LiveUpdate'   => urlencode($v['LiveUpdate']),
                    'ComId'        => urlencode($v['ComId']),
                );
            }
        }
        
        return array(200,
        array(
            'list'=>$list,
            'record_count'=>$record_count)
        );
    }
}