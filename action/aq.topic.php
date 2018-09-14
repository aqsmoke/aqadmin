<?php

$tablename = 'topic';

$tableTitle = '文章';


$aqConfig = array(
    'tid' => '1',
    'lr' => '5',
    'cid' => '5',
    'title' => '2',
    'shijian' => '2',
    'laiyuan' => '2',
    'lianjie' => '2',
    'content' => '3',
    'status' => '5'
);

$order = 'ORDER BY tid DESC';

$fieldTitle = array(
    'tid' => '帖子id',
    'lr' => '显示位置',
    'cid' => '栏目',
    'title' => '文章标题',
    'shijian' => '时间',
    'laiyuan' => '来源',
    'lianjie' => '来源地址',
    'content' => '内容',
    'status' => '是否显示'
);

$viewField = array('tid', 'lr', 'cid', 'title', 'shijian', 'laiyuan', 'status');

function cidAnathor($cid) {
    global $db;
    $sql = $db->fetch_first("SELECT name FROM cate WHERE cid = $cid");
    return $sql['name'];
}

function statusAnathor($status) {
    if ($status == 1) {
        return "不显示";
    } else {
        return "显示";
    }
}

function lrAnathor($lr) {
    if ($lr == 1) {
        return "左侧";
    } else {
        return "右侧";
    }
}

function titleAnathor($title) {
    return cutstr($title, 20);
}

$mainId = 'tid';


$perpage = 10;


$editField = array('cid', 'status', 'lr', 'shijian', 'title', 'laiyuan', 'lianjie', 'content');

function titleCheck($title) {
    if (strlen($title) < 1) {
        return "标题太短";
    } else {
        return 1;
    }
}

function cidOption($curValue = '') {
    global $db;
    $sql = $db->query("SELECT cid,name FROM cate WHERE islist = 1 ORDER BY dorder");
    while ($res = $db->fetch_array($sql)) {
        echo "<option value='" . $res['cid'] . "' " . ($curValue == $res['cid'] ? 'selected=true' : '') . ">" . $res['name'] . "</option>";
    }
}

function lrOption($curValue = '') {
    echo "<option value='1' " . ($curValue == 1 ? 'selected=true' : '') . ">左侧</option>";
    echo "<option value='2' " . ($curValue == 2 ? 'selected=true' : '') . ">右侧</option>";
}

function statusOption($curValur = '') {
    echo "<option value='1' " . ($curValue == 1 ? 'selected=true' : '') . ">不显示</option>";
    echo "<option value='0' " . ($curValue == 0 ? 'selected=true' : '') . ">显示</option>";
}

?>
