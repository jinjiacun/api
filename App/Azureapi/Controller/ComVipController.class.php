<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
   --vip等级
   --功能:新增
   --功能:批量新增
   --功能:列表查询
   --功能:查询单条
   --功能:通过关键字查询单条
 */
class ComVipController extends BaseController
{
    /**
       sql script:
       create table sp_com_vip(CVipId int primary key auto_increment,
       ComId int,
       CVipIntro text,
       VipLevel int,
       VipState int,
       VipName varchar(50),
       CVipTime timestamp,
       UpTime timestamp,
       AdminId int
       )charset=utf8;
     */
    
    protected $_module_name = 'com_vip';
    protected $_key = 'CVipId';

    protected $CVipId;
    protected $ComId;
    protected $CVipIntro;
    protected $VipLevel;
    protected $VipState;
    protected $VipName;
    protected $CVipTime;
    protected $UpTime;
    protected $AdminId;
    
    /**
       功能:新增
       
       参数:
       @@input
       @param $VipLevel int
       @param $VipName string
       @param $VipState int
       @param $AdminId int
       @param $ComId int
     */
    public function add($content){
        $data = $this->fill($content);
        
        if(!isset($data['VipLevel'])
        || !isset($data['VipName'])
        || !isset($data['VipState'])
        || !isset($data['AdminId'])
        || !isset($data['ComId'])
        ){
            return C("param_err");
        }

        $data['VipLevel'] = intval($data['VipLevel']);
        $data['VipName'] = htmlspecialchars(trim($data['VipName']));
        $data['VipState'] = intval($data['VipState']);
        $data['AdminId'] = intval($data['AdminId']);
        $data['ComId'] = intval($data['ComId']);

        if(0< $data['VipLevel']
        || '' == $data['VipName']
        || 0 < $data['VipState']
        || 0 < $data['AdminId']
        || 0 < $data['ComId']){
            return C('param_fmt_err');
        }
        
        if(False !== M($this->_module_name)->add($data)){
            return array(200,
            array(
                'is_success'=>0,
                'message'=>C('option_ok'),
                'id'=>M()->getLastInsID()
            )
            );
        }
        return array(200,
        array(
            'is_success'=>1,
            'message'=>urlencode('错误'))
        );
    }

    /**
       功能:批量新增
     */
    public function add_all($content){
        $data = $this->fill($content);
        
        if(False !== M($this->_module_name)->addAll($data)){
            return array(200,
            array(
                'is_success'=>0,
                'message'=>C('option_ok')
        }

        return array(200,
        array(
            'is_success'=>1,
            'message'=>urlencode('错误'))
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
                            'CVipId' => $v['CVipId'],
                            'ComId' => $v['ComId'],
                            'CVipIntro' => $v['CVipIntro'],
                            'VipLevel' => $v['VipLevel'],
                            'VipState' => $v['VipState'],
                            'VipName' => $v['VipName'],
                            'CVipTime' => $v['CVipTime'],
                            'UpTime' => $v['UpTime'],
                            'AdminId' => $v['AdminId']
                        );
                    }
            }

        return array(200,
        array(
            'list'=>$list
            'record_count'=>$record_count
        )
        );
    }
}