<?php
namespace Soapi\Controller;
use Soapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/*
--企业管理--
------------------------------------------------------------
function of api:
 
#添加企业
public function add
@@input
@param  $nature            #企业性质(字典编码)
@param  $logo              #企业logo
@param  $trade             #所属行业
@param  $company_name      #公司全称(唯一)
@param  $auth_level        #认证级别
@param  $company_type      #*企业类型
@param  $reg_address       #*注册地址
@param  $busin_license     #*营业执照(图片id)
@param  $code_certificate  #*机构代码证(图片id)
@param  $telephone         #*联系电话
@param  $website           #*官方网址
@param  $record            #*官网备案
@param  $find_website      #查询网址
@param  $agent_platform    #代理平台
@param  $mem_sn            #会员编号
@param  $certificate       #资质证明
@@output
@param $is_success 0-操作成功,-1-操作失败
##--------------------------------------------------------##
*/
class MemlogController extends BaseController {
/**
		 * sql script:
		  create table so_mem_log(id int primary key auto_increment,
		                             user_id int not null default 0 comment '会员id',
		                             userip varchar(255) comment '登录ip',
									 add_time int not null default 0 comment '添加日期'
									 )charset=utf8;
		 * */
		 
		 public $_module_name = 'Mem_log';
		 
}
