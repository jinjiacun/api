<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');

/**
--管理--
 */
class ComcolumnController extends BaseController
{
    /**
     * sql script:
     create table comcolumn(ColumnId int primary key auto_increment,
     ColumnName varchar(50),
     ComId int(10),
     ColumnPId int(10),
     ColumnState int(10),
     ColumnPath varchar(200),
     ColumnTime timestamp,
     AdminId int
     )charset=utf8;
     */

    protected $_module_name = 'comcolumn';
    protected $_key = 'ColumnId';

    protected $ColumnId;
    protected $ColumnName;
    protected $ComId;
    protected $ColumnPId;
    protected $ColumnState;
    protected $ColumnPath;
    protected $ColumnTime;
    protected $AdminId;
    
    public functin get_list()
    {
        list($data, $record_count) = parent::get_list($content);
        $list = array();
        if($data)
            {
                $list[] = array(
                    'ColumnId' => $v['ColumnId'],
                    'ColumnName' => $v['ColumnName'],
                    'ComId' => $v['ComId'],
                    'ColumnPId' => $v['ColumnPId'],
                    'ColumnState' => $v['ColumnState'],
                    'ColumnPath' => $v['ColumnPath'],
                    'ColumnTime' => $v['ColumnTime'],
                    'AdminId' => $v['AdminId'],
                );
            }
    }
}