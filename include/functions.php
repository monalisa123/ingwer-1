<?
defined( '_VALID_INGWER' ) or die( 'Restricted access' );

$monthName = array(
	'/^jan/i',
	'/^feb/i',
	'/^m.[^iy]/i',
	'/^ap/i',
	'/^ma[iy]/i',
	'/^jun/i',
	'/^jul/i',
	'/^au/i',
	'/^se/i',
	'/^ok/i',
	'/^no/i',
	'/^de/i');

function parseDate($d){
	global $monthName;

	$date = preg_split('/[^\wäÄ]{1,10}/',trim($d));
	if (count($date) != 3 or intval($date[0])==0 or intval($date[1])==0 or intval($date[2])==0) return $d;
	if ((int)$date[0] < 32) {
		$day = (int) $date[0];
		$year =(int) $date[2];
	}else {
		$day = (int) $date[2];
		$year =(int) $date[0];
	}

	if (strlen($date[1])>2) {
		foreach ($monthName as $k => $v) {
			if (preg_match($v,$date[1])) {
				$month=$k+1;
				break;
			}
		}
	} else $month = (int) $date[1];
	if (!isset($month) || !isset($day) ||!isset($year)) return $d;
	return strftime ('%Y-%m-%d',strtotime("$year-$month-$day"));
//	return "$year-" . str_pad($month,2,'0', STR_PAD_LEFT). "-" . str_pad($day,2,'0', STR_PAD_LEFT);
}

function copyToUtf8($sourceName,$destinationName=False){
	global $uploadPath,$charsetList;

	$cList = array();
	foreach($charsetList as $k) $cList[$k] = array("line","len","score");
	if(!$destinationName) $destinationName=tempnam($uploadPath, "Ing");
	$In = fopen($sourceName,"r");
	$Out = fopen($destinationName,"w");
	$char = "";

	while (!feof($In)){
		$line = fgets($In);
		$line = preg_replace ( '/\r\n|\r/' , "\n" , $line);
		if ($char=="") {
			//Input Charset noch unbekannt
			$maxLen=0;
			$maxScore=0;
			foreach($cList as $k => $v){
				$level = error_reporting (E_ALL & ~E_NOTICE );
				$cList[$k]['line'] = iconv($k,'UTF8//IGNORE',$line);
				error_reporting ($level);

				$cList[$k]['len'] = strlen($cList[$k]['line']);
				$cList[$k]['score'] = (preg_match ('/ä|ü|ö/i' , $line))? 0 : 1;
				$maxScore = ($maxScore>$cList[$k]['score']) ? $maxScore:$cList[$k]['score'];
				$maxLen= ($maxLen>$cList[$k]['len']) ? $maxLen:$cList[$k]['len'];
			}
			//Ueberpruefung
			foreach($cList as $k => $v){
				if (($v['len'] < $maxLen || $v['score'] < $maxScore) && count($cList)>1){
					unset($cList[$k]);
					continue;
				}
			}
			if (count($cList) == 1) $char = key($cList);

			$c = reset($cList);

			$line = $c['line'];
		} else $line = iconv($char,'UTF8//TRANSLIT',$line);
		fputs($Out,$line);
	}
	fclose($Out);
	fclose($In);
	return $destinationName;

}

function backGroundCall($nav){
	global $httpUser, $httpPass;

	$urlScheme= (array_key_exists('HTTPS',$_SERVER) && $_SERVER['HTTPS']=="on")?"ssl://":"";
        $fp = fsockopen($urlScheme.$_SERVER['SERVER_NAME'], $_SERVER['SERVER_PORT'],$err,$errstr);
	if($fp === false) return false;

	$auth = $httpUser.":".$httpPass ;
	$authString=base64_encode($auth);

	$ajaxScript = preg_replace ( '/\/[^\/]*$/' , '/indexAjax.php' ,$_SERVER['PHP_SELF']);
	$out = "GET $ajaxScript?nav=$nav&session=" . session_id() ." HTTP/1.1\r\n";
	$out .= "Authorization: Basic $authString\r\n";
	$out .= "Host: ". $_SERVER['SERVER_NAME'] . "\r\n";
	$out .= "Connection: Close\r\n\r\n";
	
	fwrite($fp, $out);
	fclose($fp);
	return true;
}




function unlinkD($name){
	return unlink($name);
	return 0;
}
?>
