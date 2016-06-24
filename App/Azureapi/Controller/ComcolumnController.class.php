<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');

/**
--栏目管理--
--功能:新增
public function add
--功能:列表查询
public function get_list
--功能:查询一条信息
public function get_info
--功能:通过关键字查询一条信息
public function get_info_by_key
-----------------------------------------------------------------
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
    
    /**
       功能:新增

       参数:
       @param 
     */
    public function add($content){
        return array(200,
        array(
            'is_success'=>1,
            'message'=>'错误'));
    }

    /**
       功能:查询列表

       参数:
       @param 
     */
    public functin get_list($content)
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

        return array(200,
        array(
            'list'=>$list,
            'record_count'=>$record_count));
    }

    /**
       功能:查询一条信息
     */
    public funtion get_info($content){
        $list = array();

        return array(200,
        $list);
    }

    /**
       功能:通过关键字查询一条信息
     */
    public function get_info_by_key($content){
        $list = array();
        return array(200,
        $list);
    }
}