<?php
namespace Api\Model;
use Think\Model\ViewModel;
class CategoryAttrViewModel extends ViewModel {
   public $viewFields = array(
     'Attr'=>array('id'=>'attr_id','name'=>'attr_name'),
     'Attr_val'=>array('id'=>'attr_val_id', 'name'=>'attr_val_name', '_on'=>'Attr.id=Attr_val.attr_id'),
     'Cat_attr_val'=>array('goods_stat', '_on'=>'Cat_attr_val.attr_val_id=Attr_val.id'),
     'Category'=>array('id'=>'cat_id', 'name'=>'cat_name', '_on'=>'Category.id=Cat_attr_val.cat_id'),
   );
 }
