<?php
function  getUserInfo(){
    $appid = "wx6d9718791b8611c4";
    $secret = "bffeab4c021548b8f8bb56356ba87fad";
    $code = $_GET["code"];
//第一步:取全局access_token
    $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$secret";
    $token = getJson($url);
//第二步:取得openid
    $oauth2Url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$secret&code=$code&grant_type=authorization_code";
    $oauth2 = getJson($oauth2Url);

//第三步:根据全局access_token和openid查询用户信息
    $access_token = $token["access_token"];
    $openid = $oauth2['openid'];
    $get_user_info_url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$access_token&openid=$openid&lang=zh_CN";
    return getJson($get_user_info_url);
}
function getJson($url){
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$output = curl_exec($ch);
curl_close($ch);
return json_decode($output, true);
}

