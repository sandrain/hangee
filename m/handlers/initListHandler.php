<?php
// initListHandler.php

require_once $_SERVER['DOCUMENT_ROOT'].'/hangee/m/lib/__core.php';

$handler = new JsonHandler();

$query = "select id from hangee_words order by id asc";
$db = new MySql();
$db->connect();
$res = $db->query($query);

$wids = array();

while ($row = mysql_fetch_array($res, MYSQL_ASSOC))
    $wids[] = $row['id'];

$db->close();
$handler->sendData($wids);
?>
