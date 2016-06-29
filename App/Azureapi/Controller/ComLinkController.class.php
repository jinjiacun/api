<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
   --管理--
*/
class ComlinkController extends BaseController
{
    /**
     sql script:
     create table comlink(LinkId int primary key auto_increment,
     LinkName varchar(50),
     LinkUrl varchar(200),
     LinkImg varchar(200),
     LinkAlt varchar(100),
     LinkState int,
     LinkType int,
     ComId int,
     LinkTime timestamp,
     LinkAdmin int
     )charset=utf8;
     */

    protected $_module_name = 'comlink';
    protected $_key = 'LinkId';
    
    protected $LinkId;
    protected $LinkName;
    protected $LinkUrl;
    protected $LinkImg;
    protected $LinkAlt;
    protected $LinkState;
    protected $LinkType;
    protected $ComId;
    protected $LinkTime;
    protected $LinkAdmin;

    public function get_list($content)
    {
        list($data, $record_count) = parent::get_list($content);
        $list = array();
        if($data)
            {
                foreach($data as $v)
                    {
                        $list[] = array(
                            'LinkId' => $v['LinkId'],
                            'LinkName' => $v['LinkName'],
                            'LinkUrl' => $v['LinkUrl'],
                            'LinkImg' => $v['LinkImg'],
                            'LinkAlt' => $v['LinkAlt'],
                            'LinkState' => $v['LinkState'],
                            'LinkType' => $v['LinkType'],
                            'ComId' => $v['ComId'],
                            'LinkTime' => $v['LinkTime'],
                            'LinkAdmin' => $v['LinkAdmin']
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