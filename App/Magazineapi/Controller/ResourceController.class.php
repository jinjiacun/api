<?php
namespace Magazineapi\Controller;
use Magazineapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--资源管理--
------------------------------------------------------------
function of api:

public function add
@@input
@param string $name 资源名称
@param string $url  资源url
@param string $description 资源描述
@param int    $is_partition 是否分割，默认不分割(0)
@@output
@param $is_success 0-操作成功,-1-操作失败
##--------------------------------------------------------##
*/
class ResourceController extends BaseController {
	/**
	 * sql script:
	 * create table hr_resource(id int primary key auto_increment,
	                             name varchar(255) comment '名称',
	                             source_name varchar(255) comment '资源名称',
	                             team_name varchar(255) comment '组名',
                                     url varchar(255) comment '连接url',
                                     description varchar(255) comment '描述',
	                             add_time int not null default 0 comment '添加日期'
	                             )charset=utf8;
	 * */
	 
	 public $_module_name = 'Resource';
	 public $id;
	 public $name;
	 public $source_name;
	 public $team_name;
        public $url;
        public $description;
	 public $add_time;      //注册时间
         
	 public function add($content)
	 /*
	 @@input
	 @param string $name         资源名称
	 @param string $source_name  资源名称
	 @param string $team_name    组名
	 @param string $url          资源url
	 @param string $description  资源描述
	 @@output
	 @param $is_success 0-操作成功,-1-操作失败
	 */
	 {
		$data = $this->fill($content);
	
		if(!isset($data['name'])
		|| !isset($data['source_name'])
		|| !isset($data['team_name'])
		|| !isset($data['url'])
		|| !isset($data['description'])
		)
		{
				return C('param_err');
		}
	
		$data['name']         = htmlspecialchars(trim($data['name']));
		$data['source_name']  = htmlspecialchars(trim($data['source_name']));
		$data['team_name']    = htmlspecialchars(trim($data['team_name']));
		$data['url']          = htmlspecialchars(trim($data['url']));
		$data['description']  = htmlspecialchars(trim($data['description']));
	
		if('' == $data['name']
		|| '' == $data['source_name']
		|| '' == $data['team_name']
		|| '' == $data['url']
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
											'source_name' => urlencode($v['source_name']),
											'team_name'   => urlencode($v['team_name']),
											'url'         => urlencode($v['url']),
											'description' => urlencode($v['description']),
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
					'source_name' => urlencode($tmp_one['source_name']),
					'team_name'   => urlencode($tmp_one['team_name']),
					'url'         => urlencode($tmp_one['url']),
					'description' => urlencode($tmp_one['description']),
					'add_time'    => intval($tmp_one['add_time']), 
				);
			}
			
			return array(
				200,
				$list
			);
	  }
}
?>
