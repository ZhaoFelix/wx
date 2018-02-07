<?php

function exportCSV($dbName,$table,$format){
    
    connectDB($dbName);
    $csvData = "";
    $sql = "select ";
    foreach ($format as $displayName => $field){
        $tableHeader .= "\"$displayName\",";
        $sql .= "$field,";
    }
    $csvData = substr($csvData, 0, strlen($csvData)-1)."\n";
    $sql = substr($sql,0,strlen($sql)-1);
    
    $sql .= "from $table";
    
    $resultData = getData($sql);
    foreach ($resultData as $rowData){
        foreach ($rowData as $cellData){
            $csvData .= "\"$cellData\",";
        }
        $csvData = substr($csvData, 0, strlen($csvData)-1)."\n";
    }
    
    header('Content-type: application/csv');
}

