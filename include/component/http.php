<?php

function httpRequest($url,$postValues=null){
    global $siteURLHead;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    
    if($postValues){
        foreach($postValues as $key=>$value) {
            $fieldsString .= $key.'='.$value.'&';
        }
        rtrim($fieldsString, '&');
        curl_setopt($ch, CURLOPT_POST, count($postValues));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldsString);
    }
    
    curl_setopt($ch, CURLOPT_REFERER,$siteURLHead);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    
    curl_close($ch);
    return $output;
}


