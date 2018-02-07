<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

// Extend cookie life time by an amount of your liking
//$cookieLifetime = 365 * 24 * 60 * 60; // A year in seconds
//setcookie(session_name(),session_id(),time()+$cookieLifetime);


//load main conguration file
$prepath = dirname(__FILE__)."/";
if(!file_exists($prepath."configuration.php")){
    die("Error: Missing <b>configuration.php</b>, please copy <b>configuration_tmp.php</b> to <b>configuration.php</b>");
}

//载入全局 配置文件
include_once($prepath."configuration.php");




//项目配置文件
$_INC_FILE = "../../../_configuration.php";
for($lInc=0;$lInc<4;$lInc++){
    if(file_exists($_INC_FILE)){
        include_once $_INC_FILE;
    }
    $_INC_FILE = substr($_INC_FILE, 3);
}



include_once($prepath."component/string.php");
include_once($prepath."component/form.php");
include_once($prepath."component/database.php");
include_once($prepath."component/language.php");
include_once($prepath."component/file.php");
include_once($prepath."component/template.php");
include_once($prepath."component/upload.php");
include_once($prepath."messagequene/startmq.php");
include_once($prepath."component/email.php");
include_once($prepath."component/http.php");
include_once($prepath."component/sms.php");
include_once($prepath."component/errorhandle.php");
//include_once($prepath."component/coreuser.php");
include_once($prepath."component/redis.php");
include_once($prepath."component/globalvars.php");
include_once($prepath."component/wechat.php");
//include_once($prepath."component/auth.php");


//载入全局函数文件
$_INC_FILE = "../../../_globalfunction.php";
for($lInc=0;$lInc<4;$lInc++){
    if(file_exists($_INC_FILE)){
        include_once $_INC_FILE;
    }
    $_INC_FILE = substr($_INC_FILE, 3);
}

//载入预加载
$_INC_FILE = "../../../_preload.php";
for($lInc=0;$lInc<4;$lInc++){
    if(file_exists($_INC_FILE)){
        include_once $_INC_FILE;
    }
    $_INC_FILE = substr($_INC_FILE, 3);
}


/*
 * Framework Teleportation
 * Calidan@AngellEcho [2010-2016]
 */