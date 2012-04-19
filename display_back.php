<?php
#
# display.php
#
# Written by HyoGi Sim <sandrain@gmail.com>
#

require_once("__lib.php");

hangee_init(1);

###########################################################################

function move_to_previous()
{
	global $maxseq;
	global $testpos;

	if ($testpos == 1)
		return;

	$_SESSION['testpos'] = $testpos - 1;

	$response = new xajaxResponse();
	$response->script("document.location.reload()");

	return $response;
}

function move_to_next()
{
	global $maxseq;
	global $testpos;

	if ($testpos == $maxseq)
		return;

	$_SESSION['testpos'] = $testpos + 1;

	$response = new xajaxResponse();
	$response->script("document.location.reload()");

	return $response;
}

function move_to_first()
{
	global $testpos;

	if ($testpos == 1)
		return;

	$_SESSION['testpos'] = 1;

	$response = new xajaxResponse();
	$response->script("document.location.reload()");

	return $response;
}

function move_to_last()
{
	global $maxseq;

	if ($testpos == $maxseq)
		return;

	$_SESSION['testpos'] = $maxseq;

	$response = new xajaxResponse();
	$response->script("document.location.reload()");

	return $response;
}

function save_session()
{
	global $dbconn;
	global $uid;

	$response = new xajaxResponse();

	$testpos = $_SESSION['testpos'];

	$sql = "update hangee_users set testpos=$testpos where id=$uid";
	$res = mysql_query($sql);
	if (!$res) {
		$msg = "query failed : $sql : ". mysql_error($dbconn);
		$response->assign("result", "innerHTML", $msg);
		$response->assign("result", "style.display", "block");
		return $response;
	}

	$response->script("alert(\"Current status have been saved!\")");
	return $response;
}

function mark_current()
{
	global $dbconn;
	global $uid;
	global $wid;
	global $mid;
	global $marked_count;

	$response = new xajaxResponse();

	if ($marked_count == 0) {
		$sql = "insert into hangee_marked (uid, wid, count) values ($uid, $wid, 1)";
		$count = 1;
	}
	else {
		$count = $marked_count + 1;
		$sql = "update hangee_marked set count=$count where id=$mid";
	}

	$res = mysql_query($sql, $dbconn);
	if (!$res) {
		$msg = "query failed : $sql : ". mysql_error($dbconn);
		$response->assign("result", "innerHTML", $msg);
		$response->assign("result", "style.display", "block");
		return $response;
	}

	for ($i = 0; $i < min($count, 5); $i++)
		$marked .= "*";

	$response->assign("test_mark", "innerHTML", $marked);
	return $response;
}

function unmark_current()
{
	global $dbconn;
	global $mid;

	$response = new xajaxResponse();

	$sql = "delete from hangee_marked where id=$mid";

	$res = mysql_query($sql, $dbconn);
	if (!$res) {
		$msg = "query failed : $sql : ". mysql_error($dbconn);
		$response->assign("result", "innerHTML", $msg);
		$response->assign("result", "style.display", "block");
		return $response;
	}

	$response->assign("test_mark", "innerHTML", "");
	return $response;
}


###########################################################################

if (!isset($_SESSION['grade']))
	Header ('Location: logout.php');
$grade = $_SESSION['grade'];

if (!isset($_SESSION['testcrc']))
	Header ('Location: tester.php');
$testcrc = $_SESSION['testcrc'];
$testpos = isset($_SESSION['testpos']) ? $_SESSION['testpos'] : get_testpos();

// Number of words to be tested
if (!isset($_SESSION['maxseq'])) {
	$sql = "select max(seq) from hangee_test where uid=$uid and testcrc=$testcrc";
	$res = mysql_query($sql, $dbconn);
	if (!$res)
		die ("query failed : $sql : ".mysql_error($dbconn));
	$row = mysql_fetch_array($res);
	$maxseq = $row['0'];
	$_SESSION['maxseq'] = $maxseq;
}
else
	$maxseq = $_SESSION['maxseq'];
$index = "$testpos / $maxseq";


// auto save session;;

$sql = "update hangee_users set testpos=$testpos where id=$uid";
$res = mysql_query($sql, $dbconn);
if (!$res)
	die ("query failed : $sql : ".mysql_error($dbconn));

/////////////////////////////////////////////////////////////////////////////////

