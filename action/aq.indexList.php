<?php

/* * **********************************************************************************************\
 * powered by aqsmoke
 * 2012.1.6
 * 评论管理
 * ********************************************************************************************** */

/* * ****\
  1 	代表主id
  2  	普通显示,编辑时候是text
  3	普通显示， 但是编辑时候是textarea
  4	代表是时间,字段里的存储方式是时间戳存储
  5	代表是select 选择框   option的输出 是用定义的函数得到  如果字段名是 aaa  那么输出option的函数就是 aaaOption()
  6	代表密码 如果设置了密码就要设置密码的加密函数 如果字段是 password 那么函数名字要定位 passwordCode(); 请看文件aq.member.php
 * **** */

//表的名字
$tablename = 'indexList';

//配置列表标题   如:评论 
//此时在列表显示为 “评论列表”  在编辑时候显示 “评论编辑” 在添加时候显示 “添加评论”
$tableTitle = '首页热门';

//字段的配置
$aqConfig = array('id' => 1, 'titleUrl' => 2, 'title' => 2, 'content' => 7, 'file' => 8);

//排序规则
$order = 'ORDER BY id DESC';

/* * *****\
 * 配置每个字段所代表的内容
 * 必须设置的
 * ***** */
$fieldTitle = array('id' => '主id', 'titleUrl' => 'url地址', 'title' => '标题', 'content' => '简介', 'file' => '附件');

//参与搜索的字段 可以不设置
//$searchField	=	array('author','status');
//function author_S(){}	//如果 author 的搜索是使用 like 搜索 就设置一个函数 为 author_S(){}; 函数里面什么都不用写
//列表中需要显示的字段  需要显示的字段里面必须包含主id
$viewField = array('id', 'titleUrl', 'title' ,'file');

//配置主id字段  如果没有主id字段   那么操作功能(删除，编辑)将不存在
$mainId = 'id';

//每页显示的条数
$perpage = 10;

//需要编辑的字段   添加时候也会用到这些
$editField = array('titleUrl', 'title', 'file', 'content');

function fileAnathor($file = ''){
    if($file){
        return '<a href="javascript:" onclick="viewPic(\''.$file.'\')">点击查看</a>';
    }else{
        return "无";
    }
}

function fileCode($files = '', $v) {
    global $aq_site;
    if(!$files){
        return "";
    }
    $config = array(
        "fileType" => array(".gif", ".png", ".jpg", ".jpeg", ".bmp"), //文件允许格式
        "fileSize" => 1000                                          //文件大小限制，单位KB
    );

    $current_type = strtolower(strrchr($files[$v]["name"], '.'));
    if (!in_array($current_type, $config['fileType'])) {
        $state = "不支持的图片类型！";
    }

    $tmp_file = $files[$v]["name"];
    $timedate = time();
    $a1 = substr($timedate, 0, 3);
    $a2 = substr($timedate, 3, 3);
    $a3 = substr($timedate, 6, 2);
    $a4 = substr($timedate, -2);
    $filepath = "attachments/" . $a1 . '/' . $a2 . '/' . $a3;
    createFolder(AQ_ROOT . '/' . $filepath);
    $fileName = $filepath . '/' . $a4 . strrchr($tmp_file, '.');
    if (@copy($files[$v]["tmp_name"], AQ_ROOT . '/' . $fileName) || (function_exists('move_uploaded_file') && @move_uploaded_file($files[$v]["tmp_name"], AQ_ROOT . '/' . $fileName))) {
        return $aq_site."/".$fileName;
    }
}

function createFolder($path) {
    if (!file_exists($path)) {
        createFolder(dirname($path));

        mkdir($path, 0777);
    }
}

?>
