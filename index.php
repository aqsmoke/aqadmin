<?php

/* * **********************************************************************************************\
 * aqadmin通用后台入口文件	
 * powered by aqsmoke
 * 2012.1.5
  \*********************************************************************************************** */

require_once('./aq.common.php');


if ($G['action'] == 'login' && $G['do'] == 'login') {
    if ($P['aq_submit']) {
        $username = $P[$loginConfig['namefield']];
        $password = $P[$loginConfig['pwfield']];
        if (empty($username)) {
            aqError('Error', '用户名不可为空');
            exit;
        }
        $remember = $P['remember'];
        $password = md5(md5($password));
        $user_arr = $loginDb->fetch_first("SELECT * FROM $loginConfig[usertable] WHERE $loginConfig[namefield] = '$username'");
        if (empty($user_arr[$loginConfig['pwfield']]) || $user_arr[$loginConfig['pwfield']] != $password) {
            $err = 1;
            $content = "对不起，登录失败";
        } else {
            $err = 0;
            $content = "恭喜你，成功登录";
            aqSetCookie('auth', $username . "\t" . $password, $remember == 1 ? 24 * 3600 : 30 * 24 * 3600);
        }
        //outPutHeader();
        //outPutNav();
        if ($err == 1)
            aqMessage($content, '提示', '', 2);
        else
            aqMessage($content, '提示', 'index.php', 2);
        //outPutFooter();
        exit;
    }
}
if ($G['action'] == 'login' && $G['do'] == 'logout') {
    aqSetCookie('auth', '', -86400 * 365);
    aqMessage('你已经退出登录', 'aq提示', 'index.php', 1);
    exit;
}

if ($G['style']) {
    aqSetCookie('style', $G['style'], 24 * 3600);
    aqMessage('样式修改完毕', '提示', $_SERVER['HTTP_REFERER'], 2);
    exit;
}

$user_arr = checkLogin();
$username = $user_arr['adminname'];
$purview = $user_arr['purview'];
$purArr = explode(',', $purview);


if (!in_array($G['do'], array('getExcel'))) {
    //加载头部
    outPutHeader();

//加载左侧导航
    outPutNav();
//echo '<h1><img src="./images/posts.png" alt=""> aqsmoke框架建设中...</h1>';
}


/* * **********************************************************************************************\
 * 中间内容区域
  \*********************************************************************************************** */
if (!$G['action'] || in_array($G['action'], array('login'))) {
    $G['action'] = '';
}

if ($G['action']) {
    if (!in_array($G['action'], $purArr)) {
        aqMessage('你没有权限管理', 'aq提示', $aq_site, 1.5);
        exit;
    }

    $db = new AqDb();
    $db->setHost($hostArr[$G['action']]);

    include(AQ_ROOT . "/action/aq." . $G['action'] . ".php");
    include(AQ_ROOT . "/include/aq.action.php");
    if (!$G['do'] || !function_exists($G['do'])) {
        $G['do'] = 'index';   //如果do不存在，或者此函数不存在， 则默认执行index 函数
    }
    $G['do']();
}

//debug 调试信息 
//调试信息放在内容上侧。因为可伸缩，并不影响观看 ,
//如要关闭调试，请在aq.config.php里面把 DEBUG 设置为 false
if (DEBUG) {
    echo '<div class="bloc"><div class="title">aqsmoke调试<a href="#" class="toggle"></a></div><div class="content">';
    echo '<pre>';
    print_r($GLOBALS);
    echo '</div></div>';
}
if (!in_array($G['do'], array('getExcel'))) {
//加载底部
    outPutFooter();
}
?>
