<?php
return array(
	 'DB_TYPE'       => 'mysql',     // 数据库类型
     'DB_HOST'       => 'localhost', // 服务器地址
     'DB_NAME'       => 'yms',       // 数据库名
     'DB_USER'       => 'root',      // 用户名
     'DB_PWD'        => '',        // 密码
     'DB_PORT'       => 3306,        // 端口
     'DB_PREFIX'     => 'yms_',    // 数据库表前缀
     'DB_CHARSET'    => 'utf8',    // 字符集
     'domain_url'    => 'http://localhost',
     'media_url_pre' => 'http://localhost/Api/Public/',
     'param_err'     => array(500, urlencode('参数不合法')),
	//'配置项'=>'配置值'
);