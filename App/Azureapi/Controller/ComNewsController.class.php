<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
   --新闻管理--
   --功能:新增
   --功能:批量新增
   --功能:列表查询
   --功能:查询单条
   --功能:通过关键字查询单条
*/
class ComNewsController extends BaseController
{
    /**
       sql script:
       create table sp_com_news(NewId char(50) primary key,
       NewsTitle varchar(200),
       NewsDocReader text,
       ColumnId int,
       ComId int,
       NewsCon text,
       NewsPDF text,
       NewsState int,
       NewShowTime timestamp,
       NewTime timestatmp,
       NewUpTime timestamp,
       AdminId int,
       AdminName varchar(50),
       NewsUrl varchar(200),
       NewsImg varchar(200),
       NewsFlag int
       )charset=utf8;
     */
    
    protected $_module_name = 'com_news';
    protected $_key = 'NewId';

    protected $NewId;
    protected $NewsTitle;
    protected $NewsDocReader;
    protected $ColumnId;
    protected $ComId;
    protected $NewsCon;
    protected $NewsPDF;
    protected $NewsState;
    protected $NewShowTime;
    protected $NewTime;
    protected $NewUpTime;
    protected $AdminId;
    protected $AdminName;
    protected $NewsUrl;
    protected $NewsImg;
    protected $NewsFlag;

    /**
       功能:新增
     */
    public function add($content){
    }

    /**
       功能:批量新增
     */
    public function add_all($content){

    }

    /**
       功能:列表查询
     */
    public function get_list($content)
    {
        list($data, $record_count) = parent::get_list($content);
        $list = array();
        if($data)
            {
                foreach($data as $v)
                    {
                        $list[] = array(
                            'NewId' => $v['NewId'],
                            'NewsTitle' => $v['NewsTitle'],
                            'NewsDocReader' => $v['NewsDocReader'],
                            'ColumnId' => $v['ColumnId'],
                            'ComId' => $v['ComId'],
                            'NewsCon' => $v['NewsCon'],
                            'NewsPDF' => $v['NewsPDF'],
                            'NewsState' => $v['NewsState'],
                            'NewShowTime' => $v['NewShowTime'],
                            'NewTime' => $v['NewTime'],
                            'NewUpTime' => $v['NewUpTime'],
                            'AdminId' => $v['AdminId'],
                            'AdminName' => $v['AdminName'],
                            'NewsUrl' => $v['NewsUrl'],
                            'NewsImg' => $v['NewsImg'],
                            'NewsFlag' => $v['NewsFlag']           
                        );
                    }
            }

        return array(200,
        array(
            'list' => $list,
            'record_count' => $record_count,
        )
        );
    }

    /**
       功能:查询单条
     */
    public function get_info($content){

    }

    /**
       功能:通过关键字查询单条
     */
    public function get_info_by_key($content){

    }

}
