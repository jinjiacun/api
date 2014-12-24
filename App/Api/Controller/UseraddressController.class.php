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
		if(!isset($data['user_id']
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
						'message'=> urlencode('操作失败');
					));
	}
	
}
