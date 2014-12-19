<?php
namespace User\Controller;
use Think\Controller;
class GoodsController extends Controller {

    #添加商品
    public function info()
    {
    	if($_POST['submit']){
    		$goods_name = I('post.goods_name');
            $handler = null;
            if($_FILES["picture"])
            {
                $handler = $_FILES['picture'];
            }

    		A('Callapi')->call_api('Goods.add', 
                              array('goods_name'=>$goods_name),
                              $handler);
    	}
    	$this->display();    	
    }
}