<?php
namespace Bugapi\Controller;
use Bugapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--分类管理--
------------------------------------------------------------
function of api:

##--------------------------------------------------------##

##--------------------------------------------------------##
*/
class CategoryController extends BaseController {
	/**
	 * sql script:
	 * create table hr_category(id int primary key auto_increment,
	                         name varchar(255) comment '项目名称',
	                         description text comment '项目说明',
	                         add_time int not null default 0 comment '添加日期'
	                         )charset=utf8;
	 * */
	 
	 protected $_module_name = 'category';
	 protected $id;
	 protected $name;          #项目名称
	 protected $description;   #项目描述
	 protected $add_time;   #新增日期
	
	public function add($content)
         /*
         @@input
         @param string $name   部门名称
         @param string $description 项目描述
         @@output
         @param $is_success 0-操作成功,-1-操作失败
         */
         {
            $data = $this->fill($content);
		
            if(!isset($data['name'])
            || !isset($data['description'])
            )
            {
                    return C('param_err');
            }
		
	        
            $data['name'] = htmlspecialchars(trim($data['name']));
            $data['description'] = htmlspecialchars(trim($data['description']));
		
            if('' == $data['name']
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
                                            'id'          => intval($v['id']),
                                            'name'        => urlencode($v['name']),
                                            'description' => urlencode(htmlspecialchars_decode($v['description'])),
                                            'add_time'    => intval($v['add_time']),
                                            
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
				'name'        => urlencode($tmp_one['name']),
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
