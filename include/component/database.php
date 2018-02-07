<?php

//call connect to database
//调用连接到数据库,框架会自动连接默认数据库
function connectDB($dbName,$forceOriginalDB = false) {
    global $DB_LINK, $TIME_ZONE,$isTemplate , $DATABASE_URL,$DATABASE_REPLICAS_URL , $DATABASE_USERNAME, $DATABASE_PASSWORD, $RDS_LIST, $IS_TESTDB;

   
    if(isset($isTemplate) && isset($DATABASE_REPLICAS_URL) && !$forceOriginalDB){
        $DATABASE_URL_CONNECT = $DATABASE_REPLICAS_URL;
    }else{
        $DATABASE_URL_CONNECT = $DATABASE_URL;
    }
    
    //创建数据库连接对象
    $DB_LINK = mysqli_connect($DATABASE_URL_CONNECT, $DATABASE_USERNAME, $DATABASE_PASSWORD) or die("Can't connect to database!");

    mysqli_select_db($DB_LINK, $dbName) or die("Can't select database($dbName)!");
    mysqli_query($DB_LINK, "set names utf8");

    if (session("TimeZone")) {
        $TIME_ZONE = session("TimeZone");
    }

    setTimeZone($TIME_ZONE);


}

//set timezone
//设置时区,框架会自动设置默认时区
function setTimeZone($zone) {
    global $DB_LINK;

    date_default_timezone_set("$zone");
    mysqli_query($DB_LINK, "SET time_zone = '$zone';");
}



//query a sql display error when sql has syntax error
function query($sql, &$error = NULL) {
    global $DB_LINK,$_SQL_EXCUTETIME;
    $displayErrors = ini_get("display_errors");

    if (!$DB_LINK) {
        die("Call query before connect to database, please check your <b>_configuration.php</b> file, and make sure \$DB_NAME is correct!");
    }

    //记录时间
    if ($displayErrors){
        $sqlStartTime = microtime(1);
        if(!isset($_SQL_EXCUTETIME)){
            $_SQL_EXCUTETIME = [];
        }
    }

    $rs = mysqli_query($DB_LINK, $sql);

    //记录时间
    if ($displayErrors){
        $exp = new Exception('SQL Error');
        for($i = count($exp->getTrace())-1;$i>=0;$i--){
             $expInfo = $exp->getTrace()[$i];
             if($expInfo["function"]!="include_once" && $expInfo["function"]!="include"){
                 break;;
             }
        }
        array_push($_SQL_EXCUTETIME, ["SQL"=>$sql."<br>".$expInfo["file"]."(".$expInfo["line"].")","ExcuteTime"=>(microtime(1)-$sqlStartTime)]);
    }

    //错误打印
    if ($displayErrors && !$rs) {

        $exp = new Exception('SQL Error');
        for($i = count($exp->getTrace())-1;$i>=0;$i--){
             $expInfo = $exp->getTrace()[$i];
             if($expInfo["function"]!="include_once" && $expInfo["function"]!="include"){
                 break;;
             }
        }

        myErrorHandler("", "SQL Error: $sql <br>" . mysqli_error($DB_LINK), $expInfo["file"], $expInfo["line"]);

    }
    if ($error) {
        $error = mysqli_error($DB_LINK);
    }

    return $rs;
}


function printSQLExcuteTime(){
    global $_SQL_EXCUTETIME;
    if($_SQL_EXCUTETIME == null){
        return;
    }
    $counter = 1;
    echo "<div style='clear:both;'>&nbsp;</div><table cellspacing=0 style='width:100%;background-color:white;color:black;padding:10px;'>";
    foreach($_SQL_EXCUTETIME as $st){
        echo "<tr><td rowspan=2 style='border-bottom:1px solid #ccc;text-align:center;padding-right:10px;'>$counter</td><td style='padding-top:5px;'>".$st["SQL"]."</td></tr>";
        echo "<tr><td style='border-bottom:1px solid #ccc;padding-bottom:5px;color:#555;'>".$st["ExcuteTime"]."s</td></tr>";
        $counter++;
    }
    echo "</table>";
}

//return a single value of a query
function getSingleData($sql, $col = 0) {
    $data = mysqli_fetch_array(query($sql));
    if ($data) {
        return $data[$col];
    } else {
        return null;
    }
}


function getDictionaryData($sql,$idCol, &$error = NULL) {
    $result = query($sql, $error);
    $arr = array();

    while ($data = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $arr[$data[$idCol]] = $data;
    }
    return $arr;
}


