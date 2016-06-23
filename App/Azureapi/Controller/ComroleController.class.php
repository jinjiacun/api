<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
   --管理--
*/
class ComroleController extends BaseController
{
    /**
       sql script:
       create table comrole(RoleId int primary key auto_increment,
       RoleName varchar(50),
       ComId int,
       RoleState int,
       Creatime timestamp,
       UpTime timestamp,
       AdminId int
       )charset=utf8;
     */
    
    protected $_module_name = 'monrole';
    protected $_key = 'RoleId';

    protected $RoleId;
    protected $RoleName;
    protected $ComId;
    protected $RoleState;
    protected $Creatime;
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
                            'RoleId' => $v['RoleId'],
                            'RoleName' => $v['RoleName'],
                            'ComId' => $v['ComId'],
                            'RoleState' => $v['RoleState'],
                            'Creatime' => $v['Creatime'],
                            'UpTime' => $v['UpTime'],
                            'AdminId' => $v['AdminId'],
                        );
                    }
            }

        return array(200,
        array(
            'list' => $list,
            'record_count' => $record_count
        )
        );
    }
}