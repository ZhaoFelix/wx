<?php


$_CUSTOM_TEMPLATE_PATTERNS = [];
$_CUSTOM_TEMPLATE_REPLACEMENTS = [];


//generate php template an return its path
function template($path) {
    global $_CUSTOM_TEMPLATE_PATTERNS,$_CUSTOM_TEMPLATE_REPLACEMENTS;
    
    $dPath = dirname($path);
    if($dPath){
        $dPath = $dPath."/";
    }
    $file = $dPath."_cache/template/" . basename($path);
    
    if (!file_exists($file) || (file_exists($file) && filectime($path)>filectime($file))) {
        
        $dir = dirname($file);
        if (!file_exists($dir)){
            mkdir($dir, 0777, true);
        }
        
        $fp = fopen($path, 'r');
        $fz = filesize($path);
        
        if ($fz) {
            $theData = fread($fp, filesize($path));
            $rSlot = " *([^}]+)";
            $patterns = array(
                "/\{include:$rSlot\}/",
                "/\{for:$rSlot *;$rSlot *;$rSlot\}/",
                "/\{\/for\}/",
                "/\{while:$rSlot\}/",
                "/\{\/while}/",
                "/\{echo:$rSlot\}/",
                "/\{e:$rSlot\}/",
                "/\{if:$rSlot\}/",
                "/\{\/if\}/",
                "/\{elseif:$rSlot\}/",
                "/\{else\}/",
                "/\{([\$][^}]+) +as +([^}]+)\}/",
                "/\{([\$][^}]+) +default +([^}]+)\}/",
                "/\{([\$][^}]+) +nl2br *\}/",
                "/\{([\$][^}]+) *= *([^}]+)\}/",
                "/\{([\$][^}]+)\+\+[^}]*}/",
                "/\{([\$][^\+\-\*\/}]+)\}/",
                "/\{([\$][^}]+)\}/",
                "/\{langtext:$rSlot\}/i",
                "/\{l:$rSlot\}/",
                "/include(_once)* +[\"'](..\/)*include\/template.php[\"'] *;/",
                "/\{foreach: *\\$([^}]+) +as +$rSlot +counter:$rSlot\}/",
                "/\{foreach: *\\$([^}]+) +as +$rSlot *\}/",
                "/\{foreach:$rSlot +as +$rSlot +counter:$rSlot\}/",
                "/\{foreach:$rSlot +as +$rSlot *\}/",
                "/\{\/foreach}/",
                "/\{\*$rSlot\*\}/",
                "/\{break\}/",
                "/\{continue\}/",
                "/\{auth:$rSlot\}/",
                "/\{\/auth\}/",
                "/\{authcheck:$rSlot\}/i",
                "/\{inpage:$rSlot\}/i",
                "/\{\/inpage\}/i",
                "/\{pagecssjs\}/i",
                "/\{$rSlot.php\}/i",
                "/\{$rSlot.css\}/i",
                "/\{$rSlot.js\}/i",
                "/\{viewport375}/i",
                "/\{viewportmobile}/i",
                "/\{@:$rSlot counter:$rSlot\}/i",
                "/\{@:$rSlot\}/i",
                "/\{\/@\}/i"
            );
            
            $replacements = array(
                
                "<?php include(template(\"\\1\"));?>",
                "<?php for(\\1;\\2;\\3){ ?>",
                "<?php }?>",
                "<?php while(\\1){ ?>",
                "<?php }?>",
                "<?php echo \\1; ?>",
                "<?php echo \\1; ?>",
                "<?php if(\\1){?>",
                "<?php }?>",
                "<?php }elseif(\\1){ ?>",
                "<?php }else{ ?>",
                "<?php echo explode('|',\"\\2\")[\\1];?>",
                "<?php e(\\1,\\2); ?>",
                "<?php enl2br(\\1);?>",
                "<?php \\1=\\2; ?>",
                "<?php \\1++;?>",
                "<?php e(\\1);?>",
                "<?php echo \\1;?>",
                "<?php echo l(\"\\1\") ?>",
                "<?php echo l(\"\\1\") ?>",
                "",
                "<?php if(isset($\\1)){\\3=-1;foreach($\\1 as \\2){\\3++?>",
                "<?php if(isset($\\1)){foreach($\\1 as \\2){?>",
                "<?php {\\3=-1;foreach(\\1 as \\2){\\3++?>",
                "<?php {foreach(\\1 as \\2){?>",
                "<?php }}?>",
                "",
                "<?php break; ?>",
                "<?php continue; ?>",
                "<?php if(authGet(\"\\1\")){ ?>",
                "<?php } ?>",
                "<?php authCheck('\\1'); ?>",
                "<?php if(inPage(\"\\1\")){ ?>",
                "<?php } ?>",
                "<?php _includePageCSSJS(); ?>",
                "<?php include(template(\"\\1.php\"));?>",
                "<?php _includeCSS(\"\\1.css\"); ?>",
                "<?php _includeJS(\"\\1.js\"); ?>",
                "<meta name='viewport' content='width=375,user-scalable=no'>",
                "<?php _viewportMobile(); ?>",
                "<?php if(isset(\\1)){\\2=-1;foreach(\\1 as \$__data__){\n \\2++; foreach(\$__data__ as \$__k=>\$__v) { \$GLOBALS[\$__k] = \$__v; }?>",
                "<?php if(isset(\\1)){foreach(\\1 as \$__data__){\n foreach(\$__data__ as \$__k=>\$__v) { \$GLOBALS[\$__k] = \$__v; }?>",
                "<?php }}?>"
            );
            
            $theData = preg_replace($patterns, $replacements, $theData);
            
            //暂时关闭自定义标签功能
            //$theData = preg_replace($_CUSTOM_TEMPLATE_PATTERNS, $_CUSTOM_TEMPLATE_REPLACEMENTS, $theData);
            
        }else {
            $theData = "";
        }
        fclose($fp);
        writeToFile($file, $theData);
        //chmod($file, 0777);
    }
    return $file;
}

