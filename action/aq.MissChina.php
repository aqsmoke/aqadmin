<?php

/* * **********************************************************************************************\
 * powered by aqsmoke
 * 2012.1.6
 * 评论管理
  \*********************************************************************************************** */


/* * ****\
  1 	代表主id
  2  	普通显示,编辑时候是text
  3	普通显示， 但是编辑时候是textarea , 不过使用的是百度编辑器
  4	代表是时间,字段里的存储方式是时间戳存储
  5	代表是select 选择框   option的输出 是用定义的函数得到  如果字段名是 aaa  那么输出option的函数就是 aaaOption()
  6	代表密码 如果设置了密码就要设置密码的加密函数 如果字段是 password 那么函数名字要定位 passwordCode(); 请看文件aq.member.php
  7         代表的是textarea
  8         代表的是file
  \***** */


//表的名字
$tablename = 'contestant';

//配置列表标题   如:评论
//此时在列表显示为 “评论列表”  在编辑时候显示 “评论编辑” 在添加时候显示 “添加评论”
$tableTitle = '选手';

//字段的配置
$aqConfig = array(
    'id' => '1',
    'pic' => '8',
    'name' => '2',
    'number' => '2',
    'url' => '2',
    'orderby' => '2'
);
/* * *****\
 * 	配置每个字段所代表的内容
 * 	必须设置的
  \****** */
$fieldTitle = array(
    'id' => '主id',
    'pic' => '照片',
    'name' => '姓名',
    'number' => '编号',
    'url' => '连接',
    'orderby' => '排序'
);

//参与搜索的字段 可以不设置
$searchField = array('number');

//如果 author 的搜索是使用 like 搜索 就设置一个函数 为 author_S(){}; 函数里面什么都不用写
//列表中需要显示的字段  需要显示的字段里面必须包含主id
$viewField = array('id', 'pic', 'name', 'number','orderby');

//配置主id字段  如果没有主id字段   那么操作功能(删除，编辑)将不存在
$mainId = 'id';
$picField = 'pic';

//每页显示的条数
$perpage = 20;

//需要编辑的字段   添加时候也会用到这些
$editField = array('pic', 'name', 'number',  'url' , 'orderby');

//如果要对某个字段进行是否输入合适判断，则建立相关函数 规则是：如： 对author进行判断，则创建函数authorCheck($author)
//只要创建好， 就可以，会自行调用函数 | 当然完全可以不设置函数。。

function nameCheck($author) {
    if (empty($author)) {
        return "请输入选手姓名";   // 错误就return  错误的提示文字
    } else {
        return '1';      //如果正确就返回 1
    }
}

function numberCheck($number) {
    if (empty($number)) {
        return "请输入选手编号";   // 错误就return  错误的提示文字
    } else {
        return '1';      //如果正确就返回 1
    }
}

//添加之前执行的
function addBefore() {
    
}

//添加之后执行的
function addAfter() {
    
}

//编辑之前执行的
function editBefore() {
    
}

//编辑之后执行的
function editAfter() {
    
}

function picCode($files = '', $v) {
    global $aq_site;

    include AQ_ROOT . "/include/aq.ftp.php";

    if (!$files) {
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
    $ftpName = "/data/httpd/htdocs/bbsfile/misschina/" . $a1 . '/' . $a2 . '/' . $a3 . '/' . $a4 . strrchr($tmp_file, '.');
    if (@copy($files[$v]["tmp_name"], AQ_ROOT . '/' . $fileName) || (function_exists('move_uploaded_file') && @move_uploaded_file($files[$v]["tmp_name"], AQ_ROOT . '/' . $fileName))) {
        $ftp = new Ftp();
        $ftp->put($ftpName, AQ_ROOT . '/' . $fileName);
        unlink(AQ_ROOT . '/' . $fileName);
        return "http://bbsfile.ifeng.com/bbsfile/misschina/" . $a1 . '/' . $a2 . '/' . $a3 . '/' . $a4 . strrchr($tmp_file, '.');
    }
}

function createFolder($path) {
    if (!file_exists($path)) {
        createFolder(dirname($path));

        mkdir($path, 0777);
    }
}

?>
