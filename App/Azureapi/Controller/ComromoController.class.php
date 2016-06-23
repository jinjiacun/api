<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');

class ComromoController extends BaseController
{
    /**
       sql script:
       create table comromo(RoMoId int primary key auto_increment,
       RoleId int,
       AMId int,
       Creatime timestamp
       )charset=utf8;
     */
    
    protected $_module_name = 'comromo';
    protected $_key = 'RoMoId';
    
    protected $RoMoId;
    protected $RoleId;
    protected $AMId;
    protected $Creatime;

    public function get_list($content)
    {
        list($data, $record_count) = parent::get_list($content);
        $list = array();
        if($data)
            {
                foreach($data as $v)
                    {
                        $list[] = array(
                            'RoMoId' => $v['RoMoId'],
                            'RoleId' => $v['RoleId'],
                            'AMId' => $v['AMId'],
                            'Creatime' => $v['Creatime']
                        );
                    }
            }
        
        return array(200,
        array(
            'list' => $list,
            'record_count'=>$record_count
        )
        );
    }
}