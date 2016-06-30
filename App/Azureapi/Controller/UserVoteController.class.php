<?php
namespace Azureapi\Controller;
use Azureapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');

class UserVoteController extends BaseController
{
    /**
       sql script:
       create table sp_user_vote(VoteId bigint(19) primary key,
       UserId int,
       UserNickName varchar(50),
       VoteIp varchar(50),
       VoteType int,
       VoteTime timestamp,
       VoteState int,
       ComId int
       )charset=utf8;
     */
    
    protected $_module_name = 'user_vote';
    protected $_key = 'VoteId';
    
    protected $VoteId;
    protected $UserId;
    protected $UserNickName;
    protected $VoteIp;
    protected $VoteType;
    protected $VoteTime;
    protected $VoteState;
    protected $ComId;

    public function get_list($conetnt)
    {
        list($data, $record_count) = parent::get_list($content);
        $list = array();
        if($data)
            {
                foreach($data as $v)
                    {
                        $list[] = array(
                            'VoteId' => $v['VoteId'],
                            'UserId' => $v['UserId'],
                            'UserNickName' => $v['UserNickName'],
                            'VoteIp' => $v['VoteIp'],
                            'VoteType' => $v['VoteType'],
                            'VoteTime' => $v['VoteTime'],
                            'VoteState' => $v['VoteState'],
                            'ComId' => $v['ComId'],
                        );
                    }
            }

        return array(200,
        array(
            'list'=>$list,
            'record_count'=>$record_count)
        );
    }
}