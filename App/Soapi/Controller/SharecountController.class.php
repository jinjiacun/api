<?php
namespace Soapi\Controller;
use  Soapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--分享次数管理--
------------------------------------------------------------
function of api:

#添加
public function add
@@input
@param $type 类型(007001-企业分享,007002-评论分享,007003-曝光分享)
@param $value_id   对应上面的id((='007001',企业id),(='007002',评论id),(='007003',曝光id)
@@output
@param $is_success 0-操作成功,-1-操作失败
##--------------------------------------------------------##
public function get_list 
##--------------------------------------------------------##
*/
class SharecountController extends BaseController {
	/**
	 * sql script:
	 * create table so_share_count(id int primary key auto_increment,
                                   type varchar(255) comment '分享类型(007001-企业分享,007002-评论分享,007003-曝光分享)',
	                              value_id int not null default 0 comment '对应的id值',
                                  times int not null default 0 comment '分享次数',
	                              add_time int not null default 0 comment '添加日期'
	                             )charset=utf8;
	 * */
	 
	 protected $_module_name = 'Share_count';
	 protected $id;
     protected $type;          #分享类型
	 protected $value_id;      #相应的值id
     protected $times;         #分享次数
     protected $add_time;      #添加日期
	
		 
	 #添加
	public function add($content)
	/*
	@@input
	@param $type     
	@param $value_id     值id
	@@output
	@param $is_success 0-操作成功,-1-操作失败
	*/
	{
		$data = $this->fill($content);
		if(!isset($data['type'])
		|| !isset($data['value_id'])
		)
		{
			return C('param_err');
		}
		
		$data['type']         = htmlentities(trim($data['type']));
		$data['value_id']     = intval($data['value_id']);
			
		if('' == $data['type']
		|| 0>= $data['value_id']
		)
		{
			return C('param_fmt_err');
		}
         
        $tmp_param = array(
			'type'=>$data['type'],
            'value_id'=>$data['value_id'],
		);
		#如果存在,则进行累加
        if(M($this->_module_name)->where($tmp_param)->find())
		{
			if(false !== M($this->_module_name)->where($tmp_param)->setInc('times',1))
			{
				
				$tmp_info = M($this->_module_name)->field('times')->where($tmp_param)->find();
				return array(
						200,
						array(
							'is_success'=>0,
							'message'=>C('option_ok'),
                            'amount'=>$tmp_info['times'],
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

	    $now = time();
        $data['times'] = 1;
	    $data['add_time'] = $now;
		
		if(M($this->_module_name)->add($data))
		{
				$tmp_info = M($this->_module_name)->field('times')->where($tmp_param)->find();
				return array(
					200,
					array(
						'is_success'=>0,
						'message'=>C('option_ok'),
						'amount'=>$tmp_info['times'],
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
	
	public function get_info($content)
	{
		$data = $this->fill($content);
		if(!isset($data['type'])
		|| !isset($data['value_id'])
		)
		{
			return C('param_err');
		}
		
		$data['type']     = htmlentities(trim($data['type']));          
		$data['value_id'] = intval($data['value_id']);
  
        if('' == $data['type']
		|| 0 >= $data['value_id']
		)
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










}
