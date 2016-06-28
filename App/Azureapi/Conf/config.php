<?php
return array(
	 'DB_TYPE'       => 'mysql',     // 数据库类型
     'DB_HOST'       => 'localhost', // 服务器地址
     'DB_NAME'       => 'Azure',       // 数据库名
     'DB_USER'       => 'root',      // 用户名
     'DB_PWD'        => '123456',        // 密码
     'DB_PORT'       => 3306,        // 端口
     'DB_PREFIX'     => 'sp_',    // 数据库表前缀
     'DB_CHARSET'    => 'utf8',    // 字符集
     'domain_url'    => 'http://localhost',
     'media_url_pre' => 'http://localhost/Api/Public/',
     'param_err'     => array(500, urlencode('参数不合法')),
     'param_fmt_err' => array(500, urlencode('参数格式不正确'))
	//'配置项'=>'配置值'
     
     'AminInitCol' => '376,378,379,380,384,392,394,397,398,399,401',//机构管理员权限
     'TeacherInitCol' => '355,377,381,378,385',//机构分析师权限
);
