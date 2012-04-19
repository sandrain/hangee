<?php
require_once("__lib.php");

connect_db();

$id = $_GET['t'];
$pass = $_GET['tt'];
$aip = $_SERVER['REMOTE_ADDR'];

$sql = "select aip,password,grade from hangee_users where id=$id";
if (!($res = mysql_query($sql, $dbconn)))
	die($sql);
$row = mysql_fetch_array($res);
if ($pass != $row['password'] || $aip != $row['aip'])
	header("Location: login.php");

disconnect_db();

session_start();
$_SESSION['user'] = $id;
$_SESSION['pass'] = $pass;
$_SESSION['grade'] = $row['grade'];

header("Location: index.php");
?>
