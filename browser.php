<?php
#
# browser.php
#
# Written by HyoGi Sim <sandrain@gmail.com>
#

require_once("__lib.php");

hangee_init();

function mark_word($wid, $num)
{
	global $dbconn;
	global $uid;
	global $search_column;
	global $search_key;

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

	$response->script("reload_parent()");
	return $response;
}

function unmark_word($wid, $num)
{
	global $dbconn;
	global $uid;
	global $search_column;
	global $search_key;

	$response = new xajaxResponse();

	$sql = "delete from hangee_marked where wid=$wid and uid=$uid";

	$res = mysql_query($sql, $dbconn);
	if (!$res) {
		$msg = "query failed : $sql : ". mysql_error($dbconn);
		$response->assign("result", "innerHTML", $msg);
		$response->assign("result", "style.display", "block");
		return $response;
	}

	$response->script("reload_parent()");
	return $response;
}

function browse_word($ch, $form='')
{
	global $dbconn;
	global $uid;

	$ch = strtolower($ch);
	$sql = '';
	$num_res = 0;

	$response = new xajaxResponse();

	connect_db();

	$goman = (substr($form, 0, 5) == 'goman') ? TRUE : FALSE;

	if ($form) {
		if ($goman) {
			$page = $ch + 500;
			$sql = "select * from hangee_words where page=$page";

			if (substr($form, -1) == 'm')
				$sql .= " and id in (select wid from hangee_marked where uid=$uid)";

			$sql .= " order by word asc";
		}
		else {
			$column = $form['column'];
			$key = trim($form['key']);

			if (strlen($key) == 0) {
				$response->assign("result", "innerHTML", "No search word is given!");
				$response->assign("result", "style.display", "block");
				return $response;
			}

			switch ($column) {
			case 'word':
				$sql = "select * from hangee_words where word like '%$key%' order by word asc";
				break;

			case 'sense':
				$sql = "select * from hangee_words where sense like '%$key%' order by word asc";
				break;

			case 'SQL':
				$sql = "select * from hangee_words where ".$key;
				break;

			default:
				$response->assign("result", "innerHTML", "Internal Error!");
				$response->assign("result", "style.display", "block");
				return $response;
				break;
			}
		}
	}
	else {
		if (strlen($ch) == 2) {
			$ch = substr($ch, 0, 1);
			$sql = "select * from hangee_words where word like '$ch%' and page < 300 "
				."and id in (select wid from hangee_marked where uid = $uid)";
		}
		else
			$sql = "select * from hangee_words where word like '$ch%'  and page < 300 order by word asc";
	}


	$res = mysql_query($sql, $dbconn);
	if (!$res) {
		$response->assign("debug", "innerHTML", "query failed: $sql".mysql_error($dbconn));
		$response->assign("debug", "style.display", "block");
		return $response;
	}

	if (($num_res = mysql_num_rows($res)) == 0) {
		$response->assign("word_browser", "innerHTML", "<strong>No words, lucky you!</strong>");
		$response->assign("word_browser", "style.display", "block");
		return $response;
	}

	$num = 0;
	if (isset($key)) {
		if ($column == 'SQL')
			$caption = "Search result of SQL";
		else
			$caption = "Search result of '<strong>".htmlspecialchars($key)."</strong>'";
	}
	else {
		if ($goman)
			$caption = "GoMAN DAY $ch";
		else
			$caption = "HanGEE words starting with '<strong>".strtoupper($ch)."</strong>'";
	}

	$pstr = $goman ? "Day" : "Page";

	$html = "<p>$num_res words are found</p>"
		."<p><a href='javascript:void(0);' onClick=\"show_sense($num_res);\"><strong>Show Sense</strong></a> | "
		."<a href='javascript:void(0);' onClick=\"hide_sense($num_res);\"><strong>Hide Sense</strong></a> | "
		."<a href='javascript:void(0);' onClick=\"words_upper($num_res);\"><strong>Upper</strong></a> | "
		."<a href='javascript:void(0);' onClick=\"words_lower($num_res);\"><strong>Lower</strong></a></p>"
		."<center>"
		."<table id='word_table'>"
		."<caption>$caption</caption>"
		."<tr><th id='num' width='5%'>Num</th><th id='page' width='8%'>$pstr</th><th id='word' width='15%'>Word</th>"
		."<th id='sense' width='40%'>Sense</th><th id='hint'>Hint</th>"
		."<!-- <th id='edit'>Edit</th></tr> -->";

	while ($row = mysql_fetch_array($res)) {
		$num++;
		$id = $row['id'];
		$page = $row['page'];
		$word = $row['word'];
		$sense = htmlspecialchars(stripslashes($row['sense']));

		if (!$goman && $page < 500) {
			$pattern = '/((a|v|vi|n|ad|pre)\.)/';
			$replace = '<br>$1';
			$sense = preg_replace($pattern, $replace, $sense);
			$sense = substr($sense, 4); // delete leading <br>
		}

		$grade = get_user_grade();
		if ($grade == '1' || $grade == '0') {
			$numstr = "<a name='$num' href=\"javascript:openPopup('edit.php?id=$id', 'editwin');\"><b>$num</b></a>";
		}
		else
			$numstr = "<a href='mailto:sandrain@gmail.com'><b>$num</b></a>";

		$word = "<a href=\"javascript:mark_word($id, $num)\"><span id='w$num'>".strtoupper($word)."</span></a>";

		$mcount = get_marked_count($id);
		if ($mcount > 0) {
			$wbackcolor = "#ffcc00";
			$word .= "&nbsp;<a href=\"javascript:unmark_word($id, $num)\"><font color='#cc0000'>";
			while ($mcount--)
				$word .= "*";
			$word .= "</font></a>";
		}
		else
			$wbackcolor = "#ffff99";

		$bgcolor = '';
		if ($page > 500) {
			$page = "D - ".($page - 500);
			$bgcolor = "bgcolor='#ccffff'";
		}

		$dict = "<a href='javascript:void(0); return false;' onClick=\"openPopup('dict.php?id=$id', 'dictwin')\">$page</a>";

		$hint = get_hint($id);
		$hintlink = "<a href='javascript:void(0);' "
			."onClick=\"javascript:openPopup('hint.php?id=$id', 'hintwin');\">Edit</a>";
		if ($hint)
			$hint = htmlspecialchars(stripslashes($hint)). "&nbsp;<span style='font-size:8pt;'>($hintlink)</span>";
		else
			$hint = $hintlink;

		$html .= "<tr onMouseOver=\"this.bgColor='#ccff99'\" onMouseOut=\"this.bgColor='#ffffff'\">"
			."<td headers='num' align='right' style='padding-right:10px;'>$numstr</td>"
			."<td headers='page' align='center' $bgcolor>$dict</td>"
			."<td headers='word' bgcolor='$wbackcolor' align='left'><p class='table_word'>$word</p></td>"
			."<td headers='sense' align='left' id='td$num' class='answer_off' "
			."onMouseOver=\"className='answer_on'\" onMouseOut=\"className='answer_off'\">"
			."<p class='table_text'>$sense</p></td>"
			."<td headers='hint' align='left'><p class='table_text'>$hint</p></td>";
	}
	$html .= "</table></center>";

	disconnect_db();

	if (isset($column) && isset($key))
		$response->script("set_search_active('$column', '$key')");
	else
		$response->script("update_current_ch('$ch');");
	$response->assign("word_browser", "innerHTML", $html);
	$response->assign("word_browser", "style.display", "block");
	$response->script("num_res = $num_res;");

	return $response;
}

