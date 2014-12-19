<?php
namespace User\Controller;
use Think\Controller;
class CallapiController extends Controller {
	#api接口回调统一函数
	/**
	*@@input
	*@param $type    数据传输类型(text-文本数据,resource-资源文件)
	*@param $method  方法名
	*@param $content 内容
	*/
	public function call_api($type, $method, $content, $handle=null)
	{
		$api = A('Api/Index');
        $method  = 'goods.add';
        $content = json_encode($content);
        $content = urldecode($content);
		$api->index($type, $method, $contentm, $handle);
	}
}