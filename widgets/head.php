<!DOCTYPE html>
<html>
<head lang="zh-CN">
    <meta charset="UTF-8">
    <link rel="stylesheet" href="./bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="./bootstrap/bootstrap-theme.min.css">

    <script src="./js/jquery.min.js"></script>
    <script src="./bootstrap/bootstrap.min.js"></script>
    <style>
        .input-group{margin: 10px auto;}
    </style>
    <title><?php
        switch($pageID){
            case 'home':echo '首页';break;
            case 'distribute':echo '任务分配';break;
            case 'remarkManager':echo '评论管理';break;
            case 'report':echo '结果报告';break;
            default:echo '首页';break;
        }
        ?></title>
</head>
<body>
<?php
error_reporting(0);
session_start();

if(($_COOKIE['SSID']!==session_id())||!($_SESSION['stat']=='login')){
    /*未登录展示登录框*/
    require(dirname(__FILE__).'/Signinboard.php');
    die("</body></html>");
}else{
    setcookie('SSID', session_id(),time()+86400);
    include(dirname(__FILE__).'/nav.php');
}
?>