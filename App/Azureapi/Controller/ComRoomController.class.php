<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');

/**
   --直播室
   --功能:新增
   --功能:批量新增
   --功能:列表查询
   --功能:查询单条
   --功能:通过关键字查询单条
*/
class ComRoomController extends BaseController
{
    /**
       sql script:
       create table sp_com_room(RoomId int primary key auto_increment,
       RoomName varchar(50),
       RoomTitle varchar(200),
       RoomIntro text,
       RoomTeacher text,
       RoomMEtip int,
       RoomPopSet int,
       RoomHisPop int,
       ComId int,
       RoomLivetime varchar(200),
       RoomMaximage varchar(200),
       RoomMinimage varchar(200),
       RoomLiveLimit int,
       RoomLivehisLimit int,
       RoomVipType int,
       RoomInterLimit int,
       LiveNum int,
       InterNum int,
       RoomEnable int,
       RoomLiveState int,
       RoomAddTime timestamp,
       RoomAddAdmin int,
       RoomUpdateTime timestamp,
       RoomUpdateAdmin int,
       RoomType int
       )charset=utf8;
     */
    
    protected $_module_name = 'com_room';
    protected $_key = 'RoomId';

    protected $_fields = array(
        'RoomId'          => 'int',
        'RoomName'        => 'str',      //直播室名称
        'RoomTitle'       => 'str',      //直播室主题
        'RoomIntro'       => 'str',      //直播室简介
        'RoomTeacher'     => 'str',      //驻场讲师多个id,之间以','分割
        'RoomMEtip'       => 'int',      //多空观点(1-做多,-1-做空,0-震荡)
        'RoomPopSet'      => 'int',      //人气初始值
        'RoomHisPop'      => 'int',      //历史最高人气
        'ComId'           => 'int',      //机构id
        'RoomLivetime'    => 'str',      //直播时间:(09:00-10:00|13:00-14:00|20:00-21:00)
        'RoomMaximage'    => 'str',      //直播室大图
        'RoomMinimage'    => 'str',      //直播室小图
        'RoomLiveLimit'   => 'int',      //查看直播等级权限:0-7
        'RoomLivehisLimit'=> 'int',      //查看直播历史等级权限:0-7
        'RoomVipType'     => 'int',      //进入直播室vip等级类型(0-最低等级,1-指定等级)
        'RoomInterLimit'  => 'int',      //参与互动权限
        'LiveNum'         => 'int',
        'InterNum'        => 'int',
        'RoomEnable'      => 'int',       //直播室状态(0-关闭,1-开启)
        'RoomLiveState'   => 'int',       //直播状态(0-直播结束,1-直播中,2-直播暂停)
        'RoomAddTime'     => 'str',       //直播室增加时间
        'RoomAddAdmin'    => 'int',       //直播室创建人
        'RoomUpdateTime'  => 'str',       //直播室更新时间
        'RoomUpdateAdmin' => 'int',       //直播室更新人
        'RoomType'        => 'int',       //直播室类型:(0-正常直播室,1-镜像直播室)
    );

    //新增检查字段
    protected $_add_check_field = array(
        'RoomAddAdmin'     => 'int', 
        'RoomUpdateAdmin'  => 'int',
        'ComId'            => 'int',
        'RoomEnable'       => 'int',
        'RoomHisPop'       => 'int',
        'RoomInterLimit'   => 'int',
        'RoomLiveLimit'    => 'int',
        'RoomIntro'        => 'str',
        'RoomTitle'        => 'str',
        'RoomType'         => 'int',
        'RoomLiveState'    => 'int',
        'RoomTeacher'      => 'str',
        'RoomMaximage'     => 'str',
        'RoomLivehisLimit' => 'int',
        'RoomLivetime'     => 'str',
        'RoomMEtip'        => 'int',
        'RoomMinimage'     => 'str',
        'RoomName'         => 'str',
        'RoomPopSet'       => 'int',
        );
    
