<?php

function sendSMS($phoneNumber, $message){
    startMQ();
    $info = ["To"=>$phoneNumber,"Message"=>$message];
    redisLPush("SEND_SMS", json_encode($info));
}

//发送短信
function _sendSMS($phoneNumber, $message, &$returnMessage = null) {
    global $SMS_USRERNAME, $SMS_PASSWORD;

    if(!function_exists("HttpPost")){
        function HttpPost($curlPost, $url) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_NOBODY, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
            $return_str = curl_exec($curl);
            curl_close($curl);
            return $return_str;
        }
    }
    
    if(!function_exists("xml_to_array")){
        function xml_to_array($xml) {
            $reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
            if (preg_match_all($reg, $xml, $matches)) {
                $count = count($matches[0]);
                for ($i = 0; $i < $count; $i++) {
                    $subxml = $matches[2][$i];
                    $key = $matches[1][$i];
                    if (preg_match($reg, $subxml)) {
                        $arr[$key] = xml_to_array($subxml);
                    } else {
                        $arr[$key] = $subxml;
                    }
                }
            }
            return $arr;
        }
    }

    $target = "http://106.ihuyi.cn/webservice/sms.php?method=Submit";

    $post_data = "account=$SMS_USRERNAME&password=$SMS_PASSWORD&mobile=$phoneNumber&content=" . rawurlencode($message);

    //密码可以使用明文密码或使用32位MD5加密
    $gets = xml_to_array(HttpPost($post_data, $target));
    $returnMessage = $gets['SubmitResult']['msg'];

    if ($gets['SubmitResult']['code'] == 2) {
        return true;
    }
    return false;
}