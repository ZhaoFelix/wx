<?php

if (!isset($WECHAT_APP_ID) || !isset($WECHAT_APP_SECRET)) {
    return;
}

function getSignPackage() {
    global $WECHAT_APP_ID;
    $jsapiTicket = getJsApiTicket();
    // 注意 URL 一定要动态获取，不能 hardcode.
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    $timestamp = time();
    $nonceStr = _createNonceStr();

    // 这里参数的顺序要按照 key 值 ASCII 码升序排序
    $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

    $signature = sha1($string);

    $signPackage = array(
        "appId" => $WECHAT_APP_ID,
        "nonceStr" => $nonceStr,
        "timestamp" => $timestamp,
        "url" => $url,
        "signature" => $signature,
        "rawString" => $string
    );
    return $signPackage;
}

function wechatJsShare($title, $link, $imgUrl, $desc, $successFunction = "", $type = 'link', $dataUrl = '') {
    $signPackage = getSignPackage();

    $link = addslashes($link);
    $title = addslashes($title);
    $title = str_replace("\n", "\\n", $title);
    $desc = addslashes($desc);
    $desc = str_replace("\n", "\\n", $desc);

    echo '<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>'
    . '<script>'
    . 'wx.config({'
    . 'debug: false,'
    . 'appId: "' . $signPackage['appId'] . '",'
    . 'timestamp: "' . $signPackage["timestamp"] . '",'
    . 'nonceStr: "' . $signPackage["nonceStr"] . '",'
    . 'signature: "' . $signPackage["signature"] . '",'
    . 'jsApiList: ['
    . '"onMenuShareTimeline",'
    . '"onMenuShareAppMessage"'
    . ']'
    . '});'
    . 'wx.ready(function () {'
    . 'wx.onMenuShareTimeline({'
    . 'title: "' . $title . '",'
    . 'link: "' . $link . '",'
    . 'imgUrl: "' . $imgUrl . '",'
    . 'success: function () {'
    . $successFunction . '},'
    . 'cancel: function () {}'
    . '});'
    . 'wx.onMenuShareAppMessage({'
    . 'title: "' . $title . '",'
    . 'desc: "' . $desc . '",'
    . 'link: "' . $link . '",'
    . 'imgUrl: "' . $imgUrl . '",'
    . 'type: "' . $type . '",'
    . 'dataUrl: "' . $dataUrl . '",'
    . 'success: function () {'
    . $successFunction . '},'
    . 'cancel: function () {}'
    . '});'
    . '});'
    . '</script>';      
}

function _createNonceStr($length = 16) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
        $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
}

function getJsApiTicket() {
    global $DB_NAME,$IS_TESTDB;
    $sufix = "";
    if($IS_TESTDB){
        $sufix = "Test";
    }
    $data = getRowData("select * from MagikidLMS$sufix.WechatData where WechatDataId = '2'");
    if (strtotime($data["ExpireTime"]) < time()) {
        $accessToken = getAccessToken();
        // 如果是企业号用以下 URL 获取 ticket
        // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
        $res = json_decode(_httpGet($url), true);
        $ticket = $res['ticket'];
        if ($ticket) {
            connectDB($DB_NAME, true);
            $expire_time = date("Y-m-d H:i:s", strtotime("+7000 second"));
            query("update MagikidLMS$sufix.WechatData set Data = '$ticket', ExpireTime = '$expire_time' where WechatDataId = '2'");
        }
    } else {
        $ticket = $data['Data'];
    }

    return $ticket;
}

function getAccessToken() {
    global $WECHAT_APP_ID, $WECHAT_APP_SECRET, $DB_NAME,$IS_TESTDB;
    $sufix = "";
    if($IS_TESTDB){
        $sufix = "Test";
    }
    $data = getRowData("select * from MagikidLMS$sufix.WechatData where WechatDataId = '1'");
    $access_token = "";
    if (strtotime($data["ExpireTime"]) < time()) {
        // 如果是企业号用以下URL获取access_token
        // $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret";
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$WECHAT_APP_ID&secret=$WECHAT_APP_SECRET";
        $res = json_decode(_httpGet($url), true);
        $access_token = $res['access_token'];
        if ($access_token) {
            $expire_time = date("Y-m-d H:i:s", strtotime("+7000 second"));
            connectDB($DB_NAME, true);
            query("update MagikidLMS$sufix.WechatData set Data = '$access_token', ExpireTime = '$expire_time' where WechatDataId = '1'");
        }
    } else {
        $access_token = $data["Data"];
    }

    return $access_token;
}

function _httpGet($url) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 500);
    // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
    // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($curl, CURLOPT_URL, $url);

    $res = curl_exec($curl);
    curl_close($curl);

    return $res;
}
