<?php
die('be careful!!');

$dbconn = mysql_connect('localhost', 'sandrain', '359035');
mysql_select_db('sandrain', $dbconn);

$table = 'stardict_etymology';
$s_ifo = 'stardict-EnglishEtymology-2.4.2/EnglishEtymology.ifo';
$s_idx = 'stardict-EnglishEtymology-2.4.2/EnglishEtymology.idx.gz';
$s_dict = 'stardict-EnglishEtymology-2.4.2/EnglishEtymology.dict.dz';
/*
$s_ifo = 'stardict-babylon-CollinsCobuild5-2.4.2/CollinsCobuild5_v1.4.1.ifo';
$s_idx = 'stardict-babylon-CollinsCobuild5-2.4.2/CollinsCobuild5_v1.4.1.idx.gz';
$s_dict = 'stardict-babylon-CollinsCobuild5-2.4.2/CollinsCobuild5_v1.4.1.dict.dz';
*/

function dict_import($info_file,$idx_file,$dict_file) {
	global $dbconn;
	global $table;
	
	if (!file_exists($info_file)) return false;
	if (!file_exists($idx_file)) return false;
	if (!file_exists($dict_file)) return false;


	$dic_info = array();
	foreach(file($info_file) as $v) {
		$v = split("=",trim($v));
		if ($v[0] == "bookname") $dic_info["fcaption"] = trim($v[1]);
		if ($v[0] == "sametypesequence") $dic_info["fmarkup"] = $v[1];
		if ($v[0] == "description") $dic_info["fcomment"] = $v[1];
	}
	//$dic_id = $conn->query('INSERT INTO t_dic (?#) VALUES(?a)', array_keys($dic_info), array_values($dic_info));

	$count = 0;

	$fd_idx = gzopen($idx_file,"rb");
	$fd_dict = gzopen($dict_file,"rb");
	do {
		// Read until \0
		$word = ""; $max_word = 255;
		while (true) {
			$ch = gzread($fd_idx,1);
			if ($ch == "\0" || gzeof($fd_idx) || $max_word-- <= 0) break;
			$word .= $ch;
		}
		//echo $word."<br/>";
		$word = addslashes($word);

		// Read offset from index
		$start = unpack("I",strrev(gzread($fd_idx,4))); $start=$start[1];
		$len = unpack("I",strrev(gzread($fd_idx,4))); $len=$len[1];

		// Read article text
		gzseek($fd_dict,$start);
		$text = gzread ($fd_dict,$len);
		$text = addslashes($text);

		$data = array(
			//"fdic_id" => $dic_id,
			"word" => $word,
			"text" => $text
		);
		$sql = "insert delayed into $table (word, text) values ('$word', '$text')";
		$res = mysql_query($sql, $dbconn);
		if (!$res)
			die ("query failed: ". mysql_error($dbconn));

		//$conn->query('INSERT DELAYED INTO t_word (?#) VALUES(?a)', array_keys($data), array_values($data));
		//echo ($sql);
		$count++;

	} while (!gzeof($fd_idx));

	gzclose($fd_idx);
	gzclose($fd_dict);

	return $count;
}

$sql = "drop table if exists $table";
$res = mysql_query($sql);
if (!$res)
	die ('query failed: '.mysql_error($dbconn));

$sql = "create table $table ("
	."id integer not null auto_increment,"
	."word char(255) not null,"
	."text text,"
	."primary key (id))";
$res = mysql_query($sql);
if (!$res)
	die ('query failed: '.mysql_error($dbconn));


$count = dict_import($s_ifo, $s_idx, $s_dict);

echo "<p>$count words are inserted!</p>";

?>
