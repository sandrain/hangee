<?php
#
# tester_hangee.php
#
# Written by HyoGi Sim <sandrain@gmail.com>
#

require_once("__lib.php");

hangee_init(1);

###########################################################################

function do_add_test_words($form)
{
	global $dbconn;
	global $table_rows;
	global $uid;
	global $testwords;

	$ch = $form['word_select'];
	$marked = $form['marked'];

	if (!$ch)
		return;

	$session_str = sprintf("%02d%s", $ch, $marked);
	$page = 500 + (int) $ch;
	$sql = "select count(*) from hangee_words where page=$page";


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
		if (strstr($testwords, $ch)) {
			$msg = "DAY $ch is already in the list!";
			$msg.= "::<b>$testwords</b>";
			return $msg;
		}

		$_SESSION['testwords'] .= ":".$session_str;
	}
	else
		$_SESSION['testwords'] = $session_str;

	$html = "<p><strong>DAY $ch</strong>, <strong>$count</strong> words</p>";
	return $html;
}

function add_test_words($form)
{
	$ch = strtolower($form['word_select']);

	if ($ch == 'all') {
		for ($i = 1; $i <= 24; $i++) {
			$form['word_select'] = $i;
			$html.= do_add_test_words($form);
		}
	}
	else 
		$html = do_add_test_words($form);

	$response = new xajaxResponse();
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

	//$sql = "delete from hangee_test where uid = $uid and testcrc <> $testcrc";
	$sql = "delete from hangee_test where uid = $uid";
	$res = mysql_query($sql, $dbconn);
	if (!$res) {
		$msg = "query failed: $sql : ".mysql_error($dbconn);
		$response->assign("result", "innerHTML", $msg);
		$response->assign("result", "style.display", "block");
		return $response;
	}

	$chunks = explode(":", $testwords);

	foreach ($chunks as $chunk) {
		$page = 500 + ((int) substr($chunk, 0, 2));
		$marked = substr($chunk, -4, 1);

		$sql = "select id from hangee_words where page=$page ";

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
			break;

		case 'n':	// Not marked only
			$sql .= " and id not in (select wid from hangee_marked where uid=$uid)";
			break;

		default: break;
		}

		/*
		$msg = "$marked:$sql";
		$response->assign("result", "innerHTML", $msg);
		$response->assign("result", "style.display", "block");
		return $response;
		*/

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

	$response->script("document.location.href='tester_goman.php'");
	return $response;
}

$xajax = new xajax();
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

	GoMAN:
	<select id="word_select" name="word_select">
	<option value="0">Select DAY</option>
	<?php
	for ($i = 1; $i <= 24; $i++) {
		echo "<option value=\"$i\">DAY $i</option>";
	}
	?>
	<option value="all">ALL</option>
	</select>

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
