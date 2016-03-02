<?php
namespace Bugapi\Controller;
use Bugapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--需求产品确认记录管理--
------------------------------------------------------------
function of api:

##--------------------------------------------------------##

##--------------------------------------------------------##
*/
class DemandproductlogController extends BaseController {
	/**
	 * sql script:
	 * create table hr_demand_product_log(id int primary key auto_increment,
	                          demand_id int not null default 0 comment '需求id',
							  user_id int not null default 0 comment '修改人',
							  result int not null default 0 comment '确认结果(0-未操作，1-暂缓,2-通过)',
							  content varchar(255) comment '内容',
							  to_user_id int not null default 0 comment '派发人',
	                          add_time int not null default 0 comment '添加日期'
	                         )charset=utf8;
	 * */
	 
	 protected $_module_name = 'demand_product_log';
	 protected $id;
	 protected $demand_id;
	 protected $user_id;
	 protected $to_user_id;
	 protected $result;
	 protected $content;
	 protected $add_time;   #新增日期
	
	public function add($content)
         /*
         @@input
         @param int    $demand_id    需求id
         @param int    $user_id      最后修改人
         @param int    $to_user_id   派发给的人
         @param string $content      历史内容
         @@output
         @param $is_success 0-操作成功,-1-操作失败
         */
         {
            $data = $this->fill($content);
		
            if(!isset($data['demand_id'])
            || !isset($data['user_id'])
            || !isset($data['to_user_id'])
            || !isset($data['result'])
            || !isset($data['content'])
            )
            {
                    return C('param_err');
            }
		
	        
            $data['demand_id']    = intval(trim($data['demand_id']));
            $data['user_id']      = intval(trim($data['user_id']));
            $data['to_user_id']   = intval(trim($data['to_user_id']));
            $data['result']       = intval(trim($data['result']));
            $data['content']      = htmlspecialchars($data['content']);
		
            if(0 > $data['demand_id']
            || 0 > $data['user_id']
            || 0 > $data['to_user_id']
            || 0 > $data['result']
            || '' == $data['content']
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
                                            'demand_id'    => intval($v['demaind_id']),
                            		        'user_id'      => intval($v['user_id']),
                            		        'to_user_id'   => intval($v['to_user_id']),
                            		        'result'       => intval($v['result']),
                            		        'content'      => urlencode($v['content']),
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
				'demand_id'    => intval($tmp_one['demaind_id']),
				'user_id'      => intval($tmp_one['user_id']),
				'to_user_id'   => intval($tmp_one['to_user_id']),
				'result'       => intval($tmp_one['result']),
				'content'      => urlencode($tmp_one['content']),
				'add_time'     => intval($tmp_one['add_time']),
			);
		}
		
		return array(
			200,
			$list
		);
         }
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
}

