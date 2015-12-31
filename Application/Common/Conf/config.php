<?php
return array(
	//'配置项'=>'配置值'
	/* 数据库设置 */
    'DB_TYPE'               =>  'mysql',     // 数据库类型
    'DB_HOST'               =>  'localhost', // 服务器地址
    'DB_NAME'               =>  'tiku',          // 数据库名
    'DB_USER'               =>  'root',      // 用户名
    'DB_PWD'                =>  'root',          // 密码
    'DB_PORT'               =>  '3306',        // 端口
    'DB_PREFIX'             =>  '',    // 数据库表前缀
    'DB_PARAMS'          	=>  array(), // 数据库连接参数    
    'DB_DEBUG'  			=>  TRUE, // 数据库调试模式 开启后可以记录SQL日志
    'DB_FIELDS_CACHE'       =>  true,        // 启用字段缓存
    'DB_CHARSET'            =>  'utf8',      // 数据库编码默认采用utf8
    'DB_DEPLOY_TYPE'        =>  0, // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    'DB_RW_SEPARATE'        =>  false,       // 数据库读写是否分离 主从式有效
    'DB_MASTER_NUM'         =>  1, // 读写分离后 主服务器数量
    'DB_SLAVE_NO'           =>  '', // 指定从服务器序号
    //	缓存设置
    'FILE_CACHE_TIME'		=>  2592000,//文件形式缓存时间,30天
    //邮件配置
    'MAIL_ADDRESS'			=>'wys030231@163.com', // 邮箱地址
	'MAIL_SMTP'				=>'smtp.163.com', // 邮箱SMTP服务器
	'MAIL_LOGINNAME'		=>'wys030231', // 邮箱登录帐号
	'MAIL_PASSWORD'			=>'ter987', // 邮箱密码
	'MAIL_CHARSET'			=>'UTF-8',//编码
	'MAIL_PORT'						=>	'25',
	'MAIL_AUTH'				=>true,//邮箱认证
	'MAIL_HTML'				=>true,//true HTML格式 false TXT格式
	//Memcached 配置
	'MEMCACHED_HOST'		=> '127.0.0.1',
	'MEMCACHED_POST'		=> '11211',
	'MEMCACHED_EXPIRE'		=> '0',
	//cookie配置
	'COOKIE_EXPIRE'			=> 604800,//7天
);