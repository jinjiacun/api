<?php
namespace Bugapi\Controller;
use Bugapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--问题管理--
------------------------------------------------------------
function of api:

##--------------------------------------------------------##

##--------------------------------------------------------##
*/
class AskController extends BaseController {
	/**
	 * sql script:
	 * create table hr_ask(id int primary key auto_increment,
							 project_id int comment '项目id',
	                         ask text comment '问题',
	                         asker text comment '提问者',
	                         `status` int not null default 0 comment '状态',
	                         answer text comment '回答',
	                         answerer varchar(255) comment '回答者',
	                         ask_time int not null comment '提问时间',
	                         answer_time int not null comment '回答时间',
	                         add_time int not null default 0 comment '添加日期'
	                         )charset=utf8;
	 * */
	 
	 protected $_module_name = 'ask';
	 protected $id;
	 protected $project_id = 0;
	 protected $ask = '';
	 protected $asker = '';
	 protected $status = 0;
	 protected $answer = '';
	 protected $answerer = '';
	 protected $ask_time = 0;
	 protected $answer_time = 0;
	 protected $add_time;   #新增日期
	
	public function add($content)
         /*
         @@input
         @param string $project_id   项目id
         @param string $ask   问题
         @param string $asker 提问者
         @param int ask_time 提问时间
         @@output
         @param $is_success 0-操作成功,-1-操作失败
         */
         {
            $data = $this->fill($content);
		
            if(!isset($data['project_id'])
            || !isset($data['ask'])
            || !isset($data['asker'])
            || !isset($data['ask_time'])
            )
            {
                    return C('param_err');
            }
		
	        
            $data['project_id'] = intval(trim($data['project_id']));
            $data['ask']        = htmlspecialchars(trim($data['ask']));
            $data['asker']      = htmlspecialchars(trim($data['asker']));
            $data['ask_time']      = intval(trim($data['ask_time']));
		
            if(0 > $data['project_id']
            || '' == $data['ask']
            || '' == $data['asker']
            || 0> $data['ask_time']
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
                                            'project_id'   => intval($v['project_id']),
											'ask' 		   => urlencode(htmlspecialchars_decode($v['ask'])),
											'asker'        => urlencode($v['asker']),
											'status'       => intval($v['status']),
											'answer'       => urlencode(htmlspecialchars_decode($v['answer'])),
											'answerer'     => urlencode($v['answerer']),
											'ask_time'     => intval($v['ask_time']),
											'answer_time'  => intval($v['answer_time']),
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
				'id'          => intval($tmp_one['id']),
				'project_id'   => intval($tmp_one['project_id']),
				'ask' 		   => urlencode(htmlspecialchars_decode($tmp_one['ask'])),
				'asker'        => urlencode($tmp_one['asker']),
				'status'       => intval($tmp_one['status']),
				'answer'       => urlencode(htmlspecialchars_decode($tmp_one['answer'])),
				'answerer'     => urlencode($tmp_one['answerer']),
				'ask_time'     => intval($tmp_one['ask_time']),
				'answer_time'  => intval($tmp_one['answer_time']),
				'add_time'    => intval($tmp_one['add_time']), 
			);
		}
		
		return array(
			200,
			$list
		);
         }
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
}
