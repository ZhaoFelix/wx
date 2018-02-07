<?php

//white data to a file
function writeToFile($filePath, $data, $mode = "w") {
    $fp = fopen($filePath, $mode);
    fwrite($fp, $data);
    fclose($fp);
}


//read data from a file
function readFileData($filePath) {
    $fp = fopen($filePath, 'r');
    $fz = filesize($filePath);
    if ($fz) {
        $theData = fread($fp, $fz);
        return $theData;
    }
    return "";
}


function logData($data,$folderName = null){
    if($folderName == null){
        $folderName = "_Logs_";
    }
    
    if(!file_exists($folderName)){
        mkdir($folderName,0777,true);
    }
    
    $data = "[".date("H:i:s")."]\n".$data;
    writeToFile($folderName."/".date("Y-m-d",time()).".log",$data."\n\n","a");
}