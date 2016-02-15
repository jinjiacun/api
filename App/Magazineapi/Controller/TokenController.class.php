<?php
namespace Soapi\Controller;
use  Soapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--移动端token管理--
------------------------------------------------------------
function of api:

#添加
public function add
@@input
@param $type 0-(ios,默认),1-(android)
@param $token 
@param $user_id 用户id
@@output
@param $is_success 0-操作成功,-1-操作失败
##--------------------------------------------------------##
public function get_list 
##--------------------------------------------------------##
*/
class TokenController extends BaseController {
	/**
	 * sql script:
	 * create table so_token(id int primary key auto_increment,
                                  type varchar(255) default '008001' comment '设备类型(008001-ios,008002-android)',
	                              token varchar(255),
	                              user_id int not null default 0 comment '用户id',
	                              is_offline int not null default 0 comment '是否离线(0-在线,1-离线)',
                                  last_time int not null default 0 comment '最后更新时间',
	                              add_time int not null default 0 comment '添加日期'
	                             )charset=utf8;
	 * */
	 
	 protected $_module_name = 'Token';
	 protected $id;
     protected $type;          #设备类型
     protected $token;         #token值
     protected $user_id;       #用户id
     protected $is_offline;    #是否离线(0-在线,1-离线)
	 protected $last_time;     #最新更新时间
     protected $add_time;      #添加日期
	
		 
	#添加
	public function add($content)
	/*
	@@input
	@param $type    设备类型(008001:(ios,默认),008002:android)  
	@param $token   设备对应的token(注意，android可能有重复，使用uid+token组合模式,xxx_xxxxx)
	@param $user_id 用户id
	@@output
	@param $is_success 0-操作成功,-1-操作失败
	*/
	{
		$data = $this->fill($content);
		if(!isset($data['type'])
		|| !isset($data['token'])
		|| !isset($data['user_id'])
		)
		{
			return C('param_err');
		}
		$data['type']         = htmlspecialchars(trim($data['type']));
		$data['token']        = htmlspecialchars(trim($data['token']));
		$data['user_id']      = intval(trim($data['user_id']));
		
			
		if('' == $data['type']
		|| '' == $data['token']
		|| 0>= $data['user_id']
		)
		{
			return C('param_fmt_err');
		}
         
        //删除存在的token
        M($this->_module_name)->where(array('token'=>$data['token']))->delete();
         
        $data['is_offline'] = 0;
         
        $tmp_param = array(
            'user_id'=>$data['user_id'],
		);
		$now = time();
		#如果存在,则进行更新
        if(M($this->_module_name)->where($tmp_param)->find())
		{
			if(false !== M($this->_module_name)->where($tmp_param)
			                                   ->save(array(
														'token'     =>$data['token'],
														'type'      =>$data['type'],
														'is_offline'=>$data['is_offline'],
														'last_time' =>$now,
			)))
			{
				return array(
						200,
						array(
							'is_success'=>0,
							'message'=>C('option_ok'),
						),
				);
			}
			else
			{
				return array(
					200,
					array(
						'is_success'=>-1,
						'message'=>C('option_fail'),
					),
				);
			}
		}

	    
        
	    $data['add_time'] = $now;
		
		if(M($this->_module_name)->add($data))
		{
				return array(
					200,
					array(
						'is_success'=>0,
						'message'=>C('option_ok')
					),
				);
		}
		
		return array(
				200,
				array(
					'is_success'=>-1,
					'message'=>C('option_fail'),
				),
		);
	}
	
	//通过user_id查看用户设备类型及其对应的token
	public function get_info($content)
	{
		$data = $this->fill($content);
		if(!isset($data['user_id']))
		{
			return C('param_err');
		}
		
		$data['user_id'] = intval($data['user_id']);
  
        if(0 >= $data['value_id'])
		{
			return C('param_fmt_err');
		}
	
		$info = array();
		$info = M($this->_module_name)->where($data)->find();
	
		return array(
			200,
			$info
		);
	}

	//下线
	public function offline($content)
	/**
	 * @@intput
	 * @param $user_id
	 * @@output
	 * @param $is_success 0-操作成功,-1-操作失败
	 * */
	{
		$data = $this->fill($content);
		if(!isset($data['user_id']))
		{
			return C('param_err');
		}
		
		$data['user_id']      = intval(trim($data['user_id']));
			
		if(0>= $data['user_id'])
		{
			return C('param_fmt_err');
		}
		
		if(false !== M($this->_module_name)->where(array('user_id'=>$data['user_id']))
		                                   ->save(array('is_offline'=>1)))
		{
				return array(
					200,
					array(
						'is_success'=>0,
						'message'=>C('option_ok')
					),
				);
		}
		
		return array(
				200,
				array(
					'is_success'=>-1,
					'message'=>C('option_fail'),
				),
		);
	}







}