//return dataset of a query
function getData($sql, &$error = NULL) {
    $result = query($sql, $error);
    $arr = array();

    while ($data = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        array_push($arr, $data);
    }
    return $arr;
}


function getRowData($sql) {
    $rowData = getData($sql);
    if (count($rowData) == 0) {
        return null;
    }
    $rowData = $rowData[0];
    return $rowData;
}

//save array to file
function getCacheData($key, $sql) {
    global $webRootPath, $_pvar;
    $filePath = $webRootPath . "cache/datacache/$key.php";
    $dir = dirname($filePath);
    if (!file_exists($dir)) {
        mkdir($dir);
    }
    if (!file_exists($filePath)) {
        $array = getData($sql);
        writeToFile($filePath, "<?php\n \$_pvar=" . var_export($array, true) . " \n?>");
    }
    include $filePath;
    return $_pvar;
}

//remove file cache
function removeCache($key) {
    $filePath = "cache/$key.php";
    unlink($filePath);
}

//insert post data, return lastest insert id
function insertData($tableName, $insertData = null, $ignorePostData = false) {
    global $DB_LINK;
    $sql = "insert into $tableName(";
    $values = " values(";
    if (!$ignorePostData && $_POST) {
        foreach ($_POST as $key => $value) {
            if ($key == "Action") {
                continue;
            }
            $sql.= $key . ",";
            if ($value === "now()") {
                $values.= "$value,";
            } else {
                $values.= "'" . addslashes($value) . "',";
            }
        }
    }
    if ($insertData) {
        foreach ($insertData as $key => $value) {
            $sql.= $key . ",";
            if ($value === "now()") {
                $values.= "$value,";
            } else {
                $values.= "'" . addslashes($value) . "',";
            }
        }
    }

    $sql = substr($sql, 0, strlen($sql) - 1) . ")";
    $values = substr($values, 0, strlen($values) - 1) . ")";
    $sql .= $values;
    query($sql);
    return mysqli_insert_id($DB_LINK);
}


function getLastInsertedId(){
    global $DB_LINK;
    return mysqli_insert_id($DB_LINK);
}

//update data and return affected rows
function updateData($tableName, $condition, $updateData = null, $ignorePostData = false) {
    global $DB_LINK;
    $sql = "update $tableName set ";
    $sets = "";
    if (!$ignorePostData && $_POST) {
        foreach ($_POST as $key => $value) {
            if ($key == "Action") {
                continue;
            }

            if ($value === "now()") {
                $sets .= "$key=now(),";
            } else {
                $sets .= "$key='" . addslashes($value) . "',";
            }

        }
    }
    if ($updateData) {
        foreach ($updateData as $key => $value) {
            if ($value === "now()") {
                $sets .= "$key=now(),";
            } else {
                $sets .= "$key='" . addslashes($value) . "',";
            }
        }
    }
    $sets = substr($sets, 0, strlen($sets) - 1) . " where ";
    $sql .= $sets . " $condition";

    query($sql);
    return mysqli_affected_rows($DB_LINK);
}

//delete data from table
function deleteData($tableName, $condition) {
    global $DB_LINK;
    $sql = "delete from $tableName where $condition";
    query($sql);
    return mysqli_affected_rows($DB_LINK);
}

//check if user missing a post field
function checkRequireField($requireField) {

    $errorMsg = NULL;
    $errorCode = 0;
    $result = NULL;

    if (is_array($requireField)) {
        foreach ($requireField as $key) {
            if (!isset($_POST[$key])) {
                $errorCode = 100;
                $errorMsg = "Missing Param '$key'";
            }
        }
    } else {
        if (!isset($_POST[$requireField])) {
            $errorCode = 100;
            $errorMsg = "Missing Param '$key'";
        }
    }
    if ($errorCode != 0) {
        $responseText["ErrorCode"] = $errorCode;
        $responseText["ErrorMessage"] = $errorMsg;
        $responseText["Result"] = $result;
        echo json_encode($responseText);
        die();
    }
}


//api result
function printResultByMessage($errorMsg, $errorCode, $result = NULL) {
    $responseText = Array();

    $responseText["ErrorCode"] = $errorCode;
    $responseText["ErrorMessage"] = $errorMsg;
    $responseText["Result"] = $result;

    echo json_encode($responseText);
    die();
}


