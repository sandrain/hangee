<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" /> 
<html xmlns="http://www.w3.org/1999/xhtml" /> 
<meta http-equiv="content-type" content="text/html; charset=utf-8" /> 
<head> 
<body> 
<?php
$dbconn = mysql_connect('localhost', 'sandrain', '359035');
mysql_select_db('sandrain', $dbconn);

$word = $_GET['word'];

$sql = "select * from stardict_cobuild where word='$word'";
$res = mysql_query($sql, $dbconn);

$row = mysql_fetch_array($res);
$text = $row['text'];
echo "<p>$text</p>";
?>
</body>
