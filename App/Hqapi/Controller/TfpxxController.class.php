<?php
namespace Hqapi\Controller;
use Hqapi\Controller;
include_once(dirname(__FILE__).'/BaseController.class.php');
/**
--停复牌管理--
------------------------------------------------------------
##--------------------------------------------------------##
*/
class TfpxxController extends BaseController {
	/**
	 * sql script:
	CREATE TABLE `tfpxx` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `code` varchar(8) DEFAULT NULL COMMENT '代码',
					  `name` varchar(10) DEFAULT NULL COMMENT '名称',
					  `haltdate` datetime DEFAULT NULL COMMENT '停牌时间',
					  `haltstopdate` datetime DEFAULT NULL COMMENT '停牌截止时间',
					  `recoverydate` datetime DEFAULT NULL COMMENT '预计复牌时间',
					  `haltterm` varchar(20) DEFAULT NULL COMMENT '停牌期限',
					  `haltreason` varchar(30) DEFAULT NULL COMMENT '停牌原因',
					  `block` varchar(10) DEFAULT NULL COMMENT '所属板块',
					  `machinetime` datetime NOT NULL COMMENT '数据库更新时间',
					  PRIMARY KEY (`id`,`machinetime`)
					) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
	 * */
	 
	 protected $_module_name = 'tfpxx';
	 protected $id;
	 protected $code;
	 protected $name;
	 protected $haltdate;
	 protected $haltstopdate;
	 protected $recoverydate;
	 protected $haltterm;
	 protected $haltreason;
	 protected $block;
	 protected $machinetime;
	 protected $key = 'id';
	 
	 
	public function get_list($content)
	{
		$_cache = S($this->_module_name.$content);
		if(!$_cache){
			//查询最新日期
			M()->query("set @a =(select DATE_FORMAT(max(machinetime),'%Y-%m-%d')
				from tfpxx);");
			$tmp_data = $this->fill($content);
			$tmp_data['where']['_string'] = "DATE_FORMAT(machinetime,'%Y-%m-%d') = @a";
			$tmp_content = json_encode($tmp_data);
			list($data, $record_count) = parent::get_list($tmp_content);

			$list = array();
			if($data)
			{
				foreach($data as $v)
				{
					$list[] = array(
							'id'              => intval($v['id']),
							'code'            => urlencode($v['code']),
							'name'            => urlencode($v['name']),
							'haltdate'        => urlencode($v['haltdate']),
							'haltstopdate'	  => $v['haltstopdate']=='0000-00-00 00:00:00'?'':urlencode($v['haltstopdate']),
							'recoverydate'    => $v['recoverydate']=='0000-00-00 00:00:00'?'':urlencode($v['recoverydate']),
							'haltterm'        => urlencode($v['haltterm']),
							'haltreason'      => urlencode($v['haltreason']),
							'block'           => urlencode($v['block']),
							//'machinetime'     => urlencode($v['machinetime']),
						);	
				}
			}
			//S($this->_module_name.$content, array($list, $record_count));
		}else{
			$list         = $_cache[0];
			$record_count = $_cache[1];			
		}


		return array(200, 
				array(
					'list'=>$list,
					'record_count'=> $record_count,
					)
				);
	}


	public function get_list_union($content){
		$_cache = S($this->_module_name.__FUNCTION__.$content);
		if(!$_cache){
			$tmp_data = $this->fill($content);
			$tmp_data['page_index'] = isset($tmp_data['page_index'])?intval($tmp_data['page_index']):1;
			$tmp_data['page_size']  = isset($tmp_data['page_size'])?intval($tmp_data['page_size']):10;
			$now_date = date("Y-m-d",strtotime("-17 day"));
			$wk_day   =date('w');
			if($wk_day == 6){
				$now_date = date("Y-m-d",strtotime("-1 day"));
			}else if($wk_day == 7){
				$now_date = date("Y-m-d",strtotime("-2 day"));
			}

			if($tmp_data['page_index'] == 1){
				$tmp_data['page_index'] = 0;
			}else if($tmp_data['page_index'] > 1)
			{
				$tmp_data['page_index'] = ($tmp_data['page_index']-1)*$tmp_data['page_size'];
			}

			/*
			M()->query("set @a =(select DATE_FORMAT(max(machinetime),'%Y-%m-%d')
				from tfpxx);");
			*/
			//查询最新日期
			$_sql_str = "(select * 
				          from tfpxx 
				          where date_format(recoverydate,'%Y-%m-%d') = '$now_date'
				          and date_format(machinetime, '%Y-%m-%d') = '$now_date')
						union all(
						select * from tfpxx 
						where date_format(machinetime,'%Y-%m-%d') = '$now_date'
						and date_format(recoverydate, '%Y-%m-%d') <> '$now_date'
						order by haltdate desc) limit ".$tmp_data['page_index'].','.$tmp_data['page_size'];
	        $_sql_str1 = "select count(1) as t from tfpxx where date_format(machinetime,'%Y-%m-%d') ='$now_date'";
		    $data = M()->query($_sql_str);
		    
		    $amount = M()->query($_sql_str1);
		    $record_count = $amount[0]['t'];

			$list = array();
			if($data)
			{
				foreach($data as $v)
				{
					$list[] = array(
							'id'              => intval($v['id']),
							'code'            => urlencode($v['code']),
							'name'            => urlencode($v['name']),
							'haltdate'        => urlencode($v['haltdate']),
							'haltstopdate'	  => $v['haltstopdate']=='0000-00-00 00:00:00'?'':urlencode($v['haltstopdate']),
							'recoverydate'    => $v['recoverydate']=='0000-00-00 00:00:00'?'':urlencode($v['recoverydate']),
							'haltterm'        => urlencode($v['haltterm']),
							'haltreason'      => urlencode($v['haltreason']),
							'block'           => urlencode($v['block']),
							//'machinetime'     => urlencode($v['machinetime']),
						);	
				}
			}
			S($this->_module_name.__FUNCTION__.$content, array($list, $record_count));
		}else{
			$list         = $_cache[0];
			$record_count = $_cache[1];			
		}


		return array(200, 
				array(
					'list'=>$list,
					'record_count'=> $record_count,
					)
				);
	}
	 
}
