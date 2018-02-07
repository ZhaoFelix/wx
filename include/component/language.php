<?php




//set language function
//can set to cn,en(recomended) or 0,1,2
function setLang($language) {
    global $lang, $LANGUAGES;
    if(is_numeric($language)){
        if($language>=count($LANGUAGES)){
            return;
        }
        $_SESSION["LangIndex"] = $language;
    }else{
        $r = array_keys($LANGUAGES, $language);
        if(count($r)>0){
            $_SESSION["LangIndex"] = $r[0];
        }else{
            return;
        }
    }
    $lang = "_" . $LANGUAGES[$_SESSION["LangIndex"]];
    if (!headers_sent()) {
        setcookie("Language", $LANGUAGES[$_SESSION["LangIndex"]], time() + 3600 * 24 * 365);
    }
}

//get language text from string like "中文|Chinese"
function getLangText($langStr) {
    $langStr = explode("|", $langStr);
    return $langStr[$_SESSION["LangIndex"]];
}

//get language 
function l($langStr){
    return getLangText($langStr);
}

//call to init language string by reading cookie value or set by browser's language
function initLang() {
    global $LANGUAGES;
    
    if (isset($_COOKIE["Language"])) {
        $browerLang = $_COOKIE["Language"];
    } else {
        if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
            $browerLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        }else{
            $browerLang = "en";
        }
    }
    if(isset($_SESSION["LangIndex"])){
       $index = $_SESSION["LangIndex"];
    }else{
       $index = array_search($browerLang, $LANGUAGES); 
    }
    
    if ($index) {
        setLang($index);
    }else{
        setLang(0);
    }
}


//set language if a page set ?lang=cn / ?lang=en
if (isset($_GET["lang"])) {
    setLang($_GET["lang"]);
}

