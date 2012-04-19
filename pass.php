<?php

echo time();
echo "<BR>";

if (isset($_GET['pass']))
	echo md5($_GET['pass']);

?>
