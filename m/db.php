<?php
require $_SERVER['DOCUMENT_ROOT'].'/hangee/m/lib/__core.php';

$page = new Page();
$page->setTitle('HanGEE');
$page->setMaster('m');
$page->setScript('/hangee/m/db.js');

ob_start();
?>

<div id="result"></div>

<?php
$page->setContent(ob_get_contents());
ob_clean();

$page->render();
?>
