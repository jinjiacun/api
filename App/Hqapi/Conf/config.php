<?php
return array(
	 'MODULE_NAME'    => 'Hqapi',
     'IS_DEBUG'      => 0,
     'DB_TYPE'       => 'mysql',     // 数据库类型
     'DB_HOST'       => '221.6.167.248', // 服务器地址
     'DB_NAME'       => 'stocks',       // 数据库名
     'DB_USER'       => 'public',      // 用户名
     'DB_PWD'        => 'cngold',        // 密码
     'DB_PORT'       => 3306,        // 端口
     'DB_PREFIX'     => '',    // 数据库表前缀
     'DB_CHARSET'    => 'utf8',    // 字符集

	 'RTX_SERVER'    => '192.168.1.205',	 
	 'RTX_PORT'      => '8006',
		
	 'media_physical_path' => 'C:/wamp/www/yms_api/Public/', #媒体物理路径
	 'swf_tool_path'       => 'C:/SWFTools/pdf2swf.exe',#swf生成工具路径
     
	 'DATA_CACHE_TYPE' => 'Memcache',
	 'MEMCACHE_HOST'   => 'tcp://127.0.0.1:11211',
	// 'DATA_CACHE_TYPE' => 'file',
	 'DATA_CACHE_TIME' => 3600*24,
	 'PROJECT_CLASS'   => array(
	 		     1 => '中金网',
			     2 => '金融家',
			     3 => '直播室',
 			     4 => '官网',
			     5 => '投了么',
			     6 => '其他',			     
	 ),
		
     'domain_url'    => 'http://192.168.1.131',
     'media_url_pre' => 'http://192.168.1.131/yms_api/Public/',
     'param_err'     => array(500, urlencode('参数不合法')),
     'param_fmt_err' => array(500, urlencode('参数格式不正确')),
     'option_ok'     => urlencode('操作成功'),
     'option_fail'   => urlencode('操作失败'),
     'option_no_allow' => urlencode('不允许操作'),
     'is_exists'     => urlencode('存在'),
     'no_exists'  => urlencode('不存在'),
     'api_user_url'  => 'http://192.168.1.31:8300/Api/',
     'api_user_domain' => 'http://192.168.1.31:8300',
     'api_user_pic_url' => 'http://192.168.1.31:8300/imgcode?m=',
     'api_user_photo_url' => 'http://192.168.1.31:8310',
     'api_user_photo_def_url' => 'http://192.168.1.31:8310/useravatar.jpg',
     'KEY'                => 'souh*e_i#2?0>1&5',
     'mosquitto_server_url'=> 'http://192.168.1.131/phpmquttclient/send_mqtt.php',
	//'配置项'=>'配置值'
);
