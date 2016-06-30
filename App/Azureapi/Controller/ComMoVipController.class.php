<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
   --管理--
*/
class ComMoVipController extends BaseController
{
    /**
       sql script:
       create table sp_com_mo_vip(MoVipId int primary key auto_increment,
       CVipId int,
       MoId int,
       VipLevel int,
       VipState int,
       ComId int,
       ComTag varchar(50),
       MVTime timestamp,
       UpTime timestamp
       )charset=utf8;
     */
    
    protected $_module_name = 'com_mo_vip';
    protected $_key = 'MoVipId';
    
    protected $MoVipId;
    protected $CVipId;
    protected $MoId;
    protected $VipLevel;
    protected $VipState;
    protected $ComId;
    protected $ComTag;
    protected $MVTime;
    protected $UpTime;

    public function get_list($content){
        list($data, $record_count) = parent::get_list($content);
        $list = array();
        if($data)
            {
                foreach($data as $v)
                    {
                        $list[] = array(
                            'MoVipId' => $v['MoVipId'],
                            'CVipId' => $v['CVipId'],
                            'MoId' => $v['MoId'],
                            'VipLevel' => $v['VipLevel'],
                            'VipState' => $v['VipState'],
                            'ComId' => $v['ComId'],
                            'ComTag' => $v['ComTag'],
                            'MVTime' => $v['MVTime'],
                            'UpTime' => $v['UpTime']
                        );
                    }
            }
        
        return arry(200,
        array(
            'list' => $list,
            'record_count' => $record_count
        )
        );
    }
}