<?php
// core.php

// Global settings..
$docroot = $_SERVER['DOCUMENT_ROOT'].'/hangee/m';
$libdirs = array($docroot.'/lib');

// Auto include classes..
function __autoload($class) {
    global $libdirs;

    $found = false;
    foreach ($libdirs as $dir) {
        $file = "$dir/$class.php";
        if (file_exists($file)) {
            include_once($file);
            $found = true;
            break;
        }
    }

    if (!$found)
        throw new Exception("Unable to load $class");
}

?>
