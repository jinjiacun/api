<?php
#曝光企业
namespace Soapi\Model;
use Think\Model\ViewModel;
class InexposalcompanyViewModel extends ViewModel {
	public $viewFields = array(
		'In_exposal'=>array('id','user_id', 'add_time', 'content'),
		'Company'   =>array('id'=>'company_id','company_name', 'auth_level', '_on'=>"Company.id=In_exposal.company_id"),
	);
}
