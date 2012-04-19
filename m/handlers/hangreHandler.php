<?php
// hangreHandler.php

require_once $_SERVER['DOCUMENT_ROOT'].'/hangee/m/lib/__core.php';

$handler = new JsonHandler();
$request = $handler->getRequest();
if ($request == null) {
    $handler->sendError('Invalid Request!');
    die();
}

$char = $request->character;
$query = "select * from hangee_words where word like '$char%' and page < 500 order by word asc";

$db = new MySql();
$db->connect();
$res = $db->query($query);

$words = array();

while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
    $current = new WordRecord();
    $current->wid = $row['id'];
    $current->page = $row['page'];
    $current->word = $row['word'];
    $current->sense = $row['sense'];
    $words[] = $current;
}

$db->close();
$handler->sendData($words);
?>
