<?php

function getInfo(){
    //scope=snsapi_base 实例
    $appid = 'wx6d9718791b8611c4';
    $redirect_uri = urlencode('http://hotpot.bedeveloper.cn/wx/getUserInfo.php');
    $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appid&redirect_uri=$redirect_uri&response_type=code&scope=snsapi_base&state=1#wechat_redirect";
    header("Location:".$url);
}


?>