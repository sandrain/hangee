<?

// Init configuration variable
$config = array(
	'db_host' => "localhost",
	'db_user' => "root",
	'db_password' => "sxz",
	'db_database' => "32bit",

	'debug' => false,
	'version'	=> '0.5',

	'date_format' => "d.m.Y",
	'date_format_js' => "%d.%m.%Y",
	'datetime_format' => "d.m.Y H:i:s",

	'db_date_format' => "Y-m-d",
	'db_datetime_format' => "Y-m-d H:i:s",
);

$config['path_base'] = realpath(dirname(__FILE__).DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."data");
$config['path_filedb'] = $config['path_base'].DIRECTORY_SEPARATOR."filedb";  
$config['path_queue'] = $config['path_base'].DIRECTORY_SEPARATOR."filedb";
$config['debug'] = false;

$config['prefix_length'] = 2;

# Maximum words to return to the user
$config['min_search_prefix'] = 2;

# Maximum words to return to the user
$config['max_search'] = 2000;

Z::configMerge($config);
?>