$xajax = new xajax();
$xajax->registerFunction("browse_word");
$xajax->registerFunction("mark_word");
$xajax->registerFunction("unmark_word");
$xajax->processRequest();

hangee_header("HanGEE: Word Browsing");

$xajax->printJavascript("xajax");

/*
echo "<div id='navigation'><p>HanGEE:&nbsp;&nbsp;";

for ($ch = ord('A'); $ch <= ord('Z'); $ch++) {
	$chr = chr($ch);
	echo "<a href='browser.php?ch=$chr'>$chr</a>\n";
}

echo "<a href='javascript:void(0);' onClick='show_search(); return false;'>Search</a></p>";
*/

echo "<div id='navigation'>";
echo "<p><span>HanGEE:&nbsp;&nbsp;"
	."<input type='checkbox' id='hangee_marked' value='m' onClick='browse_hangee();'>"
	."<label for='hangee_marked'>Marked only</label>"
	."&nbsp;&nbsp;<select id='hangeesel' onChange='browse_hangee();'><option value='0'>== SELECT ==</option>";
for ($i = ord('A'); $i <= ord('Z'); $i++)
	echo "<option value='".chr($i)."'>".chr($i)."</option>";
echo "</select></span>";

echo "<span style='margin-left: 50px;'>GoMAN:&nbsp;&nbsp;"
	."<input type='checkbox' id='goman_marked' value='m' onClick='browse_goman();'>"
	."<label for='goman_marked'>Marked only</label>"
	."&nbsp;&nbsp;<select id='gomansel' onChange='browse_goman();'><option value='0'>== SELECT ==</option>";
