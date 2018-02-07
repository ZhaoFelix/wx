<?php

include_once '../configuration.php';
include_once '../component/redis.php';
include_once '../component/email.php';
include_once '../component/sms.php';

function _MQSendNotice(){

    while(true){
        $info = json_decode(redisRPop("SEND_MAIL"),true);
        if($info){
            $error = _sendMail($info["From"], $info["To"], $info["Subject"], $info["Body"], $info["CC"]);
            if($error){
                echo $error."\n";
            }
        }
        
        $info = json_decode(redisRPop("SEND_SMS"),true);
        if($info){
            _sendSMS($info["To"], $info["Message"],$error);
            if($error){
                echo $error."\n";
            }
        }
        
        disconnectRedis();
        sleep(1);
    }
}



$runType = php_sapi_name();
if($runType == "cli"){
    echo "Running Mail/SMS Message Quene";
    _MQSendNotice();
}
    
//nohup php mqsendnotice.php &
//nohup php mqsendnoticetest.php &
//ps -ef|grep mqsendnotice.php