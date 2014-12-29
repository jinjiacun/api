<?php
namespace Admin\Model;
use Think\Model\ViewModel;
class AttrvalModel extends Model {
 public $viewFields = array(
     'Attr_val'=>array('attr_val_id'=>'id','attr_val_name'=>'name'),
   );
 }