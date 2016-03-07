<?php
namespace Api\Model;
use Think\Model\ViewModel;
class AttrViewModel extends ViewModel {
   public $viewFields = array(
     'Attr'=>array('id'=>'attr_id','name'=>'attr_name'),
     'Attr_val'=>array('id'=>'attr_val_id', 'name'=>'attr_val_name', '_on'=>'Attr.id=Attr_val.attr_id'),
   );
 }
