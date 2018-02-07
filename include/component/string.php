<?php

//api result
function generateMessage($errorMsg, $errorCode, $result = NULL) {
    $response = Array();

    $response["ErrorCode"] = $errorCode;
    $response["ErrorMessage"] = $errorMsg;
    $response["Result"] = $result;

    return $response;
}

//第一个是原串,第二个是 部份串
function startWith($haystack, $needle) {
    return strpos($haystack, $needle) === 0;
}

//第一个是原串,第二个是 部份串
 function endWith($haystack, $needle) {   
      $length = strlen($needle);  
      if($length == 0)
      {    
          return true;  
      }  
      return (substr($haystack, -$length) === $needle);
 }
 
 function toChineseNum($num){
    $char = array("零","一","二","三","四","五","六","七","八","九");
    $dw = array("","十","百","千","万","亿","兆");
    $retval = "";
    $proZero = false;
    for($i = 0;$i < strlen($num);$i++)
    {
        if($i > 0)    $temp = (int)(($num % pow (10,$i+1)) / pow (10,$i));
        else $temp = (int)($num % pow (10,1));
        
        if($proZero == true && $temp == 0) continue;
        
        if($temp == 0) $proZero = true;
        else $proZero = false;
        
        if($proZero)
        {
            if($retval == "") continue;
            $retval = $char[$temp].$retval;
        }
        else $retval = $char[$temp].$dw[$i].$retval;
    }
    if($retval == "一十") $retval = "十";
    return $retval;
 }

//substring function for utf8 charset
function utfSubstring($str, $len=400) {
    if (strlen($str) <= $len)
        return $str;
    for ($i = 0; $i < $len-3; $i++) {
        $temp_str = substr($str, 0, 1);
        if (ord($temp_str) > 127) {
            $i++;
            if ($i < $len) {
                $new_str[] = substr($str, 0, 3);
                $str = substr($str, 3);
            }
        } else {
            $new_str[] = substr($str, 0, 1);
            $str = substr($str, 1);
        }
    }
    return join($new_str) . "...";
}


//
function translateTime($timeString,$displayDateOnly = false) {
    if($timeString == "0000-00-00 00:00:00"){
        return "";
    }
    
    $now_time = date("Y-m-d H:i:s");
    $now_time = strtotime($now_time);
    $show_time = strtotime($timeString);
    $dur = $now_time - $show_time;
    
    if ($dur < -86400) {
        return -floor($dur / 86400) . '天后';
    } else if ($dur < -3600) {
        return -floor($dur / 3600) . '小时后';
    } else if ($dur <= -60) {
        return -floor($dur / 60) . '分钟后';
    } else if ($dur > -60 && $dur < 0) {
        return -$dur . '秒后';
    } else if ($dur <= 5 && $dur >= 0) {
        return '刚刚';
    } else if ($dur < 60 && $dur >5) {
        return $dur . '秒前';
    } else if ($dur >= 60 && $dur<3600) {
        return floor($dur / 60) . '分钟前';
    } else if ($dur >= 3600 && $dur<86400) {
        return floor($dur / 3600) . '小时前';
    } else if ($dur >= 86400 && $dur<86400*20) {
        return floor($dur / 86400) . '天前';
    } else {
        
        if($displayDateOnly){
            return explode(" ", $timeString)[0];
        }
        return $timeString;
    }
}




