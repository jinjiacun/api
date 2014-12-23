<?phe
namespace api\Controller;
use Api\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--会员收货地址管理--
------------------------------------------------------------
function of api:
public function set_defalt_address           设置默认收获地址
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
		return array(200,
                    array());
	}
	
}
