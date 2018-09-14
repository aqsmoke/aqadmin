<?php
/************************************************************************************************\
	*powered by aqsmoke
	*2012.1.6
	*账号管理
\************************************************************************************************/


/************************************************************************************************\
	1 	代表主id
	2  	普通显示
	3	普通显示， 但是编辑时候是textarea
	4	代表是时间	字段里的存储方式， 是时间戳存储
	5	代表是select 选择框   option的输出 是用定义的函数得到  如果字段名是 aaa  那么输出option的函数就是 aaaOption()
	6	代表密码 如果设置了密码就要设置密码的加密函数 如果字段是 password 那么函数名字要定位 passwordCode();
\************************************************************************************************/
//评论表的配置
/*************** 表的名字 *********************/
$tablename	=	'adminuser';

/*****************  字段的配置  *****************************/
$aqConfig	=	array(
	'adminid'	=>		'1', 		
	'adminname'	=>		'2',
	'password'	=>		'6',
        'purview'       =>              '9'
);

/********************* 列表中需要显示的字段  需要显示的字段里面必须包含主id***************************/
$viewField	=	array('adminid','adminname','password','purview');

/***************** 配置主id字段  如果没有主id字段   那么操作功能将不存在*********************************/
$mainId	=	'adminid';

/**************  每页显示的条数 *********************/
$perpage	=	5;

/**************** 需要编辑的字段 *********************/
$editField	=	array('adminname','password','purview');

/************** 配置每个字段所代表的内容 ******************/
$fieldTitle	=	array(
	'adminid'	=>		'主id', 		
	'adminname'	=>		'账号',
	'password'	=>		'密码',
        'purview'       =>              '权限'
);

function passwordCheck($pas){
	if(strlen($pas) < 6){
		return "密码太短，请输入6位以上密码";
	}else{
		return '1';
	}
}

/******************* 配置列表标题   如  评论 ***********************************/
$tableTitle	=	'账号';      //此时在列表显示为 “评论列表”  在编辑时候显示 “评论编辑” 在添加时候显示 “添加评论”

/*******************  字段 password 的加密函数 ************************/
function passwordCode($password){
	return md5(md5($password));
}

function purviewCheckbox($cur = ''){
    global $navArr;
    $numPur = explode(',', $cur);
    foreach($navArr as $k => $v){
        echo "<input type='checkbox' ".(in_array($v, $numPur) ? "checked='checked'" : "")." name='purview[]' value='".$v."'>  ".$k."<br />";
    }
}

function purviewCode($cur = ''){
    return implode(',',$cur);
}


?>
