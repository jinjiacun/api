<?php
namespace Soapi\Controller;
use Api\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--企业管理管理--
------------------------------------------------------------
function of api:
 
#添加企业
public function add
@@input

@@output

##--------------------------------------------------------##
*/
class CompanyController extends BaseController {
	    /**
		 * sql script:
		  create table so_company(id int primary key auto_increment,
		                             nature varchar(10) comment '企业性质',
		   		                     trade varchar(10) comment '所属行业',
		   		                     company_name varchar(255) comment '公司全称',
		   		                     auth_level varchar(10) comment '认证级别',
		   		                     company_type varchar(255) comment '企业类型',
		   		                     reg_address varchar(255) comment '注册地址',
		   		                     busin_license int not null default 0 comment '营业执照',
		   		                     code_certificate int not null default 0 comment 'code_certificate',
		   		                     telephone varchar(255) comment '联系电话',
		   		                     website varchar(255) comment '官方网址',
		   		                     record varchar(255) comment '官网备案',
		   		                     find_website varchar(255) comment '查询网址',
		   		                     add_blk_amount int not null default 0 comment '加黑人数',
		   		                     exp_amount int not null default 0 comment '曝光人数',          
									 add_time int not null default 0 comment '添加日期'
									 )charset=utf8;
		 * */
	 
		protected $_module_name = 'company';
		
		protected $id;
		protected $nature;            #企业性质(字典编码)
		protected $trade;             #所属行业
		protected $company_name;      #公司全称(唯一)
		protected $auth_level;        #认证级别
		protected $company_type;      #*企业类型
		protected $reg_address;       #*注册地址
		protected $busin_license;     #*营业执照(图片id)
		protected $code_certificate;  #*机构代码证(图片id)
		protected $telephone;         #*联系电话
		protected $website;           #*官方网址
		protected $record;            #*官网备案
		protected $find_website;      #查询网址
		protected $add_blk_amount;    #加黑人数
		protected $exp_amount;        #曝光人数
		protected $add_time;          #添加日期
}
