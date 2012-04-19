<?
class Z {
    static $conn = FALSE;
    static $config = array();

    
    /* Returns database connection */
    function c() {
        global $conn;
        if (self::$conn) return self::$conn;
        
		self::$conn = DbSimple_Generic::connect(sprintf(
		    "mysql://%s:%s@%s/%s",
	    	Z::config("db_user"),
	    	Z::config("db_password"),
	    	Z::config("db_host"),
	    	Z::config("db_database")
		));
	
		/* Set error handler */
		if (!function_exists("databaseErrorHandler"))  {
		function databaseErrorHandler($message, $info) {
	    // 	Если использовалась @, ничего не делать.
	    	if (!error_reporting()) return;
		    // Выводим подробную информацию об ошибке.
	    	var_dump($message);
	    	var_dump($info);
	    	//	debug($message);
	    	//	debug($info);
	    	exit();
		}
		}
		self::$conn->setErrorHandler('databaseErrorHandler');
          
        /*$conn->setLogger('myLogger');
        function myLogger($db, $sql) {
          // Находим контекст вызова этого запроса.
        //  $caller = $db->findLibraryCaller();
        //  $tip = "at ".@$caller['file'].' line '.@$caller['line'];
          // Печатаем запрос (конечно, Debug_HackerConsole лучше).
        //  echo "<xmp title=\"$tip\">"; 
        //  print_r($sql); 
        //  echo "</xmp>";
          debug($sql);
        }
          */
		self::$conn->query("SET names utf8");
    }
    
    
    function config($var) {
		return self::$config[$var];
    }
    
    function configMerge($upd) {
		self::$config = array_merge(self::$config, $upd);
    }
    
}
                        


function dict_import($info_file,$idx_file,$dict_file) {
	global $conn;
	
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
	$dic_id = $conn->query('INSERT INTO t_dic (?#) VALUES(?a)', array_keys($dic_info), array_values($dic_info));


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
		echo $word."<br/>";

		// Read offset from index
		$start = unpack("I",strrev(gzread($fd_idx,4))); $start=$start[1];
		$len = unpack("I",strrev(gzread($fd_idx,4))); $len=$len[1];

		// Read article text
		gzseek($fd_dict,$start);
		$text = gzread ($fd_dict,$len);

		$data = array(
			"fdic_id" => $dic_id,
			"fword" => $word,
			"ftext" => $text
		);
		//$conn->query('INSERT DELAYED INTO t_word (?#) VALUES(?a)', array_keys($data), array_values($data));
	} while (!gzeof($fd_idx));

	gzclose($fd_idx);
	gzclose($fd_dict);
}

function strip_magic_slashes($astr) {
    if (is_array($astr)) {
	foreach($astr as $k => $v) $astr[$k] = strip_magic_slashes($v);
    } else {
	$astr = stripslashes($astr);
    }
    return $astr;
}

/**
 * Convert user input to number
 */
function inputNumber($str) {
	$str = str_replace(",",".", $str);
	$str = str_replace(" ","", $str);
	return trim($str);
} 

function no_cache() {
    Header("Pragma: no-cache");
    Header("Cache-Control: no-cache");
    Header("Expires: Thu Jan  1 00:00:00 1970");
}


#-----------------------------------------------------------------------------------------
# Database management and helpers 
#-----------------------------------------------------------------------------------------

/**
 * Returns collecttion as array of rows of array of fields
 */
function d_getCollectionData($collection) {
	$result = array();
	foreach($collection as $v) {
		$item = array();
		foreach($v as $k2 => $v2) {
			$item[ $k2 ] = $v2;
		}
		if (isset($v->fid)) $item['fid'] = $v->fid; 
		$result[] = $item;
	}
	return $result;
}

// Show unhandled exception
function handleException($e) {
	global $config, $user;
	
	# Try to extract current user
	if (is_object($user))
		$user_caption = $user->getCaption()." #".$user->fid;
	
  	ob_start();
  	include "php/template/error.php";
	$error = ob_get_contents();
	
	# Save to file
	$fd = @fopen($config['data_path'].DIRECTORY_SEPARATOR."error.log", "ab");
	if (!$fd) return;
	fwrite($fd, "-- ".date("d-m-Y H:i:s")." ----------------------------------------------------------------------------------");
	fwrite($fd, $error);
	fclose($fd);
	
	# try to send mail
	try {
		require_once('phpmailer/class.phpmailer.php');
		
		$mail = new PHPMailer();
		$mail->IsSMTP(); // telling the class to use SMTP
		$mail->Host       = $config["mail_smtp_host"]; // SMTP server
		#$mail->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)
		$mail->CharSet = "UTF-8";
		$mail->SetFrom($config["mail_from"]);
		$mail->Subject    = "FMFB MasterScore APS Error";
		$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
		$mail->MsgHTML($error);
		
		$address = "whoto@otherdomain.com";
		foreach($config['error_mail'] as $v)
			$mail->AddAddress($v);
		$mail->Send();
	} catch (Exception $e) {}
	
}

/**
 * Make data RTF compliant
 */
function escapeRtf($data) {
	if (empty($data)) return $data;
    if (is_array($data)) {
		foreach($data as $k => $v) $data[$k] = escapeRtf($v);
    } else {
    	$data = mb_convert_encoding($data,"UTF-16","UTF-8");
		$data = str_split($data,2);
		$tmp = "";
		foreach($data as $k => $v) $tmp .= '\u'.(ord($v[0])*256 + ord($v[1])).'?';
		$data = $tmp;
   	}
   	return $data;
}
	
	