/*
$sql = "select hangee_words.id as wid, word, sense, hangee_hints.id as hid, hint, hangee_marked.id as mid, count "
."from hangee_words, hangee_hints, hangee_marked "
."where hangee_words.id = hangee_hints.wid and hangee_hints.uid = $uid "
."and hangee_words.id = hangee_marked.wid "
."and hangee_words.id in (select wid from hangee_test where uid=$uid and seq=$testpos and testcrc=$testcrc)";

$res = mysql_query($sql, $dbconn);
if (!$res)
	die ("query failed : $sql : ".mysql_error($dbconn));
$row = mysql_fetch_array($res);

$wid = $row['id'];
$word = $row['word'];
$sense = $row['sense'];
$hid = $row['hid'];
$hint = $row['hint'];
$mid = $row['mid'];
$marked_count = $row['count'];
*/


/////////////////////////////////////////////////////////////////////////////////

// Read word information from test table
$sql = "select * from hangee_words where id in "
	."(select wid from hangee_test where uid=$uid and seq=$testpos and testcrc=$testcrc)";
$res = mysql_query($sql, $dbconn);
if (!$res)
	die ("query failed : $sql : ".mysql_error($dbconn));
$row = mysql_fetch_array($res);
$wid = $row['id'];
$word = $row['word'];
$sense = htmlspecialchars(stripslashes($row['sense']));

//$sense = preg_replace('(a\.|v\.|vi\.|n\.|ad\.|pre\.)', '<br>${1}', $sense);
$pattern = '/((a|v|vi|n|ad|pre)\.)/';
$replace = '<br>$1';
$sense = preg_replace($pattern, $replace, $sense);
$sense = substr($sense, 4); // delete leading <br>

// Read the hint string
$sql = "select id, hint from hangee_hints where uid=$uid and wid=$wid";
$res = mysql_query($sql, $dbconn);
if (!$res)
	die ("query failed : $sql : ".mysql_error($dbconn));

$hidelink = "<a href=\"javascript:hide_div('test_hint');\">Hide</a>";
if (mysql_num_rows($res) > 0) {
	$row = mysql_fetch_array($res);
	$hid = $row['id'];
	$editlink = "<a href=\"javascript:openPopup('hint.php?id=$wid', 'hintwin');\">Edit</a>";
	$hint = "<p id='hint_text'>".htmlspecialchars(stripslashes($row['hint']))."</p><p> $editlink or $hidelink</p>";
}
else {
	$hid = null;
	$hintlink = "<a href=\"javascript:openPopup('hint.php?id=$wid', 'hintwin');\">Enter a new hint</a>";
	$hidelink = "<a href=\"javascript:hide_div('test_hint');\">Hide</a>";
	$hint = "<p>No hint for this word. $hintlink or $hidelink</p>";
}

// Read if marked
$sql = "select id, count from hangee_marked where uid=$uid and wid=$wid";
$res = mysql_query($sql, $dbconn);
if (!$res)
	die ("query failed : $sql : ".mysql_error($dbconn));
$mid = 0;
$marked_count = 0;
if (mysql_num_rows($res) > 0) {
	$row = mysql_fetch_array($res);
	$mid = $row['id'];
	$marked_count = $row['count'];
}

$marked = '';
for ($i = 0; $i < min($marked_count, 5); $i++)
	$marked .= "*";

// pronounce data (using google)
/*
$mp3 = "gsay/".substr($word,0,1)."/$word.mp3";
$mp3_exists = file_exists($mp3);
*/

$gmp3 = "http://www.gstatic.com/dictionary/static/sounds/de/0/$word.mp3";
$gmp3_exists = url_exists($gmp3);

###########################################################################

$xajax = new xajax();
$xajax->registerFunction("move_to_previous");
$xajax->registerFunction("move_to_next");
$xajax->registerFunction("move_to_first");
$xajax->registerFunction("move_to_last");
$xajax->registerFunction("save_session");
$xajax->registerFunction("mark_current");
$xajax->registerFunction("unmark_current");

$xajax->processRequest();

hangee_header("HanGEE: Word Test");
$xajax->printJavascript("xajax");
?>

<!--// Keyboard shortcut code is from http://www.openjs.com/scripts/events/keyboard_shortcuts/ //-->
<script language='javascript' type='text/javascript' src='js/shortcut.js'></script>

<script language='javascript' type='text/javascript'>
<!--//

var current_case = 0; // uppercase

document.body.bgColor = '#dddddd';

function swap_case()
{
	div = document.getElementById('test_word');
	text = div.innerHTML;

	if (current_case == 0) {
		div.innerHTML = text.toLowerCase();
		current_case = 1;
	}
	else {
		div.innerHTML = text.toUpperCase();
		current_case = 0;
	}
}


function show_div(name)
{
	(document.getElementById(name)).style.display = 'block';
}

function hide_div(name)
{
	(document.getElementById(name)).style.display = 'none';
}

function pronounce()
{
	div = document.getElementById('hidden_player');
	div.innerHTML = "<embed src='<?php echo $gmp3;?>' type='application/x-mplayer2' autostart='true' loop='false' volume='100' hidden='true' />";
	return true;
}

