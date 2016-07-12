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
     'param_fmt_err' => array(500, urlencode('参数格式不正确')),
     'option_ok'     => urlencode('操作成功'),
     'option_fail'   => urlencode('操作失败'),
     'option_no_allow' => urlencode('不允许操作'),
     'is_exists'     => urlencode('存在'),
     'no_exists'     => urlencode('不存在'),
     'media'         => array(
         '003001'   =>'友情链接',
     ),
     
	//'配置项'=>'配置值'
     
     'AminInitCol' => '376,378,379,380,384,392,394,397,398,399,401',//机构管理员权限
     'PassUrl'=>'http://192.168.1.45:8088/OpenAzure.ashx',//审核同步地址
     'TeacherInitCol' => '355,377,381,378,385',//机构分析师权限
     'VipInitModule' => '171,176,180|171,176,180|171,176,180',
     'DefaultRoomTitle' => '趋势为先,轻仓操作,乃为金海啸龙之根本',
     'DefaultRoomMaximage' => 'http://zy.cngold.com.cn/image/roomdefault.png',
     'DefaultRoomMinimage' => 'http://zy.cngold.com.cn/image/roomdefault.png',
     'DefaultRoomName' => '定制直播室',
     'DefaultLiveContent' => '欢迎您来到直播室',
     'DefaultAdavatar' => 'http://zy.cngold.com.cn/image/teacherdefault.png',
     'AnalystPrefixName' => 'teacher_',
     'AdminPrefixName' => 'admin_',
     
);
