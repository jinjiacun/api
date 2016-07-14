<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
   --消息管理--
*/
class ComMessageController extends BaseController
{
    /**
     sql script:
     create table sp_com_message(CMId char(50) primary key,
     ComId int,
     CMTitle varchar(100),
     CMCon text,
     CMState int,
     CMFlag int,
     CMTime timestamp,
     CMUpTime timestamp,
     FMId bigint(19)
     )charset=utf8;
     */

    protected $_module_name = 'com_message';
    protected $_key = 'CMId';
    
    protected $CMId;
    protected $ComId;
    protected $CMTitle;
    protected $CMCon;
    protected $CMState;
    protected $CMFlag;
    protected $CMTime;
    protected $CMUpTime;
    protected $FMId;

    public function get_list($content)
    {
        list($data, $record_count) = parent::get_list($content);
        $list = array();
        if($data)
            {
                foreach($data as $v)
                    {
                        $list[] = array(
                            'CMId'     => urlencode($v['CMId']),
                            'ComId'    => urlencode($v['ComId']),
                            'CMTitle'  => urlencode($v['CMTitle']),
                            'CMCon'    => urlencode(base64_encode($v['CMCon'])),
                            'CMState'  => urlencode($v['CMState']),
                            'CMFlag'   => urlencode($v['CMFlag']),
                            'CMTime'   => urlencode($v['CMTime']),
                            'CMUpTime' => urlencode($v['CMUpTime']),
                            'FMId'     => urlencode($v['FMId'])
                        );
                    }
            }
        
        return array(200,
        array(
            'list'=> $list,
            'record_count'=>$record_count
        )
        );
    }
}