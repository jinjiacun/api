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
#通过id查询单条
public function get_info
@@input
@param $id 企业id
@@output
@param  $id
@param  $logo              #企业logo
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
@param  $agent_platform    #代理平台
@param  $mem_sn            #会员编号
@param  $certificate       #资质证明
@param  $add_blk_amount    #加黑人数
@param  $exp_amount        #曝光人数
@param  $add_time          #添加日期
##--------------------------------------------------------##
public function get_list
##--------------------------------------------------------##
#查询企业映射
public function get_id_name_map
@@input
@@output
格式[{'id':'name'},...,{}]
##--------------------------------------------------------##
#查询企业级别映射
public function get_id_auth_level_map
@@input
@@output
格式[{'id':'name'},...,{}]
##--------------------------------------------------------##
#搜索
public function search
@@input
@param $name   企业别名 
@@output
@param  $id                #企业id
@param  $logo              #企业logo
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
@param  $agent_platform    #代理平台
@param  $mem_sn            #会员编号
@param  $certificate       #资质证明
@param  $add_time          #添加日期
##--------------------------------------------------------##
#查询企业名称是否存在
public function exists_name
@@input
@param company_name  企业名称 
@@output
@param $is_exists 0-存在,-1-不存在
##--------------------------------------------------------##
#黑榜排行
public function black_sort
@@input
@@output
@param $id            企业id
@param $company_name  企业名称
@param $amount        加黑人数
@param $last_time     最早曝光时间
@param $user_list     用户Id列表
##--------------------------------------------------------##
#通过id查询名称
public function get_name_by_id
@@input
@param $id
@@output
@param $name
##--------------------------------------------------------##
#通过id查询名称
private function get_auth_level_by_id
@@input
@param $id
@@output
@param $name
##--------------------------------------------------------##
#通过企业id获取企业图片集合
public function get_pic_list
@@input
@param $id
@@output
@param $media_id  图片id
@param $dict_sn   媒体类型编码
@param $media_url 媒体url
*/
class CompanyController extends BaseController {
	    /**
		 * sql script:
		  create table so_company(id int primary key auto_increment,
		                             logo int not null default 0 comment '企业logo',
		                             nature varchar(10) comment '企业性质',
		   		                     trade varchar(10) comment '所属行业',
		   		                     company_name varchar(255) comment '公司全称',
		   		                     auth_level varchar(10) comment '认证级别',
		   		                     reg_address varchar(255) comment '联系地址',
		   		                     busin_license int not null default 0 comment '营业执照',
		   		                     control_busin_license int not null default comment '是否有营业执照',
		   		                     code_certificate int not null default 0 comment '机构代码证',		   		                    
		   		                     control_code_certificate int null default 0 comment '是否有机构代码证',
		   		                     telephone varchar(255) comment '联系电话',
		   		                     website varchar(255) comment '官方网址',
		   		                     record varchar(255) comment '官网备案',
		   		                     regulators_id int not null default 0 comment '监管机构id',
		   		                     find_website varchar(255) comment '查询网址',
		   		                     agent_platform varchar(255) comment '代理平台',
									 mem_sn varchar(255) comment '会员编号',
									 certificate int not null default 0 comment '资质证明',
									 control_certificate int not null default 0 comment '是否有资质证明',
									 assist_amount int not null default 0 comment '点赞',
		   		                     add_blk_amount int not null default 0 comment '加黑人数',
		   		                     exp_amount int not null default 0 comment '曝光人数',
		   		                     com_amount int not null default 0 comment '评论人数',
									 add_time int not null default 0 comment '添加日期'
									 )charset=utf8;
		 * */
	 
		protected $_module_name = 'company';
		
