<?php
require_once("__lib.php");

hangee_init_light();

$wid = $_GET['id'];
$word = $_GET['word'];

if (!empty($wid)) {
	$res = mysql_query("select word from hangee_words where id=$wid", $dbconn);
	if (!$res)
		die ("query failed!");

	$row = mysql_fetch_array($res);

	$word = $row['word'];
}

if (!empty($word)) {
	if (strchr($word, "%"))
		$sql = "select word, text from stardict_cobuild where word like '$word'";
	else
		$sql = "select word, text from stardict_cobuild where word='$word' or word like '$word(%)'";

	$res = mysql_query($sql, $dbconn);
	$count = mysql_num_rows($res);
	if (!$count)
		$dict_cobuild = 'Not found';
	else {
		$dict_cobuild = "";
		$i = 1;
		while ($row = mysql_fetch_array($res)) {
			if ($i > 1)
				$dict_cobuild .= "<hr style='width:95%; border: dotted 1px #ffcc00;'>";
			$dict_cobuild .= "<p>".stripslashes($row['text'])."</p>";
			$i++;
		}
	}

	$res = mysql_query("select text from stardict_etymology where word='$word'", $dbconn);
	if (!mysql_num_rows($res))
		$dict_etymology = 'Not found';
	else {
		$row = mysql_fetch_array($res);
		$dict_etymology = stripslashes($row['text']);
	}
}

$dict_cobuild = str_replace('bword://', 'dict.php?word=', $dict_cobuild);

//////////////////////////////////////////////////////////////////////////////////////////

function search_dict($form)
{
	$key = trim($form['key']);
	if (empty($key))
		return;

	$response = new xajaxResponse();

	$togo = "dict.php?word=$key";
	$response->script("document.location.href='$togo'");

	return $response;
}

$xajax = new xajax();
$xajax->registerFunction("search_dict");
$xajax->processRequest();

hangee_popup_header("HanGEE: Dictionary");
$xajax->printJavascript("xajax");

$word = strtolower($word);
$gmp3 = "http://www.gstatic.com/dictionary/static/sounds/de/0/$word.mp3";

?>

<div id='hidden_player'>
<embed id='gsay_player' src='<?=$gmp3?>' type='application/x-mplayer2' autostart='false' loop='false'
	volume='100' hidden='true' enablejavascript='true' />
</div>

<!--// Keyboard shortcut code is from http://www.openjs.com/scripts/events/keyboard_shortcuts/ //-->
<script language='javascript' type='text/javascript' src='js/shortcut.js'></script>
<script language='javascript' type='text/javascript'>
<!--//
shortcut.add("Shift+Alt+D",	function () { self.close(); });
shortcut.add("Shift+Enter",	function () { (document.getElementById('dsform')).key.focus(); });
shortcut.add("Ctrl+Enter",	function () { (document.getElementById('gsay_player')).Play(); });
//-->
</script>


<div id='header'><p style='font-weight:bold;'>Dictionary Search Result of 
	<strong style='color:blue; text-decoration:underline'><?php echo $word;?></strong></p></div>

<div id='dicsearch'>
<form id='dsform' onSubmit='javascript:void(0); return false;'>
<input type='text' name='key' size='40' maxlength='50' value='<?=$word?>'>
<button onClick="xajax_search_dict(xajax.getFormValues('dsform')); return false;">SEARCH</button>
</form>
</div>

<div class='dicheader'>Collins Cobuild (<strong style='color:blue'><?=$count?></strong>)</div>
<div class='dictionary' id='cobuild'><?php echo $dict_cobuild;?></div>
<div class='dicheader'>English Etymology</div>
<div class='dictionary' id='etymology'><?php echo $dict_etymology;?></div>
<!-- <p style='padding:10px;text-align:right'><button onClick='self.close();'>CLOSE</button></p> -->
</p>

<?php
hangee_popup_footer();
hangee_exit();
?>
