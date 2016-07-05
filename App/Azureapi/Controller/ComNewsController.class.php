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
        $data = $this->fill($content);
        
        if(!isset($data['NewId'])
        || !isset($data['AdminId'])
        || !isset($data['AdminName'])
        || !isset($data['NewsTitle'])
        || !isset($data['NewsCon'])
        || !isset($data['NewsDocReader'])
        || !isset($data['ColumnId'])
        || !isset($data['ComId'])
        || !isset($data['NewsState'])
        || !isset($data['NewsFlag'])
        || !isset($data['NewShowTime'])){
            return C('param_err');
        }
        
        $data['NewId'] = htmlspecialchars(trim($data['NewId']));
        $data['AdminId'] = intval($data['AdminId']);
        $data['AdminName'] = htmlspecialchars(trim($data['AdminName']));
        $data['NewsTitle'] = htmlspecialchars(trim($data['NewsTitle']));
        $data['NewsCon'] = htmlspecialchars(trim($data['NewsCon']));
        $data['NewsDocReader'] = htmlspecialchars(trim($data['NewsDocReader']));
        $data['ColumnId'] = intval($data['ColumnId']);
        $data['ComId'] = intval($data['ComId']);
        $data['NewsState'] = intval($data['NewsState']);
        $data['NewsFlag'] = intval($data['NewsFlag']);
        $data['NewShowTime'] = htmlspecialchars(trim($data['NewShowTime']));

        if('' == $data['NewId']
        || 0 > $data['AdminId']
        || '' == $data['AdminName']
        || '' == $data['NewsTitle']
        || '' == $data['NewsCon']
        || '' == $data['NewsDocReader']
        || 0 >  $data['ColumnId']
        || 0 >  $data['NewsState']
        || 0 >  $data['NewsFlag']
        || 0 >  $data['NewShowTime']
        || 0 >  $data['ComId']){
            return C('param_fmt_err');
        }
        
        $data['NewTime'] = date('Y-m-d H:i:s');
        $data['NewUpTime'] = date('Y-m-d H:i:s');
        
        if(False !== M($this->_module_name)->add($data)){
            return array(200,
            array(
                'is_success'=> 0,
                'message'=>C('option_ok'),
                'id'=>M()->getLastInsID()
            ));
        }

        return array(200,
        array(
            'is_success'=>1,
            'message'=>C('option_fail')
        ));
    }

    /**
       功能:批量新增
     */
    public function add_all($content){
        $data = $this->fill($content);
            
        if(False !== M($this->_module_name)->addAll($data)){
            return array(200,
            array(
                'is_success' => 0,
                'message'    => C('option_ok'))
            );
        }
        
        return array(200,
        array(
            'is_success' => 1,
            'message' => urlencode('添加失败'))
        );  
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
        $data = $this->fill($content);
        
        if(count($data) <= 0){
            return C('param_err');
        }
        
        $list = array();
        $tmp_one = M($this->_module_name)->where($data)->find();
        if($tmp_one){
            if(count($tmp_one) >0){
                $list = array(
                            'NewId'         => $tmp_one['NewId'],
                            'NewsTitle'     => $tmp_one['NewsTitle'],
                            'NewsDocReader' => $tmp_one['NewsDocReader'],
                            'ColumnId'      => $tmp_one['ColumnId'],
                            'ComId'         => $tmp_one['ComId'],
                            'NewsCon'       => $tmp_one['NewsCon'],
                            'NewsPDF'       => $tmp_one['NewsPDF'],
                            'NewsState'     => $tmp_one['NewsState'],
                            'NewShowTime'   => $tmp_one['NewShowTime'],
                            'NewTime'       => $tmp_one['NewTime'],
                            'NewUpTime'     => $tmp_one['NewUpTime'],
                            'AdminId'       => $tmp_one['AdminId'],
                            'AdminName'     => $tmp_one['AdminName'],
                            'NewsUrl'       => $tmp_one['NewsUrl'],
                            'NewsImg'       => $tmp_one['NewsImg'],
                            'NewsFlag'      => $tmp_one['NewsFlag']           
                );
            }
        }

        return array(200,$list);
    }

    /**
       功能:通过关键字查询单条
     */
    public function get_info_by_key($content){
         $data = $this->fill($content);
         if(!isset($data[$this->_key])){
             return C('param_err');
         }
         
         $list = array();
         $tmp_one = M($this->_module_name)->find($data[$this->_key]);
         if($tmp_one){
            if(count($tmp_one) >0){
                $list = array(
                            'NewId'         => $tmp_one['NewId'],
                            'NewsTitle'     => $tmp_one['NewsTitle'],
                            'NewsDocReader' => $tmp_one['NewsDocReader'],
                            'ColumnId'      => $tmp_one['ColumnId'],
                            'ComId'         => $tmp_one['ComId'],
                            'NewsCon'       => $tmp_one['NewsCon'],
                            'NewsPDF'       => $tmp_one['NewsPDF'],
                            'NewsState'     => $tmp_one['NewsState'],
                            'NewShowTime'   => $tmp_one['NewShowTime'],
                            'NewTime'       => $tmp_one['NewTime'],
                            'NewUpTime'     => $tmp_one['NewUpTime'],
                            'AdminId'       => $tmp_one['AdminId'],
                            'AdminName'     => $tmp_one['AdminName'],
                            'NewsUrl'       => $tmp_one['NewsUrl'],
                            'NewsImg'       => $tmp_one['NewsImg'],
                            'NewsFlag'      => $tmp_one['NewsFlag']           
                );
            }
        }
        
         return array(200,$list);
    }

}
