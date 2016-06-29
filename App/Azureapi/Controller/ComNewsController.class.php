<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
   --管理--
*/
class ComnewsController extends Controller
{
    /**
       sql script:
       create table comnews(NewsId char(50) primary key,
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
    public function get_list($content)
    {
        list($data, $record_count) = parent::get_list($content);
        $list = array();
        if($data)
            {
                foreach($data as $v)
                    {
                        $list[] = array(
                            'NewsId' => $v['NewsId'],
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
}