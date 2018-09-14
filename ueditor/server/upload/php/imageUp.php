<?php
    include '../../../../aq.common.php';
    //上传配置
    $config = array(
        "uploadPath"=>"../uploadimages/",                          //保存路径
        "fileType"=>array(".gif",".png",".jpg",".jpeg",".bmp"),   //文件允许格式
        "fileSize"=>1000                                          //文件大小限制，单位KB
    );
    
    //文件上传状态,当成功时返回SUCCESS，其余值将直接返回对应字符窜并显示在图片预览框，同时可以在前端页面通过回调函数获取对应字符窜
    $state = "SUCCESS";$fileName="";

    $title = htmlspecialchars($_POST['pictitle'], ENT_QUOTES);
    $path  = $config['uploadPath'];
    if(!file_exists($path)){
        mkdir("$path", 0777);
    }
    //格式验证
    $current_type = strtolower(strrchr($_FILES["picdata"]["name"], '.'));
    if(!in_array($current_type, $config['fileType'])){
        $state = "不支持的图片类型！";
    }
    //大小验证
    $file_size = 1024 * $config['fileSize'];
    if( $_FILES["picdata"]["size"] > $file_size ){
        $state = "图片大小超出限制！";
    }
    //保存图片
    if($state == "SUCCESS"){
        $tmp_file=$_FILES["picdata"]["name"];
	$timedate	=	time();
	$a1	=	substr($timedate,0,3);
	$a2	=	substr($timedate,3,3);
	$a3	=	substr($timedate,6,2);
	$a4	=	substr($timedate,-2);
	$filepath	=	"attachments/".$a1.'/'.$a2.'/'.$a3;
	createFolder(AQ_ROOT.'/'.$filepath);
        $fileName = $filepath.'/'.$a4.strrchr($tmp_file,'.');
	if(@copy($_FILES["picdata"]["tmp_name"],AQ_ROOT.'/'.$fileName) || (function_exists('move_uploaded_file') && @move_uploaded_file($_FILES["picdata"]["tmp_name"],AQ_ROOT.'/'.$fileName))) {
	//		@unlink($attach['tmp_name']);
	//		$attach_saved = true;
	}else{
		$state	=	"联系qq:164059655";
	}
        //$result = move_uploaded_file($_FILES["picdata"]["tmp_name"],AQ_ROOT.'/'.$fileName);
    }
function createFolder($path) 
{ 
if (!file_exists($path)) 
{ 
createFolder(dirname($path)); 

mkdir($path, 0777); 
} 
} 
    
    //向浏览器返回数据json数据
    $file= $aq_site.'/'.$fileName;  //为方便理解，替换掉所有类似../和./等相对路径标识
    echo "{'url':'".$file."','title':'".$title."','state':'".$state."'}";

?>

