<?php
/************************************************************************************************\
	*powered by aqsmoke
	*2012.1.6
	*评论管理
\************************************************************************************************/


/******\
	1 	代表主id
	2  	普通显示,编辑时候是text
	3	普通显示， 但是编辑时候是textarea
	4	代表是时间,字段里的存储方式是时间戳存储
	5	代表是select 选择框   option的输出 是用定义的函数得到  如果字段名是 aaa  那么输出option的函数就是 aaaOption()
	6	代表密码 如果设置了密码就要设置密码的加密函数 如果字段是 password 那么函数名字要定位 passwordCode(); 请看文件aq.member.php
\******/

//表的名字
$tablename	=	'emailList';

//配置列表标题   如:评论 
//此时在列表显示为 “评论列表”  在编辑时候显示 “评论编辑” 在添加时候显示 “添加评论”
$tableTitle	=	'邮箱';      

//字段的配置
$aqConfig	=	array(
	'id'		=>		'1', 		
	'email'	=>		    '2',
	'dateline'	=>		'2'
);
/*******\
 	 *	配置每个字段所代表的内容
 	 *	必须设置的
\*******/
$fieldTitle	=	array(
	'id'		=>		'主id', 		
	'email'	=>		    '邮箱',
	'dateline'	=>		'时间'
);

//参与搜索的字段 可以不设置
$searchField	=	array('email');


//列表中需要显示的字段  需要显示的字段里面必须包含主id
$viewField	=	array('id','email' , 'dateline');

//配置主id字段  如果没有主id字段   那么操作功能(删除，编辑)将不存在
$mainId		=	'id';

//每页显示的条数
$perpage	=	10;

//需要编辑的字段   添加时候也会用到这些
$editField	=	array('emial' , 'dateline');

//如果要对某个字段进行是否输入合适判断，则建立相关函数 规则是：如： 对author进行判断，则创建函数authorCheck($author)
//只要创建好， 就可以，会自行调用函数 | 当然完全可以不设置函数。。

function datelineAnathor($cur = ''){
	return date('Y-m-d H:i:s' , $cur);
}

?>
