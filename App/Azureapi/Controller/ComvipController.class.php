<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');

class ComvipController extends BaseController
{
    /**
       sql script:
       create table comvip(CVipId int primary key auto_increment,
       ComId int,
       CVipIntro text,
       VipLevel int,
       VipState int,
       VipName varchar(50),
       CVipTime timestamp,
       UpTime timestamp,
       AdminId int
       )charset=utf8;
     */
    
    protected $_module_name = 'comvip';
    protected $_key = 'CVipId';

    protected $CVipId;
    protected $ComId;
    protected $CVipIntro;
    protected $VipLevel;
    protected $VipState;
    protected $VipName;
    protected $CVipTime;
    protected $UpTime;
    protected $AdminId;

    public function get_list($content)
    {
        list($data, $record_count) = parent::get_list($content);
        $list = array();
        if($data)
            {
                foreach($data as $v)
                    {
                        $list[] = array(
                            'CVipId' => $v['CVipId'],
                            'ComId' => $v['ComId'],
                            'CVipIntro' => $v['CVipIntro'],
                            'VipLevel' => $v['VipLevel'],
                            'VipState' => $v['VipState'],
                            'VipName' => $v['VipName'],
                            'CVipTime' => $v['CVipTime'],
                            'UpTime' => $v['UpTime'],
                            'AdminId' => $v['AdminId']
                        );
                    }
            }

        return array(200,
        array(
            'list'=>$list
            'record_count'=>$record_count
        )
        );
    }
}