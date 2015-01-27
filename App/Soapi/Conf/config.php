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
     
	 'DATA_CACHE_TYPE' => 'Memcache',
	 'MEMCACHE_HOST'   => 'tcp://127.0.0.1:11211',
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
     'api_user_url'  => 'http://192.168.1.31:8300/Api/',
     'api_user_domain' => 'http://192.168.1.31:8300',
     'api_user_pic_url' => 'http://192.168.1.31:8300/imgcode?m=',
     'api_user_photo_url' => 'http://192.168.1.31:8310',
     'api_user_photo_def_url' => 'http://192.168.1.31:8310/useravatar.jpg',
	//'配置项'=>'配置值'
);
