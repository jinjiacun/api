<?php
#曝光企业
namespace Soapi\Model;
use Think\Model\ViewModel;
class InexposalcompanyViewModel extends ViewModel {
	public $viewFields = array(
		'In_exposal'=>array('id','user_id', 'add_time', 'content','pic_1'),
		'Company'   =>array('id'=>'company_id','company_name', 'auth_level','nature','logo_url','alias_list', '_on'=>"Company.id=In_exposal.company_id"),
	);
}
