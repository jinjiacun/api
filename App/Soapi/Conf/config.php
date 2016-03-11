<?php
return array(
     'DB_TYPE'       => 'mysql',     // 数据库类型
     'DB_HOST'       => 'localhost', // 服务器地址
     'DB_NAME'       => 'so_black',       // 数据库名
     'DB_USER'       => 'root',      // 用户名
     'DB_PWD'        => '123456',        // 密码
     'DB_PORT'       => 3306,        // 端口
     'DB_PREFIX'     => 'so_',    // 数据库表前缀
     'DB_CHARSET'    => 'utf8',    // 字符集
     
	 //'DATA_CACHE_TYPE' => 'Memcache',
	 //'MEMCACHE_HOST'   => 'tcp://127.0.0.1:11211',
	 'DATA_CACHE_TYPE' => 'file',
	 'DATA_CACHE_TIME' => '3600',
		
     'domain_url'    => 'http://192.168.1.131',
     'media_url_pre' => 'http://192.168.1.131/yms_api/Public/',
     'param_err'     => array(500, urlencode('参数不合法')),
     'param_fmt_err' => array(500, urlencode('参数格式不正确')),
     'option_ok'     => urlencode('操作成功'),
     'option_fail'   => urlencode('操作失败'),
     'option_no_allow' => urlencode('不允许操作'),
     'is_exists'     => urlencode('存在'),
     'no_exists'  => urlencode('不存在'),
     'api_user_document'=>'http://192.168.1.31:8300/Api/Readme',
     'api_user_url'  => 'http://192.168.1.31:8300/Api/',
     'api_user_domain' => 'http://192.168.1.31:8300',
     'api_user_pic_url' => 'http://192.168.1.31:8300/imgcode?m=',
     'api_user_photo_url' => 'http://192.168.1.31:8310',
     'api_user_photo_def_url' => 'http://192.168.1.31:8310/useravatar.jpg',
     'KEY'                => 'souh*e_i#2?0>1&5',
     'push_android_url'   =>'http://192.168.1.131/phpmquttclient/send_mqtt.php',
     'push_ios_url'       =>'',
     #推送事件类型
     'push_event_type' =>array(
                           #回复主贴评论推送
		                   'comment_master'=> array('value'=>'010001', 
		                                            'src_event_param'=>'comment_id:<COMMENT_ID>,parent_id:<PARENT_ID>,content:<CONTENT>',
		                                            'des_event_param'=>'type:010001,comment_id:<COMMENT_ID>'),
		                   #回复跟帖评论推送                         
		                   'comment'       => array('value'=>'010002',
		                                            'src_event_param'=>'parent_id:<COMMENT_ID>,parent_id:<PARENT_ID>,content:<CONTENT>',
		                                            'des_event_param'=>'type:010002,comment_id:<COMMENT_ID>'),
		                   #企业评级改变推送 
		                   'company'       => array('value'=>'010003',
		                                            'src_event_param'=>'company_id:<COMPANY_ID>,nature:<NATURE>,auth_level:<AUTH_LEVEL>',
		                                            'des_event_param'=>'type:010003,company_id:<COMPANY_ID>,nature:<NATURE>,auth_level:<AUTH_LEVEL>'),
		                   #负面新闻推送
		                   'company_news'  => array('value'=>'010004',
		                                            'src_event_param'=>'news_id:<NEWS_ID>,company_id:<COMPANY_ID>',
		                                            'des_event_param'=>'type:010004,news_id:<NEWS_ID>'), 
		                   #曝光回复推送
		                   'exposal_re'    => array('value'=>'010005',
		                                            'src_event_param'=>'comment_id:<COMMENT_ID>,exposal_id:<EXPOSAL_ID>,user_id:<USER_ID>,content:<CONTENT>',
		                                            'des_event_param'=>'type:010005,exposal_id:<EXPOSAL_ID>'), 
		                   #曝光回复的推送
		                   'exposal_rre'   => array('value'=>'010006',
		                                            'src_event_param'=>'comment_id:<COMMENT_ID>,parent_id:<PARENT_ID>,exposal_id:<EXPOSAL_ID>,content:<CONTENT>',
		                                            'des_event_param'=>'type:010006,comment_id:<COMMENT_ID>'), 
		                                            
	 ),
	 'resource_url'=>'http://tongji.cngold.com.cn/tools/PostSouheiData?mobile=%s&uname=%s&url=%s&preurl=%s&agent=%s&screen=%s&remark=%s',
	//'配置项'=>'配置值'
);
