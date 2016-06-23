<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
   --管理--
*/
class CommoController extends BaseController
{
    /**
       sql script:
       create table commo(ComMoId int primary key auto_increment,
       ComId int,
       MoId int,
       MoType int,
       CMState int,
       CMTime timestamp,
       CMUpTime timestamp,
       AdminId int
       )charset=utf8;
     */
    
    protected $_module_name = 'commo';
    protected $_key = 'ComMoId';
    
    protected $ComMoId;
    protected $ComId;
    protected $MoId;
    protected $MoType;
    protected $CMState;
    protected $CMTime;
    protected $CMUpTime;
    protected $AdminId;

    public function get_list($content){
        list($data, $record_count) = parent::get_list($content);
        $list = array();
        if($data)
            {
                foreach($data as $v)
                    {
                        'ComMoId' => $v['ComMoId'],
                            'ComId' => $v['ComId'],
                            'MoId' => $v['MoId'],
                            'MoType' => $v['MoType'],
                            'CMState' => $v['CMState'],
                            'CMTime' => $v['CMTime'],
                            'CMUpTime' => $v['CMUpTime'],
                            'Adminid' => $v['AdminId'],
                    }
            }
        
        return array(200,
        array(
            'list' => $list,
            'record_count' => $record_count)
        );
    }
}