function insertAndPrintResult($inertSQL, $selectSQL) {
    $errorMsg = NULL;
    $result = NULL;

    $responseText = Array();
    $errorCode = 0;

    query($inertSQL, $errorMsg);
    if ($errorMsg) {
        $errorCode = 101;
    }

    if ($errorCode != 0) {
        $responseText["ErrorCode"] = $errorCode;
        $responseText["ErrorMessage"] = $errorMsg;
        $responseText["Result"] = $result;
        echo json_encode($responseText);
        return;
    }

    printResult($selectSQL);
    die();
}



function printResult($selectSQL, $isSingleRow = false) {
    $errorMsg = NULL;

    $responseText = Array();
    $errorCode = 0;

    $result = getData($selectSQL, $errorMsg);
    if ($errorMsg) {
        $errorCode = 101;
    }

    if ($isSingleRow) {
        if (count($result) > 0) {
            $result = $result[0];
        } else {
            $result = null;
        }
    }

    $responseText["ErrorCode"] = $errorCode;
    $responseText["ErrorMessage"] = $errorMsg;
    $responseText["Result"] = $result;

    echo json_encode($responseText);
    die();
}


// node db

/*
    $fileInfo = [
        "BannerImage"=>[
            "Path" => "HomePage/".md5(time()).rand(1,10000).".jpg",
            "Width" => 1024,
            "Height" => 400
        ]
    ];

    setNode($HomePageKey,"BannerBigCover.xxxx",$fileInfo);

*/



//数组形式的添加节点
function addNode($key,$node="",$fileInfo=""){
    if(endWith($node, ".")){
        return;
    }
    __changeNodeValue(true,$key,$fileInfo,$node);
}

//字典形式的设置节点
function setNode($key,$node="",$fileInfo=""){
    if(endWith($node, ".")){
        return;
    }
    __changeNodeValue(false,$key,$fileInfo,$node);
}

//删除一个节点
function removeNode($key,$node){
    if(endWith($node, ".")){
        return;
    }
    $jsonDataFromDB = getSingleData("select Data from PageData where Page='$key'");
    $jsonData= json_decode($jsonDataFromDB,true);

    if($jsonDataFromDB && !$jsonData){
        return;
    }

    if(!$jsonData){
        $jsonData = [];
    }
    $finalData = &$jsonData;

    $nodes = explode(".", $node);

    for($i=0;$i<count($nodes)-1;$i++){
        $jsonData = &$jsonData[$nodes[$i]];
    }



    $lastKey = $nodes[count($nodes)-1];

    if(is_numeric($lastKey)){
        array_splice($jsonData,$lastKey,1);
    }else{
        unset($jsonData[$lastKey]);
    }
    query("update PageData set Data = '". addslashes(json_encode($finalData))."' where Page = '$key'");

}

function __changeNodeValue($isAdd,$pageKey,$fileInfo="",$node=""){
    $jsonDataFromDB = getSingleData("select Data from PageData where Page='$pageKey'");
    $jsonData = json_decode($jsonDataFromDB,true);

    if($jsonDataFromDB && !$jsonData){
        return;
    }

    if(!$jsonData){
        $jsonData = [];
        query("insert into PageData(Page) values('$pageKey')");
    }

    $finalData = &$jsonData;

    if($node !== ""){
        $nodes = explode(".", $node);
        foreach($nodes as $n){
            if(!isset($jsonData[$n])){
                $jsonData[$n]=[];
            }
            $jsonData = &$jsonData[$n];
        }
    }

    if($isAdd){
        $obj = [];
        array_push($jsonData, $obj);
        $jsonData = &$jsonData[count($jsonData)-1];
    }

    foreach($_POST as $key => $value){
        if($key == "Action"){
            continue;
        }
        $jsonData[$key] = htmlspecialchars($value);
    }



    foreach($_FILES as $key => $value){
        if($_FILES[$key]['error'] == 0){

            if(isset($jsonData[$key])){
                 $oldPath = $jsonData[$key];
                 AWSS3DeleteFile($oldPath);
            }

            if(!isset($fileInfo[$key])){
                echo "Error: \$_FILES['$key'] incorrect";
            }

            $path = $fileInfo[$key]["Path"];
            $url = AWSS3SavePostImageFile($key, $path, $fileInfo[$key]["Width"], $fileInfo[$key]["Height"]);

            $jsonData[$key] = $url;
        }
    }


    query("update PageData set Data = '". addslashes(json_encode($finalData))."' where Page = '$pageKey'");

}


//connect to database
if(isset($DB_NAME)){
    connectDB($DB_NAME);
}
