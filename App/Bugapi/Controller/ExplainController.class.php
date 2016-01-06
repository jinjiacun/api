<?php
namespace Bugapi\Controller;
use Bugapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--说明管理--
------------------------------------------------------------
function of api:

##--------------------------------------------------------##

##--------------------------------------------------------##
*/
class CategoryController extends BaseController {
	/**
	 * sql script:
	 * create table hr_explain(id int primary key auto_increment,
	                         project_id int not null default 0 comment '项目id',
	                         title varchar(255) comment '标题',
	                         description text comment '说明',
	                         add_time int not null default 0 comment '添加日期'
	                         )charset=utf8;
	 * */
	 
	 protected $_module_name = 'explain';
	 protected $id;
	 protected $project_id;    #项目id
	 protected $title;         #标题
	 protected $description;   #项目描述
	 protected $add_time;   #新增日期
	
	public function add($content)
         /*
         @@input
         @param int    $project_id 项目id
         @param string $title      标题
         @param string $description 项目描述
         @@output
         @param $is_success 0-操作成功,-1-操作失败
         */
         {
            $data = $this->fill($content);
		
            if(!isset($data['project_id'])
            || !isset($data['title'])
            || !isset($data['description'])
            )
            {
                    return C('param_err');
            }
		
	        
	        $data['project_id']  = intval(trim($data['project_id']));
            $data['title']       = htmlspecialchars(trim($data['title']));
            $data['description'] = htmlspecialchars(trim($data['description']));
		
            if(0 > $data['project_id']
            || '' == $data['title']
            || '' == $data['description']
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
                                            'title'        => urlencode($v['title']),
                                            'description'  => urlencode(htmlspecialchars_decode($v['description'])),
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
				'project_id'  => intval($tmp_one['project_id']),
				'title'        => urlencode($tmp_one['title']),
                'description' => urlencode(htmlspecialchars_decode($tmp_one['description'])),
				'add_time'    => intval($tmp_one['add_time']), 
			);
		}
		
		return array(
			200,
			$list
		);
         }
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
}
