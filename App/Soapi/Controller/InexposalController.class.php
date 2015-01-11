<?php
namespace Soapi\Controller;
use Soapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--管理--
------------------------------------------------------------
function of api:
 

#我要曝光
public function add
@@input
@param $user_id;          #用户id
@param $nature;           #企业性质(字典编码)
@param $trade;            #所属行业(字典编码)
@param $company_name;     #公司名称
@param $amount;           #涉及金额
@param $website;          #公司网址
@param $content;          #曝光内容
@param $pic_1;            #上传图片
@param $pic_2;            
@param $pic_3;       
@param $pic_4;       
@param $pic_5; 
@@output
@param $is_sucess 0-成功，-1-失败
##--------------------------------------------------------##
#查询我的曝光
public function get_list
##--------------------------------------------------------##
#申请可信企业
public function add_ex
@@input
@param $user_id         *用户id
@param $nature          *企业性质(字典编码)
@param $trade           *所属行业(字典编码)
@param company_name     *公司全称
@param corporation      *公司简称
@param reg_address      *注册地址
@param company_type     *企业类型
@param busin_license    *营业执照(图片id)
@param code_certificate *机构代码证(图片id)
@param telephone        *联系电话
@param website          *官方网址
@param record           *官网备案
@param agent_platform   代理平台
@param mem_sn           *会员编码
@param certificate      *资质证明(图片id)
@param find_website     查询网址 
@@output
@param $is_sucess 0-成功，-1-失败
##--------------------------------------------------------##
#查询我的可信企业申请
public function get_list_ex
##--------------------------------------------------------##
#关联企业
public function chang_relate
@@input
@param id           
@param $company_id 企业id
@@output
@param $is_success 0-操作成功,-1-操作失败
##--------------------------------------------------------## 
*/
class InexposalController extends BaseController {
	
	/**
	 * sql script:
	 * create table so_in_exposal(id int primary key auto_increment,
	                              user_id int not null default 0 comment '用户id',
	                              company_id int not null default 0 comment '关联企业',
	                              type    varchar(10) comment '类别(0-曝光，1-申请可信企业)',
	                              nature  varchar(10) comment '企业性质',
	                              trade   varchar(10) comment '所属行业',
	                              company_name varchar(255) comment '企业名称',
	                              corporation  varchar(255) comment '企业简介',
	  							  reg_address varchar(255) comment '注册地址',
	                              company_type varchar(255) comment '公司类型',
	                              busin_license int not null default 0 comment '营业执照',
	     						  code_certificate int not null default 0 comment '机构代码证',
	                              telephone varchar(255) comment '联系电话',
	                              amount varchar(255) comment '涉及金额',
	                              website varchar(255) comment '公司网址',
	                              record varchar(255) comment '官网备案',
	                              content text comment '曝光内容',
	                              pic_1 int not null default 0 comment '图片1',
	                              pic_2 int not null default 0 comment '图片2',
	                              pic_3 int not null default 0 comment '图片3',
	                              pic_4 int not null default 0 comment '图片4',
	                              pic_5 int not null default 0 comment '图片5',
	                              agent_platform varchar(255) comment '代理平台',
	                              mem_sn varchar(255) comment '会员编号',
	                              certificate int not null default 0 comment '资质证明',
	                              find_website varchar(255) comment 'find_website',
	                              add_time int not null default 0 comment '添加日期'
	                             )charset=utf8;
	 * */
	
	var $_module_name = 'in_exposal';

	var $id;
	var $user_id;          #用户id
	var $type;             #类型
	var $nature;           #企业性质
	var $trade;            #所属行业
	var $company_name;     #公司名称(企业全称)
	var $corporation;      #企业简称
	var $reg_address;      #注册地址
	var $company_type;     #企业类型
	var $busin_license;    #营业执照
	var $code_certificate; #机构代码证
	var $telephone;        #联系电话
	
	var $amount;       #涉及金额
	var $website;      #公司网址
	var $record;       #官网备案
	var $content;      #曝光内容
	var $pic_1;        #上传图片
	var $pic_2;       
	var $pic_3;       
	var $pic_4;       
	var $pic_5;       
	
	//代理信息
	var $agent_platform; #代理平台
	var $mem_sn;         #会员编号
	var $certificate;    #资质证明
	var $find_website;   #查询网址
	
	var $add_time;       #添加日期
	
	
	#我要曝光
	public function add($content)
	/*
	@@input
	@param $user_id;          #用户id
	@param $nature;           #企业性质
	@param $trade;            #所属行业
	@param $company_name;     #公司名称
	@param $amount;           #涉及金额
	@param $website;          #公司网址
	@param $content;          #曝光内容
	@param $pic_1;            #上传图片
	@param $pic_2;       
	@param $pic_3;       
	@param $pic_4;       
	@param $pic_5; 
	@@output
	@param $is_sucess 0-成功，-1-失败
	*/
	{
		$data = $this->fill($content);
		
		if(!isset($data['user_id'])
		|| !isset($data['nature'])
		|| !isset($data['trade'])
		|| !isset($data['company_name'])
		|| !isset($data['amount'])
		|| !isset($data['website'])
		|| !isset($data['content'])
		|| !isset($data['pic_1'])
		)
		{
			return C('param_err');
		}
		
		$data['user_id'] = intval($data['user_id']);
		$data['nature'] = htmlspecialchars(trim($data['nature']));
		$data['trade'] = htmlspecialchars(trim($data['trade']));
		$data['company_name'] = htmlspecialchars(trim($data['company_name']));
		$data['amount'] = htmlspecialchars(trim($data['amount']));
		$data['website'] = htmlspecialchars(trim($data['website']));
		$data['content'] = htmlspecialchars(trim($data['content']));
		$data['pic_1'] = htmlspecialchars(trim($data['pic_1']));
		
		if(0 >= $data['user_id']
		|| '' == $data['nature']
		|| '' == $data['trade']
		|| '' == $data['company_name']
		|| '' == $data['amount']
		|| '' == $data['website']
		|| '' == $data['content']
		|| 0 >= $data['pic_1']
		)
		{
			return C('param_fmt_err');
		}
		
		$data['type'] = 0;
	}
	
	#查询我的曝光
	public function get_list($content)
	{
		
	}
	
	#申请可信企业
	public function add_ex($content)
	/*
	@@input
	@param $user_id         *用户id
	@param $nature          *企业性质(字典编码)
	@param $trade           *所属行业
	@param company_name     *公司全称
	@param corporation      *公司简称
	@param reg_address      *注册地址
	@param company_type     *企业类型
	@param busin_license    *营业执照
	@param code_certificate *机构代码证
	@param telephone        *联系电话
	@param website          *官方网址
	@param record           *官网备案
	@param agent_platform   代理平台
	@param mem_sn           *会员编码
	@param certificate      *资质证明
	@param find_website     查询网址 
	@@output
	@param $is_sucess 0-成功，-1-失败
	*/
	{
		
	}

	#查询我的可信企业申请
	public function get_list_ex($content)
	{
		
	}
	
	#关联企业
	public function chang_relate($content)
	/*
	@@input
	@param id           
	@param $company_id 企业id
	@@output
	@param $is_success 0-操作成功,-1-操作失败
	*/
	{
		
	}
}
?>