		protected $id;
		protected $logo;                     #企业logo
		protected $nature;                   #企业性质(字典编码)
		protected $trade;                    #所属行业
		protected $company_name;             #公司全称(唯一)
		protected $auth_level;               #认证级别
		protected $reg_address;              #*联系地址
		protected $busin_license;            #*营业执照(图片id)
		protected $control_busin_license;    #是否有营业执照
		protected $code_certificate;         #*机构代码证(图片id)
		protected $control_code_certificate; #是否有机构代码证
		protected $telephone;                #*联系电话
		protected $website;                  #*官方网址
		protected $record;                   #*官网备案
		protected $regulators_id;            #监管机构id
		protected $find_website;             #查询网址
		protected $agent_platform;           #代理平台
		protected $mem_sn;                   #会员编号
		protected $certificate;              #资质证明
		protected $control_certificate;      #是否有资质证明
		protected $add_blk_amount;           #加黑人数
		protected $exp_amount;               #曝光人数
		protected $add_time;                 #添加日期
		
		
		#添加企业
		public function add($content)
		/*
		@@input
		@param  $logo                   	#企业logo
		@param  $nature                 	#*企业性质(字典编码)
		@param  $trade                  	#*所属行业
		@param  $company_name           	#*公司全称(唯一)
		@param  $auth_level             	#认证级别
		@param  $reg_address            	#联系地址
		@param  $busin_license            	#营业执照(图片id)		
		@param  $control_busin_license 		#是否有营业执照
		@param  $code_certificate       	#机构代码证(图片id)
		@param  $control_code_certificate;  #是否有机构代码证
		@param  $telephone              	#联系电话
		@param  $website                	#官方网址
		@param  $record                 	#官网备案
		@param  $regulators_id          	#监管机构id
		@param  $find_website           	#查询网址
		@param  $agent_platform         	#代理平台
		@param  $mem_sn                 	#会员编号
		@param  $certificate            	#资质证明
		@param  $control_certificate    	#是否有资质证明
		@@output
		@param $is_success 0-操作成功,-1-操作失败
		*/
		{
			$data = $this->fill($content);
			
			if(!isset($data['nature'])
			|| !isset($data['trade'])
			|| !isset($data['company_name'])
			)
			{
				return C('param_err');
			}
			
			$data['nature'] = htmlspecialchars(trim($data['nature']));
			$data['trade'] = htmlspecialchars(trim($data['trade']));
			$data['company_name'] = htmlspecialchars(trim($data['company_name']));
			
			//isset($data['auth_level'])?$data['auth_level']=htmlspecialchars(trim($data['auth_level'])):;
			
			
			if('' == $data['nature']
			|| '' == $data['trade']
			|| '' == $data['company_name']
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
		@param  $id
		@param  $logo              			#企业logo
		@param  $nature            			#企业性质(字典编码)
		@param  $trade             			#所属行业
		@param  $company_name      			#公司全称(唯一)
		@param  $auth_level        			#认证级别
		@param  $reg_address       			#*联系地址
		@param  $busin_license     			#*营业执照(图片id)
		@param  $control_busin_license 		#是否有营业执照 
		@param  $code_certificate  			#*机构代码证(图片id)
		@param  $control_code_certificate;  #是否有机构代码证
		@param  $telephone         			#*联系电话
		@param  $website           			#*官方网址
		@param  $record            			#*官网备案
		@param  $regulators_id     			#监管机构id
		@param  $find_website      			#查询网址
		@param  $agent_platform    			#代理平台
		@param  $mem_sn            			#会员编号
		@param  $certificate       			#资质证明
		@param  $control_certificate    	#是否有资质证明
		@param  $add_blk_amount    			#加黑人数
	    @param  $exp_amount        			#曝光人数
		@param  $add_time          			#添加日期
		*/
		{
			$data = $this->fill($content);
		
			if(!isset($data['id']))
			{
				return C('param_err');
			}
		
			$data['id'] = intval($data['id']);
		
			if(0>= $data['id'])
			{
				return C('param_fmt_err');
			}
		
			$list = array();
			$tmp_one = M($this->_module_name)->find($data['id']);
			if($tmp_one)
			{
				$list = array(
						'id'                => intval($tmp_one['id']),
						'logo'              => intval($tmp_one['logo']),
						'logo_url'          => $this->get_pic_url($tmp_one['logo']),
						'nature'            => urlencode($tmp_one['nature']),
						'trade'             => urlencode($tmp_one['trade']),
						'company_name'      => urlencode($tmp_one['company_name']),
						'alias_list'        => urlencode(A('Soapi/Companyalias')->get_name($tmp_one['id'])), #企业别名
						'auth_level'        => urlencode($tmp_one['auth_level']),
						'reg_address'       => urlencode($tmp_one['reg_address']),
						'busin_license'     => intval($tmp_one['busin_license']),
						'busin_license_url' => $this->get_pic_url($tmp_one['busin_license']),
						'control_busin_license' => intval($tmp_one['control_busin_license']),
						'code_certificate'  => intval($tmp_one['code_certificate']),
						'code_certificate_url' => $this->get_pic_url($tmp_one['code_certificate']),
						'control_code_certificate'=> intval($tmp_one['control_code_certificate']),
						'telephone'         => urlencode($tmp_one['telephone']),
						'website'           => $tmp_one['website'],
						'record'            => urlencode($tmp_one['record']),
						'regulators_id'     => intval($tmp_one['regulators_id']),
						'regulators_id_n'   => A('Soapi/Regulators')->get_name_by_id($tmp_one['regulators_id']),
						'find_website'      => $tmp_one['find_website'],
						'agent_platform'    => intval($tmp_one['agent_platform']),
						'agent_platform_n'  => urlencode($this->get_name_by_id($tmp_one['agent_platform'])),
						'agent_platform_auth_level'=>$this->get_auth_level_by_id($tmp_one['agent_platform']),
						'mem_sn'            => $tmp_one['mem_sn'],
						'certificate'       => intval($tmp_one['certificate']),
						'certificate_url'   => $this->get_pic_url($tmp_one['certificate']),
						'control_certificate'=> intval($tmp_one['control_certificate']),
						'assist_amount'     => intval($tmp_one['assist_amount']),
						'add_blk_amount'    => intval($tmp_one['add_blk_amount']),						 		
						'exp_amount'        => intval($tmp_one['exp_amount']),	
						'com_amount'        => intval($tmp_one['com_amount']),			
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
							'logo'              => intval($v['logo']),
							'logo_url'          => $this->get_pic_url($v['logo']),
							'nature'            => urlencode($v['nature']),
							'trade'             => urlencode($v['trade']),
							'company_name'      => urlencode($v['company_name']),
							'auth_level'        => urlencode($v['auth_level']),
							'reg_address'       => urlencode($v['reg_address']),
							'busin_license'     => intval($v['busin_license']),
							'busin_license_url' => $this->get_pic_url($v['busin_license']),
							'control_busin_license' => intval($v['control_busin_license']),
							'code_certificate'  => intval($v['code_certificate']),
							'code_certificate_url' => $this->get_pic_url($v['code_certificate']),
							'control_code_certificate'=> intval($v['control_code_certificate']),
							'telephone'         => urlencode($v['telephone']),
							'website'           => $v['website'],
							'record'            => urlencode($v['record']),
							'regulators_id'     => intval($v['regulators_id']),
							'find_website'      => $v['find_website'],
							'agent_platform'    => urlencode($v['agent_platform']),
							'agent_platform_n'  => urlencode($this->get_name_by_id($v['agent_platform'])),
							'mem_sn'            => urlencode($v['mem_sn']),
							'certificate'       => intval($v['certificate']),
							'certificate_url'   => $this->get_pic_url($v['certificate']),
							'control_certificate'=> intval($v['control_certificate']),
							'assist_amount'     => intval($v['assist_amount']),
							'add_blk_amount'    => intval($v['add_blk_amount']),
							'exp_amount'        => intval($v['exp_amount']),
							'com_amount'        => intval($v['com_amount']),
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
		
		#查询企业映射
		public function get_id_name_map($content)
		/*
		@@input
		@@output
		格式[{'id':'name'},...,{}]
		*/
		{
			$data = $this->fill($content);
			$list = array();
			$tmp_list = M($this->_module_name)->field('id,company_name')
			                                  ->where($data['where'])
			                                  ->select();
			if($tmp_list
			&& 0< count($tmp_list))
			{
				foreach($tmp_list as $v)
				{
					$list[intval($v['id'])] = urlencode($v['company_name']);
				}
				unset($tmp_list, $v);
			}
			
			return array(
				200,
				$list
			);
		}
		
		#查询企业级别映射
		public function get_id_auth_level_map($content)
		/*
		@@input
		@@output
		格式[{'id':'name'},...,{}]
		*/
		{
			$data = $this->fill($content);
			$list = array();
			$tmp_list = M($this->_module_name)->field('id,auth_level')
			                                 // ->where($data['where'])
			                                  ->select();
			if($tmp_list
			&& 0< count($tmp_list))
			{
				foreach($tmp_list as $v)
				{
					$list[intval($v['id'])] = urlencode($v['auth_level']);
				}
				unset($tmp_list, $v);
			}
			
			return array(
				200,
				$list
			);
		}
		
		#搜索
		public function search($content)
		/*
		@@input
		@param $name    企业别名 
		@param $user_id 会员id
		@@output
		@param  $id                企业id
		@param  $logo              #企业logo
		@param  $nature            #企业性质(字典编码)
		@param  $trade             #所属行业
		@param  $company_name      #公司全称(唯一)
		@param  $auth_level        #认证级别
		@param  $reg_address       #*联系地址
		@param  $busin_license     #*营业执照(图片id)
		@param  $code_certificate  #*机构代码证(图片id)
		@param  $telephone         #*联系电话
		@param  $website           #*官方网址
		@param  $record            #*官网备案
		@param  $find_website      #查询网址
		@param  $agent_platform    #代理平台
		@param  $mem_sn            #会员编号
		@param  $certificate       #资质证明
		@param  $add_time          #添加日期
		*/
		{
			$data = $this->fill($content);
			
			if(!isset($data['name'])
			//|| !isset($data['user_id'])
			)
			{
				return C('param_err');
			}
			
			$data['name']    = htmlspecialchars(trim($data['name']));
			//$data['user_id'] = intval($data['user_id']);
			
			if('' == $data['name'])
			{
				return C('param_fmt_err');
			}
			
			//纪录查询信息
			$content = array(
			//	'user_id' => $data['user_id'],
				'keyword' => $data['name'],
			);
			A('Soapi/Querylog')->add(json_encode($content));
			unset($data['user_id']);
			
			$list = array();
			$record_count = 0;
			#通过别名查询company_id集合
			$company_id_list = array();
			$company_ids     = "";
			
			#别名搜索
			$tmp_list = M('Company_alias')->field("company_id")
			                              ->where(array('name'=>array('like','%'.$data['name'].'%')))
			                              ->select();
			if($tmp_list
			&& 0<count($tmp_list))
			{
				foreach($tmp_list as $k=> $v)
				{
					$company_id_list[] = $v['company_id'];
				}
				unset($tmp_list, $k, $v);
			}
			$company_ids = implode(',', $company_id_list);
			$tmp_list = array();			
			
			
			//D('CompanyaliasView')->where($data)->select();
			if('' != $company_ids)
			{
				$where['id'] = array('in', $company_ids);
				$tmp_list = M($this->_module_name)->where($where)->select();
				//$record_count = D('CompanyaliasView')->where($data)->count();
				$record_count = M($this->_module_name)->where($where)->count();
			}
			#查询企业名称
			else
			{
				if(isset($where))unset($where);
				$where['company_name'] = array('like','%'.$data['name'].'%');
				$tmp_list = M($this->_module_name)->where($where)->select();
				//$record_count = D('CompanyaliasView')->where($data)->count();
				$record_count = M($this->_module_name)->where($where)->count();
			}
			
			#查询网址
			if(0== $record_count)
			{
				if(isset($where))unset($where);
				$where['website'] = array('like', '%'.$data['name'].'%');
				$tmp_list = M($this->_module_name)->where($where)->select();
				//$record_count = D('CompanyaliasView')->where($data)->count();
				$record_count = M($this->_module_name)->where($where)->count();
			} 
			
			if($tmp_list
			&& 0< count($tmp_list))
			{
				foreach($tmp_list as $v)
				{
					$list[] = array(
						'id'                => intval($v['id']),
						'logo'              => intval($v['logo']),
						'logo_url'          => $this->get_pic_url($v['logo']),
						'nature'            => urlencode($v['nature']),
						'trade'             => urlencode($v['trade']),
						'company_name'      => urlencode($v['company_name']),
						'auth_level'        => urlencode($v['auth_level']),
						'reg_address'       => urlencode($v['reg_address']),
						'busin_license'     => intval($v['busin_license']),
						'busin_license_url' => $this->get_pic_url($v['busin_license']),
						'control_busin_license' => intval($v['control_busin_license']),
						'code_certificate'  => intval($v['code_certificate']),
						'code_certificate_url' => $this->get_pic_url($v['code_certificate']),
						'control_code_certificate'=> intval($v['control_code_certificate']),
						'telephone'         => urlencode($v['telephone']),
						'website'           => $v['website'],
						'record'            => urlencode($v['record']),
						'regulators_id'     => intval($v['regulators_id']),
						'regulators_id_n'   => urlencode(A('Soapi/Regulators')->get_name_by_id($v['regulators_id'])),
						'find_website'      => $v['find_website'],
						'agent_platform'    => urlencode($v['agent_platform']),
						'agent_platform_n'  => urlencode($this->get_name_by_id($v['agent_platform'])),
						'mem_sn'            => $v['mem_sn'],
						'certificate'       => intval($v['certificate']),
						'certificate_url'   => $this->get_pic_url($v['certificate']),
						'control_certificate'=> intval($v['control_certificate']),
						'add_time'          => intval($v['add_time']),
						'alias_list'        => urlencode(A('Soapi/Companyalias')->get_name($v['id'])), #企业别名
						'assist_amount'     => intval($v['assist_amount']),
						'add_blk_amount'    => intval($v['add_blk_amount']),
						'exp_amount'        => intval($v['exp_amount']),
						'com_amount'        => intval($v['com_amount']),
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
		
		#查询企业名称是否存在
		public function exists_name($content)
		/*
		@@input
		@param company_name  企业名称 
		@@output
		@param $is_exists 0-存在,-1-不存在
		*/
		{
			$data = $this->fill($content);
			if(!isset($data['company_name']))
			{
				return C('param_err');
			}
			
			$data['company_name'] = htmlspecialchars(trim($data['company_name']));
			
			if('' == $data['company_name'])
			{
				return C('param_fmt_err');
			}
			
			if($this->__exists('company_name', $data['company_name']))
			{
				return array(
					200,
					array(
						'is_exists'=>0,
						'message'=>C('is_exists'),
					),
				);
			}
			
			return array(
					200,
					array(
						'is_exists'=>-1,
						'message'=>C('no_exists'),
					),
				);
		}
		
		#查询企业名称是否存在
		public function exists_name_ex($content)
		/*
		@@input
		@param $id
		@param $company_name  企业名称 
		@@output
		@param $is_exists 0-存在,-1-不存在
		*/
		{
			$data = $this->fill($content);
			if(!isset($data['company_name'])
			|| !isset($data['id'])
			)
			{
				return C('param_err');
			}
			
			$data['company_name'] = htmlspecialchars(trim($data['company_name']));
			$data['id'] = intval($data['id']);
			
			if('' == $data['company_name']
			|| 0 >= $data['id']
			)
			{
				return C('param_fmt_err');
			}
			
			if($this->__exists_ex('company_name', $data['company_name'], $data['id']))
			{
				return array(
					200,
					array(
						'is_exists'=>0,
						'message'=>C('is_exists'),
					),
				);
			}
			
			return array(
					200,
					array(
						'is_exists'=>-1,
						'message'=>C('no_exists'),
					),
				);
		}
		
		
		#黑榜排行
		public function black_sort($content)
		/*
		@@input
		@@output
		@param $id            企业id
		@param $company_name  企业名称
		@param $amount        加黑人数
		@param $last_time     最早曝光时间
		@param $user_list     用户Id列表
		*/
		{
			$tmp_data    = json_decode($content, true);
			$tmp_data['where']['exp_amount'] = array('neq',0);
			$content = json_encode($tmp_data);
			list(,$data) = $this->get_list($content);
			$list 			= $data['list'];
			$record_count 	= $data['record_count'];
			
			if($list
			&& 0< count($list))
			{
				foreach($list as $k=>$v)
				{
					$content = array(
						'company_id'=> intval($v['id'])
					);
					/*
					list(,$amount) = A('Soapi/Inexposal')
					                ->stat_user_amount(json_encode($content));
					*/
					$amount = $v['add_blk_amount'];
					$list[$k]['amount'] = $amount;
					list(,$tmp_user_list) = A('Soapi/Inexposal')
					                    ->stat_user_top(json_encode($content));
					if($tmp_user_list
					&& 0< count($tmp_user_list))
					{
						foreach($tmp_user_list as $v)
						{
							$user_list[] = array(
								'user_id'  =>$v,
								'nickname' =>$this->_get_nickname($v),
							);
						}
						unset($v, $tmp_user_list);
					}
					$list[$k]['user_list'] = $user_list;
					unset($user_list);
					list(,$min_time) = A('Soapi/Inexposal')
					                   ->stat_user_min_date(json_encode($content));
					$list[$k]['last_time'] = $min_time;
				}
				unset($k, $v);
			}
				
			return array(
				200,
                                array(
                                    'list'=>$list,
                                    'record_count'=>$record_count
                                )				
			);
			
		}
		
		#获取最大的值对应的信息
		public function Max($content)
		/*
		 @@input
		 @param $field_name 要统计的字段名称
		 @@output
		 * */
		{
			$data = $this->fill($content);
			
			if(!isset($data['field_name']))
			{
				return C('param_err');
			}
			
			$data['field_name'] = htmlspecialchars(trim($data['field_name']));
			
			if('' == $data['field_name'])
			{
				return C('param_fmt_err');
			}
			
			$list = array();
			$list = $this->__get_Max($data['field_name'],array('auth_level'=>'006001'));
			
			return array(
				200,
				$list
			);
		}
		
		#通过id查询名称
		public function get_name_by_id($id)
		/*
		@@input
		@param $id
		@@output
		@param $name
		*/
		{
			if(0>= $id)
				return '';
				
			$tmp = M($this->_module_name)->field('company_name')->find($id);
			return $tmp['company_name'];
		}
		
		#通过id查询名称
		private function get_auth_level_by_id($id)
		/*
		@@input
		@param $id
		@@output
		@param $name
		*/
		{
			if(0>= $id)
				return '';
				
			$tmp = M($this->_module_name)->field('auth_level')->find($id);
			return $tmp['auth_level'];
		}
		
		
		#通过企业id获取企业图片集合
		public function get_pic_list($content)
		/*
		@@input
		@param $id
		@@output
		@param $media_id  图片id
		@param $dict_sn   媒体类型编码
		@param $media_url 媒体url
		*/
		{
			$data = $this->fill($content);
			
			if(!isset($data['id']))
			{
				return C('param_err');
			}
			
			$data['id'] = intval($data['id']);
			
			if(0>= $data['id'])
			{
				return C('param_fmt_err');
			}
			
			$media_list = array();
			$media_count = 0;
			
			//查询此企业相关的图片(企业图片,曝光图片,可信企业认证图片,媒体曝光图片,
            //			        企业评论图片,曝光评论图片，           )
			//todo
			
			return array(
				200,
				array(
					'list'=>$media_list,
					'record_count'=>$media_count
				)
			);
		}
		
		
		//删除企业信息
		public function delete($content)
		/*
		 @@input
		 @param $company_id
		 @@output
		 @param $is_success 0-成功,-1-失败
		*/
		{
			$data = $this->fill($content);
			
			if(!isset($data['id']))
			{
				return C('param_err');
			}
			
			$data['id'] = intval($data['id']);
			
			if(0>= $data['id'])
			{
				return C('param_fmt_err');
			}
			
			//删除企业别名			
			if(false === M('Company_alias')->
			            where(array('company_id'=>$data['id']))->
			            delete())
			{
				return array(
					200,
					array(
						'is_success'=>-2,
						'message'=>urlencode('删除别名失败'),
					),
				);
			}
						
			//删除企业新闻
			if(false === M('News')->
			            where(array('company_id'=>$data['id']))->
			            delete())
			{
				return array(
					200,
					array(
						'is_success'=>-3,
						'message'=>urlencode('删除企业新闻失败'),
					),
				);
			}
			
			//查询曝光企业
			$tmp_list = M("In_exposal")->
			            field("id")->
			            where(array("company_id"=>$data['id']))->
			            select;
			$tmp_ids = "";
			$tmp_id_list = array();
		    if($tmp_list
		    && 0<count($tmp_list))
		    {
				foreach($tmp_list as $v)
				{
					$tmp_id_list[] = $v['id'];
				}
				unset($tmp_list, $v);
			}
			$tmp_ids = implode(',', $tmp_id_list);
			unset($tmp_id_list);
			
			//删除企业曝光评论
			if(false === M('Com_exposal')->
			            where(array('exposal_id'=>array("in",$tmp_ids)))->
			            delete())
			{
				return array(
					200,
					array(
						'is_success'=>-7,
						'message'=>urlencode('删除企业曝光评论失败'),
					),
				);
			}			
			
			//删除申请
			if(false === M('In_exposal')->
			            where(array('company_id'=>$data['id']))->
			            delete())
			{
				return array(
					200,
					array(
						'is_success'=>-4,
						'message'=>urlencode('删除申请信息失败'),
					),
				);
			}
			
			//删除评论
			if(false === M('Comment')->
			            where(array('company_id'=>$data['id']))->
			            delete())
			{
				return array(
					200,
					array(
						'is_success'=>-5,
						'message'=>urlencode('删除评论失败'),
					),
				);
			}
			
			//删除企业新闻评论
			if(false === M('Com_news')->
			            where(array('company_id'=>$data['id']))->
			            delete())
			{
				return array(
					200,
					array(
						'is_success'=>-6,
						'message'=>urlencode('删除企业新闻评论失败'),
					),
				);
			}
			
			//删除企业
        	if(false === M($this->_module_name)->
			            where(array('id'=>$data['id']))->
			            delete())
			{
				return array(
					200,
					array(
						'is_success'=>-1,
						'message'=>urlencode('删除企业失败'),
					),
				);
			}
			
			return array(
				200,
				array(
					'is_success'=>0,
					'message'=>urlencode('成功删除企业'),
				),
			);
		}
		
		
		
		
		
		
		
		
		
		
		
		
}
