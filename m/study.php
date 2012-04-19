<?php
// index.php
require $_SERVER['DOCUMENT_ROOT'].'/hangee/m/lib/__core.php';

$page = new Page();
$page->setTitle('HanGEE::Study');
$page->setMaster('m');
$page->setScript('/hangee/m/study.js');

ob_start();
?>


<?php
$page->setContent(ob_get_contents());
ob_clean();

$page->render();
?>
