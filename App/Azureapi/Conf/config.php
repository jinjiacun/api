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
     'param_field'     => 'err_field',
     'param_err'     => array(500, urlencode(session('err_field').'参数不合法')),
     'param_fmt_err' => array(500, urlencode(session('err_field').'参数格式不正确')),
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
     
     //sql语句模板
     'TEMPLATE_SQL'=>array(
     	             //直播统计信息
     	             'STAT_LIVE_BROADCAST' => 'SELECT n.AdminName AS NAME,
                                                    n.num AS TotalCount,
                                                    m.num AS TodayCount 
											                        FROM (SELECT sp_room_live.AdminName,
											                                     COUNT(1) AS num
											                              FROM sp_com_admin_lte  INNER JOIN sp_room_live 
											                                   ON sp_room_live.AdminId = sp_com_admin_lte.ComAdminId
											                              WHERE  ComId=<ComId>
											                              GROUP BY sp_room_live.AdminName) n
											                        LEFT JOIN (SELECT 
											                                     sp_room_live.AdminName,COUNT(1) AS num
											                                   FROM sp_com_admin_lte  INNER JOIN sp_room_live 
											                                        ON sp_room_live.AdminId = sp_com_admin_lte.ComAdminId
											                                   WHERE ComId=<ComId>
											                                         AND TO_DAYS(LiveTime) = TO_DAYS(NOW())
											                                   GROUP BY sp_room_live.AdminName ) m 
											                        ON n.AdminName=m.AdminName',
                        
                    //互动操作
                    'INTERACT' => 'SELECT n.UserNickName AS NAME,
                                 	     n.num AS TotalCount,
																       m.num AS TodayCount 
																	FROM  
																	    (SELECT sp_room_inter.UserNickName,
																	            COUNT(1) AS num 
																	     FROM sp_com_admin_lte
																	          INNER JOIN sp_room_inter  ON sp_room_inter.UserId = sp_com_admin_lte.ComAdminId 
																	     WHERE ComId=<ComId> AND InterType=1 
																	     GROUP BY sp_room_inter.UserNickName) n 
																	     LEFT JOIN (SELECT sp_room_inter.UserNickName,
																	                       COUNT(1) AS num 
																	                FROM sp_com_admin_lte 
																	                     INNER JOIN sp_room_inter 
																	                     ON sp_room_inter.UserId = sp_com_admin_lte.ComAdminId 
																	                WHERE  ComId=<ComId>  
																	                       AND InterType=1 
																	                       AND TO_DAYS(InterTime) = TO_DAYS(NOW())
																	                GROUP BY sp_room_inter.UserNickName ) m 
																	      ON n.UserNickName=m.UserNickName',
									//操作建议
									'OPTION' => 'SELECT n.AdminName AS NAME,
															        n.num AS TotalCount,
															        m.num AS TodayCount 
															 FROM (SELECT sp_room_live.AdminName,
															              COUNT(1) AS num
															        FROM sp_com_admin_lte INNER JOIN sp_room_live 
															            ON sp_room_live.AdminId = sp_com_admin_lte.ComAdminId
															        WHERE ComId=<ComId>
															             AND LiveVipGrade>0
															        GROUP BY sp_room_live.AdminName) n
															       LEFT JOIN (
															        SELECT sp_room_live.AdminName,
															               COUNT(1) AS num
															        FROM sp_com_admin_lte INNER JOIN sp_room_live 
															             ON sp_room_live.AdminId = sp_com_admin_lte.ComAdminId
															        WHERE ComId=<ComId>
															              AND TO_DAYS(LiveTime) = TO_DAYS(NOW()) 
															              AND LiveVipGrade>0
															        GROUP BY sp_room_live.AdminName 
															        ) m 
															ON n.AdminName=m.AdminName',
								 //金评信息
								'GLOD_COMMENT'=>'SELECT n.AdminName AS NAME,
																	        n.num AS TotalCount,
																	        m.num AS TodayCount 
																	FROM (SELECT sp_com_news.AdminName,
																	             COUNT(1) AS num
																	      FROM sp_com_admin_lte INNER JOIN sp_com_news 
																	                       ON sp_com_news.AdminId = sp_com_admin_lte.ComAdminId
																	      WHERE ComId=<ComId>
																	            AND sp_com_news.ColumnId=888
																	      GROUP BY sp_com_news.AdminName) n
																	      LEFT JOIN(SELECT sp_com_news.AdminName,
																	                       COUNT(1) AS num
																	                FROM sp_com_admin_lte INNER JOIN sp_com_news
																	                                            ON sp_com_news.AdminId = sp_com_admin_lte.ComAdminId
																	                WHERE ComId=<ComId>
																	                      AND sp_com_news.ColumnId=888
																	                      AND TO_DAYS(NewUpTime) = TO_DAYS(NOW())
																	                      GROUP BY sp_com_news.AdminName) m
																	                ON n.AdminName=m.AdminName', 
								//多空观点
	              'LONG_SHORT_VIEW'=>' SELECT n.AdminName AS NAME,
																			        n.num AS TotalCount,
																			        m.num AS TodayCount 
																			FROM (SELECT sp_com_tip.AdminName,
																			             COUNT(1) AS num
																			      FROM sp_com_admin_lte INNER JOIN sp_com_tip
																			                       ON sp_com_tip.AdminId = sp_com_admin_lte.ComAdminId
																			      WHERE  ComId=<ComId>
																			      GROUP BY sp_com_tip.AdminName) n
																			      LEFT JOIN(SELECT sp_com_tip.AdminName,
																			                       COUNT(1) AS num
																			                FROM sp_com_admin_lte INNER JOIN sp_com_tip
																			                                 ON sp_com_tip.AdminId = sp_com_admin_lte.ComAdminId
																			                WHERE ComId=<ComId>
																			                      AND TO_DAYS(sp_com_tip.T_Time) = TO_DAYS(NOW())
																			                GROUP BY sp_com_tip.AdminName) m
																			                ON n.AdminName=m.AdminName ',
                //账户诊断
	              'ACCOUNT_DIAGNOSIS'=>'SELECT n.AdminName AS NAME,
																			       n.num AS TotalCount,
																			       m.num AS TodayCount 
																			FROM (SELECT sp_com_dia.AdminName,
																			             COUNT(1) AS num
																			      FROM sp_com_admin_lte INNER JOIN sp_com_dia
																			                       ON sp_com_dia.AdminId = sp_com_admin_lte.ComAdminId
																			      WHERE ComId=<ComId>
																			      GROUP BY sp_com_dia.AdminName) n
																			      LEFT JOIN(SELECT sp_com_dia.AdminName,
																			                       COUNT(1) AS num
																			                FROM sp_com_admin_lte INNER JOIN sp_com_dia
																			                                 ON sp_com_dia.AdminId = sp_com_admin_lte.ComAdminId
																			                WHERE  ComId=<ComId>
																			                       AND TO_DAYS(sp_com_dia.DiaFinTime) = TO_DAYS(NOW())
																			                GROUP BY sp_com_dia.AdminName) m
																			      ON n.AdminName=m.AdminName',
                //公司新闻                    
                'NEWS'=>' SELECT n.AdminName AS NAME,n.num AS TotalCount,m.num AS sp_today_count FROM 
                                    (SELECT sp_com_news.AdminName,COUNT(1) AS num
                                    FROM sp_com_admin_lte
                                    INNER JOIN sp_com_news 
                                    ON sp_com_news.AdminId = sp_com_admin_lte.ComAdminId
                                    WHERE ComId=<ComId>
                                    AND sp_com_news.ColumnId=222
                                    GROUP BY sp_com_news.AdminName) n
                                    LEFT JOIN(
                                    SELECT sp_com_news.AdminName,COUNT(1) AS num
                                    FROM sp_com_admin_lte
                                    INNER JOIN sp_com_news
                                    ON sp_com_news.AdminId = sp_com_admin_lte.ComAdminId
                                    WHERE ComId=<ComId>
                                    AND sp_com_news.ColumnId=222
                                    AND TO_DAYS(NewUpTime) = TO_DAYS(NOW())
                                    GROUP BY sp_com_news.AdminName) m
                                    ON n.AdminName=m.AdminName',
     ),
     
);
