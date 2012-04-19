<?php 
$sesspath = $_SERVER['DOCUMENT_ROOT']."/.sessions";
session_save_path($sesspath);
ini_set('session.gc_maxlifetime', 86400);	// seconds
ini_set('session.cache_expire', 1440);		// minutes
session_set_cookie_params(0, "/");
phpinfo();
?>
