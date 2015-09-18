<?php
namespace Soapi\Model;
use Think\Model\ViewModel;
class CompanyaliasViewModel extends ViewModel {
   public $viewFields = array(
     'Company'=>array('id','nature','trade','company_name','auth_level','company_type','reg_address',
                     'busin_license','code_certificate','telephone','website','record','find_website',
                     'agent_platform','mem_sn','certificate','add_time'),
     'Company_alias'=>array('name', '_on'=>'Company_alias.company_id=Company.id'),
   );
 }
