<?php
namespace Soapi\Model;
use Think\Model\ViewModel;
class CompanyaliasViewModel extends ViewModel {
   public $viewFields = array(
     'Company'=>array('id','nature','trade','company_name'),
     'Company_alias'=>array('name', '_on'=>'Company_alias.company_id=Compnay.id'),
   );
 }