<?
#
#  Main site controller
#
ob_start();
$module = strtolower(trim($_REQUEST["m"]));

#----------------------------------------------------------------------------------
# Protected modules here (auth needed for access)
#----------------------------------------------------------------------------------

# Default layout for user pages
$layout = getTemplate('layout');
if ($_REQUEST["ax"]) $layout = '<?=$content?>';


switch ($module) {
	case '':
	case 'home':
		$layout = '<?=$content?>';
		eval("?>".getTemplate('dict2')."<?");
	break;

	case 'word':
		$start_time = microtime(true);
		
		$kw = mb_convert_case($_GET["kw"], MB_CASE_LOWER); 
		$kw_len = mb_strlen($kw);
		
		# Search all words in index
		$prefix = mb_substr($kw, 0, Z::config("prefix_length"));
		if (mb_strlen($prefix) < 2) return "";

		$list = Z::c()->select("SELECT d.fidx_path, i.fstart, i.flength FROM t_idx i LEFT JOIN t_dic d ON (i.fdic_id = d.fid) WHERE fchar LIKE ?", $prefix."%");
		$word_list = array();
		foreach($list as $v) {
		    $fd = fopen( $v["fidx_path"],"rb");
    		fseek($fd, $v["fstart"] );
    		$block = fread($fd, $v["flength"] );
    		fclose($fd);

		    # Parse block
    		$idx=0;
    		while (true) {
				$next_zero = @strpos($block, "\0", $idx);

				# End of block
				if ($next_zero === false) break;
	
				# Extract word
				$word_list[] = substr($block, $idx, $next_zero - $idx);

				# Calculate start of next word
				$idx = $next_zero + 8 + 1;
		    }
		}
		# filter words by prefix entered
		$search_prefix = mb_convert_case($search, MB_CASE_LOWER);
		$search_len = mb_strlen($search);
		foreach($word_list as $k => $v) {
			$v = mb_convert_case($v, MB_CASE_LOWER);
    		if ( mb_substr($v, 0, $kw_len) != $kw ) unset($word_list[$k]);
    		//$word_prefix = mb_convert_case(mb_substr($v, 0, $search_len), MB_CASE_LOWER);
		    //if ($word_prefix != $search_prefix) unset($word_list[$k]);
		}

		# Remove duplicates
		$word_list = array_unique($word_list);

		sort($word_list);

		# Limit to 
		if (count($word_list) > Z::config("max_search")) 
			$word_list = array_slice($word_list, 0, Z::config("max_search") ); 

		# Do some statistic
		$end_time = microtime(true);
		$stat = sprintf(
			"Spent %f seconds looking in %d words", 
			$end_time - $start_time,
			Z::c()->selectCell("SELECT sum(fwordcount) FROM t_dic")
		);
		
		echo json_encode( array("s" => $stat, "w" => $word_list) );exit;
	break;

	case 'show':
		$kw = $_GET["kw"]; 

		$result = array();
		
		# Search all words in index
		$prefix = mb_substr($kw, 0, Z::config("prefix_length"));
		$list = Z::c()->select("SELECT d.fbookname, d.fidx_path, d.fdict_path, i.fstart, i.flength FROM t_idx i LEFT JOIN t_dic d ON (i.fdic_id = d.fid) WHERE fchar LIKE ?", $prefix."%");
		$word_list = array();
		foreach($list as $v) {
			if (!file_exists($v["fdict_path"])) continue;
			
		    $fd = fopen( $v["fidx_path"],"rb");
    		fseek($fd, $v["fstart"] );
    		$block = fread($fd, $v["flength"] + 2 );
    		fclose($fd);

		    # Parse block
    		$idx=0;
    		while (true) {
				$next_zero = @strpos($block, "\0", $idx);

				# End of block
				if ($next_zero === FALSE) break;
	
				# Extract word
				$word = substr($block, $idx, $next_zero - $idx);
				
				if ($word == $kw) {
					$start = unpack("I",strrev(  substr($block, $next_zero + 1, 4)  )); $start=$start[1];
  					$len = unpack("I",strrev( substr($block, $next_zero + 5, 4) )); $len=$len[1];
					
					$fd = fopen($v["fdict_path"], "rb");
					fseek($fd, $start);
					$text = fread($fd, $len);
					fclose($fd);
					
					$result[] = array(
						"fbookname" => $v["fbookname"], 
						"word" => $word, 
						"text" => DicFormat::format( $text ) 
					);
				}

				# Calculate start of next word
				$idx = $next_zero + 8 + 1;
		    }
		}
		
		$layout = '<?=$content?>';
		eval("?>".getTemplate('show')."<?");
	break;


	case 'stat':
		$layout = '<?=$content?>';
		$list = Z::c()->query("SELECT fid, fversion, fwordcount, fbookname, fdescription FROM t_dic WHERE fstate_id = ? ORDER BY fbookname", Dic::DIC_GOOD);
		foreach($list as $k => $v)
			$list[$k]["fdescription"] = nl2br($list[$k]["fdescription"]);
		echo json_encode($list); 
	break;
	
	case 'admin.login':
		if ($_POST["password"] == Z::config("admin_password")) {
			$_SESSION["is_admin"] = 1;	
			echo json_encode(true);
		} else {
			echo json_encode(false);
		}
	break;

	case 'admin':
		if (!$_SESSION["is_admin"]) redirect("?m=home"); 	
		eval("?>".getTemplate('admin')."<?");
	break;

	case 'admin.reindex':
		if (!$_SESSION["is_admin"]) redirect("?m=home");
		
		$dic_path = array(
			//realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."dic",
			"D:/app/StarDict/dic/li12",
			"/home/ameoba32/stardict"
		);
		
		# Rescan dictionaries
		$scan = new DicScan();
		$scan->DirArray( $dic_path ) ;
		$scan->clean();
		$scan->save();
		
		# Do index
		$index = new DicIndex();
		$index->make();
		
		$dic_list = Z::c()->query("select * from t_dic");
		 	
		eval("?>".getTemplate('admin.reindex')."<?");
	break;

	
}

$content=ob_get_contents();
ob_end_clean();
eval('?>'.$layout.'<?');

?><?# If you like software like this, i can make it. email me: ameoba32@gmail.com?>
