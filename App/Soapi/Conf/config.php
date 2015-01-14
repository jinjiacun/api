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
     'domain_url'    => 'http://192.168.1.131',
     'media_url_pre' => 'http://192.168.1.131/yms_api/Public/',
     'param_err'     => array(500, urlencode('参数不合法')),
     'param_fmt_err' => array(500, urlencode('参数格式不正确')),
     'option_ok'     => urlencode('操作成功'),
     'option_fail'   => urlencode('操作失败'),
     'api_user_url'  => 'http://192.168.1.31:8300/Api/',
	//'配置项'=>'配置值'
);
