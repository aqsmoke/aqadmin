<?php

/* * **********************************************************************************************\
 * 通用函数库	
 * powered by aqsmoke
 * 2012.1.5
  \*********************************************************************************************** */

//检查登录

function checkLogin() {
    global $loginDb, $loginConfig, $C;
    $err = 0;
    if (isset($C[AQ_COOKIEPRE . 'auth']) && $C[AQ_COOKIEPRE . 'auth']) {
        list($username, $password) = explode("\t", $C[AQ_COOKIEPRE . 'auth']);
        $user_arr = $loginDb->fetch_first("SELECT * FROM $loginConfig[usertable] WHERE $loginConfig[namefield] = '$username'");
        if (empty($user_arr[$loginConfig['pwfield']]) || $user_arr[$loginConfig['pwfield']] != $password) {
            $err = 1;
        }
    } else {
        $err = 1;
    }
    if ($err == 1) {
        outPutHeader();
        outPutNav();

        echo '<div class="bloc"><div class="title">aqadmin登录<a href="#" class="toggle"></a></div><div class="content">';
        formHead('login', 'login');
        echo '
		<div  id="error"></div>
		<div class="input">
    			<label for="input1">用户名：</label>
    			<input type="text" id="input1" name="' . $loginConfig[namefield] . '">
    			请输入你的用户名
		</div>
		';

        echo '
		<div class="input">
    			<label for="input1">密码：</label>
    			<input type="password" id="input1" name="' . $loginConfig[pwfield] . '">
    			请输入你的密码
		</div>
		';
        echo '
		<div class="input">
            		<!--<label class="label">自动登录</label>-->
            		<div id="uniform-radio1" class="radio">
            			<input type="radio" style="opacity: 0; " value="1" checked="checked" name="remember" id="">
            		</div>
            		<label class="inline" for="radio1">保存一天</label> 
            		
            		<div id="uniform-radio2" class="radio">
            			<input type="radio" style="opacity: 0; " value="2" name="remember" id="">
            		</div>
            		<label class="inline" for="radio2">保存一个月</label>
        	</div>
        	';
        formFooter();
        echo '</div></div>';
        outPutFooter();
        exit;
    } else {
        return $user_arr;
    }
}

function aqError($title, $message) {
    echo '<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />';
    echo "<script>";
    echo "parent.$('#error').html(\"<div id='notif' class='notif error'><strong>$title :</strong>$message.</div>\");";
    echo "parent.window.setTimeout(\"parent.$('#notif').fadeOut('5000',function(){parent.$('#error').empty();})\",'2000')";
    echo "</script>";
}

