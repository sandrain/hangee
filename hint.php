<?php
#
# hint.php
#
# Written by HyoGi Sim <sandrain@gmail.com>
#

require_once("__lib.php");

hangee_init();

function edit_hint_record($form)
{
	global $dbconn;
	global $uid;

	$wid = $form['wid'];
	$hid = $form['hid'];
	$ch = $form['ch'];
	$hint = addslashes(trim($form['hint']));

	$response = new xajaxResponse();

	if ($hid)	// update
		$sql = "update hangee_hints set hint='$hint' where id=$hid";
	else		// insert
		$sql = "insert into hangee_hints (uid, wid, hint) "
			."values ($uid, $wid, '$hint')";

	$res = mysql_query($sql, $dbconn);
	if (!$res) {
		$msg = "query failed : $sql : ". mysql_error($dbconn);
		$response->assign("result", "innerHTML", $msg);
		$response->assign("result", "style.display", "block");
		return $response;
	}

	$response->script("close_and_reset_parent()");
	return $response;
}

function remove_hint_record($form)
{
	global $dbconn;

	$response = new xajaxResponse();

	$hid = $form['hid'];
	$ch = $form['ch'];

	$sql = "delete from hangee_hints where id=$hid";
	$res = mysql_query($sql, $dbconn);
	if (!$res) {
		$msg = "query failed : $sql : ". mysql_error($dbconn);
		$response->assign("result", "innerHTML", $hid);
		$response->assign("result", "style.display", "block");
		return $response;
	}

	$response->script("close_and_reset_parent()");
	return $response;
}

if (!isset($_GET['id'])) {
	echo "<script language='javascript' type='text/javascript'><!--//\n"
		."alert(\"Invalid access!\");\n"
		."//--></script>";
	die ("Invalid access!");
}
$wid = $_GET['id'];

$sql = "select word from hangee_words where id=$wid";
$res = mysql_query($sql, $dbconn);
if (!$res)
	die ("query failed : $sql : ".mysql_error($dbconn));
$row = mysql_fetch_array($res);
$word = $row['word'];

$sql = "select id, hint from hangee_hints where uid=$uid and wid=$wid";
$res = mysql_query($sql, $dbconn);
if (!$res)
	die ("query failed : $sql : ".mysql_error($dbconn));

$hid = 0;
if (mysql_num_rows($res) > 0) {
	$row = mysql_fetch_array($res);
	$hint = htmlspecialchars(stripslashes($row['hint']));
	$hid = $row['id'];
	$op = "update";
}
else {
	$hint = '';	// No hint yet
	$op = "insert";
}

$xajax = new xajax();
$xajax->registerFunction("edit_hint_record");
$xajax->registerFunction("remove_hint_record");
$xajax->processRequest();

hangee_popup_header("HanGEE: Word record edit");
$xajax->printJavascript("xajax");
?>

<script language="javascript" type="text/javascript">
<!--//

function close_and_reset_parent()
{
	//window.opener.location.reload();
	if (typeof(window.opener.xajax_show_hint) == 'function')
		window.opener.xajax_show_hint();

	if (typeof(window.opener.reload_parent) == 'function')
		window.opener.reload_parent();
	else
		window.opener.location.reload();

	window.opener.focus();
	self.close();
}

function confirm_remove_hint()
{
	msg = "Are you positive to remove this hint?"

	if (confirm(msg))
		xajax_remove_hint_record(xajax.getFormValues('hint_form'));
	return;
}

//-->
</script>

<div id="header"><strong>HanGEE: Word Hint Editor</strong></div>
<div id="word_browser" style="display:block">

<p style='font-size:12pt; font-weight:bold'><strong><?php echo strtoupper($word);?></strong></p>

<p>Please enter or modify the hint string for this word.</p>

<form id="hint_form" onSubmit="javascript:void(0); return false;">
<input type="hidden" name="wid" value="<?php echo $wid;?>" />
<input type="hidden" name="hid" value="<?php echo $hid;?>" />
<input type="hidden" name="ch" value="<?php echo substr($word, 0, 1);?>" />
<!-- <input type="text" name="hint" size="50" maxlength="200" value="<?php echo $hint;?>"> -->
<textarea name='hint' class='dictext'><?php echo $hint;?></textarea>

<p><button onClick="xajax_edit_hint_record(xajax.getFormValues('hint_form'));">Edit</button>


<p>Or, </p>

<?php if ($hid) { ?>
<button onClick="confirm_remove_hint();">Remove Hint</button>
<?php } ?>
<button onClick="self.close();">Cancel</button></p>


</form>

<script language='javascript' type='text/javascript'>
<!--//
(document.getElementById('hint_form')).hint.focus();
//-->
</script>

</div>

<?php
hangee_popup_footer();
hangee_exit();
?>
