<?php
#
# sheets.php
#
# Written by HyoGi Sim <sandrain@gmail.com>
#

require_once("__lib.php");

hangee_init();

function mark_word($wid)
{
	global $dbconn;
	global $uid;

	$response = new xajaxResponse();

	$count = min(get_marked_count($wid) + 1, 5);
	$sql = ($count == 1) ?	// First insert?
		"insert into hangee_marked (uid, wid, count) values ($uid, $wid, $count)" :
		"update hangee_marked set count = $count where uid=$uid and wid=$wid";

	$res = mysql_query($sql, $dbconn);
	if (!$res) {
		$msg = "query failed : $sql : ". mysql_error($dbconn);
		$response->assign("result", "innerHTML", $msg);
		$response->assign("result", "style.display", "block");
		return $response;
	}

	$response->script("document.location.reload();");
	return $response;
}

function unmark_word($wid, $mcount)
{
	global $dbconn;
	global $uid;

	$response = new xajaxResponse();

	$mcount--;

	if ($mcount)
		$sql = "update hangee_marked set count=$mcount where wid=$wid and uid=$uid";
	else
		$sql = "delete from hangee_marked where wid=$wid and uid=$uid";

	$res = mysql_query($sql, $dbconn);
	if (!$res) {
		$msg = "query failed : $sql : ". mysql_error($dbconn);
		$response->assign("result", "innerHTML", $msg);
		$response->assign("result", "style.display", "block");
		return $response;
	}

	$response->script("document.location.reload();");
	return $response;
}

function show_hint($wid)
{
	$hint = get_hint($wid);
	if ($hint)
		$hint = htmlspecialchars(stripslashes($hint));
	else
		$hint = "No hint!";

	$response = new xajaxResponse();
	$response->assign("result", "innerHTML", $hint);
	$response->assign("result", "style.display", "block");

	return $response;
}

$xajax = new xajax();
$xajax->registerFunction("mark_word");
$xajax->registerFunction("unmark_word");
$xajax->registerFunction("show_hint");
$xajax->processRequest();

hangee_header("HanGEE: Word Browsing");

$xajax->printJavascript("xajax");


#########################################################################

$words_per_page = 20;

if (isset($_GET['page'])) {
	$page = $_GET['page'];
}
else {
	$pos = get_user_testpos();
	$page = $pos == 0 ? 1 : ceil($pos / 20);
}

$sql = "select count(*) from hangee_test where uid=$uid";
$res = mysql_query($sql, $dbconn);
if (!$res)
	die ("query failed: $sql : ".mysql_error($dbconn));
$row = mysql_fetch_array($res);
$count = $row[0];

if ($count == 0) {
	echo "<div id='word_sheet'>No test is prepared. You should set the test on "
		."the <a href='tester.php'>Tester page</a>.</div>";
	hangee_footer();
	hangee_exit();
	die();
}

$max_page = ceil($count / $words_per_page);
if ($page > $max_page)
	$page = $max_page;


?>

<!--// Keyboard shortcut code is from http://www.openjs.com/scripts/events/keyboard_shortcuts/ //-->
<script language='javascript' type='text/javascript' src='js/shortcut.js'></script>

<script language='javascript' type='text/javascript'>
<!--//

var opened = 0;
var hint = 0;
var hint_sense = '';
var page = <?php echo $page;?>;
var maxPage = <?php echo $max_page;?>;

function showHint(wid, sense)
{
	opened = 0;
	hint = wid;
	hint_sense = sense;
	xajax_show_hint(wid);
}

function showSense(sense, wid)
{
	res = document.getElementById('result');
	link = "<div style='padding:5px'><a href='javascript:void(0);' onClick='xajax_mark_word("
		+ wid + "); return false;'>Mark</a> "
		+ "or <a href='javascript:void(0);' onClick='hideSense(); return false;'>"
		+ "Hide</a></div>";
	/*
	res = document.getElementById(wid + '');
	link = '';
	*/

	res.innerHTML = sense + link;
	res.style.display = 'block';

	opened = wid;
	hint = 0;
}

function hideSense()
{
	res = document.getElementById('result');
	res.style.display = 'none';
	opened = 0;
	hint = 0;
}

