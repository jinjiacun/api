<?php
namespace Bugapi\Controller;
use Bugapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--需求反馈管理--
------------------------------------------------------------
function of api:

##--------------------------------------------------------##

##--------------------------------------------------------##
*/
class DemandfeedbackController extends BaseController {
	/**
	 * sql script:
	 * create table hr_demand_feedback(id int primary key auto_increment,
	                          demand_id int not null default 0 comment '需求id',
	                          position_id int not null default 0 comment '职位id',
							  user_id int not null default 0 comment '用户id',
							  content text comment '反馈内容',
	                          add_time int not null default 0 comment '添加日期'
	                         )charset=utf8;
	 * */
	 
	 protected $_module_name = 'demand_feedback';
	 protected $id;
	 protected $demand_id;
	 protected $position_id;
	 protected $user_id;
	 protected $content;
	 protected $add_time;   #新增日期
	
	public function add($content)
         /*
         @@input
         @param int    $demand_id    需求id
         @param int    $position_id  职位id
         @param int    $user_id      用户id
         @param string $content      内容
         @@output
         @param $is_success 0-操作成功,-1-操作失败
         */
         {
            $data = $this->fill($content);
		
            if(!isset($data['demand_id'])
			|| !isset($data['position_id'])
            || !isset($data['user_id'])
            || !isset($data['content'])
            )
            {
                    return C('param_err');
            }
		
	        
            $data['demand_id']     = intval(trim($data['demand_id']));
			$data['position_id']   = intval(trim($data['position_id']));
            $data['user_id']       = intval(trim($data['user_id']));
            $data['content']       = htmlspecialchars(trim($data['content']));
		
            if(0 > $data['demand_id']
			|| 0 > $data['position_id']
            || 0 >  $data['user_id']
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
											'demand_id'    => intval($v['demand_id']),
											'position_id'  => intval($v['position_id']),
											'user_id'      => intval($v['user_id']),
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
				'demand_id'    => intval($tmp_one['demand_id']),
				'position_id'  => intval($tmp_one['position_id']),
				'user_id'      => intval($tmp_one['user_id']),
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
