<?php
#
# tester_hangee.php
#
# Written by HyoGi Sim <sandrain@gmail.com>
#

require_once("__lib.php");

hangee_init(1);

###########################################################################

function populate_pages($form)
{
	global $dbconn;

	$response = new xajaxResponse();
	$ch = strtolower($form['word_select']);

	if (!$ch) {
		$response->assign("pages", "value", "");
		return $response;
	}

	$sql = "select min(page),max(page) from hangee_words where page < 300";
	if ($ch != 'all')
		$sql .= " and word like '$ch%'";
	$res = mysql_query($sql, $dbconn);
	if (!$res) {
		$msg = "query failed: $sql : ".mysql_error($dbconn);
		$response->assign("debug", "innerHTML", $msg);
		$response->assign("debug", "style.display", "block");
		return $response;
	}
	$row = mysql_fetch_array($res);
	$min = $row[0];
	$max = $row[1];

	$pagestr = $min == $max ? "$min" : "$min-$max";

	$response->assign("pages", "value", $pagestr);
	return $response;
}

function do_add_test_words($form)
{
	global $dbconn;
	global $table_rows;
	global $uid;
	global $testwords;

	$ch = strtolower($form['word_select']);
	$pagestr = trim($form['pages']);
	if ($pagestr)
		$pages = explode("-", $pagestr);
	else
		$pages = array();
	$marked = $form['marked'];

	if (!$ch)
		return;

	$sql = "select min(page), max(page) from hangee_words where word like '$ch%' and page < 300";
	$res = mysql_query($sql, $dbconn);
	if (!$res) {
		$msg = "query failed: $sql : ".mysql_error($dbconn);
		return $msg;
	}

	$row = mysql_fetch_array($res);
	$min = $row[0];
	$max = $row[1];

	if ($min == null && $max == null) {
		$msg = "No words starting with $ch!";
		return $msg;
	}

	switch (count($pages)) {
	case 1:
		if ($pages[0] < $min || $pages[0] > $max) {
			$msg = "Page range wrong: $pagestr";
			return $msg;
		}
		$sql = "select count(*) from hangee_words where word like '$ch%' and "
			."page = ".$pages[0];
		break;

	case 2:
		if (($pages[0] > $pages[1]) || ($pages[0] < $min) || ($pages[1] > $max) ) {
			$msg = "Page range wrong: $pagestr";
			return $msg;
		}

		$min = $pages[0];
		$max = $pages[1];

		$sql = "select count(*) from hangee_words where word like '$ch%' and "
			."page >= $min and page <= $max";
		break;

	default:
		$sql = "select count(*) from hangee_words where word like '$ch%' and page < 300";
		$pagestr = "--";
		break;
	}

	$uch = strtoupper($ch);
	$session_str = $uch.$pagestr.$marked;

	if ($marked == "m") {
		$op = $form['mc_op'];
		$mc = $form['mc_sel'];

		$session_str .= $op.$mc;

		switch ($op) {
		case 'eq': $cond = "count = $mc"; break;
		case 'gt': $cond = "count >= $mc"; break;
		case 'lt': $cond = "count <= $mc"; break;
		default: break;
		}

		$sql .= " and id in (select wid from hangee_marked where uid=$uid and $cond)";

		//$sql .= " and id in (select wid from hangee_marked where uid=$uid)";
	}
	else if ($marked == "n") {
		$session_str .= "000";

		$sql .= " and id not in (select wid from hangee_marked where uid=$uid)";
	}
	else {	// $marked == 'a'
		$session_str .= "000";
	}

	$res = mysql_query($sql, $dbconn);
	if (!$res) {
		$msg = "query failed: $sql : ".mysql_error($dbconn);
		return $msg;
	}
	$row = mysql_fetch_array($res);
	$count = $row[0];

	if (isset($_SESSION['testwords'])) {
		$testwords = $_SESSION['testwords'];
		if (strstr($testwords, $uch)) {
			$msg = "$uch is already in the list!";
			$msg.= "::<b>$testwords</b>";
			return $msg;
		}

		$_SESSION['testwords'] .= ":".$session_str;
	}
	else
		$_SESSION['testwords'] = $session_str;

	$html = "<p><strong>$uch</strong>, Page <strong>$pagestr</strong>, <strong>$count</strong> words</p>";
	return $html;
}

function add_test_words($form)
{
	$ch = strtolower($form['word_select']);
	$html = '';
	$response = new xajaxResponse();

	if ($ch == 'all') {
		for ($i = ord('a'); $i <= ord('z'); $i++) {
			$form['word_select'] = chr($i);
			$form['pages'] = '';
			$html.= do_add_test_words($form);
		}
	}
	else 
		$html = do_add_test_words($form);

	$response->append("word_browser", "innerHTML", $html);
	$response->assign("start_button", "style.display", "block");
	$response->assign("word_browser", "style.display", "block");
	$response->assign("result", "style.display", "none");

	return $response;
}

