<?php
#
# This is project initialization unit
#  this file is called on the top of application
#
set_time_limit(300);


# --------------------------------------------------------------------
# Include Paths
# --------------------------------------------------------------------
set_include_path(
	get_include_path() . 
	PATH_SEPARATOR . 
	realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR."pear" .
	PATH_SEPARATOR . 
	realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR."lib" .
	PATH_SEPARATOR . 
	realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR."lib".DIRECTORY_SEPARATOR."doctrine" .
	PATH_SEPARATOR . 
	realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR."class" .
	PATH_SEPARATOR . 
	realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."dictionary" .
	PATH_SEPARATOR . 
	"C:/Program Files/php5/PEAR"
);
# Basic functions needed to bootstrap
require "misc.php";


# --------------------------------------------------------------------
# Application config (with local config)
# --------------------------------------------------------------------
include_once "var.php";
$config_local = realpath(dirname(__FILE__))."/var.local.php";
if (file_exists($config_local)) include $config_local;


# --------------------------------------------------------------------
# Include other files
# --------------------------------------------------------------------
include_once "DbSimple/Generic.php";
include_once "DbSimple/Mysql.php";

function __autoload($class) {
	$include_file = "";
	if (substr($class,0,3) == 'Dic') $include_file =  $class.".class.php";
	if ($include_file == "") throw new Exception("No file found to include for class '$class'");
	if (!include_once($include_file))  throw new Exception("No file '$include_file' found to include for class '$class'");
}




# --------------------------------------------------------------------
# Initilization logic
# --------------------------------------------------------------------
if (ini_get('magic_quotes_gpc')) $_POST = strip_magic_slashes($_POST);
if (ini_get('magic_quotes_gpc')) $_GET = strip_magic_slashes($_GET);
if (ini_get('magic_quotes_gpc')) $_REQUEST = strip_magic_slashes($_REQUEST);


mb_internal_encoding("UTF8");


# Database setup
Z::c();



#
# Random seed
#
function make_seed() {
    list($usec, $sec) = explode(' ', microtime());
    return (float) $sec + ((float) $usec * 100000);
}
srand(make_seed());
// 

#
# Attach a debugger console
#
#if (!$_GET['ajax'] && $config["debug"]) {
#	require_once "HackerConsole/Main.php";
#	new Debug_HackerConsole_Main(true);
#}
function debug($vr) {
	global $config;
	echo $vr;
	if ($_GET["ajax"] || !$config["debug"]) return;
	if (class_exists("Debug_HackerConsole_Main")) Debug_HackerConsole_Main::out($vr);
}



?>