function resetHint(hint)
{
	div = document.getElementById('test_hint');
	html = '';


	if (hint) {
		html = "<p id='hint_text'>" + hint + "</p>";
		html+= "<p><a href=\"javascript:hide_div('teste_hint');\">Hide</a>";
		html+= " or <a href=\"javascript:openPopup('hint.php?id=<?php echo $wid;?>', 'hintwin');\">Edit</a>";
	}
	else {
		html = "<p><a href=\"javascript:openPopup('hint.php?id=<?php echo $wid;?>', 'hintwin');\">";
		html+= "Enter a new hint</a> or ";
		html+= "<a href=\"javascript:hide_div('test_hint');\">Hide</a></p>";
	}

	div.innerHTML = html;
	div.style.display = 'block';
}


/** keyboard shortcuts **/

shortcut.add("P", function () { xajax_move_to_previous(); });
shortcut.add("N", function () { xajax_move_to_next(); });
shortcut.add("0", function () { xajax_move_to_first(); });
shortcut.add("9", function () { xajax_move_to_last(); });

shortcut.add("Enter", function () { pronounce(); });

shortcut.add("A", function () { show_div('test_sense'); });
shortcut.add("Shift+A", function () { hide_div('test_sense'); });

shortcut.add("H", function () { show_div('test_hint'); });
shortcut.add("Shift+H", function () { hide_div('test_hint'); });
shortcut.add("Ctrl+Shift+H", function () { openPopup('hint.php?id=<?php echo $wid;?>', 'hintwin'); });

shortcut.add("M", function () { xajax_mark_current(); });
shortcut.add("Shift+M", function () { xajax_unmark_current(); });

shortcut.add("E", function () { openPopup('edit.php?id=<?php echo $wid;?>', 'editwin'); });

shortcut.add("U", function () { swap_case(); });

/* shortcut.add("Shift+S", function () { xajax_save_session(); }); */

//-->
</script>


<table id="test_navigation">
<tr>
<td align='left'>
  <a href='javascript:void(0)' onClick='javascript:xajax_move_to_previous(); return false;'>Previous</a> |
  <a href='javascript:void(0)' onClick='javascript:xajax_move_to_first(); return false;'>First</a></td>
<td align='center'>
  <?php if ($gmp3_exists) { ?>
    <a href='javascript:void(0)' onClick='javascript:pronounce(); return false;'>Pronounce</a> |
  <?php } else { ?>
    <font color="#666666">Pronounce</font> |
  <?php } ?>
  <a href='javascript:void(0)' onClick='javascript:show_div("test_sense"); return false;'>Sense</a> |
  <a href='javascript:void(0)' onClick='javascript:show_div("test_hint"); return false;'>Hint</a> |
  <a href='javascript:void(0)' onClick='javascript:swap_case(); return false;'>Swap case</a> |
  <?php if ($grade == '0' || $grade == '1') {?>
    <a href="javascript:openPopup('edit.php?id=<?php echo $wid;?>', 'editwin');">Edit</a> |
  <?php } ?>
  <a href='javascript:void(0)' onClick='javascript:xajax_mark_current(); return false;'>Mark</a> |
  <?php if ($mid) { ?>
    <a href='javascript:void(0)' onClick='javascript:xajax_unmark_current(); return false;'>Unmark</a> |
  <?php } else { ?>
    <font color='#666666'>Unmark</font> |
  <?php } ?>
  <?php //<a href='javascript:void(0)' onClick='javascript:xajax_save_session(); return false;'>Save</a> | ?>
  <a href='javascript:void(0)' 
    onClick='javascript:openPopup("help.php", "helpwin"); return false;'><?php echo htmlspecialchars("?");?></a>
  </td>
<td align='right'>
  <a href='javascript:void(0)' onClick='javascript:xajax_move_to_last(); return false;'>Last</a> |
  <a href='javascript:void(0)' onClick='javascript:xajax_move_to_next(); return false;'>Next</a></td>
</tr>
</table>

<div id="hidden_player"></div>

<div id="test_hint"><?php echo $hint; ?></div>

<table id="answer_table">
<tr>
<td valign='top' width='90%'><div id="test_sense" onClick="this.style.display='none'"><?php echo $sense;?></div></td>
<td valign='top' width='10%'><div id="test_index"><?php echo $index;?></div></td>
</tr>
</table>

<div id="test_word">
  <a href='javascript:void(0)' onClick='javascript:xajax_move_to_next(); return false;'><?php echo strtoupper($word);?></a>
  </div>
<div id="test_mark"><?php echo $marked;?></div>

<?php
hangee_footer();
hangee_exit();
?>
