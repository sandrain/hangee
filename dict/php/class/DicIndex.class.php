<?

class DicIndex {
    static $block_size = 65535;

	/**
	 * Index all dictionaries found
	 * 
	 */    
    function make() {
		$list = Z::c()->select("SELECT * FROM t_dic WHERE fstate_id = 1");
		foreach($list as $dic) {
		    # Clean DB index
		    Z::c()->query("DELETE FROM t_idx WHERE fdic_id = ?", $dic["fid"]);

		    # Make index
		    try {
		    	$this->makeOne( $dic );
		    	# Success
		    	Z::c()->query("UPDATE t_dic SET fstate_id = ? WHERE fid = ?", 2, $dic["fid"]);
		    }	catch(Exception $e) {
		    	# Failure
		    	Z::c()->query("UPDATE t_dic SET fstate_id = -1, ferror = ?  WHERE fid = ?", -1, $e->getMessage(), $dic["fid"]);
		    }
		    
		}
    }
    

    // Create index for one .IDX file
    function makeOne($dic) {
		$block = "";
		$fd = fopen($dic["fidx_path"], "rb");

		$offset=0;
		$rec = false;
		while (true) {
		    # if not EOF read next block
		    if (!feof($fd)) $block .= fread($fd, self::$block_size); else break;
		    
		    $block_size = strlen($block);
		    $next_offset = $offset + $block_size;

		    # Process block
		    $position = 0; // Points to beginning of every word
		    $last_zero = 0;
		    while (true) {
				$next_zero = @strpos($block, "\0", $position);
				$next_position = $next_zero + 8 + 1;

				# End of block
				if ($next_zero === false || $next_position > $block_size) {
					# Cut remaining block and break
					$block = substr($block, $position, $block_size);
					break;
				}
					
				# Extract word and prefix
				$word = substr($block, $position, $next_zero - $position);
				$prefix = mb_convert_case(mb_substr($word, 0, Z::config("prefix_length")), MB_CASE_LOWER);
	
				# Init prefix on first iteration
				if (!$rec) $rec = array( "start" => 0, "end" => 0, "prefix" =>  $prefix) ;
		
				# Update end of prefix block
				$rec["end"] = $offset + $next_zero + 8;
	
				# Save prefix block
				if ($rec["prefix"] != $prefix) {
				    $this->savePrefix($dic["fid"], $rec);
			    	# Start new prefix record
			    	$rec["prefix"] = $prefix;
			    	$rec["start"] = $offset + $position;
				}
			
				$position = $next_position;
		    }
		    $offset = $next_offset;
		}
		
		fclose($fd);
    }

    function savePrefix($dic_id, $rec) {
        # Prefix ended, save it
        $data = array(
    	    "fchar" => $rec["prefix"],
    	    "fdic_id" => $dic_id,
    	    "fstart" => $rec["start"],
    	    "flength" => $rec["end"] - $rec["start"],
    	);
        $sql = Z::c()->query("INSERT into t_idx (?#) VALUES (?a)", array_keys($data), array_values($data));
    }

}

?>