//提示
function aqMessage($message, $title = 'aq提示', $aq_site= '', $time = 0) {
    echo '<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />';
    echo "<script>
	var dialog = parent.art.dialog({
		title: '$title',
		content: '$message',
		width:400,
		init: function () {
			this.title('$title').time($time);
			return false;
		},
		close: function (){ ";
    if ($aq_site)
        echo " parent.window.location.href='$aq_site';";
    echo "	},
		cancelVal: '关闭',
		cancel: true //为true等价于function(){}
	});	
	</script>";
}

function aqMessage1($message, $title = 'aq提示', $aq_site= '', $time = 0) {
    echo '<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />';
    echo "<script>
	var dialog = art.dialog({
		title: '$title',
		content: '$message',
		width:400,
		init: function () {
			this.title('$title').time($time);
			return false;
		},
		close: function (){ ";
    if ($aq_site)
        echo " window.location.href='$aq_site';";
    echo "	},
		cancelVal: '关闭',
		cancel: true //为true等价于function(){}
	});	
	</script>";
}

//输出表单头部
function formHead($action, $do, $method = 'POST') {
    echo '<form action="index.php?action=' . $action . '&do=' . $do . '" method="' . $method . '" enctype="multipart/form-data" target="aq_iframe">';
}

//输出表单底部
function formFooter() {
    echo '<div class="submit"><input type="submit" value="提交" name="aq_submit"></div>';
    echo '</form>';
}

function aqPrint($content) {
    echo '<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />';
    $aqRand = rand(10000, 90000);
    echo "<div id='aqPrint_$aqRand' style='display:none'><pre>";
    print_r($content);
    echo "<p /><font color='red'>可以跟随滚动条</font>";
    echo "</div>";
    echo "<script>
	var aqRand	=	$aqRand;
	var dialog = parent.art.dialog({
	title:'aqadmin打印调试',
	cancel:function(){
		cancel:true;
	},
	cancelVal: '点击可以关闭我',
    	content: document.getElementById('aqPrint_$aqRand'),
    	id: aqRand
	})
	</script>";
}

//设置cookie
function aqSetCookie($key, $value, $life, $prefix=1) {
    global $_SERVER, $aq_site;
    $timestamp = time();
    $AQ_COOKIEPRE = AQ_COOKIEPRE;
    setcookie(($prefix ? $AQ_COOKIEPRE : '') . $key, $value, $life ? $timestamp + $life : 0, '/', '', $_SERVER['SERVER_PORT'] == 443 ? 1 : 0);
}

function outPutHeader() {
    global $username, $style;
    echo '<!DOCTYPE html>
		<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>smoke后台管理</title>
		<link rel="stylesheet" href="css/style.css">
		<link rel="stylesheet" href="css/jquery.wysiwyg.old-school.css">
		<!-- jQuery AND jQueryUI -->
		<script type="text/javascript" src="./js/jquery.min.js"></script>
		<script type="text/javascript" src="./js/jquery-ui.min.js"></script>
		<script type="text/javascript" src="./js/min.js"></script>
		</head>
		<body>
		<script type="text/javascript" src="./js/main.js"></script>
		<link rel="stylesheet" href="css/style1.css">
		<link href="./artDialog/skins/' . $style . '.css" rel="stylesheet" />
		<script src="./artDialog/basic/artDialog.basic.js"></script>
		<!--jQuery AND jQueryUI END-->

		
		<iframe name="aq_iframe" width="0" height="0" style="display:none;"></iframe>
		<!--HEAD--> 
		<div id="head">
			<div class="left">
		                <a href="#" class="button profile"><img src="./images/huser.png" alt=""></a>
		                Hi, ';
    if ($username) {
        echo "<a href='#'>$username</a> | ";
        echo "<a href='index.php?action=login&do=logout' target='aq_iframe'>退出登录</a>";
    } else {
        echo '<a href="#">游客</a>';
    }
    //右上角搜索框        
    echo '</div><div class="right"><form action="#" id="search" class="search placeholder"><label>搜索一下</label><input type="text" value="" name="q" class="text"><input type="submit" value="rechercher" class="submit"></form></div></div><!--HEAD END-->';
}

function outPutFooter() {
    echo '</div><div id="ui-datepicker-div" class="ui-datepicker ui-widget ui-widget-content ui-helper-clearfix ui-corner-all"></div></body></html>';
}

function outPutNav() {
    global $STYLE_ARR, $navArr;
    echo '<!--SIDEBAR--><div id="sidebar"><ul><li class="current hover"><a href="#"><img src="./images/layout.png" alt="">管理中心</a><ul>';
    foreach ($navArr as $k => $v) {
        echo '<li><a href="index.php?action=' . $v . '">' . $k . '</li>';
    }
    echo '</ul></li><li><a href="#"><img src="./images/layout.png" alt="修改样式">修改样式</a>	<ul style="display:none;">';
    foreach ($STYLE_ARR as $k => $v) {
        echo "<li><a href='index.php?style=$v' target='aq_iframe'>$v</a></li>";
    }
    echo '</ul></li></ul><a href="#collapse" id="menucollapse">隐藏一下</a></div><div id="content" class="white">';
}

function multi($num, $perpage, $curpage, $mpurl, $maxpages = 0, $page = 10, $autogoto = TRUE, $simple = FALSE) {
    if (defined('IN_ADMINCP')) {
        $shownum = $showkbd = TRUE;
        $lang['prev'] = '&lsaquo;&lsaquo;';
        $lang['next'] = '&rsaquo;&rsaquo;';
    } else {
        $shownum = $showkbd = FALSE;
        $lang['prev'] = '&nbsp';
        $lang['next'] = $GLOBALS['dlang']['nextpage'];
    }

    $multipage = '';
    $mpurl .= strpos($mpurl, '?') ? '&amp;' : '?';
    $realpages = 1;
    if ($num > $perpage) {
        $offset = 2;

        $realpages = @ceil($num / $perpage);
        $pages = $maxpages && $maxpages < $realpages ? $maxpages : $realpages;

        if ($page > $pages) {
            $from = 1;
            $to = $pages;
        } else {
            $from = $curpage - $offset;
            $to = $from + $page - 1;
            if ($from < 1) {
                $to = $curpage + 1 - $from;
                $from = 1;
                if ($to - $from < $page) {
                    $to = $page;
                }
            } elseif ($to > $pages) {
                $from = $pages - $page + 1;
                $to = $pages;
            }
        }

        $multipage = ($curpage - $offset > 1 && $pages > $page ? '<a href="' . $mpurl . 'page=1" class="first"' . $ajaxtarget . '>1 ...</a>' : '') .
                ($curpage > 1 && !$simple ? '<a href="' . $mpurl . 'page=' . ($curpage - 1) . '" class="prev"' . $ajaxtarget . '>' . $lang['prev'] . '</a>' : '');
        for ($i = $from; $i <= $to; $i++) {
            $multipage .= $i == $curpage ? '<a class="current">' . $i . '</a>' :
                    '<a href="' . $mpurl . 'page=' . $i . ($ajaxtarget && $i == $pages && $autogoto ? '#' : '') . '"' . $ajaxtarget . '>' . $i . '</a>';
        }

        $multipage .= ( $to < $pages ? '<a href="' . $mpurl . 'page=' . $pages . '" class="last"' . $ajaxtarget . '>... ' . $realpages . '</a>' : '') .
                ($curpage < $pages && !$simple ? '<a href="' . $mpurl . 'page=' . ($curpage + 1) . '" class="next"' . $ajaxtarget . '>' . $lang['next'] . '</a>' : '') .
                ($showkbd && !$simple && $pages > $page && !$ajaxtarget ? '<kbd><input type="text" name="custompage" size="3" onkeydown="if(event.keyCode==13) {window.location=\'' . $mpurl . 'page=\'+this.value; return false;}" /></kbd>' : '');

        $multipage = $multipage ? '<div class="pagination">' . ($shownum && !$simple ? '<em>&nbsp;' . $num . '&nbsp;</em>' : '') . $multipage . '</div>' : '';
    }
    $maxpage = $realpages;
    return $multipage;
}

function cutstr($string, $length, $dot = ' ...') {
    global $charset;
    if (strlen($string) <= $length) {
        return $string;
    }
    $string = str_replace(array('&', '"', '<', '>'), array('&', '"', '<', '>'), $string);
    $strcut = '';
    if (strtolower($charset) == 'utf-8') {
        $n = $tn = $noc = 0;
        while ($n < strlen($string)) {
            $t = ord($string[$n]);
            if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                $tn = 1;
                $n++;
                $noc++;
            } elseif (194 <= $t && $t <= 223) {
                $tn = 2;
                $n += 2;
                $noc += 2;
            } elseif (224 <= $t && $t <= 239) {
                $tn = 3;
                $n += 3;
                $noc += 2;
            } elseif (240 <= $t && $t <= 247) {
                $tn = 4;
                $n += 4;
                $noc += 2;
            } elseif (248 <= $t && $t <= 251) {
                $tn = 5;
                $n += 5;
                $noc += 2;
            } elseif ($t == 252 || $t == 253) {
                $tn = 6;
                $n += 6;
                $noc += 2;
            } else {
                $n++;
            }
            if ($noc >= $length) {
                break;
            }
        }
        if ($noc > $length) {
            $n -= $tn;
        }
        $strcut = substr($string, 0, $n);
    } else {
        for ($i = 0; $i < $length; $i++) {
            $strcut .= ord($string[$i]) > 127 ? $string[$i] . $string[++$i] : $string[$i];
        }
    }
    $strcut = str_replace(array('&', '"', '<', '>'), array('&', '"', '<', '>'), $strcut);
    return $strcut . $dot;
}

?>