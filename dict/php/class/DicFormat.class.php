<?php

class DicFormat {

    function format( $text ) {

		$tgs = array("k","abr","c","ex","dtrn","nu","tr","rref");
		// Replace tags
		foreach($tgs as $t) {
			$text = str_replace(
				array("<".$t.">","</".$t.">"),
				array("<span class='xdxf_".$t."'>","</span>"),
				$text
			);
		}

		# Replace links to other words
		preg_match_all("/<kref>(.*?)<\/kref>/im", $text, $out);
		foreach($out[0] as $k => $v) 
			$text = str_replace(
				$out[0][$k], 
				'<a class="link" href="javascript:showWord(\''.urlencode($out[1][$k]).'\');">'.$out[1][$k].'</a>',
				$text
			);
		return $text;	
    }
}
?>