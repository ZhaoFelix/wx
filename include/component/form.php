<?php

//get data from a form by POST method
function post($label, $arg=0) {
    if (!isset($_POST[$label]))
        return null;
    if ($arg == 1) {
        return md5($_POST[$label]);
    }if ($arg == 2) {
        return addslashes($_POST[$label]);
    }

    return addslashes(convertTag($_POST[$label]));
}


//change tag
function convertTag($val){
    $val = str_replace("<", "&lt;", $val);
    $val = str_replace(">", "&gt;", $val);
    return $val;
}


//get data from a form by POST method
function postInt($label) {
    if (!isset($_POST[$label])){
        die();
    }
    return ensureNumber($_POST[$label]);
}

//request data
function request($key){
    if (!isset($_REQUEST[$key])) {
        return null;
    }else{
        return htmlspecialchars($_REQUEST[$key]);
    }
}

//get data from a form by GET method
function get($label,$default = null) {
    if (!isset($_GET[$label])) {
        return $default;
    }else{
        return htmlspecialchars($_GET[$label]);
    }
}

//get an Id 
function getInt($label,$default = null){
    if (!isset($_GET[$label])) {
        return $default;
    }else{
        return ensureNumber(htmlspecialchars($_GET[$label]));
    }
}

//make sure it's a number
function ensureNumber($num) {
    if(is_numeric($num)){
        return $num;
    }
    die();
}

function cacheGetInt($key,$default = null){
    if (!isset($_GET[$key])) {
        $sessionValue = session("CacheGet_".__FILE__."_$key");
        if($sessionValue != null){
            return $sessionValue;
        }
        return $default;
    }else{
        $getValue = htmlspecialchars($_GET[$key]);
        $_SESSION["CacheGet_".__FILE__."_$key"] = $getValue;
        return ensureNumber($getValue);
    }
}

//get value from URL and cache it
function cacheGet($key,$default = null){
    if (!isset($_GET[$key])) {
        $sessionValue = session("CacheGet_".__FILE__."_$key");
        if($sessionValue != null){
            return $sessionValue;
        }
        return $default;
    }else{
        $getValue = htmlspecialchars($_GET[$key]);
        $_SESSION["CacheGet_".__FILE__."_$key"] = $getValue;
        return $getValue;
    }
}

//remove cache get
function removeCacheGet($key){
    unset($_SESSION["CacheGet_".__FILE__."_$key"]);
}

//get session data
function session($label) {
    if (isset($_SESSION[$label])){
        return $_SESSION[$label];
    }else{
        return null;
    }
}



function isSignValidate() {
    if(!isset($_POST["Signature"])){
        return false;
    }
    
    global  $formEncodeKey;

    $data = $_POST;
    ksort($data);

    $encodeString = "";
    foreach ($data as $key => $value) {
        if ($key == 'Signature') {
            continue;
        }
        $encodeString = $encodeString . $key . $value;
    }
    
    $checkcode = md5($encodeString . $formEncodeKey);
    return ($checkcode == $_POST["Signature"]);
}


function callAction($requireLogin = true){
    if($requireLogin && !session("IsLogin")){
        $msg = "";
        if(ini_get("display_errors")){
            $msg = "Error: Require Login";
        }
        die($msg);
    }
    
    $action = post("Action");
    $action2 = strtolower(substr($action, 0, 1)).substr($action, 1);
    
    if(function_exists($action2)){
        $action2();
    }else if(function_exists($action)){
        $action();
    }
    
    
}