<?php
namespace Soapi\Controller;
use Soapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--用户昵称管理--
------------------------------------------------------------
function of api:

#通过user_id查询一条昵称
public function get_nickname_by_id
#遍历更新所有昵称
public function item_update
#添加一条纪录
public function add
##--------------------------------------------------------##
public function get_list
*/
class UsernicknameController extends BaseController {
	/**
	 * sql script:
	 * create table so_user_nickname(
								user_id int not null default 0 comment '用户id',
								nickname varchar(255) comment '用户昵称'
	                      )charset=utf8;
	 * */
	protected $_module_name = "User_nickname";
	protected $user_id;
	protected $nickname;
		
	#通过id查询一条昵称
	public function get_nickname_by_id($content)
	/**
	 @@input
	 @param $user_id
	 @@output
	 @param $user_id
	 @param $nickname
	 * */
	{
		$data = $this->fill($content);
		
		if(!isset($data['user_id']))
		{
			return C('param_err');
		}
		
		$data['user_id'] = intval($data['user_id']);
		
		if(0>= $data['user_id'])
		{
			return C('param_fmt_err');
		}
		$user_nickname_list = S('user_nickname_list');
		$info = array();
		
		
		if(empty($user_nickname_list))
		{
			$tmp_result = M($this->_module_name)->select();
			$Data = array();
			if($tmp_result)
			{
				foreach($tmp_result as $v)
				{
					$Data[intval($v['user_id'])] = $v['nickname'];
				}
			}			
			S('user_nickname_list',$Data);
			$user_nickname_list = $Data;
		}
		//S('user_nickname_list', NULL);
		
		if(isset($user_nickname_list[$data['user_id']]))
		{
				$info = array(
					'nickname'=>$user_nickname_list[$data['user_id']],
				);
		}
		
		
		//$info = M($this->_module_name)->field('nickname')->find($data['user_id']);
		
		return array(
			200,
			$info
		);
	}
	
	#遍历更新所有昵称
	public function item_update($user_id, $nickname)
	{
		if(false !== M($this->_module_name)->where(array('user_id'=>$user_id))
		                                   ->save(array('nickname'=>$nickname)))
		{
			$user_nickname_list = S('user_nickname_list');
			$user_nickname_list[$user_id] = $nickname;
			S('user_nickname_list', $user_nickname_list);
			return true;
		}
		
		return false;
	}
	
	#添加一条纪录
	public function add($content)
	{
		$data = $this->fill($content);
		
		if(!isset($data['user_id'])
		|| !isset($data['nickname'])
		)
		{
			return C('param_err');
		}
		
		$data['user_id']  = intval($data['user_id']);
		$data['nickname'] = htmlspecialchars(trim($data['nickname']));
		
		if(0 >= $data['user_id']
		|| '' == $data['nickname']
		)
		{
			return C('param_fmt_err');
		}
		
		if(false !== M($this->_module_name)->add($data))
		{
			return array(
				200,
				array(
					'is_success'=>0,
					'message'   =>C('option_ok'),
				),
			);
		}
		
		return array(
				200,
				array(
					'is_success'=>-1,
					'message'   =>C('option_fail'),
				),
		);
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}