function e(&$var,$default=null){
    if(isset($var)){
        if($var === null || $var === ""){
            echo $default;
        }else{
            echo $var;
        }
    }else{
        echo $default;
    }
}

function enl2br(&$var){
    if(isset($var)){
        if($var === null || $var === ""){
            echo "";
        }else{
            echo nl2br($var);
        }
    }
}

function removeStaticPage($pageName){
    $pagePath="cache/staticpage/".$pageName.".gphp";
    unlink($pagePath);
}


function removeAllStaticPage(){
    $pagePath="cache/staticpage";
    rmdir($pagePath);
    mkdir($pagePath);
}

function includeTemplate($path){
    if(file_exists($path)){
        include template($path);
    }
}


function addTag($tagName,$params,$outputStart,$outEnd=null){
    global $_CUSTOM_TEMPLATE_PATTERNS,$_CUSTOM_TEMPLATE_REPLACEMENTS;
    $rSlot = " *([^}]+)";
    
    $pattern = "/<$tagName";
    
    if(count($params)){
        $pattern.=" +";
    }
    
    $c = 1;
    foreach($params as $p){
        $pattern.= "$p *= *\"$rSlot\" *";
        $outputStart = preg_replace("/\{$p\}/","\\\\$c",$outputStart);
        $c++;
    }
    
    $pattern.=">/";
    $replacement = $outputStart;
    
       
    array_push($_CUSTOM_TEMPLATE_PATTERNS,$pattern);
    array_push($_CUSTOM_TEMPLATE_REPLACEMENTS,$replacement);
    
    if($outEnd != null){
        $pattern = "/<\/$tagName>/";
        
        $c = 1;
        foreach($params as $p){
            $outEnd = preg_replace("/\{$p\}/","\\\\$c",$outEnd);
            $c++;
        }
        
        $replacement = $outEnd;
        
        array_push($_CUSTOM_TEMPLATE_PATTERNS,$pattern);
        array_push($_CUSTOM_TEMPLATE_REPLACEMENTS,$replacement);
        
    }
}


function inPage($pageNames){
    $pages = explode("|", $pageNames);
    $isInPage = false;
    foreach($pages as $pageName){
        if(endWith(explode("?",$_SERVER['REQUEST_URI'])[0],"/") && ($pageName =="index.php" || $pageName=="index")){
            $isInPage = true;
        }
        if(strpos($_SERVER['REQUEST_URI'], $pageName)!=false){
            $isInPage = true;
        }
    }
    return $isInPage;
}

function _includePageCSSJS(){
    $fileName = str_replace(".php","", basename($_SERVER['PHP_SELF']));
    if(file_exists("css/$fileName.css")){
        echo "<link rel='stylesheet' type='text/css' href='css/$fileName.css' />\n";
    }
    if(file_exists("js/$fileName.js")){
        echo "<script type='text/javascript' src='js/$fileName.js'></script>\n";
    }
}

function _includeCSS($cssPath){
    global $VERSION;
    $ver = "";
    if(isset($VERSION)){
        $ver = $VERSION;
    }
    echo "<link rel='stylesheet' type='text/css' href='$cssPath?$ver' />\n";
}

function _includeJS($jsPath){
    global $VERSION;
    $ver = "";
    if(isset($VERSION)){
        $ver = $VERSION;
    }
    echo "<script type='text/javascript' src='$jsPath?$ver'></script>\n";
}


function _viewportMobile(){
    global $isMobileDevice;
    if($isMobileDevice){
        echo "<meta name='viewport' content='width=375,user-scalable=no'>";
    }else{
        echo "<meta name='viewport' content='width=device-width,user-scalable=no'>";
    }
    
}