<?php

function startMQ(){
    $sufix = "";
    if(ini_get("display_errors")){
        $sufix = "test";
    }
    
    $isRunning = false;
    $out = "";
    exec("ps -ef|grep mqsendnotice$sufix.php",$out);
    foreach($out as $o){
        if(is_array($o)){
            continue;
        }
        if(strpos($o, "php mqsendnotice$sufix.php")){
            $isRunning = true;
            break;
        }
    }
    if(!$isRunning){
        exec("nohup php mqsendnotice$sufix.php &");
    }
}