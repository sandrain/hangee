<?php
#
# logout.php
#
# Written by HyoGi Sim <sandrain@gmail.com>
#

require_once("__lib.php");

hangee_init();

unset($_SESSION['user']);
unset($_SESSION['pass']);
unset($_SESSION['testpos']);
unset($_SESSION['testcrc']);
unset($_SESSION['maxseq']);

hangee_exit();

header('Location: index.php');

?>
