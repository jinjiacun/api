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
		/*
		$list = S('list');
		if(empty($list))
		{
			$list = array(
				1,2,3
			);
		
			S('list', $list);
			var_dump($list);
		}
		*/
		
		//var_dump(S('list'));
	}
	
	public function test_api()
	{
		$begin = microtime(true);
		//$tmp_info = M()->query("select count(distinct(user_id)) as tp_count from so_inexposal where type=0 and is_delete=0 and compan_id>0");
		$obj = M('Ad')->select();
		$end = microtime(true);
		$result_str = sprintf("time:%s",$end - $begin);
		
		echo $result_str;

		return array(
			200,
			array(
				$result_str
			),
		);			
	}
	
	public function run_script_company_exp_user()
	{
		 $list = M('Company')->field('id')->select();
		 
		 foreach($list as $v)
		 {
			  $data['company_id'] = intval($v['id']);
			  //更新最新三个用户和最新曝光时间
			    $tmp_param = array(
					'company_id'=>$data['company_id'],
			    );
			    list(,$min_time) = A('Soapi/Inexposal')                                                                                   
                                        ->stat_user_min_date(json_encode($tmp_param));
			    
			    list(,$tmp_user_list) = A('Soapi/Inexposal')                                                                              
                                        ->stat_user_top(json_encode($tmp_param));
                $tmp_data = array(
					'where'=>array(
						'id'=>$data['company_id']
					),
					'data'=> array(
						'user_id_1'=>isset($tmp_user_list[0])?intval($tmp_user_list[0]):0,
						'user_id_2'=>isset($tmp_user_list[1])?intval($tmp_user_list[1]):0,
						'user_id_3'=>isset($tmp_user_list[2])?intval($tmp_user_list[2]):0,
						'last_time'=>$min_time
					)
                );
			    A('Soapi/Company')->update(json_encode($tmp_data));
		 }
		echo 'success';
	}
	
	public function run_script_company_pic_ex()
	{
		#logo_url,alias_list,
		#busin_license_url,code_certificate_url,
		#agent_platform_n,certificate_url
		
		$obj = M('Company');
		$list = $obj->field('id,logo,busin_license,code_certificate,agent_platform,certificate')->select();
		foreach($list as $v)
		{
				$id = intval($v['id']);
				$tmp_data = array(
					'data'=>array(
						'logo_url'             => $this->get_pic_url(intval($v['logo'])),
						'alias_list'           => A('Soapi/Companyalias')->get_name($id),
						'busin_license_url'    => $this->get_pic_url(intval($v['busin_license'])),
						'code_certificate_url' => $this->get_pic_url(intval($v['code_certificate'])),	
						'agent_platform_n'	   => A('Soapi/Company')->get_name_by_id($v['agent_platform']),
						'certificate_url'	   => $this->get_pic_url(intval($v['certificate'])),
					),
					'where'=>array(
						'id'=>$id
					),
				);
				$obj->where($tmp_data['where'])->save($tmp_data['data']);
				unset($tmp_data);
		}
	}


	public function get_des($content)
	{
		$obj_des = new \Org\Util\DES();
		//for($i=1; $i<10; $i++)
		//{
		//	M('Comment')->page(1,10)->where(array())->select();
		//}		
		echo $obj_des->encrypt($content.date('Y-m-d'));
	}

	//测试安卓推动
	public function test_android_push()
	{
		$obj = A('Soapi/Pushmessage');
		$obj->push_android('test', 'test', 1, 1,'test android');
	}
	
	//android下载
	public function android_down()
	{
		Header("HTTP/1.1 301 Moved Permanently");
		Header("Location: http://www.souhei.com.cn/Public/down/android/souhei.apk");
	}
}
