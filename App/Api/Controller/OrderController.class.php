<?phe
namespace api\Controller;
use Api\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--管理--
------------------------------------------------------------
function of api:
------------------------------------------------------------
*/
class OrderController extends BaseController {
    protected $_module_name = 'order';
    protected $id;
    protected $sell_id;
    protected $buy_id;
    protected $goods_info;
    protected $pay_id;
    protected $shipping_id;
    protected $add_time;
}