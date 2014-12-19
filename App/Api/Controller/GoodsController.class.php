<?php
namespace api\Controller;
use Think\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--商品管理--
*/
class GoodsController extends BaseController {

    protected $_module_name = 'goods';
    protected $id;              #id
    protected $goods_sn;        #商品编号
    protected $post_no;         #邮票志号
    protected $post_name;       #邮票名称
    protected $post_condition;  #邮票品相
    protected $post_spec;       #邮票规格
    protected $post_cat;        #邮票品类
    protected $post_price;      #邮票单价
    protected $post_number;     #邮票数量
    protected $post_shop_price; #销售价格
    protected $post_photo_list; #邮票照片
    protected $post_zodiac;     #生肖系列
    protected $zodiac_cat;      #生肖种类
    protected $yearbook_spec;   #年册规格
    protected $chronological;   #编年年份
    protected $issue;           #发行日期
    protected $post_company;    #邮票单位
    protected $transaction_type;#交易类型
    protected $end_of_date;     #有效期至
    protected $promise;         #承诺
    protected $add_time;        #添加日期

    public function index(){
        
    }


    public function get_list($content)
    {
        $data = parent::get_list($content);

        $list = array();
        if($data)
        {
            foreach($data as $v)
            {
                $list[] = array(
                        'id'               => intval($v['Id']),
                        'goods_sn'         => urlencode($v['goods_sn']),
                        'goods_no'         => urlencode($v['goods_sn']),
                        'goods_name'       => urlencode($v['goods_name']),
                        'post_condition'   => urlencode($v['post_condition']),
                        'post_spec'        => urlencode($v['post_spec']),
                        'post_cat'         => intval($v['post_cat']),
                        'post_price'       => $v['post_price'],
                        'post_number'      => $v['post_number'],
                        'post_shop_price'  => $v['post_shop_price'],
                        'post_zodiac'      => $v['post_zodiac'],
                        'zodiac_cat'       => $v['zodiac_cat'],
                        'yearbook_spec'    => $v['yearbook_spec'],
                        'chronological'    => $v['chronological'],
                        'issue'            => $v['issue'],
                        'post_company'     => $v['post_company'],
                        'transaction_type' => $v['transaction_type'],
                        'end_of_date'      => $v['end_of_date'],
                        'promise'          => $v['promise'],
                        'add_time'         => $v['add_time'],
                    );  
            }
            unset($v);
        }

        return array(200, $list);
    }

    #添加商品
    /**
    *@@input
    *@param $goods_name    商品名称
    *@param $picture       图片
    *@@output
    *
    *@@api_description goods.add
    *@@call_api call_api
    *@param $method=goods.add
    *@param $content={'goods_name':'xxx'}
    *@@call_api call_api
    *@param {'status':200,'content':{'is_success':xxx,'message':'xxx'}}
    */
    /*public function add($content)
    {
    	//反解析
    	#格式化并检查参数
		$format_params = json_decode($content, true);
		extract($format_params);
        
		echo $goods_name;
    }*/
}