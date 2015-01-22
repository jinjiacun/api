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
#查询曝光企业映射
public function get_id_name_map
@@input
@@output
格式[{'id':'name'},...,{}]
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
#统计曝光人数
public function stat_user_amount
@@input
@param $company_id 企业id
@@output
##--------------------------------------------------------##
#曝光动态
public function dynamic
@@input
@@output
@param $user_id      会员id
@param $add_time     曝光时间
@param $company_name 企业名称
@param $content      曝光内容
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
	                              top_num int not null default 0 comment '顶数目',
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
	@param $user_id          #用户id
	@param $nature           #企业性质
	@param $trade            #所属行业
	@param $company_name     #公司名称
	@param $amount           #涉及金额
	@param $website          #公司网址
	@param $content          #曝光内容
	@param $pic_1            #上传图片
	@param $pic_2       
	@param $pic_3       
	@param $pic_4       
	@param $pic_5 
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
		$data['add_time'] = time();
		
		if(M($this->_module_name)->add($data))
		{
			return array(
				200,
				array(
					'is_success'=>0,
					'message'=>C('option_ok'),
					'id'=> M()->getLastInsID(),
				),
			);
		}
		
		return array(
				200,
				array(
					'is_success'=>0,
					'message'=>C('option_fail'),
				),
			);
	}
	
	#查询我的曝光
	public function get_list($content)
	{
		list($data, $record_count) = parent::get_list($content);

		$list = array();
		if($data)
		{
			foreach($data as $v)
			{
				$list[] = array(
				        'id'           => intval($v['id']),
				        'company_id'   => intval($v['company_id']),
						'user_id'      => intval($v['user_id']),
						'nickname'     => $this->_get_nickname($v['user_id']),
						'nature'       => $v['nature'],  
						'trade'        => $v['trade'],  
						'company_name' => urlencode($v['company_name']),  
						'amount'       => $v['amount'],  
						'website'      => $v['website'],  
						'content'      => urlencode($v['content']),  
						'pic_1'        => intval($v['pic_1']),  
						'pic_1_url'    => $this->get_pic_url($v['pic_1']),
						'pic_2'        => intval($v['pic_2']),
						'pic_2_url'    => $this->get_pic_url($v['pic_2']),
						'pic_3'        => intval($v['pic_3']),
						'pic_3_url'    => $this->get_pic_url($v['pic_3']),
						'pic_4'        => intval($v['pic_4']),
						'pic_4_url'    => $this->get_pic_url($v['pic_4']),
						'pic_5'        => intval($v['pic_5']),
						'pic_5_url'    => $this->get_pic_url($v['pic_5']),
						'top_num'      => intval($v['top_num']),
						'add_time'     => intval($v['add_time']),
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
	
	#查询曝光企业映射
	public function get_id_name_map($content)
	/*
	@@input
	@@output
	*/
	{
		$list = array();
		$tmp_list = M($this->_module_name)->field('id, company_name')
		                                  ->select();
		if($tmp_list
		&& 0< count($tmp_list))
		{
			foreach($tmp_list as $v)
			{
				$list[intval($v['id'])] = urlencode($v['company_name']);
			}
			unset($v, $tmp_list);
		}
		
		return array(
			200,
			$list
		);
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
		$data = $this->fill($content);
		
		if(!isset($data['user_id'])
		|| !isset($data['nature'])
		|| !isset($data['trade'])
		|| !isset($data['company_name'])
		|| !isset($data['corporation'])
		|| !isset($data['reg_address'])
		|| !isset($data['company_type'])
		|| !isset($data['busin_license'])
		|| !isset($data['code_certificate'])
		|| !isset($data['telephone'])
		|| !isset($data['website'])
		|| !isset($data['record'])
		|| !isset($data['agent_platform'])
		|| !isset($data['mem_sn'])
		|| !isset($data['certificate'])
		|| !isset($data['find_website'])
		)
		{
			return C('param_err');
		}
		
		$data['type'] = 1;
		$data['add_time'] = time();
		
		if(M($this->_module_name)->add($data))
		{
			return array(
				200,
				array(
					'is_success'=>0,
					'message'=>C('option_ok'),
					'id'=> M()->getLastInsID(),
				),
			);
		}
		
		return array(
				200,
				array(
					'is_success'=>0,
					'message'=>C('option_fail'),
				),
			);
	}

	#查询我的可信企业申请
	public function get_list_ex($content)
	/**
	@param $id
    @param $company_id      关联企业id
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
	 * */
	{
		list($data, $record_count) = parent::get_list($content);

		$list = array();
		if($data)
		{
			foreach($data as $v)
			{
				$list[] = array(
				        'id'               => intval($v['id']),
				        'company_id'       => intval($v['company_id']),
						'user_id'          => intval($v['user_id']),
						'nature'           => $v['nature'],  
						'trade'            => $v['trade'],  
						'company_name'     => urlencode($v['company_name']),  
						'corporation'      => urlencode($v['corporation']),
						'reg_address'      => urlencode($v['reg_address']),
						'company_type'     => urlencode($v['company_type']),
						'busin_license'    => intval($v['busin_license']),
						'busin_license_url'=> $this->get_pic_url($v['busin_license']),
						'code_certificate' => intval($v['code_certificate']),
						'code_certificate_url' => $this->get_pic_url($v['code_certificate']),
						'telephone'        => urlencode($v['telephone']),
						'website'          => $v['website'],
						'record'           => urlencode($v['record']),
						'agent_platform'   => urlencode($v['agent_platform']),
						'mem_sn'           => urlencode($v['mem_sn']),
						'certificate'      => intval($v['certificate']),
						'certificate_url'  => $this->get_pic_url($v['certificate']),
						'find_website'	   => $v['find_website'],
						'add_time'         => intval($v['add_time']),
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
		$data = $this->fill($content);
		if(!isset($data['id'])
		|| !isset($data['company_id'])
		)
		{
			return C('param_err');
		}
		
		$data['id']         = intval($data['id']);
		$data['company_id'] = intval($data['company_id']);
		
		if(0>= $data['id']
		|| 0>= $data['company_id']
		)
		{
			return C('param_fmt_err');
		}
		
		if(isset($content)) unset($content);
		
		$content = array(
			'company_id'=> $data['company_id']
		);
		if(M($this->_module_name)->where(array('id'=>$data['id']))
		                         ->save($content))
		{
			return array(
				200,
				array(
					'is_success'=>0,
					'message'=>C('option_ok'),
				),
			);
		}
		return array(
				200,
				array(
					'is_success'=>-1,
					'message'=>C('option_fail'),
				),
			);
	}
	
	#统计曝光人数
	public function stat_user_amount($content)
	/*
	@@input
	@param $company_id 企业id
	@@output
	*/
	{
		$data = $this->fill($content);
		/*
		if(!isset($data['company_id']))
		{
			return C('param_err');
		}
		*/
		
		//$data['company_id'] = intval($data['company_id']);
		
		/*
		if(0>= $data['company_id'])
		{
			return C('param_fmt_err');
		}
		*/
		
		$amount = 0;
		if(isset($data['company_id'])
		&& 0< $data['company_id'])
			$amount = M($this->_module_name)->distinct('user_id')->where($data)->count();
		else
		{
			$where['company_id'] = array('neq', 0);
			$amount = M($this->_module_name)->distinct('user_id')->where($data)->count();
		}
		
		return array(
			200,
			$amount
		);
	}
	
	
	#统计前3个会员
	public function stat_user_top($content)
	/*
	@@input
	@param $company_id 企业id
	@@output
	*/
	{
		$data = $this->fill($content);
		if(!isset($data['company_id']))
		{
			return C('param_err');
		}
		
		$data['company_id'] = intval($data['company_id']);
		
		if(0>= $data['company_id'])
		{
			return C('param_fmt_err');
		}
		
		$list = array();
		$tmp_list = M($this->_module_name)->field('user_id')
		                                  ->distinct('user_id')
		                                  ->where($data)
		                                  ->limit(3)
		                                  ->select();
		if($tmp_list
		&& 0<count($tmp_list))
		{
			foreach($tmp_list as $v)
			{
				$list[] = intval($v['user_id']);
			}
			unset($v);
		}
		
		return array(
			200,
			$list
		);
	}
	
	#最早评论时间
	public function stat_user_min_date($content)
	{
		$data = $this->fill($content);
		if(!isset($data['company_id']))
		{
			return C('param_err');
		}
		
		$data['company_id'] = intval($data['company_id']);
		
		if(0>= $data['company_id'])
		{
			return C('param_fmt_err');
		}
		
		$last_time = 0;
		$last_time = M($this->_module_name)->where($data)->min('add_time');
		
		return array(
			200,
			$last_time
		);
	}
	
	#曝光动态
	public function dynamic($content)
	/*
	@@input
	@param $page_index  默认为1 
	@param $page_size   默认为10
	@@output
	@param $user_id      会员id
	@param $add_time     曝光时间
	@param $company_name 企业名称
	@param $content      曝光内容
	*/
	{		
		$list = array();
		$record_count = 0;
		
		$data = $this->fill($content);
		
		$data['page_index'] = isset($data['page_index'])?intval($data['page_index']):1;
		$data['page_size']  = isset($data['page_size'])?intval($data['page_size']):10;
		$data['order']      = isset($data['order'])?$data['order']:array('id'=>'desc');
		
		$where['auth_level'] = array('neq', '006003');
		
		$tmp_list = D('InexposalcompanyView')
		            ->page($page_index, $page_size)
		            ->where($where)->select();
		$record_count = D('InexposalcompanyView')->where($where)->count();
		
		if($tmp_list
		&& 0< count($tmp_list))
		{
			foreach($tmp_list as $v)
			{
				$list[] = array(
					'user_id'  =>intval($v['user_id']),
					'nickname' =>$this->_get_nickname($v['user_id']),
					'add_time' =>intval($v['add_time']),
					'company_name' => urlencode($v['company_name']),
					'content' => urlencode($v['content']),
				);
			}
			unset($v, $tmp_list);
		}
		
		return array(
			200,
			array(
				'list'=>$list,
				'record_count'=>$record_count
			)
		);
	}
}
?>
