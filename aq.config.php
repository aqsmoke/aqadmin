<?php

/* * **********************************************************************************************\
 * powered by aqsmoke
 * 2012.1.5
 * 配置文件，使用程序第一步就要配置这个
 * ********************************************************************************************** */

$aq_site = 'http://aqsmoke.cn/aqadmin';
$charset = 'utf-8';

define('AQ_COOKIEPRE', 'aq_');

//数据库的配置
//$AQ_HOST = array('aq_host1' => array('host' => 'localhost', 'user' => 'root', 'pw' => '', 'db' => 'aqadmintest', 'charset' => 'utf-8'));

//导航的设置
$navArr = array('栏目管理' => 'cate', // 如果设置为comment 则相对应要建立一个 aq.comment.php 在action文件夹 配置主要在这里
    '文章管理' => 'topic', '单页管理' => 'single', '邮件管理' => 'email', '首页管理' => 'indexList', '账号管理' => 'member', '中华小姐' => 'MissChina' ,'位置管理' => 'thinkNode');

// 配置每个功能所对应的数据库。 
$hostArr = array(
    'cate' => array(
        'host' => 'localhost',
        'user' => 'root',
        'pw' => '',
        'db' => 'aqadmintest',
        'charset' => 'utf-8'
    ),
    'topic' => array(
        'host' => 'localhost',
        'user' => 'root',
        'pw' => '',
        'db' => 'aqadmintest',
        'charset' => 'utf-8'
    ),
    'single' => array(
        'host' => 'localhost',
        'user' => 'root',
        'pw' => '',
        'db' => 'aqadmintest',
        'charset' => 'utf-8'
    ),
    'email' => array(
        'host' => 'localhost',
        'user' => 'root',
        'pw' => '',
        'db' => 'aqadmintest',
        'charset' => 'utf-8'
    ),
    'indexList' => array(
        'host' => 'localhost',
        'user' => 'root',
        'pw' => '',
        'db' => 'aqadmintest',
        'charset' => 'utf-8'
    ),
    'member' => array(
        'host' => 'localhost',
        'user' => 'root',
        'pw' => '',
        'db' => 'aqadmintest',
        'charset' => 'utf-8'
    ),
    'MissChina' => array(
        'host' => 'localhost',
        'user' => 'root',
        'pw' => '',
        'db' => 'aqadmintest',
        'charset' => 'utf-8'
    ),
    'thinkNode' => array(
        'host' => 'localhost',
        'user' => 'root',
        'pw' => '',
        'db' => 'demo',
        'charset' => 'utf-8'
    )
);


$STYLE_ARR = array(1 => 'aero', 2 => 'black', 3 => 'blue', 4 => 'chrome', 5 => 'default', 6 => 'green', 7 => 'idialog', 8 => 'opera', 9 => 'simple', 10 => 'twitter');

define('DEBUG', true);
define('LOG_ON', TRUE);
define('LOG_PATH', AQ_ROOT . '/data/log/');
define('ERROR_URL', '');
define('ERROR_HANDLE', TRUE);

//


$loginConfig = array('usertable' => 'adminuser', //  这是用户表的名字
    'namefield' => 'adminname', // 这是用户名字字段
    'pwfield' => 'password')// 这是用户密码字段
;
?>
