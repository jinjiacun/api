<?php
#企业新闻
namespace Soapi\Model;
use Think\Model\ViewModel;
class CompanynewsViewModel extends ViewModel {
   public $viewFields = array(
     'Comment'=>array('id', 'company_id','user_id','type','content'),
     'Company'=>array('id'=>'company_id','company_name','_on'=>'Comment.company_id=Company.id'),
     'News'=>array('id'=>'news_id','title', '_left'=>'Comment.news_id=News.id')
   );
 }
