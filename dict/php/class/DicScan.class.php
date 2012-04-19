<?

class DicScan {

    function DirArray($path_array) {
		$this->dic_list = array();
		foreach($path_array as $path) $this->Dir($path);
    }

	/**
	 * Read dictionary description
	 * 
	 */
    function readIfo($ifo_file) {
		$result = array();
        $fd=@fopen($ifo_file,"rb");
        if (!$fd) return $result;
        while($s = trim(fgets($fd, 32273))) {
		    if (  ($pos = strpos($s,"=")) === FALSE) continue;
	    	$result[ substr($s, 0, $pos ) ] = substr($s, $pos + 1, strlen($s));
        }
        fclose($fd);
        return $result;
    }

	
	/**
	 * Do a recursive scan, looking for dictionaries 
	 * 
	 */
    function Dir($path) {
		if (substr($path,-1) != DIRECTORY_SEPARATOR) $path .= DIRECTORY_SEPARATOR;
		$r = opendir($path);
		while ($d = readdir($r)) {
		    if ($d[0] == '.') continue;
	
		    # Match .ifo file
		    if (preg_match("/^(.*?)\.ifo$/", $d, $out)) {
				$data = $this->readIfo($path.$d);

				# Look for IDX file
				if (file_exists($path.$out[1].".idx"))
					$data["idx_path"] = $path.$out[1].".idx";
				else $data["error"] = "Index (.idx) file not found";
				
				# Look for DICT file
				if (file_exists($path.$out[1].".dict"))
					$data["dict_path"] = $path.$out[1].".dict";
				else if (file_exists($path.$out[1].".dict.dz"))
					$data["dict_path"] = $path.$out[1].".dict.dz";
				else $data["error"] = "Dictionary file not found";
				
				$this->dic_list[] = $data;
		    }
		    
		    # Match dir           
		    if (is_dir($path.$d)) $this->Dir($path.$d);
		}
		closedir($r);
    }

	/**
	 * Save all dictionaries found
	 */
    function save() {
		foreach($this->dic_list as $dic) {
		    if (Z::c()->selectCell("SELECT count(*) FROM t_dic WHERE fidx_path = ?", $dic["idx_path"])) continue;
		    $tmp = array(
				"fstate_id" => 1,
				"fidx_path" => $dic["idx_path"],
				"fdict_path" => $dic["dict_path"],
				"fversion" => $dic["version"],
				"fwordcount" => $dic["wordcount"],
				"fidxfilesize" => $dic["idxfilesize"],
				"fbookname" => $dic["bookname"],
				"fdate" => $dic["date"],
				"fsts" => $dic["sametypesequence"],
				"fdescription" => $dic["description"],
				"ferror" => $dic["error"],
		    );

		    # Set error state
		    if ($tmp["ferror"]) $tmp["fstate_id"] = -1;
		    
			Z::c()->query("INSERT INTO t_dic (?#) VALUES (?a)", array_keys($tmp), array_values($tmp));
		}
    }

	/**
	 * Clean all dictionaries
	 */
	function clean() {
		Z::c()->query("DELETE FROM t_dic");
		Z::c()->query("DELETE FROM t_idx");
	}

}


?>