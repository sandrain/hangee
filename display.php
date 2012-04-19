<?php
#
# display.php
#
# Written by HyoGi Sim <sandrain@gmail.com>
#

require_once("__lib.php");

hangee_init(1);

//$timecheck = array();

//$timecheck[] = time(); // [0]

###########################################################################

function move_to_previous()
{
	global $maxseq;
	global $testpos;

	$response = new xajaxResponse();
	if ($testpos == 1) {
		$response->alert("You're on the first page.");
		return $response;
	}

	$_SESSION['testpos'] = $testpos - 1;

	$response->script("document.location.reload()");

	return $response;
}

function move_to_next()
{
	global $maxseq;
	global $testpos;

	$response = new xajaxResponse();
	if ($testpos == $maxseq) {
		$response->alert("You're on the last page.");
		return $response;
	}

	$_SESSION['testpos'] = $testpos + 1;

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

function move_to($pos)
{
	global $maxseq;

	$response = new xajaxResponse();
	if ($pos > $maxseq) {
		$response->script("alert(\"$pos is out of range!\")");
		return $response;
	}

	$_SESSION['testpos'] = $pos;
	$response->script("document.location.reload()");
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
		$count = min($marked_count + 1, 5);
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

	if (!$mid)
		return;

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

//$timecheck[] = time(); // [1]

if (!isset($_SESSION['grade']))
	Header ('Location: logout.php');
$grade = $_SESSION['grade'];

if (!isset($_SESSION['testcrc']))
	Header ('Location: tester.php');
$testcrc = $_SESSION['testcrc'];
$testpos = isset($_SESSION['testpos']) ? $_SESSION['testpos'] : get_testpos();
if ($testpos == 0)
	$testpos = 1;

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
$percent = round((100.0 * $testpos) / $maxseq, 2);

// auto save session;;
//$timecheck[] = time(); // [2]

$sql = "update hangee_users set testpos=$testpos where id=$uid";
$res = mysql_query($sql, $dbconn);
if (!$res)
	die ("query failed : $sql : ".mysql_error($dbconn));

/////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////

//$timecheck[] = time(); // [3]

// Read word information from test table
$sql = "select * from hangee_words where id = "
	."(select wid from hangee_test where uid=$uid and seq=$testpos and testcrc=$testcrc)";
$res = mysql_query($sql, $dbconn);
if (!$res)
	die ("query failed : $sql : ".mysql_error($dbconn));
$row = mysql_fetch_array($res);
$wid = $row['id'];
$word = $row['word'];
$sense = htmlspecialchars(stripslashes($row['sense']));

$pattern = '/((a|v|vi|n|ad|pre)\.)/';
$replace = '<br>$1';

if ($row['page'] < 300) {
	$sense = preg_replace($pattern, $replace, $sense);
	$sense = substr($sense, 4); // delete leading <br>
}

//$timecheck[] = time(); // [4]

// Read if marked
$sql = "select id, count from hangee_marked where uid=$uid and wid=$wid";
$res = mysql_query($sql, $dbconn);
if (!$res) {
	die ("$testpos : $word : query failed : $sql : ".mysql_error($dbconn));
}
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

//$timecheck[] = time(); // [5]

// pronounce data (using google)
$gmp3 = "http://www.gstatic.com/dictionary/static/sounds/de/0/$word.mp3";
// skip this checking logic, sometime it takes too much time.
//$gmp3_exists = url_exists($gmp3);
$gmp3_exists = True;

//$timecheck[] = time(); // [6]


###########################################################################

function show_hint()
{
	global $dbconn;
	global $wid;
	global $uid;
	$response = new xajaxResponse();

	$sql = "select hint from hangee_hints where wid=$wid and uid=$uid";
	$res = mysql_query($sql, $dbconn);
	if (!$res) {
		$msg = "query failed: $sql: ". mysql_error($dbconn);
		$response->assign("result", "innerHTML", $msg);
		$response->assign("result", "style.display", "block");
		return $response;
	}

	$hidelink = "<a href=\"javascript:hide_div('test_hint');\">Hide</a>.";
	if (mysql_num_rows($res) > 0) {
		$row = mysql_fetch_array($res);
		$editlink = "<a href=\"javascript:openPopup('hint.php?id=$wid', 'hintwin');\">Edit</a>";
		$hint = nl2br(htmlspecialchars(stripslashes($row['hint'])));
		//$hint = preg_replace('/(\&#58;|\&#60;|\&#62;|\&lt;|\&gt;)/', "<font color='blue'>&#58</font>", $hint);
		$hint = "<p id='hint_text'>$hint</p><p> $editlink or $hidelink</p>";
	}
	else {
		$hintlink = "<a href=\"javascript:openPopup('hint.php?id=$wid', 'hintwin');\">Enter a new hint</a>";
		$hint = "<p>No hint for this word. $hintlink or $hidelink</p>";
	}

	$response->assign("test_hint", "innerHTML", $hint);
	$response->assign("test_hint", "style.display", "block");

	return $response;
}

$xajax = new xajax();
$xajax->registerFunction("move_to_previous");
$xajax->registerFunction("move_to_next");
$xajax->registerFunction("move_to_first");
$xajax->registerFunction("move_to_last");
$xajax->registerFunction("move_to");
$xajax->registerFunction("mark_current");
$xajax->registerFunction("unmark_current");
$xajax->registerFunction("show_hint");
$xajax->registerFunction("pronounce");

$xajax->processRequest();

hangee_header("HanGEE: Word Test");
$xajax->printJavascript("xajax");

//$timecheck[] = time(); // [7]
?>

<!--// Keyboard shortcut code is from http://www.openjs.com/scripts/events/keyboard_shortcuts/ //-->
<script language='javascript' type='text/javascript' src='js/shortcut.js'></script>

<script language='javascript' type='text/javascript'>
<!--//

document.body.bgColor = '#dddddd';

// uppercase
var current_case = 0;

function swap_case()
{
	var div = document.getElementById('test_word');
	var text = div.innerHTML;

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

var current_sense = 0;	// Hide

function toggle_sense()
{
	if (current_sense) {
		hide_div('test_sense');
		current_sense = 0;
	}
	else {
		show_div('test_sense');
		current_sense = 1;
	}
}

var current_hint = 0;	// Hide

function toggle_hint()
{
	if (current_hint) {
		hide_div('test_hint');
		current_hint = 0;
	}
	else {
		xajax_show_hint();
		current_hint = 1;
	}
}

function pronounce()
{
	/*
	div = document.getElementById('hidden_player');
	div.innerHTML = "<embed src='<?php echo $gmp3;?>' type='application/x-mplayer2' autostart='true' loop='false' volume='100' hidden='true' />";
	return true;
	*/
	var gsay = document.getElementById('gsay_player');
	if (gsay) {
		gsay.Play();
	}
}

function resetHint(hint)
{
	var div = document.getElementById('test_hint');
	var html = '';


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

function move_to()
{
	var pos = prompt("Input the number that you like to move to: ");
	xajax_move_to(parseInt(pos));
}

function show_dictionary()
{
	openPopup('dict.php?id=<?php echo $wid;?>', 'dictwin');
	return false;
}

/** keyboard shortcuts **/

shortcut.add("P", function () { xajax_move_to_previous(); });
shortcut.add("N", function () { xajax_move_to_next(); });
shortcut.add("0", function () { xajax_move_to_first(); });
shortcut.add("9", function () { xajax_move_to_last(); });
shortcut.add("G", function () { move_to(); });

shortcut.add("Space", function () { pronounce(); });

shortcut.add("A", function () { toggle_sense(); });
shortcut.add("H", function () { toggle_hint(); });

shortcut.add("D", function () { show_dictionary(); });
shortcut.add("Shift+A", function () { openPopup('edit.php?id=<?php echo $wid;?>', 'editwin'); });
shortcut.add("Shift+H", function () { openPopup('hint.php?id=<?php echo $wid;?>', 'hintwin'); });

shortcut.add("M", function () { xajax_mark_current(); });
shortcut.add("Shift+M", function () { xajax_unmark_current(); });


shortcut.add("U", function () { swap_case(); });

// Set title with testpos
document.title += '<?php echo " :: ".strtoupper($word)." ($testpos/$maxseq = $percent %)";?>';

//-->
</script>


<table id="test_navigation">
<tr>
<td align='left'>
  <a href='javascript:void(0)' onClick='javascript:xajax_move_to_previous(); return false;'>Previous</a> |
  <a href='javascript:void(0)' onClick='javascript:xajax_move_to_first(); return false;'>First</a></td>
<td align='center'>
<?php
//echo "<a href='javascript:void(0)' onClick='javascript:pronounce(); return false;'>Pronounce</a> |";
if ($gmp3_exists) { 
	echo "<a href='javascript:void(0)' onClick='javascript:pronounce(); return false;'>Pronounce</a> |";
}
else {
	echo "<font color='#666666'>Pronounce</font> |";
}
?>
  <a href='javascript:void(0)' onClick='javascript:show_div("test_sense"); return false;'>Sense</a> |
  <a href='javascript:void(0)' onClick="javascript:show_dictionary(); return false;">Dictionary</a> |
  <a href='javascript:void(0)' onClick='javascript:xajax_show_hint(); return false;'>Hint</a> |
  <a href='javascript:void(0)' onClick='javascript:swap_case(); return false;'>Swap case</a> |
  <?php if ($grade == '0' || $grade == '1') {?>
    <a href="javascript:openPopup('edit.php?id=<?php echo $wid;?>', 'editwin');">Edit</a> |
  <?php } ?>
  <a href='javascript:void(0)' onClick='javascript:xajax_mark_current(); return false;'>Mark</a> |
  <a href='javascript:void(0)' onClick='javascript:xajax_unmark_current(); return false;'>Unmark</a> |
  <?php //<a href='javascript:void(0)' onClick='javascript:xajax_save_session(); return false;'>Save</a> | ?>
  <a href='javascript:void(0)' onClick='javascript:move_to(); return false;'>Go To</a>
  </td>
<td align='right'>
  <a href='javascript:void(0)' onClick='javascript:xajax_move_to_last(); return false;'>Last</a> |
  <a href='javascript:void(0)' onClick='javascript:xajax_move_to_next(); return false;'>Next</a></td>
</tr>
</table>

<div id="hidden_player"><?php /**/
if ($gmp3_exists)
	echo "<embed id='gsay_player' src='$gmp3' type='application/x-mplayer2' "
		."autostart='false' loop='false' volume='100' hidden='true' "
		//."onerror='alert(\"sound file not exist!\")' "
		."enablejavascript='true' />";
?></div>
<div id="test_hint"></div>

<table id="answer_table">
<tr>
<td valign='top' width='80%'><div id="test_sense" onClick="this.style.display='none'"><?php echo $sense;?></div></td>
<td valign='top' width='20%'><div id="test_index"><?php echo $index;?></div></td>
</tr>
</table>

<div id="test_word">
  <a href='javascript:void(0)' onClick='javascript:xajax_move_to_next(); return false;'><?php echo strtoupper($word);?></a>
  </div>
<div id="test_mark"><?php echo $marked;?></div>

<?php
//$timecheck[] = time(); // [8]
//echo "<!--";
//print_r ($timecheck);
//echo "-->";
hangee_footer();
hangee_exit();
?>