#-----------------------------------------------------------------------------------------
# Site management and helpers
#-----------------------------------------------------------------------------------------
function getTemplate( $atpl ) {
	$file_name = "php/template/{$atpl}.php";
	if (!file_exists($file_name)) return "TEMPLATE: {$atpl}.{$lang} not found";
	return join("", file($file_name));
}

function getTemplateEval( $atpl, $variable = array() ) {
	$file_name = "php/template/{$atpl}.php";
	if (!file_exists($file_name)) return "TEMPLATE: {$atpl}.{$lang} not found";

	# Variables for template
	extract($variable);
	
	ob_start();	
	eval("?>".join("", file($file_name))."<?");
	$content = ob_get_contents();
	ob_end_clean();
	return $content;
}

// Convert associated array to select array
function select_convert($arr) {
	$result = array();
	foreach($arr as $k => $v) {
		$result[] = array("fid" => $k, "fcaption" => $v);
	}
	return $result;
}

function select_option($data, $selected, $null = false) {
	if (!is_array($data)) return;
	foreach($data as $v) {
		if ($v["fid"] == $selected) {
			echo '<option value="'.htmlspecialchars($v["fid"]).'" selected>'.htmlspecialchars($v["fcaption"]).'</option>';
		} else {
			echo '<option value="'.htmlspecialchars($v["fid"]).'">'.htmlspecialchars($v["fcaption"]).'</option>';
		}
		echo "\n";
	}
}

function select_option_dic($dictionary, $selected, $null = false) {
	$data = SPD_Dic::dicSelect($dictionary);
	
	foreach($data as $v) {
		if ($v["fid"] == $selected) {
			echo '<option value="'.htmlspecialchars($v["fid"]).'" selected>'.htmlspecialchars($v["fcaption"]).'</option>';
		} else {
			echo '<option value="'.htmlspecialchars($v["fid"]).'">'.htmlspecialchars($v["fcaption"]).'</option>';
		}
		echo "\n";
	}
}

function select_option_obj($data, $selected, $null = false) {
	foreach($data as $v) {
		if ($v->get("fid") == $selected) {
			echo '<option value="'.htmlspecialchars($v->get("fid")).'" selected>'.htmlspecialchars($v->getCaption()).'</option>';
		} else {
			echo '<option value="'.htmlspecialchars($v->get("fid")).'">'.htmlspecialchars($v->getCaption()).'</option>';
		}
	echo "\n";
	}
}

function htmlsafe($astr) {
        if (is_array($astr)) {
                foreach($astr as $k => $v) $astr[$k] = htmlsafe($v);
        } else {
                $astr = htmlspecialchars( $astr);
        }
        return $astr;
}

function sh($astr) {
	return htmlspecialchars($astr);
}
function shbr($astr) { return nl2br(htmlspecialchars($astr));}
function shnbr($astr) { return htmlspecialchars($astr);}

// TODO: check where to apply this function
function sanitation($value) {
	return strip_tags($value);
}

// safe html date (rfs:3339)
function shd($date) {
	if ($date == "") return "";
	return htmlspecialchars(date("d.m.Y",strtotime($date)));
}

// safe html number
function shn($number, $digits = 0) {
	//if ($number == "") return "";
	return number_format($number, $digits, ".", "" );
}

function localeData($type) {
	if ($type == 'month') return array("РЇРЅРІ", "Р¤РµРІ", "РњР°СЂ", "РђРїСЂ", "РњР°Р№", "Р�СЋРЅ", "Р�СЋР»", "РђРІРі", "РЎРµРЅ", "РћРєС‚", "РќРѕСЏ", "Р”РµРє");
}

function sendFile($file_name, $content_type, $content_length = 0) {
	if (eregi("MSIE", $_SERVER['HTTP_USER_AGENT'])) {
    	// IE download
		header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
		header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: public"); 
		header("Content-Description: File Transfer");
		if ($content_length != 0) header("Content-Length: ".$content_length);
		header('Content-Type: '.$content_type);
		
		// IE Cant really open russian file names
		// TODO: Fix it with translit here
		$file_name = mb_convert_encoding($file_name,"cp1251", "UTF8");
		header('Content-Disposition: attachment; filename="'.$file_name.'"');
   	} else {
   		// other download
		if ($content_length != 0) header("Content-Length: ".$content_length);
   		header("Content-Type: ".$content_type."; ; charset=utf-8");
		header('Content-Disposition: attachment; filename="'.mb_encode_mimeheader($file_name).'"');
	}	
}

#-----------------------------------------------------------------------------------------
# Error handler
#-----------------------------------------------------------------------------------------
/**
 * Create error messag in session and assign id number to it
 */
function errorSet($message) {
	$id = rand(1,100000);
	$_SESSION["error_".$id] = $message;
	return $id;	
}

function errorGet($id) {
	if (intval($id) != $id) return false;
	if (isset($_SESSION["error_".$id])) return $_SESSION["error_".$id];
	return "";
}


#-----------------------------------------------------------------------------------------
# Date functions
#-----------------------------------------------------------------------------------------
function dateFirstDayOfMonth($date) {
	$year = date("Y", $date);
	$month = date("m", $date);
	return mktime(0,0,0,$month,1,$year);
}
function dateLastDayOfMonth($date) {
	$year = date("Y", $date);
	$month = date("m", $date);
	return mktime(0,0,0,$month+1,0,$year);
}

// Convert date to database specific format
function dateToDB($date) {
	global $config;
	return date($config['db_date_format'], strtotime($date));	
}

function datetimeToDB($datetime) {
	global $config;
	return date($config['db_datetime_format'], strtotime($datetime));	
}

function redirect($url) {                                                                                                                               
	Header("Location: ".$url);
	exit;                                                                                                                                            
}

?>