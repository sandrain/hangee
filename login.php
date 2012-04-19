<?php
#
# login.php
#
# Written by HyoGi Sim <sandrain@gmail.com>
#

require_once("__lib.php");

function hangee_login($form)
{
	global $dbconn;

	$email = trim($form['email']);
	$password = $form['password'];
	$aip = $_SERVER['REMOTE_ADDR'];
	$atime = time();

	$response = new xajaxResponse();

	connect_db();
	$sql = "select id, email, password from hangee_users where email='$email'";
	if (!($res = mysql_query($sql, $dbconn))) {
		$response->assign("debug", "innerHTML", "query failed: $sql".mysql_error($dbconn));
		$response->assign("debug", "style.display", "block");
		return $response;
	}

	if (!mysql_num_rows($res)) {
		$msg = "Log in failed. Please check your email address.";
		$response->assign("result", "innerHTML", $msg);
		$response->assign("result", "style.display", "block");
		return $response;
	}

	$row = mysql_fetch_array($res);
	$db_id = $row['id'];
	$db_password = $row['password'];

	if (md5($password) != $db_password) {
		$msg = "Log in failed. Please check your password.";
		$response->assign("result", "innerHTML", $msg);
		$response->assign("result", "style.display", "block");
		return $response;
	}

	$sql = "update hangee_users set aip='$aip', atime=$atime where id=$db_id";
	if (!($res = mysql_query($sql, $dbconn))) {
		$response->assign("debug", "innerHTML", "query failed: $sql".mysql_error($dbconn));
		$response->assign("debug", "style.display", "block");
		return $response;
	}

	disconnect_db();

	$getdata = "t=$db_id&tt=$db_password";

	$response->script("document.location.href='session.php?$getdata'");

	return $response;
}

$xajax = new xajax();
$xajax->registerFunction("hangee_login");
$xajax->processRequest();

hangee_header("HanGEE Login");
$xajax->printJavascript("xajax");
?>

<div id="login">
<form id="login_form" onSubmit="javascript:void(0); return false;">
<p><label for="email">Email:</label> <input type="text" id='email' name="email" size="20" maxlength="100" />
<label for="password">Password:</label> <input type="password" name="password" size="20" maxlength="30" />
<button onClick="xajax_hangee_login(xajax.getFormValues('login_form'));">OK</button></p>
</form>
</div>

<script language='javascript' type='text/javascript'>
<!--//
(document.getElementById('email')).focus();
//-->
</script>



<?php
hangee_footer();
?>
