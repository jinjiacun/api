<?php
namespace api\Controller;
use Think\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--商品管理--
------------------------------------------------------------
function of api:

public function get_list  

public function get_goods_ids_by_cat_attr_val   获取商品id通过分类及其属性值
@@input
@param $cat_id         分类
@param $attr_id_map    属性id和属性值id之间映射，
*                      例如:属性名1-id 属性值1 属性值2 属性值3
@@output
@param $goods_ids      商品id集合，之间用逗号隔开
##--------------------------------------------------------##    
------------------------------------------------------------
*/
class GoodsController extends BaseController {

    protected $_module_name = 'goods';
    protected $id;              #id
    protected $user_id;         #用户id
    protected $goods_sn;        #商品编号
    protected $post_no;         #邮票志号
    protected $post_name;       #邮票名称
    protected $post_condition;  #邮票品相编码
    protected $post_spec;       #邮票规格编码
    protected $post_cat;        #邮票品类编码
    protected $post_price;      #邮票单价
    protected $post_number;     #邮票数量
    protected $post_shop_price; #销售价格
    protected $post_photo_list; #邮票照片
    protected $post_zodiac;     #生肖系列
    protected $zodiac_cat;      #生肖种类
    protected $yearbook_spec;   #年册规格
    protected $chronological;   #编年年份
    protected $issue;           #发行日期
    protected $post_unit;       #邮票单位编码
    protected $transaction_type;#交易类型
    protected $end_of_date;     #有效期至
    protected $promise;         #承诺
    protected $is_check;        #是否审核通过
    protected $add_time;        #添加日期

    public function index(){
        
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
                        'id'               => intval($v['id']),
                        'goods_sn'         => urlencode($v['goods_sn']),
                        'post_no'          => urlencode($v['post_no']),
                        'post_name'        => urlencode($v['post_name']),
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
                        'promise'          => urlencode($v['promise']),
                        'add_time'         => $v['add_time'],
                    );  
            }
            unset($v);
        }

        return array(200, array('list'=>$list, 
                                'record_count'=>$record_count));
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

	#获取商品id通过分类及其属性值
    public function get_goods_ids_by_cat_attr_val($content)
    /*   
	@@input
	@param $cat_id         分类
	@param $attr_id_map    属性id和属性值id之间映射，
	*                      例如:属性名1-id 属性值1 属性值2 属性值3
	@@output
	@param $goods_ids      商品id集合，之间用逗号隔开
	*/
	{
	   $data = $this->fill($content);

       if(!isset($data['cat_id'])
       || !isset($data['attr_id_map'])
        )
       {
            return C('param_err');
       }

       $data['cat_id'] = intval($data['cat_id']);
       $data['attr_id_map'] = intval($data['attr_id_map']);

       if(0>= $data['cat_id']
       || '' == $data['attr_id_map']
       )
       {
            return C('param_fmt_err');
       }

       if(0<= $data['attr_id_map'])
       {
        $where_list = array();
        foreach($data['attr_id_map'] as $v)
        {
            $tmp_list = array();
            foreach($v as $s_v)
            {
                $tmp_list[] = $s_v;
            }
            $tmp_str = implode(' or ', $tmp_list);
            $where_list[] = sprintf("(%s)", $tmp_str);
            unset($s_v);
        }
        unset($v);
        $where_str = implode(' and ', $where_list);
       }

       if('' != $where_str)
       {
            $list = M('Goods_attr_val')->field("id")->where($where_str)->select();
       }
       else
       {
            $list = M('Goods_attr_val')->field("id")->select();
       }

       return array(
            200,
            $list
        );
	}
}
