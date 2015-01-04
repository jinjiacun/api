<?php
namespace api\Controller;
use Api\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--会员收货地址管理--
------------------------------------------------------------
function of api:
public function set_defalt_address           设置默认收获地址
@@input
@param $user_id    用户id(*)
@param $address_id 收获地址id(*)
@@output
@param $is_success 0-成功设置,-1-设置失败
------------------------------------------------------------
public function get_list
------------------------------------------------------------
public function get_info                    通过关键字获取一条收获地址信息
@@input
@param $id
@@output
------------------------------------------------------------
*/
class UseraddressController extends BaseController {
    protected $_module_name = 'user_address';
	protected $id;          
    protected $user_id;      #用户id
    protected $province;     #省份
    protected $city;         #城市
    protected $district;     #地区
    protected $town;         #镇
    protected $address;      #详细地址                  
    protected $zipcode;	     #邮政编码
	protected $consignee;    #收获人
    protected $mobile;       #手机
    protected $telephone;    #固定电话
	protected $add_time;     #添加日期

	#设置默认收获地址
	public function set_default_address($content)
	{
        $data = $this->fill($content);
		if(!isset($data['user_id'])
        && !isset($data['address_id'])
        )
		{
			return C('param_err');
		}		

		$data['user_id'] = intval($data['user_id']);
		$data['address_id'] = intval($data['address_id']);

		if(0>= $data['user_id']
		|| 0>= $data['address_id']
		)
		{
			return C('param_fmt_err');
		}
		
		$where = array(
					'id'=>$data['user_id'],
					);
		$update_data = array(
					'address_id'=> $data['address_id'],
					);
		if(M('User')->where($where)->save($update_data))
		{
			return array(200,
						array(
							'is_success'=>0,
							'message'=> urlencode('成功操作'),
						)
					);
		}

	
		return array(200,
                    array(
						'is_success'=>-1,
						'message'=> urlencode('操作失败'),
					));
	}
	
	public function get_list($content)
	{
		list($data, $record_count) = parent::get_list($content);

		$list = array();
		if($data)
		{
			foreach($data as $v)
			{
				$list[] = array(
						'id'        => intval($v['id']),
						'user_id'   => intval($v['user_id']),
						'province'  => urlencode($v['province']),
						'city'      => urlencode($v['city']),
						'district'  => urlencode($v['district']),
						'town'      => urlencode($v['town']),
						'address'   => urlencode($v['address']),
						'zipcode'   => urlencode($v['zipcode']),
						'consignee' => urlencode($v['consignee']),
						'mobile'    => urlencode($v['mobile']),
						'telephone' => urlencode($v['telephone']),
						'add_time'  => intval($v['add_time']),
					);	
			}
		}

		return array(200, 
				array(
					'list'=>$list,
					'record_count'=> $record_count,
					)
				);
	}
	
	#通过关键字获取一条收获地址信息
	public function get_info($content)
	/*
	@@input
	@param $id
	@@output
	*/
	{
		list(,$data) = parent::get_info($content);
		
		$list = array();
		if($data)
		{
			$list = array(
				'id'        => intval($data['id']),
				'user_id'   => intval($data['user_id']),
				'province'  => urlencode($data['province']),
				'city'      => urlencode($data['city']),
				'district'  => urlencode($data['district']),
				'town'      => urlencode($data['town']),
				'address'   => urlencode($data['address']),
				'zipcode'   => urlencode($data['zipcode']),
				'consignee' => urlencode($data['consignee']),
				'mobile'    => urlencode($data['mobile']),
				'telephone' => urlencode($data['telephone']),
				'add_time'  => intval($data['add_time']),
			);
		}
		
		return array(
			200,
			$list
		);
	}
}
