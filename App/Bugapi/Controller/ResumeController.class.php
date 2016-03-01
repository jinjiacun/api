<?php
namespace Bugapi\Controller;
use Bugapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--简历管理--
------------------------------------------------------------
function of api:

public function add
@@input
@param string $number       编号
@param string $candidates   编号
@param string $telephone    手机号码
@param int    $position_id  岗位
@param int    $part_id      部门
@param string $accessories  附件
@param string $remartk      备注
@param int    $create       创建人
@@output
@param $is_success 0-操作成功,-1-操作失败
##--------------------------------------------------------##
*/
class ResumeController extends BaseController {
	/**
	 * sql script:
	 * create table hr_resume(id int primary key auto_increment,
	                             number varchar(255) comment '编号',
	                             title varchar(255) comment '标题',
	                             candidates varchar(255) comment '应聘人',
	                             telephone varchar(255) comment '手机号码',
	                             position_id int not null default 0 comment '岗位id',
	                             part_id int not null default 0 comment '部门id',
	                             source_id int not null default 0 comment '来源id',
	                             status int not null default 0 comment '状态',
	                             accessories varchar(255) comment '附件',
                                    remartk varchar(255) comment '备注',
                                    stage int not null default 1 comment '阶段',
                                    stage_time int not null default 0 comment '阶段时间',
                                    close int not null default 0 comment '关闭(默认0，未关闭)',
                                    create int not null default 0 comment '创建人',
                                    last int not null default 0 comment '最后更新人',
                                    last_time int not null default 0 comment '最后更新时间',
	                             add_time int not null default 0 comment '添加日期'
	                             )charset=utf8;
	 * */
	 
	 public $_module_name = 'Resume';
	 public $id;
	 public $number;
	 public $candidates;
	 public $telephone;
	 public $position_id;
	 public $part_id;
	 public $source_id;
        public $status;
        public $accessories;
        public $remartk;
        public $stage;
        public $stage_time;
        public $close;
        public $create;
        public $last;
        public $last_time;
	 public $add_time;     
         
	 public function add($content)
	 /*
	 @@input
	 @param string $number       编号
	 @param string $candidates   编号
	 @param string $telephone    手机号码
	 @param int    $position_id  岗位
	 @param int    $part_id      部门
	 @param string $accessories  附件
	 @param string $remartk      备注
	 @param int    $create       创建人
	 @@output
	 @param $is_success 0-操作成功,-1-操作失败
	 */
	 {
		$data = $this->fill($content);
	
		if(!isset($data['number'])
		/*
		|| !isset($data['candidates'])
		|| !isset($data['telephone'])
		|| !isset($data['position_id'])
		*/
		|| !isset($data['part_id'])
		|| !isset($data['accessories'])
		#|| !isset($data['remartk'])
		|| !isset($data['create'])
		)
		{
				return C('param_err');
		}
	
	       $data['number']       = htmlspecialchars(trim($data['number']));
	       /*
	       $data['candidates']   = htmlspecialchars(trim($data['candidates']));
	       $data['telephone']    = htmlspecialchars(trim($data['telephone']));
	       $data['position_id']  = intval(trim($data['position_id']));
	       */
	       $data['part_id']      = intval(trim($data['part_id']));
	       $data['accessories']  = htmlspecialchars(trim($data['accessories']));
	       #$data['remartk']      = htmlspecialchars(trim($data['remartk']));
	       $data['create']       = intval(trim($data['create']));
	
		if('' == $data['number']
		/*
		|| '' == $data['candidates']
		|| '' == $data['telephone']
		|| 0  > $data['position_id']
		*/
		|| 0  > $data['part_id']
		|| '' == $data['accessories']
		#|| '' == $data['remartk']
		|| 0 > $data['create']
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
											'title'       => urlencode($v['title']),
											'number'      => urlencode($v['number']),
											'candidates'  => urlencode($v['candidates']),
											'telephone'   => urlencode($v['telephone']),
											'position_id' => intval($v['position_id']),
											'part_id'     => intval($v['part_id']),		
											'source_id'   => intval($v['source_id']),
											'status'      => urlencode($v['status']),
											'accessories' => urlencode($v['accessories']),
											'remartk'     => urlencode($v['remartk']),
											'stage'       => intval($v['stage']),
											'stage_time'  => intval($v['stage_time']),
											'close'       => intval($v['close']),
											'create'      => intval($v['create']),
											'last'        => intval($v['last']),
											'last_time'   => intval($v['last_time']),
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
					'id'              => intval($tmp_one['id']),
					'title'           => urlencode($tmp_one['title']),
					'number'          => urlencode($tmp_one['number']),
					'candidates'      => urlencode($tmp_one['candidates']),
					'telephone'       => urlencode($tmp_one['telephone']),
					'position_id'     => intval($tmp_one['position_id']),
					'part_id'         => intval($tmp_one['part_id']),
					'source_id'       => intval($tmp_one['source_id']),
					'status'          => urlencode($tmp_one['status']),
					'accessories'     => urlencode($tmp_one['accessories']),
					'accessories_url' => $this->get_pic_url($tmp_one['accessories']),
					'remartk'         => urlencode($tmp_one['remartk']),
					'stage'           => intval($tmp_one['stage']),
					'stage_time'      => intval($tmp_one['stage_time']),
					'close'           => intval($tmp_one['close']),
					'create'          => intval($tmp_one['create']),
					'last'            => intval($tmp_one['last']),
					'last_time'       => intval($tmp_one['last_time']),
					'add_time'        => intval($tmp_one['add_time']), 
				);
			}
			
			return array(
				200,
				$list
			);
	  }
}
?>
