<?php
return array(
	//'配置项'=>'配置值'
	'AUTH_ON' => true, //认证开关
    'AUTH_TYPE' => 1, // 认证方式，1为时时认证；2为登录认证。
    'AUTH_GROUP' => 'admin_group', //用户组数据表名
    'AUTH_GROUP_ACCESS' => 'admin_group_access', //用户组明细表
    'AUTH_RULE' => 'admin_rule', //权限规则表
    'AUTH_USER' => 'admin'//用户信息表
);