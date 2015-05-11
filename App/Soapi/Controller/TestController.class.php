<?php
namespace Soapi\Controller;
use Soapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
class TestController extends BaseController {
	protected $_module_name = 'test';
	
	public function test_user($content)
	{
		$url = "http://192.168.1.31:8300/Api/RegisterByMobile";
		$params = array(
			'nickname'  => 'jime',
			'mobile'    => '15021725013',
			'validated' => 0,
			'pswd'      => '123456',
			'userip'    => '192.168.1.113',
		);
		$params['safekey'] = $this->mk_passwd($params);
		
		echo $this->post($url, $params);
	}
	
    public function comment_auth_map()
	{
		#查询企业id和认证等级映射
		$map_auth = array();
		$tmp_list = M('Company')->field("id,auth_level")->select();
		foreach($tmp_list as $k=>$v)
		{
			$map_auth[intval($v['id'])] = $v['auth_level'];
		}
		unset($tmp_list, $k, $v);
	
		$tmp_list = M('Comment')->field('id,company_id')->select();
		foreach($tmp_list as $k=>$v)
		{	
			$id = intval($v['id']);
			$company_id = intval($v['company_id']);
			$auth_level = $map_auth[$company_id];
			$_where = array(
				'id'=>$id,
			);
			M('Comment')->where($_where)->save(array('auth_level'=>$auth_level));
		}
		unset($tmp_list, $v, $k);
		return array(
				200,
				array(
					'is_success'=>0,
					'message'=>C('option_ok'),
				),
		);		
	}		
	
	public function test_cache()
	{
		$list = S('list');
		if(empty($list))
		{
			$list = array(
				1,2,3
			);
		
			S('list', $list);
			var_dump($list);
		}
		
		
		//var_dump(S('list'));
	}
	
	public function test_api()
	{
		$begin = microtime(true);
		$tmp_info = M()->query("select count(distinct(user_id)) as tp_count from so_inexposal where type=0 and is_delete=0 and compan_id>0");
		$end = microtime(true);
		$result_str = sprintf("数据库查询时间:%s",$end - $begin);
		
		return array(
			200,
			array(
				$result_str
			),
		);			
	}	


}
