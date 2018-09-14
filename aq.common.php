<?php
/************************************************************************************************\
	*powered by aqsmoke
	*2012.1.5
\************************************************************************************************/

error_reporting(0);

define('IN_AQADMIN',true);
define('AQ_ROOT', dirname(__FILE__));
date_default_timezone_set('PRC');  //����ʱ������ʱ��

// ���������ļ�
include_once(AQ_ROOT.'/aq.config.php');

//���ش�������
require_once(AQ_ROOT.'/class/class.AqError.php');


$username	=	'';
$G	=	$_GET;
$P	=	$_POST;
$C 	=	$_COOKIE;
unset($_GET);
unset($_POST);
unset($_COOKIE);

//��ݿ�����
include_once(AQ_ROOT.'/class/class.AqDb.php');


$loginDb    =   new AqDb();
$loginDb->setHost($hostArr['member']);
//����ͨ�ú���
require_once(AQ_ROOT.'/include/aq.func.php');

//



$style	=	'';
$style	=	$C[AQ_COOKIEPRE.'style'] ? $C[AQ_COOKIEPRE.'style'] : 'default';


?>
