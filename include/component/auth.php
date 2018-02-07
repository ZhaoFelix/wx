<?php


function updateAuth(){
    global $uid,$isLogin,$_UserAuth;
    if($isLogin){
        if(session("RequireAuth")==null){
           if(getSingleData("SHOW TABLES LIKE 'Auth'")){
               $_SESSION["RequireAuth"] = "Yes";
           }else{
               $_SESSION["RequireAuth"] = "No";
           }
        }

        if($_SESSION["RequireAuth"] == "Yes"){
           $authInfo = getSingleData("select Auth From Auth where Uid=$uid");
           if($authInfo === null){
              query("insert into Auth(Uid,Auth) values($uid,'{}')");
           }else if($authInfo === ""){
              query("update Auth set Auth = '{}' where Uid = $uid");
           }else{
               $_UserAuth = json_decode($authInfo,true);
              redisSet("AUTH_$uid", $authInfo);
              redisExpire("AUTH_$uid", 3600);
           }
        }
     }

}

//初始化配置
if($isLogin && redisGet("AUTH_$uid") == null){
    updateAuth();
}


//删除全部
function authClear($userId=null){
    global $isLogin,$uid;
    
    if(!$isLogin){
        return;
    }
    
    if($userId == null){
        $userId = $uid;
    }
    
    redisDel("AUTH_$userId");
    query("update Auth set Auth = '{}' where Uid = $userId");
}

//取出所有权限
function authGetAll($userId=null){
    global $isLogin,$uid;
    if(!$isLogin){
        return [];
    }
    if($userId == null){
        $userId = $uid;
    }
    
    $authInfo = redisGet("AUTH_$userId");
    
    if(class_exists("Redis") && getRedis()){
        $userAuth = json_decode($authInfo,true);
    }else{
        $userAuth = json_decode(getSingleData("select Auth From Auth where Uid=$userId"),true);
    }
    
    if(!$userAuth){
        $userAuth = [];
    }
    
    return $userAuth;
}

function authSetAll($arr,$userId=null){
    global $isLogin,$AUTH_LIST,$uid;

    if(!$isLogin){
        return;
    }
    
    foreach($arr as $a){
        if(!in_array($a, $AUTH_LIST)){
            die("$a not in \$AUTH_LIST, please add first");
        }
    }
    
    if($userId == null){
        $userId = $uid;
    }
    
    $authInfo = addslashes(json_encode($arr));
    redisSet("AUTH_$userId", json_encode($arr));

    query("update Auth set Auth = '$authInfo' where Uid = $userId");
    
}

//给用户设置权限
function authSet($key,$value,$userId=null){
    global $isLogin,$AUTH_LIST;

    if(!$isLogin){
        return;
    }
    
    if(!isset($AUTH_LIST)){
        return;
    }else if(!in_array($key, $AUTH_LIST)){
        die("$key not in \$AUTH_LIST, please add first");
    }
    
    global $uid;
    if($userId == null){
        $userId = $uid;
    }
    
    $userAuth = json_decode(redisGet("AUTH_$userId"),true);
    $userAuth[$key] = $value;
    $authInfo = json_encode($userAuth);
    redisSet("AUTH_$userId", $authInfo);
    
    $authInfo = addslashes($authInfo);
    query("update Auth set Auth = '$authInfo' where Uid = $userId");
}

//取用户权限
function authGet($key,$userId=null){
    global $isLogin,$AUTH_LIST,$uid,$_UserAuth;
    if(!$isLogin){
        return null;
    }
    
    if(!isset($AUTH_LIST)){
        return null;
    }else if(!in_array($key, $AUTH_LIST)){
        die("$key not in \$AUTH_LIST, please add first");
    }

    
    if($userId == null){
        $userId = $uid;
    }
    
    //同一个页面不需要重复取权限
    if(!$_UserAuth){
        if(class_exists("Redis") && getRedis()){
            $authInfo = redisGet("AUTH_$userId");
            $userAuth = json_decode($authInfo,true);
        }else{
            $userAuth = json_decode(getSingleData("select Auth From Auth where Uid=$userId"),true);
        }
        $_UserAuth = $userAuth;
    }else{
        $userAuth = $_UserAuth;
    }
    
    
    if($userAuth==null){
        return null;
    }
    if(!isset($userAuth[$key])){
        return null;
    }
    

    
    return $userAuth[$key];
}

//判断权限,如果没有权限就停止执行 可以写多个 A|B|C
function authCheck($keys,$value=true){
    $keys = explode("|", $keys);
    foreach($keys as $k){
        if(authGet($k)!=$value){
            die();
        }
    }
}