function start_test($random)
{
	global $dbconn;
	global $uid;

	$response = new xajaxResponse();

	$testwords = $_SESSION['testwords'];
	$testcrc = crc32($testwords + (string) time());

	$sql = "delete from hangee_test where uid = $uid and testcrc <> $testcrc";
	$res = mysql_query($sql, $dbconn);
	if (!$res) {
		$msg = "query failed: $sql : ".mysql_error($dbconn);
		$response->assign("result", "innerHTML", $msg);
		$response->assign("result", "style.display", "block");
		return $response;
	}

	$chunks = explode(":", $testwords);

	foreach ($chunks as $chunk) {
		$ch = strtolower(substr($chunk, 0, 1));
		$marked = substr($chunk, -4, 1);
		$pages = explode("-", substr($chunk, 1, strlen($chunk) - 5));
		//$marked = substr($chunk, -1, 1);
		//$pages = explode("-", substr($chunk, 1, strlen($chunk) - 2));

		$sql = "select id from hangee_words where word like '$ch%' ";
		switch (count($pages)) {
		case 1:
			$sql .= "and page = ".$pages[0]." ";
			break;

		case 2:
			$min = $pages[0];
			$max = $pages[1];
			$sql .= "and page >= $min and page <= $max ";
			break;

		default:
		}

		switch ($marked) {
		case 'a': break; // All words, do nothing

		case 'm':	// Marked only
			$op = substr($chunk, -3, 2);
			$mc = substr($chunk, -1, 1);

			switch ($op) {
			case 'eq': $cond = "count = $mc"; break;
			case 'gt': $cond = "count >= $mc"; break;
			case 'lt': $cond = "count <= $mc"; break;
			default: break;
			}

			$sql .= " and id in (select wid from hangee_marked where uid=$uid and $cond)";
			//$sql .= " and id in (select wid from hangee_marked where uid=$uid)";
			break;

		case 'n':	// Not marked only
			$sql .= " and id not in (select wid from hangee_marked where uid=$uid)";
			break;

		default: break;
		}

		$res = mysql_query($sql, $dbconn);
		if (!$res) {
			$msg = "query failed: $sql : ".mysql_error($dbconn);
			$response->assign("result", "innerHTML", $msg);
			$response->assign("result", "style.display", "block");
		}

		$rows = array();
		while ($row = mysql_fetch_assoc($res))
			$rows[] = $row;

		foreach ($rows as $row) {
			$wid = $row['id'];
			$sql = "insert into hangee_test (uid, wid, testcrc) "
				."values ($uid, $wid, $testcrc)";
			
			$res = mysql_query($sql, $dbconn);
			if (!$res) {
				$msg = "query failed: $sql : ".mysql_error($dbconn);
				$response->assign("result", "innerHTML", $msg);
				$response->assign("result", "style.display", "block");
			}
		}
	}

	$sql = "select id,wid from hangee_test where uid=$uid and testcrc=$testcrc ";
	if ($random)
		$sql .= "order by rand()";
	else
		$sql .= "order by wid asc";
	$res = mysql_query($sql, $dbconn);
	if (!$res) {
		$msg = "query failed: $sql : ".mysql_error($dbconn);
		$response->assign("result", "innerHTML", $msg);
		$response->assign("result", "style.display", "block");
		return $response;
	}


	$rows = array();
	while ($row = mysql_fetch_assoc($res))
		$rows[] = $row;

	$seq = 1;
	$msg = $random;
	foreach ($rows as $row) {
		$id = $row['id'];
		$sql = "update hangee_test set seq=$seq where id=$id";
		$res = mysql_query($sql, $dbconn);
		if (!$res) {
			$msg = "query failed: $sql : ".mysql_error($dbconn);
			$response->assign("result", "innerHTML", $msg);
			$response->assign("result", "style.display", "block");
			return $response;
		}
		$seq++;
	}

	$sql = "update hangee_users set testcrc = $testcrc, testpos = 1 where id=$uid";
	$res = mysql_query($sql, $dbconn);
	if (!$res) {
		$msg = "query failed: $sql : ".mysql_error($dbconn);
		$response->assign("result", "innerHTML", $msg);
		$response->assign("result", "style.display", "block");
		return $response;
	}

	$_SESSION['testcrc'] = $testcrc;
	$_SESSION['testpos'] = 1;
	unset ($_SESSION['testwords']);

	$response->script("document.location.href='display.php'");

	return $response;
}

function clear_list()
{
	$response = new xajaxResponse();

	unset($_SESSION['testwords']);

	$response->assign("word_browser", "innerHTML", "");
	$response->assign("word_browser", "style.display", "none");
	$response->assign("start_button", "style.display", "none");

	return $response;
}

