<?php
#
# __lib.php
#
# Library for hangee.
#

require("xajax/xajax_core/xajax.inc.php");

$uid = 0;
$upass = null;

function hangee_init($session = 0)
{
	global $dbconn;
	global $uid;
	global $upass;
	global $poll_start;
	global $poll_end;

	$aip = $_SERVER['REMOTE_ADDR'];
	$atime = time();

	session_start();

	connect_db();

	if (!isset($_SESSION['user']))
		header('Location: login.php');

	$uid = $_SESSION['user'];
	$upass = $_SESSION['pass'];

	if (!$session)
		unset($_SESSION['testwords']);

	$sql = "select aip, atime from hangee_users where id=$uid";
	if (!($res = mysql_query($sql, $dbconn)))
		die ("query failed: ".mysql_error());

	$row = mysql_fetch_array($res);
	if (($aip != $row['aip'])/* || (($atime - $row['atime']) > 3600)*/)
		header('Location: login.php');

	$sql = "update hangee_users set atime=$atime where id=$uid";
	if (!($res = mysql_query($sql, $dbconn)))
		die ("query failed: ".mysql_error());
}

function hangee_init_light()
{
	global $dbconn;
	connect_db();
}

function hangee_exit()
{
	disconnect_db();
}

function hangee_header($title)
{
	global $uid;

$html =<<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" />
<html xmlns="http://www.w3.org/1999/xhtml" />
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<head>
<title>$title</title>
<link rel="icon" type="image/ico" href="/sandrain.ico">
<link rel="stylesheet" type="text/css" href="css/hangee.css" />
<script type="text/javascript" src="js/hangee.js"></script>
<body>

<div id="menu">
  <a href="index.php">Home</a> |
  <a href="browser.php">Browser</a> |
  <a href="tester_hangee.php">HanGEE</a> |
  <a href="tester_goman.php">GoMAN</a> |
  <a href="sheets.php">Sheets</a> |
  <a href="help.php">Help</a>
</div>

<div id="header">
EOF;
	if ($uid > 0)
		$html .= "<p><b>HanGEE</b>: <a href=\"logout.php\">Log Out</a></p>";
	else
		$html .= "<p><b>HanGEE</b></p>";

	$html .= "</div><div id=\"content\"><div id=\"debug\" onClick='this.style.display=\"none\"'></div>";
	$html .= "<div id=\"result\" onClick='this.style.display=\"none\"'></div>";
	echo $html;
}

function hangee_footer()
{
$html =<<<EOF
</div>
</body>
</html>
EOF;
	echo $html;
}

function hangee_popup_header($title)
{
$html =<<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" />
<html xmlns="http://www.w3.org/1999/xhtml" />
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densitydpi=medium-dpi" /> 
<head>
<title>$title</title>
<link rel="stylesheet" type="text/css" href="css/hangee.css" />
<script type="text/javascript" src="js/hangee.js"></script>
<body>
<div id="content"><div id="debug" onClick="this.style.display='none'"></div>
<div id="result" onClick="this.style.display='none;"></div>
EOF;
	echo $html;
}

function hangee_popup_footer()
{
	hangee_footer();
}

function connect_db()
{
	global $dbconn;

	$dbconn = mysql_connect("localhost", "sandrain", "359035");
	mysql_select_db("sandrain", $dbconn);
}

function disconnect_db()
{
	global $dbconn;

	mysql_close($dbconn);
}

function mysql_fetch_all($res)
{
	$rows = array();
	while ($row = mysql_fetch_assoc($res))
		$rows[] = $row;

	return $rows;
}

function get_hint($wid)
{
	global $uid;

	$con = mysql_connect('localhost', 'sandrain', '359035');
	mysql_select_db('sandrain', $con);

	$sql = "select hint from hangee_hints where wid=$wid and uid=$uid";
	$res = mysql_query($sql, $con);
	if (!$res)
		die ("query failed: $sql : ".mysql_error($res));

	if (mysql_num_rows($res) > 0) {
		$row = mysql_fetch_array($res);
		return $row['hint'];
	}

	return false;
}

function get_user_grade()
{
	global $uid;

	$con = mysql_connect('localhost', 'sandrain', '359035');
	mysql_select_db('sandrain', $con);

	$sql = "select grade from hangee_users where id=$uid";
	$res = mysql_query($sql, $con);
	$row = mysql_fetch_array($res);
	$grade = $row['grade'];

	return $grade;
}

function get_testpos()
{
	global $uid;

	$con = mysql_connect('localhost', 'sandrain', '359035');
	mysql_select_db('sandrain', $con);

	$sql = "select testpos from hangee_users where id=$uid";
	$res = mysql_query($sql, $con);
	$row = mysql_fetch_array($res);
	$testpos = $row['testpos'];

	return $testpos;
}

function remote_file_exists($url)
{
	return (@fclose(@fopen($url, "r")));
}


/*

following two functions..
Too slow or doesn't work!!!

function url_exists($url)
{
	$url = str_replace("http://", "", $url);
	if (strstr($url, "/")) {
		$url = explode("/", $url, 2);
		$url[1] = "/".$url[1];
	} else {
		$url = array($url, "/");
	}

	$fh = fsockopen($url[0], 80);
	if ($fh) {
		fputs($fh,"GET ".$url[1]." HTTP/1.1\nHost:".$url[0]."\n\n");
		if (fread($fh, 22) == "HTTP/1.1 404 Not Found") { return FALSE; }
		else { return TRUE;    }

	} else { return FALSE;}
}

function url_exists($strURL) {
	$resURL = curl_init();
	curl_setopt($resURL, CURLOPT_URL, $strURL);
	curl_setopt($resURL, CURLOPT_BINARYTRANSFER, 1);
	curl_setopt($resURL, CURLOPT_HEADERFUNCTION, 'curlHeaderCallback');
	curl_setopt($resURL, CURLOPT_FAILONERROR, 1);

	curl_exec ($resURL);

	$intReturnCode = curl_getinfo($resURL, CURLINFO_HTTP_CODE);
	curl_close ($resURL);

	if ($intReturnCode != 200 && $intReturnCode != 302 && $intReturnCode != 304)
		return false;
	else
		return true;
}
*/


function get_marked_count($wid)
{
	global $uid;

	$conn = mysql_connect('localhost', 'sandrain', '359035');
	if (!$conn)
		die ("mysql_connect failed: ". mysql_error($conn));
	mysql_select_db('sandrain', $conn);

	$sql = "select id, count from hangee_marked where uid=$uid and wid=$wid";
	$res = mysql_query($sql, $conn);
	if (!$res)
		die ("query failed : $sql : ".mysql_error($conn));
	$marked_count = 0;
	if (mysql_num_rows($res) > 0) {
		$row = mysql_fetch_array($res);
		$marked_count = $row['count'];
	}

	return $marked_count;
}

function get_user_testpos()
{
	global $uid;
	global $dbconn;

	$sql = "select testpos from hangee_users where id=$uid limit 1";
	$res = mysql_query($sql);
	if (!$res)
		return 0;
	$row = mysql_fetch_array($res);

	return $row['testpos'];
}

?>