for ($i = 1; $i <= 24; $i++)
	echo "<option value='$i'>DAY $i</option>";
echo "</select></span>";

echo "<span style='margin-left: 50px;'><a href='javascript:void(0);' onClick='show_search(); return false;'>"
	."SEARCH</a></span></p></div>";
?>

<!--// Keyboard shortcut code is from http://www.openjs.com/scripts/events/keyboard_shortcuts/ //-->
<script language='javascript' type='text/javascript' src='js/shortcut.js'></script>

<script language='javascript' type='text/javascript'>
<!--//

var current_mode = 0; // 0: HanGEE, 1: GoMAN
var search_opened;


function set_mode_hangee()
{
	current_mode = 0;
}

function set_mode_goman()
{
	current_mode = 1;
}

function set_mode_search()
{
	current_mode = 2;
}

function browse_goman()
{
	var sel = document.getElementById('gomansel');
	var idx = sel.selectedIndex;
	var marked = (document.getElementById('goman_marked')).checked;
	var list = marked ? 'gomanm' : 'goman';

	if (idx > 0) {
		xajax_browse_word(idx, list);
		set_mode_goman();
	}
}

function browse_hangee()
{
	var sel = document.getElementById('hangeesel');
	var idx = sel.selectedIndex;
	var ch = sel.value;
	var marked = (document.getElementById('hangee_marked')).checked;

	if (marked)
		ch = ch + 'm';

	if (idx > 0) {
		xajax_browse_word(ch);
		set_mode_hangee();
	}
}

function browse_search()
{
	xajax_browse_word('', xajax.getFormValues('word_search_form'));
	set_mode_search();
}

function reload_parent()
{
	switch (current_mode) {
	case 0: browse_hangee(); break;
	case 1: browse_goman(); break;
	default: browse_search(); break;
	}
}

function mark_word(wid, num)
{
	if (!confirm("Do you like to mark this word?"))
		return;

	xajax_mark_word(wid, num);
}

function unmark_word(wid, num)
{
	if (!confirm("Do you like to clear marks of this word?"))
		return;

	xajax_unmark_word(wid, num);
}




/******************************************************************************/
/** SEARCH **/

function show_search()
{
	var search = document.getElementById('word_search');
	search.style.display = 'block';
	document.search.key.focus();
	document.search.key.select();
	search_openek = true;
}

function toggle_search()
{
	if (search_opened) {
		document.getElementById('word_search').style.display = 'none';
		search_opened = false;
	}
	else {
		show_search();
		search_opened = true;
	}
}

function set_search_active(scolumn, skey)
{
	var form = document.search;
	form.active.value = '1';
}

/*******************/

function show_sense(num)
{
	for (i = 1; i <= num; i++) {
		idx = new Number(i);
		name = 'td' + idx.toString();
		obj = document.getElementById(name);
		obj.className = 'answer_on';
	}
}

function hide_sense(num)
{
	for (i = 1; i <= num; i++) {
		idx = new Number(i);
		obj = document.getElementById('td' + idx.toString());
		obj.className = 'answer_off';
	}
}

function words_upper(num)
{
	for (i = 1; i <= num; i++) {
		idx = new Number(i);
		wp = document.getElementById('w' + idx.toString());
		word = wp.innerHTML;
		wp.innerHTML = word.toUpperCase();
	}
}

function words_lower(num)
{
	for (i = 1; i <= num; i++) {
		idx = new Number(i);
		wp = document.getElementById('w' + idx.toString());
		word = wp.innerHTML;
		wp.innerHTML = word.toLowerCase();
	}
}