function check_registered_test()
{
	global $uid;
	global $dbconn;

	$sql = "select testcrc from hangee_users where id=$uid";
	$res = mysql_query($sql, $dbconn);
	if (!$res)
		die ("query failed: $sql : ".mysql_error($dbconn));

	$row = mysql_fetch_array($res);
	$testcrc = $row['testcrc'];

	if ($testcrc == 0)
		return false;
	if (isset($_SESSION['testcrc']) && $_SESSION['testcrc'] != $testcrc)
		return false;

	$sql = "select count(*) from hangee_test where uid=$uid and testcrc=$testcrc";
	$res = mysql_query($sql, $dbconn);
	if (!$res)
		die ("query failed: $sql : ".mysql_error($dbconn));

	$row = mysql_fetch_array($res);
	$count = $row[0];

	if ($count > 0) {
		$_SESSION['testcrc'] = $testcrc;
		return true;
	}

	return false;
}

function register_new_test()
{
	global $dbconn;
	global $uid;

	$response = new xajaxResponse();

	if (isset($_SESSION['testwords']))
		unset($_SESSION['testwords']);
	if (isset($_SESSION['testpos']))
		unset($_SESSION['testpos']);
	if (isset($_SESSION['maxseq']))
		unset($_SESSION['maxseq']);
	if (isset($_SESSION['testcrc']))
		unset($_SESSION['testcrc']);

	$sql = "update hangee_users set testcrc = 0, testpos = 0 where id=$uid";
	$res = mysql_query($sql, $dbconn);
	if (!$res) {
		$msg = "query failed: $sql : ".mysql_error($dbconn);
		$response->assign("result", "innerHTML", $msg);
		$response->assign("result", "style.display", "block");
		return $response;
	}

	$sql = "delete from hangee_test where uid=$uid";
	$res = mysql_query($sql, $dbconn);
	if (!$res) {
		$msg = "query failed: $sql : ".mysql_error($dbconn);
		$response->assign("result", "innerHTML", $msg);
		$response->assign("result", "style.display", "block");
		return $response;
	}

	$response->script("document.location.href='tester.php'");
	return $response;
}

$xajax = new xajax();
$xajax->registerFunction("populate_pages");
$xajax->registerFunction("add_test_words");
$xajax->registerFunction("start_test");
$xajax->registerFunction("clear_list");
$xajax->registerFunction("register_new_test");
$xajax->processRequest();

###########################################################################

hangee_header("HanGEE: Tester");
$xajax->printJavascript("xajax");
?>

<script language='javascript' type='text/javascript'>
<!--//

function destroy_test_condition()
{
	if (confirm('Are you sure?'))
		xajax_register_new_test();
	return;
}

function show_mpref()
{
	var mpref = document.getElementById('mpref');
	mpref.style.display = 'inline-block';
}

function hide_mpref()
{
	var mpref = document.getElementById('mpref');
	mpref.style.display = 'none';
}

//-->
</script>

<?php if (check_registered_test()) { // if a test already registered for this user ?>
	<div id="word_browser" style='display:block'>
	  <p>There exists a test that you haven't finished, yet. Do you like to resume it?</p>
	  <p><button onClick='document.location.href = "display.php";'>Resume</button>
	     <button onClick='destroy_test_condition();'>Destroy it</button></p>
	</div>

<?php } else { //////////////////////// if not registed ////////////////////////// ?>
	<div id="navigation">

	<p> <!--// HanGEE //-->
	<form id="test_select_form" onSubmit='javascript:void(0); return false;'>

	HanGEE:
	<select id="word_select" name="word_select" onChange="xajax_populate_pages(xajax.getFormValues('test_select_form'));">
	<option value="">ALPHABET</option>
	<?php
	for ($i = ord('A'); $i <= ord('Z'); $i++) {
		$ch = chr($i);
		echo "<option value=\"$ch\">$ch</option>";
	}
	?>
	<option value="all">ALL</option>
	</select>

	<label for="pages">Pages:</label>&nbsp;<input type="text" name="pages" id="pages" size="15"></input>

	<input type="radio" name="marked" value="a" onClick="hide_mpref();"  checked/>&nbsp;<label for="marked">ALL</label>
	<input type="radio" name="marked" value="m" onClick="show_mpref();" />&nbsp;<label for="marked">Marked Only</label>
	<input type="radio" name="marked" value="n" onClick="hide_mpref();" />&nbsp;<label for="marked">Not Marked Only</label>

	<span id='mpref' style='margin-left:10px; margin-right: 10px; display:none;'>( Marked count
	  <select name='mc_op'>
	    <option value='gt' selected> &gt;= </option>
	    <option value='lt'> &lt;= </option>
	    <option value='eq'> == </option>
	  </select>
	  <select name='mc_sel'>
	    <option value='1' selected>1</option>
	    <option value='2'>2</option>
	    <option value='3'>3</option>
	    <option value='4'>4</option>
	    <option value='5'>5</option>
	  </select> )
	</span>

	<button onClick="xajax_add_test_words(xajax.getFormValues('test_select_form'));">ADD</button>

	</form>
	</p>

	</div>

	<div id="word_browser"></div>

	<div id="start_button">
	<button onClick="xajax_start_test(confirm('Do you like to test in random order?'));">Start the Test</button>
	<button onClick="xajax_clear_list();">Clear</button>
	</div>

<?php } /////////////////////////////// end of if ////////////////////////////////
hangee_footer();
hangee_exit();
?>
