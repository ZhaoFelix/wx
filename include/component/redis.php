<?php

function getRedis(){
    global $RDS_URL,$REDIS_OBJECT,$REDIS_DB;
    if(class_exists("Redis") && isset($RDS_URL)){
        if($REDIS_OBJECT==null){
            if(!isset($REDIS_DB)){
                $REDIS_DB = 0;
            }
            $REDIS_OBJECT = new Redis();
            if($REDIS_OBJECT->connect($RDS_URL)){
                $REDIS_OBJECT->select($REDIS_DB);
            }else{
                $REDIS_OBJECT = null;
            }
            
        }
        return $REDIS_OBJECT;
    }
    return null;
}

function disconnectRedis(){
    global $REDIS_OBJECT;
    unset($REDIS_OBJECT);
    $REDIS_OBJECT = null;
}


function redisSet($key,$value){
    $redis = getRedis();
    if($redis){
        $redis->set($key,$value);
    }
}

function redisDel($key){
    $redis = getRedis();
    if($redis){
        $redis->del($key);
    }
}

function redisExpire($key,$sec){
    $redis = getRedis();
    if($redis){
        $redis->expire($key,$sec);
    }
}


function redisGet($key){
    $redis = getRedis();
    if($redis){
        return $redis->get($key);
    }
    return null;
}

function redisRPush($key,$value){
    $redis = getRedis();
    if($redis){
        $redis->rPush($key,$value);
    }
}

function redisLPush($key,$value){
    $redis = getRedis();
    if($redis){
        $redis->lPush($key,$value);
    }
}

function redisLPop($key){
    $redis = getRedis();
    if($redis){
        return $redis->lPop($key);
    }
    return null;
}

function redisRPop($key){
    $redis = getRedis();
    if($redis){
        return $redis->rPop($key);
    }
    return null;
}

function redisHSet($key,$field,$value){
    $redis = getRedis();
    if($redis){
        $redis->hSet($key,$field,$value);
    }
}

function redisHGet($key,$field){
    $redis = getRedis();
    if($redis){
        return $redis->hGet($key,$field);
    }
    return null;
}