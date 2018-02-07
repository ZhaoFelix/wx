<?php

// error handler function
function myErrorHandler($errno, $errstr, $errfile, $errline) {

    echo "<pre style=\"color:black;background-color:white;font-family:'Monospaced','Helvetica', sans-serif;line-height:20px;font-size:13px;\">";
    $lines = explode("\n", htmlspecialchars(readFileData($errfile)));
    $c = 1;
    foreach ($lines as $l) {
        $lineColor = "";
        if ($c % 2 == 0) {
            $lineColor = "background-color:#f4f4f4;";
        }

        if ($c == $errline) {
            echo "<div style='background-color:#faa;'><div style='text-align:right;float:left;width:30px;margin-right:5px;'>$c</div> $l</div>";
            echo "<div style='color:red;background-color:white;padding-left:30px;'>$errstr</div>";
        } else {
            echo "<div style='$lineColor'><div style='text-align:right;float:left;width:30px;margin-right:5px;'>$c</div> $l</div>";
        }

        $c++;
    }
    echo "</pre>";
    die();
}

function shutdown() {
    $e = error_get_last();
    if ($e == null) {
        return;
    }
    myErrorHandler("", $e['message'], $e['file'], $e['line']);
}


if(ini_get("display_errors")){
    set_error_handler("myErrorHandler", E_ERROR);
    register_shutdown_function('shutdown');
}

