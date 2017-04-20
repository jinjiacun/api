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
	 
	 
	public function get_list($content)
	{
		$_cache = S($this->_module_name.$content);
		if(!$_cache){
			list($data, $record_count) = parent::get_list($content);

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
			S($this->_module_name.$content, array($list, $record_count));
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