function select_change(obj)
{
	var opt = obj.options[obj.selectedIndex];
	var text = document.getElementById('text_key');
	var span = document.getElementById('sql_prefix');

	if (opt.value == 'SQL') {
		text.size = 70;
		span.style.display = 'inline-block';
	}
	else {
		text.size = 30;
		span.style.display = 'none';
	}

	text.value = '';
	text.focus();
}

//-->
</script>

<div id="word_search" class='explain'>
<form name='search' id='word_search_form' onSubmit='javascript:void(0); return false;'>
<select id='selcol' name='column' onChange='select_change(this);'>
<option value='word' selected>Word</option>
<option value='sense'>Sense</option>
<option value='SQL'>SQL</option>
</select>
<input type='hidden' name='active' value='0'>
<span id='sql_prefix' style='display:none;'><strong>SELECT * FROM HANGEE_WORDS WHERE</strong> </span>
<input id='text_key' type='text' name='key' size='30' maxlength='512'>
<!-- <button onClick="xajax_browse_word('', xajax.getFormValues('word_search_form'));">SEARCH</button> -->
<button onClick="javascript:browse_search();">SEARCH</button>
<button onClick="(document.getElementById('word_search')).style.display='none';">HIDE</button>
</form>
</div>


<div id="word_browser">
</div>

<?php
if (isset($_GET['ch'])) {
	$ch = strtolower($_GET['ch']);
	if (ord($ch) >= ord('a') && ord($ch) <= ord('z')) {
		echo "<script language='javascript' type='text/javascript'>\n<!--//\n"
			."xajax_browse_word('$ch');\n"
			."current_ch = '$ch';\n"
			."//-->\n</script>";
	}
}
?>

<script language='javascript' type='text/javascript'>
<!--//

var num_res = 0;

shortcut.add("Shift+Alt+A", function () { xajax_browse_word('A'); });
shortcut.add("Shift+Alt+B", function () { xajax_browse_word('B'); });
shortcut.add("Shift+Alt+C", function () { xajax_browse_word('C'); });
shortcut.add("Shift+Alt+D", function () { xajax_browse_word('D'); });
shortcut.add("Shift+Alt+E", function () { xajax_browse_word('E'); });
shortcut.add("Shift+Alt+F", function () { xajax_browse_word('F'); });
shortcut.add("Shift+Alt+G", function () { xajax_browse_word('G'); });
shortcut.add("Shift+Alt+H", function () { xajax_browse_word('H'); });
shortcut.add("Shift+Alt+I", function () { xajax_browse_word('I'); });
shortcut.add("Shift+Alt+J", function () { xajax_browse_word('J'); });
shortcut.add("Shift+Alt+K", function () { xajax_browse_word('K'); });
shortcut.add("Shift+Alt+L", function () { xajax_browse_word('L'); });
shortcut.add("Shift+Alt+M", function () { xajax_browse_word('M'); });
shortcut.add("Shift+Alt+N", function () { xajax_browse_word('N'); });
shortcut.add("Shift+Alt+O", function () { xajax_browse_word('O'); });
shortcut.add("Shift+Alt+P", function () { xajax_browse_word('P'); });
shortcut.add("Shift+Alt+Q", function () { xajax_browse_word('Q'); });
shortcut.add("Shift+Alt+R", function () { xajax_browse_word('R'); });
shortcut.add("Shift+Alt+S", function () { xajax_browse_word('S'); });
shortcut.add("Shift+Alt+T", function () { xajax_browse_word('T'); });
shortcut.add("Shift+Alt+U", function () { xajax_browse_word('U'); });
shortcut.add("Shift+Alt+V", function () { xajax_browse_word('V'); });
shortcut.add("Shift+Alt+W", function () { xajax_browse_word('W'); });
shortcut.add("Shift+Alt+X", function () { xajax_browse_word('X'); });
shortcut.add("Shift+Alt+Y", function () { xajax_browse_word('Y'); });
shortcut.add("Shift+Alt+Z", function () { xajax_browse_word('Z'); });
shortcut.add("Shift+Enter", function () { toggle_search(); });
shortcut.add("Shift+S", function () { show_sense(num_res); });
shortcut.add("Shift+H", function () { hide_sense(num_res); });
shortcut.add("Shift+U", function () { words_upper(num_res); });
shortcut.add("Shift+L", function () { words_lower(num_res); });
//-->
</script>

<?php
hangee_footer();
hangee_exit();
?>
