<?php
namespace Admin\Model;
use Think\Model\ViewModel;
class AttrModel extends Model {
 public $viewFields = array(
     'Attr'=>array('attr_id'=>'id','attr_name'=>'name'),
   );
 }