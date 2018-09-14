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
$tablename	=	'single';

//配置列表标题   如:评论 
//此时在列表显示为 “评论列表”  在编辑时候显示 “评论编辑” 在添加时候显示 “添加评论”
$tableTitle	=	'单页';      

//字段的配置
$aqConfig	=	array(
	'sid'		=>		'1', 		
	'cid'		=>		'5',
	'title'		=>		'2',
	'content'	=>		'3'
);


//排序规则
$order		=	'ORDER BY sid DESC';

/*******\
 	 *	配置每个字段所代表的内容
 	 *	必须设置的
\*******/
$fieldTitle	=	array(
	'sid'		=>		'单页id', 		
	'cid'		=>		'栏目',
	'title'		=>		'标题',
	'content'	=>		'内容'
);

//参与搜索的字段 可以不设置
//$searchField	=	array('author','status');
//function author_S(){}	//如果 author 的搜索是使用 like 搜索 就设置一个函数 为 author_S(){}; 函数里面什么都不用写

//列表中需要显示的字段  需要显示的字段里面必须包含主id
$viewField	=	array('sid','cid','title');
function cidAnathor($cid){
	global $db;
	$sql	=	$db->fetch_first("SELECT name FROM cate WHERE cid = $cid");
	return $sql['name'];
}

//配置主id字段  如果没有主id字段   那么操作功能(删除，编辑)将不存在
$mainId		=	'sid';

//每页显示的条数
$perpage	=	10;

//需要编辑的字段   添加时候也会用到这些
$editField	=	array('cid','title','content');

//如果要对某个字段进行是否输入合适判断，则建立相关函数 规则是：如： 对author进行判断，则创建函数authorCheck($author)
//只要创建好， 就可以，会自行调用函数 | 当然完全可以不设置函数。。

function titleCheck($title){
	if(strlen($title) < 1){
		return "标题太短";
	}else{
		return 1;
	}
}

function cidCheck($cid){
	global $db,$G;
	if($G['do'] == 'edit'){
		return 1;
	}
	$sql	=	$db->fetch_first("SELECT sid FROM single WHERE cid = $cid");
	if($sql){
		return "此单页有内容";
	}else{
		return 1;
	}
}

/*function authorCheck($author){
	if(strlen($author) < 3 ){    
		return "作者名太短";   // 错误就return  错误的提示文字
	}elseif(strlen($author) >= 10){
		return "作者名太长";
	}else{
		return '1';      //如果正确就返回 1
	}
}*/

/*function emailCheck($email){
	if(strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email)){
		return '1';
	}else{
		return "邮箱输入错误";
	}
}*/

//字段 status 的option输出函数
//里面的参数必须如下格式去写， 因为编辑时候需要传默认的值，添加时候就不需要传值
function cidOption($curValue = ''){
	global $db;
	$sql	=	$db->query("SELECT cid,name FROM cate WHERE islist = 0 ORDER BY dorder");
	while($res	=	$db->fetch_array($sql)){
		echo "<option value='".$res['cid']."' ".($curValue==$res['cid'] ? 'selected=true' : '').">".$res['name']."</option>";
	}
}

?>