function moveToSelectedPage()
{
	select = document.getElementById('page_select');
	page = select.options[select.selectedIndex].value;

	document.location.href = 'sheets.php?page=' + page;
}

function unmark_word(wid, mcount)
{
	if (confirm("Do you like to unmark this word?"))
		xajax_unmark_word(wid, mcount);
}

function senseHint()
{
	if (!hint)
		return;

	showSense(hint_sense, hint);
}

function markOpened()
{
	if (!opened)
		return;

	if (confirm("Do you like to mark the current opened word?"))
		xajax_mark_word(opened);
}

function nextPage()
{
	if (page == maxPage)
		return;

	document.location.href = 'sheets.php?page=' + (page + 1);
}

function prevPage()
{
	if (page == 1)
		return;

	document.location.href = 'sheets.php?page=' + (page - 1);
}

function pageSelect()
{
	select = document.getElementById('page_select');
	select.focus();
}

shortcut.add("Shift+H", function () { hideSense(); });
shortcut.add("M", function () { markOpened(); });
shortcut.add("A", function () { senseHint(); });

shortcut.add("N", function () { nextPage(); });
shortcut.add("P", function () { prevPage(); });
shortcut.add("G", function () { pageSelect(); });

//-->
</script>


<?php

$first = $words_per_page * ($page - 1);
$sql = "select wid, seq, word, sense from hangee_words, hangee_test "
	."where hangee_test.uid=$uid "
	."and hangee_test.wid=hangee_words.id "
	."order by hangee_test.seq asc limit $first, $words_per_page";
$res = mysql_query($sql, $dbconn);
if (!$res)
	die ("query failed: $sql : ".mysql_error($dbconn));

echo "<div id='word_sheet'><center><table id='sheet_table'>\n";

while ($row = mysql_fetch_array($res)) {
	$wid = $row['wid'];
	$seq = $row['seq'];
	$word = strtoupper($row['word']);
	$sense = htmlspecialchars(stripslashes($row['sense']));
	$pattern = '/((a|v|vi|n|ad|pre)\.)/';
	$replace = '<br>$1';
	$sense = preg_replace($pattern, $replace, $sense);
	$sense = substr($sense, 4); // delete leading <br>

	$seq = "<a href='javascript:void(0);' onClick='showHint($wid, \"$sense\"); return false;'><font color='black'>$seq</font></a>";
	$word = "<a href='javascript:void(0);' onClick='showSense(\"$sense\", $wid); return false;'>$word</a>";

	$mcount = get_marked_count($wid);
	if ($mcount > 0) {
		$star = "<a href='javascript:void(0);' onClick=\"unmark_word($wid, $mcount); return false;\">"
			."<font color='#cc0000'>";
		$count = min($mcount, 5);
		while ($count--)
			$star .= "*";
		$star .= "</font></a>";
	}
	else
		$star = "";


	echo "<tr><td align='right'>$seq .</td>"
		."<td align='left'><strong>$word $star</strong></td>"
		."<td width='300px' style='border-bottom: 1px solid #000000'>"
		//."<div id='$wid' class='sheet_answer' onClick='this.style.display=\"none\"'></div>"
		."</td></tr>";
}

echo "</table></center>\n";

$prev = $page == 1 ? "" : "<strong><a href='sheets.php?page=".($page-1)."'>Prev</a></strong>";
$next = $page == $max_page ? "" : "<strong><a href='sheets.php?page=".($page+1)."'>Next</a></strong>";
$or = strlen($prev) > 0 && strlen($next) > 0 ? " | " : "";

echo "<div id='sheet_link'>Go to $prev $or $next"
	."&nbsp; or directly to page "
	."<select id='page_select' name='psel' onChange='moveToSelectedPage();'>\n";

for ($i = 1; $i <= $max_page; $i++) {
	$selected = $page == $i ? "selected" : "";
	echo "<option value='$i' $selected>&nbsp;&nbsp;$i&nbsp;&nbsp;</option>";
}

echo "</select> / $max_page</div></div>\n";

hangee_footer();
hangee_exit();
?>
