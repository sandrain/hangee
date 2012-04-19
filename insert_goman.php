<?php
require_once("__lib.php");

$goman = "handata/goman_hyosim.csv";

die();

if (!($handle = fopen($goman, "r")))
	die ("failed to open file: $goman");

connect_db();

$num = 0;
while ($data = fgetcsv($handle)) {
	$num++;
	$page = $data[0];
	$word = strtolower($data[1]);
	$sense = addslashes($data[2]);

	$page = 500 + substr($page, 1);

	//echo "$num) $page:$word: $sense <br>";

	$sql = "insert into hangee_words (page, word, sense) values ($page, '$word', '$sense')";
	$res = mysql_query($sql, $dbconn);
	if (!$res)
		die ("failed: ".mysql_error($dbconn));
	echo "insert $num <br>";
}

disconnect_db();

echo "<p>$num records were inserted.</p>";

fclose($handle);
?>