    /**
       功能:新增
       
       参数:
       @@input
       @param $RoomAddAdmin int 直播室创建人
       @param $RoomUpdateAdmin int 直播室更新人
       @param $ComId int 机构id
       @param $RoomEnable int 直播室是否可用
       @param $RoomHisPop int 历史最高人气
       @param $RoomInterLimit int 参与互动权限
       @param $RoomLiveLimit int 查看直播等级权限:0-7
       @param $RoomTitle string 直播室主题
       @param $RoomType int 直播室类型
       @param $RoomLiveState int 直播状态
       @param $RoomTeacher string 主场讲师
       @param $RoomMaximage string 直播室大图
       @param $RoomLivehisLimit int 查看历史直播权限
       @param $RoomLiveTime string 直播时间
       @param $RoomMEtip int 多空观点
       @param $RoomMinimage 直播室小图
       @param $RoomName string  直播室名称
       @param $RoomPopSet int 人气初始值
     */
    public function add($content){
        $data = $this->fill($content);
        
        foreach($this->add_check_field as $k=>$v){
            if(!isset($$k)){
                session(C('param_field'), $k);
                return C('param_err');
            }
            
            if('int' == $v){
                $data[$k] = intval($data[$k]);   
            }
            else{
                $data[$k] = htmlspecialchars(trim($data[$k]));
            }

            if('int' == $v){
                if($data[$k] < 0){
                    session(C('param_field'), $k);
                    return C('param_fmt_err');
                }
            }
            else{
                if($data[$k] == ''){
                    session(C('param_field'), $k);
                    return C('param_fmt_err');
                }
            }
        }
        unset($k, $v);

        $data['RoomAddTime'] = date('Y-m-d H:i:s');
        $data['RoomUpdateTime'] = date('Y-m-d H:i:s');

        if(False !== M($this->_module_name)->add($data)){
            return array(200,
            array(
                'is_success'=>0,
                'message'=>C('option_ok'),
                'id' => M()->getLastInsID()
            ));
        }

        return array(200,
        array(
            'is_success'=>1,
            'message'=>urlencode('错误')));
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
                            'RoomId'           => urlencode($v['RoomId']),
                            'RoomName'         => urlencode($v['RoomName']),
                            'RoomTitle'        => urlencode($v['RoomTitle']),
                            'RoomIntro'        => urlencode($v['RoomIntro']),
                            'RoomTeacher'      => urlencode($v['RoomTeacher']),
                            'RoomMEtip'        => urlencode($v['RoomMEtip']),
                            'RoomPopSet'       => urlencode($v['RoomPopSet']),
                            'RoomHisPop'       => urlencode($v['RoomHisPop']),
                            'ComId'            => urlencode($v['ComId']),
                            'RoomLivetime'     => urlencode($v['RoomLivetime']),
                            'RoomMaximage'     => urlencode($v['RoomMaximage']),
                            'RoomMinimage'     => urlencode($v['RoomMinimage']),
                            'RoomLiveLimit'    => urlencode($v['RoomLiveLimit']),
                            'RoomLivehisLimit' => urlencode($v['RoomLivehisLimit']),
                            'RoomVipType'      => urlencode($v['RoomVipType']),
                            'RoomInterLimit'   => urlencode($v['RoomInterLimit']),
                            'LiveNum'          => urlencode($v['LiveNum']),
                            'InterNum'         => urlencode($v['InterNum']),
                            'RoomEnable'       => urlencode($v['RoomEnable']),
                            'RoomLiveState'    => urlencode($v['RoomLiveState']),
                            'RoomAddTime'      => urlencode($v['RoomAddTime']),
                            'RoomUpdateTime'   => urlencode($v['RoomUpdateTime']),
                            'RoomUpdateAdmin'  => urlencode($v['RoomUpdateAdmin']),
                            'RoomType'         => urlencode($v['RoomType']) 
                        );
                    }   
            }

        return array(200,
        array(
            'list'=> $list,
            'record_count'=> $record_count)
        );
    }

    /**
       功能:查询单条
     */
    public function get_info($content){
        $data = $this->fill($content);
        
        if(count($data) <=0){
            return C('param_fmt_err');
        }

        $list = array();
        $tmp_one = M($this->_module_name)->where($data)->find();
        if($tmp_one){
            $list = array(
                'RoomId'           => intval($tmp_one['RoomId']),
                'RoomName'         => urlencode($tmp_one['RoomName']),
                'RoomTitle'        => urlencode($tmp_one['RoomTitle']),
                'RoomIntro'        => urlencode($tmp_one['RoomIntro']),
                'RoomTeacher'      => urlencode($tmp_one['RoomTeacher']),
                'RoomMEtip'        => urlencode($tmp_one['RoomMEtip']),
                'RoomPopSet'       => urlencode($tmp_one['RoomPopSet']),
                'RoomHisPop'       => urlencode($tmp_one['RoomHisPop']),
                'ComId'            => intval($tmp_one['ComId']),
                'RoomLivetime'     => urlencode($tmp_one['RoomLivetime']),
                'RoomMaximage'     => urlencode($tmp_one['RoomMaximage']),
                'RoomMinimage'     => urlencode($tmp_one['RoomMinimage']),
                'RoomLiveLimit'    => urlencode($tmp_one['RoomLiveLimit']),
                'RoomLivehisLimit' => urlencode($tmp_one['RoomLivehisLimit']),
                'RoomVipType'      => urlencode($tmp_one['RoomVipType']),
                'RoomInterLimit'   => urlencode($tmp_one['RoomInterLimit']),
                'LiveNum'          => urlencode($tmp_one['LiveNum']),
                'InterNum'         => urlencode($tmp_one['InterNum']),
                'RoomEnable'       => urlencode($tmp_one['RoomEnable']),
                'RoomLiveState'    => urlencode($tmp_one['RoomLiveState']),
                'RoomAddTime'      => urlencode($tmp_one['RoomAddTime']),
                'RoomUpdateTime'   => urlencode($tmp_one['RoomUpdateTime']),
                'RoomUpdateAdmin'  => urlencode($tmp_one['RoomUpdateAdmin']),
                'RoomType'         => urlencode($tmp_one['RoomType']) 
                );
        }

        return array(200, $list);
    }
    
    /**
       功能:通过关键字查询单条
     */
    public function get_info_by_key($content){
         $data = $this->fill($content);
        
        if(!isset($data[$this->_key])){
            return C('param_fmt_err');
        }

        $list = array();
        $tmp_one = M($this->_module_name)->find($data[$this->_key]);
        if($tmp_one){
            $list = array(
                'RoomId'           => intval($tmp_one['RoomId']),
                'RoomName'         => urlencode($tmp_one['RoomName']),
                'RoomTitle'        => urlencode($tmp_one['RoomTitle']),
                'RoomIntro'        => urlencode($tmp_one['RoomIntro']),
                'RoomTeacher'      => urlencode($tmp_one['RoomTeacher']),
                'RoomMEtip'        => urlencode($tmp_one['RoomMEtip']),
                'RoomPopSet'       => urlencode($tmp_one['RoomPopSet']),
                'RoomHisPop'       => urlencode($tmp_one['RoomHisPop']),
                'ComId'            => intval($tmp_one['ComId']),
                'RoomLivetime'     => urlencode($tmp_one['RoomLivetime']),
                'RoomMaximage'     => urlencode($tmp_one['RoomMaximage']),
                'RoomMinimage'     => urlencode($tmp_one['RoomMinimage']),
                'RoomLiveLimit'    => urlencode($tmp_one['RoomLiveLimit']),
                'RoomLivehisLimit' => urlencode($tmp_one['RoomLivehisLimit']),
                'RoomVipType'      => urlencode($tmp_one['RoomVipType']),
                'RoomInterLimit'   => urlencode($tmp_one['RoomInterLimit']),
                'LiveNum'          => urlencode($tmp_one['LiveNum']),
                'InterNum'         => urlencode($tmp_one['InterNum']),
                'RoomEnable'       => urlencode($tmp_one['RoomEnable']),
                'RoomLiveState'    => urlencode($tmp_one['RoomLiveState']),
                'RoomAddTime'      => urlencode($tmp_one['RoomAddTime']),
                'RoomUpdateTime'   => urlencode($tmp_one['RoomUpdateTime']),
                'RoomUpdateAdmin'  => urlencode($tmp_one['RoomUpdateAdmin']),
                'RoomType'         => urlencode($tmp_one['RoomType']) 
                );
        }

        return array(200, $list);
    }
}