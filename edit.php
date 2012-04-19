<?php
#
# edit.php
#
# Written by HyoGi Sim <sandrain@gmail.com>
#

require_once("__lib.php");

hangee_init();

function edit_word_record($form)
{
	global $dbconn;
	global $uid;

	$response = new xajaxResponse();

	$wid = $form['wid'];
	$word = trim($form['word']);
	$page = trim($form['page']);
	$sense = addslashes(trim($form['sense']));
	$dictionary = addslashes(trim($form['dictionary']));

	// TODO! check the integrity of data

	$sql = "update hangee_words set "
		."word='$word', page=$page, sense='$sense' "
		."where id=$wid";

	$res = mysql_query($sql, $dbconn);
	if (!$res) {
		$msg = "query failed: $sql : ". mysql_error($dbconn);
		$response->assign("result", "innerHTML", $msg);
		$response->assign("result", "style.display", "block");
		return $response;
	}

	$response->script("close_and_reload_parent()");
	return $response;
}

if (!isset($_GET['id'])) {
	echo "<script language='javascript' type='text/javascript'><!--//\n"
		."alert(\"Invalid access!\");\n"
		."//--></script>";
	die ("Invalid access!");
}

$grade = get_user_grade();
if ($grade != '0' && $grade != '1')
	die ("You do not have permission to edit the word record!");

$wid = $_GET['id'];

$sql = "select * from hangee_words where id=$wid";
$res = mysql_query($sql, $dbconn);
if (!$res)
	die ("query failed: $sql :".mysql_error($dbconn));
$row = mysql_fetch_array($res);
$id = $row['id'];
$page = $row['page'];
$word = $row['word'];
$sense = htmlspecialchars(stripslashes($row['sense']));
$dict = htmlspecialchars(stripslashes($row['dictionary']));

$xajax = new xajax();
$xajax->registerFunction("edit_word_record");
$xajax->processRequest();

hangee_popup_header("HanGEE: Word record edit");
$xajax->printJavascript("xajax");
?>

<script language="javascript" type="text/javascript">
<!--//

function strpos(haystack, needle, offset)
{
	var i = (haystack+'').indexOf(needle, (offset || 0));
	return i == -1 ? false : i;
}

function close_and_reload_parent(ch)
{
	if (typeof(window.opener.reload_parent) == 'function')
		window.opener.reload_parent();
	else
		window.opener.location.reload();

	window.opener.focus();
	self.close();
}

//-->
</script>

<div id="header"><strong>HanGEE: Word Record Editor</strong></div>
<div id="word_browser" style="display:block">

<form id="edit_form" onSubmit="javascript:void(0); return false;">
<input type="hidden" name="wid" value="<?php echo $wid;?>" />
<p><label for="word">Word</label>
	<input type="text" name="word" size="20" maxlength="20" value="<?php echo $word;?>"/></p>
<p><label for="page">Page</label>
	<input type="text" name="page" size="10" maxlength="10" value="<?php echo $page;?>"/></p>
<p><label for="sense">Sense</label>
	<input type="text" name="sense" size="50" maxlength="255" value="<?php echo $sense;?>"/></p>


<p><button onClick="xajax_edit_word_record(xajax.getFormValues('edit_form'));">Edit</button>
<button onClick="self.close();">Cancel</button></p>
</form>

</div>

<script language='javascript' type='text/javascript'>
<!--//
(document.getElementById('edit_form')).sense.focus();
//->
</script>

<?
hangee_popup_footer();
hangee_exit();
?>
