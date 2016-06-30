<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');

class ComTipController extends BaseController
{
    /**
       sql script:
       create table sp_com_tip(TipId bigint(19) primary key auto_increment,
       AdminId int,
       AdminName varchar(50),
       T_Type int,
       T_Con text,
       T_Time timestamp,
       T_State int,
       AdminAvatar text,
       ComId int
       )charset=utf8;
     */
    
    protected $_module_name = 'com_tip';
    protected $_key = 'TipId';


    protected $TipId;
    protected $AdminId;
    protected $AdminName;
    protected $T_Type;
    protected $T_Con;
    protected $T_Time;
    protected $T_State;
    protected $AdminAvatar;
    protected $ComId;

    public function get_list($content)
    {
        list($data, $record_count) = parent::get_list($content);
        $list = array();
        if($data)
            {
                foreach($data as $v)
                    {
                        $list[] = array(
                            'TipId' => $v['TipId'],
                            'AdminId' => $v['AdminId'],
                            'AdminName' => $v['AdminName'],
                            'T_Type' => $v['T_Type'],
                            'T_Con' => $v['T_Con'],
                            'T_Time' => $v['T_Time'],
                            'T_State' => $v['T_State'],
                            'AdminAvatar' => $v['AdminAvatar'],
                            'ComId' => $v['ComId'],
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