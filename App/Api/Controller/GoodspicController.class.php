<?php
namespace api\Controller;
use Think\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--商品图片管理--
*/
class GoodspicController extends BaseController {
	 protected $_module_name = 'goods_pic';
     protected $id;              #id
     protected $goods_id;
     protected $media_id;
}	