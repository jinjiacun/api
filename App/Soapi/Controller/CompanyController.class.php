<?php
namespace Soapi\Controller;
use Soapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--企业管理--
------------------------------------------------------------
function of api:
 
#添加企业
public function add
@@input
@param  $nature            #企业性质(字典编码)
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
@@output
@param $is_success 0-操作成功,-1-操作失败
##--------------------------------------------------------##
#通过id查询单条
public function get_info
@@input
@param $id 企业id
@@output
@param  $nature            #企业性质(字典编码)
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
@param  $add_time          #添加日期
##--------------------------------------------------------##
public function get_list
##--------------------------------------------------------##
#搜索
public function search
@@input
@param $name   企业别名 
@@output
@param  $id                企业id
@param  $nature            #企业性质(字典编码)
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
@param  $add_time          #添加日期
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
		
		
		#添加企业
		public function add($content)
		/*
		@@input
		@param  $nature            #*企业性质(字典编码)
		@param  $trade             #*所属行业
		@param  $company_name      #*公司全称(唯一)
		@param  $auth_level        #认证级别
		@param  $company_type      #*企业类型
		@param  $reg_address       #注册地址
		@param  $busin_license     #营业执照(图片id)
		@param  $code_certificate  #机构代码证(图片id)
		@param  $telephone         #联系电话
		@param  $website           #官方网址
		@param  $record            #官网备案
		@param  $find_website      #查询网址
		@@output
		@param $is_success 0-操作成功,-1-操作失败
		*/
		{
			$data = $this->fill($content);
			
			if(!isset($data['nature'])
			|| !isset($data['trade'])
			|| !isset($data['company_name'])
			|| !isset($data['company_type'])
			)
			{
				return C('param_err');
			}
			
			$data['nature'] = htmlspecialchars(trim(($data['nature']));
			$data['trade'] = htmlspecialchars(trim(($data['trade']));
			$data['company_name'] = htmlspecialchars(trim(($data['company_name']));
			$data['company_type'] = htmlspecialchars(trim(($data['company_type']));
			
			isset($data['auth_level'])?{$data['auth_level']=htmlspecialchars(trim($data['auth_level']))}:{};
			
			
			if('' == $data['nature']
			|| '' == $data['trade']
			|| '' == $data['company_name']
			|| '' == $data['company_type']
			)
			{
				return C('param_fmt_err');
			}
			
			$data['add_time'] = time();
			if(M($this->_module_name)->add($data))
			{
				return array(
					200,
					array(
						'is_success'=>0,
						'message'=>C('option_ok'),
						'id'=> M()->getLastInsID(),
					)
				);
			}
			
			return array(
					200,
					array(
						'is_success'=>0,
						'message'=>C('option_fail')
					)
				);
		}
		
		
		#通过id查询单条
		public function get_info($content)
		/*
		@@input
		@param $id 企业id
		@@output
		@param  $nature            #企业性质(字典编码)
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
		@param  $add_time          #添加日期
		*/
		{
			$data = $this->fill($content);
		
			if(!isset($data['id']))
			{
				return C('param_err');
			}
		
			$data = intval($data['id']);
		
			if(0>= $data['id'])
			{
				return C('param_fmt_err');
			}
		
			$list = array();
			$tmp_one = M($this->_module_name)->find($data['id']);
			if($tmp_one)
			{
				$list = array(
						'nature'            => urlencode($tmp_one['nature']),
						'trade'             => urlencode($tmp_one['trade']),
						'company_name'      => urlencode($tmp_one['company_name']),
						'auth_level'        => urlencode($tmp_one['auth_level']),
						'company_type'      => urlencode($tmp_one['company_type']),
						'reg_address'       => urlencode($tmp_one['reg_address']),
						'busin_license'     => intval($tmp_one['busin_license']),
						'code_certificate'  => intval($tmp_one['code_certificate']),
						'telephone'         => urlencode($tmp_one['telephone']),
						'website'           => $tmp_one['website'],
						'record'            => urlencode($tmp_one['record']),
						'find_website'      => $tmp_one['find_website'],
						'add_time'          => intval($tmp_one['add_time']),
				);
			}
		
			return array(
				200,
				$list
			);
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
							'id'                => intval($v['id']),
							'nature'            => urlencode($v['nature']),
							'trade'             => urlencode($v['trade']),
							'company_name'      => urlencode($v['company_name']),
							'auth_level'        => urlencode($v['auth_level']),
							'company_type'      => urlencode($v['company_type']),
							'reg_address'       => urlencode($v['reg_address']),
							'busin_license'     => intval($v['busin_license']),
							'code_certificate'  => intval($v['code_certificate']),
							'telephone'         => urlencode($v['telephone']),
							'website'           => $v['website'],
							'record'            => urlencode($v['record']),
							'find_website'      => $v['find_website'],
							'add_time'          => intval($v['add_time']),							
						);	
				}
			}

			return array(200, 
					array(
						'list'=>$list,
						'record_count'=> $record_count,
						)
					);
		}
		
		#搜索
		public function search($content)
		/*
		@@input
		@param $name   企业别名 
		@@output
		@param  $id                企业id
		@param  $nature            #企业性质(字典编码)
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
		@param  $add_time          #添加日期
		*/
		{
			$data = $this->fill($content);
			
			if(!isset($data['name']))
			{
				return C('param_err');
			}
			
			$data = htmlspecialchars(trim($data['name']));
			
			if('' == $data['name'])
			{
				return C('param_fmt_err');
			}
			
			$list = array();
			$record_count = 0;
			$tmp_list = D('CompanyaliasView')->where($data)->select();
			$record_count = D('CompanyaliasView')->where($data)->count();
			if($tmp_list
			&& 0< count($tmp_list))
			{
				foreach($tmp_list as $v)
				{
					$list[] = array(
						'id'                => intval($v['id']),
						'nature'            => urlencode($v['nature']),
						'trade'             => urlencode($v['trade']),
						'company_name'      => urlencode($v['company_name']),
						'auth_level'        => urlencode($v['auth_level']),
						'company_type'      => urlencode($v['company_type']),
						'reg_address'       => urlencode($v['reg_address']),
						'busin_license'     => intval($v['busin_license']),
						'code_certificate'  => intval($v['code_certificate']),
						'telephone'         => urlencode($v['telephone']),
						'website'           => $v['website'],
						'record'            => urlencode($v['record']),
						'find_website'      => $v['find_website'],
						'add_time'          => intval($v['add_time']),
					);
				}
				unset($v, $tmp_list);
			}
			
			return array(
				200,
				array(
					'list'=>$list,
					'record_count'=>$record_count
				),
			);
		}
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
}
