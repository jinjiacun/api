<?php
namespace Bugapi\Controller;
use Bugapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--需求管理--
------------------------------------------------------------
function of api:

##--------------------------------------------------------##

##--------------------------------------------------------##
*/
class DemandController extends BaseController {
	/**
	 * sql script:
	 * create table hr_demand(id int primary key auto_increment,
	                          number varchar(255) comment '编号',
							  description varchar(255) comment '描述',
							  project_id int not null default 0 comment '项目id',
	                          level int not null default 0 comment '优先级',
	                          plan_online int not null default 0 comment '预计上线时间',
	                          mast_develop int not null default 0 comment '开发主管',
	                          mast_product int not null default 0 comment '产品主管',
	                          status int not null default 0 comment '状态',
	                          create int not null default 0 comment '创建人',
	                          last_online int not null default 0 comment '最终上线时间',
	                          last_person int not null default 0 comment '最后更新人',
	                          last_time   int not null default 0 comment '最新更新时间',
	                          add_time int not null default 0 comment '添加日期'
	                         )charset=utf8;
	 * */
	 
	 protected $_module_name = 'demand';
	 protected $id;
	 protected $number;
	 protected $description;
	 protected $project_id = 0;
	 protected $level      = 0;
	 protected $plan_online = 0;
	 protected $mast_develop = 0;
	 protected $mast_product = 0;
	 protected $status = 0;
	 protected $create = 0;
	 protected $last_online = 0;
	 protected $last_person = 0;
	 protected $last_time   = 0;
	 protected $add_time;   #新增日期
	
	public function add($content)
         /*
         @@input
         @param int    $project_id   项目id
         @param string $number       编号
         @param string $description  描述
         @param int    $level        优先级
         @param int    $plan_online  计划上线时间
         @param int    $mast_develop 开发主管
         @param int    $mast_product 产品主管
         @param int    $create       创建人
         @@output
         @param $is_success 0-操作成功,-1-操作失败
         */
         {
            $data = $this->fill($content);
		
            if(!isset($data['project_id'])
            || !isset($data['number'])
            || !isset($data['description'])
            || !isset($data['level'])
            || !isset($data['plan_online'])
            || !isset($data['mast_develop'])
            || !isset($data['mast_product'])
            || !isset($data['create'])
            )
            {
                    return C('param_err');
            }
		
	        
            $data['project_id']    = intval(trim($data['project_id']));
            $data['number']        = htmlspecialchars(trim($data['number']));
            $data['description']   = htmlspecialchars(trim($data['description']));
            $data['level']         = intval(trim($data['level']));
            $data['plan_online']   = intval(trim($data['plan_online']));
            $data['mast_develop']  = intval(trim($data['mast_develop']));
            $data['mast_product']  = intval(trim($data['mast_product']));
            $data['create']        = intval(trim($data['create']));
		
            if(0 > $data['project_id']
            || '' == $data['number']
            || '' == $data['description']
            || 0 >  $data['level']
            || 0 >  $data['plan_online']
            || 0 >  $data['mast_develop']
            || 0 >  $data['mast_product']
            || 0 >  $data['create']
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
                                    'id'=>M()->getLastInsID(),
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
                                            'number'       => urlencode($v['number']),
											'description'  => urlencode($v['description']),
											'project_id'   => intval($v['project_id']),
											'level'        => intval($v['level']),
											'plan_online'  => intval($v['plan_online']),
											'mast_develop' => intval($v['mast_develop']),
											'mast_product' => intval($v['mast_product']),
											'status'       => intval($v['status']),
											'create'       => intval($v['create']),
											'last_online'  => intval($v['last_online']),
											'last_person'  => intval($v['last_person']),
											'last_time'    => intval($v['last_time']),
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
         
         public function get_info($content)
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
				'id'           => intval($tmp_one['id']),          
				'number'       => urlencode($tmp_one['number']),
				'description'  => urlencode($tmp_one['description']),
				'project_id'   => intval($tmp_one['project_id']),
				'level'        => intval($tmp_one['level']),
				'plan_online'  => intval($tmp_one['plan_online']),
				'mast_develop' => intval($tmp_one['mast_develop']),
				'mast_product' => intval($tmp_one['mast_product']),
				'status'       => intval($tmp_one['status']),
				'create'       => intval($tmp_one['create']),
				'last_online'  => intval($tmp_one['last_online']),
				'last_person'  => intval($tmp_one['last_person']),
				'last_time'    => intval($tmp_one['last_time']),
				'add_time'     => intval($tmp_one['add_time']),
			);
		}
		
		return array(
			200,
			$list
		);
         }
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
}
