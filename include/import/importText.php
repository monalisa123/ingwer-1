<?
defined( '_VALID_INGWER' ) or die( 'Restricted access' );


ini_set('auto_detect_line_endings',true);

$F = fopen($tmpFile,'r');
$noImportField=array('Textkörper','word_count','lemma_count','Suchwort');

$importSession = new Session('import',(isset($session))? $session : false);
$importSession->Data['countBegin']=1;


function fgetNrBlank($file,$nr=0,$trimBlank=false){

	$buf="";
	$blank=0;
	$bufBlank="";

	while (!feof($file)){
		$line=fgets($file);
		//Unix Zeilenende was sonst
		$line = preg_replace ( '/\r\n|\r/' , "\n" , $line);
		
		if ($trimBlank) {
			if (preg_match('/^\s*$/',$line)) continue;
			else return $line;
		}
			
		if (preg_match('/^\s*$/',$line)) {
			$blank++;
			$bufBlank .= $line;
		}else {
			$blank=0;
			$buf .= $bufBlank . $line;
			$bufBlank = "";
		}
		if ($blank>=$nr) {
			return $buf;
		}
	}
	return $buf;
}


//Artikel zaehlen
if($F){
	$blank=0;
	while (!feof($F) && trim(fgets($F)==""));
	while (!feof($F)) {
		$buf = trim(fgets($F));
		if ($buf=="") $blank++;
		else $blank=0;
		if ($blank==3) $importSession->Data['countBegin']++;
	}
	rewind($F);
}
$importSession->Data['message'] =  "start";
$importSession->Data['status'] =  "start";
$importSession->save();

//Artikel verarbeiten
if($F){
	$importSession->Data['count'] = 0;
	$importSession->Data['countError'] = 0;
	$importSession->Data['countAll'] = 0;
	$importSession->Data['dup'] = 0;
	$checkDuplicate = array_key_exists('overwrite',$form);

	while (!feof($F)) {
		$formatError = true;
		//Fuehrende Leerzeilen entfernen
		if (!feof($F)) $line=fgetNrBlank($F,0,true);

		if (feof($F))continue;

		//Felder einlesen
		$paper = new Paper();
		$importSession->Data['countAll']++;

		foreach ($paper->MType->List as $kM => $vM) {
			if (in_array($vM['name'],$noImportField)) continue;
			if (!$paper->MType->isExport($kM)) continue;
			if ($vM['id'] != 1) $line = fgetNrBlank($F);
			if (trim($line) == "kA") continue;
			if ($line == "") {
				$message .= "Importfehler Artikel Nr. {$importSession->Data['countAll']}: (falsche Anzahl Metainformationen):\n";
				foreach ($paper->Data as $kD => $vD) $message .= "$kD:  " . (is_array($vD)? implode(';',$vD):$vD) ."\n";
				$message .= "\n\n\n";
				$formatError = false;
				$importSession->Data['countError']++;
				break;
			}
			switch ($vM['meta_type']){
				case 'multilist':  $geo = explode(';',trim($line));
					$paper->Data[$vM['name']]=array();
					foreach($geo as $vG) {if (strlen(trim($vG))>0) $paper->Data[$vM['name']][]=trim($vG);}
					break;
				case 'int':$paper->Data[$vM['name']] = trim(preg_replace ( '/[^0-9]*([0-9]*)/' , '$1' , $line));
					break;
				case 'date': $paper->Data[$vM['name']] = trim (parseDate($line));
					break;
				default: if ($vM['name']=='Serie'){
				       		$paper->Data[$vM['name']] = trim(preg_replace ( '/Serie:?(.*)/i' , '$1' , $line));
					} else if ($vM['name']=='Seite'){
				       		$paper->Data[$vM['name']] = trim(preg_replace ( '/Seite:?(.*)/i' , '$1' , $line));
					}
					else $paper->Data[$vM['name']] = trim ($line);
					break;
			}
		}

		if ($formatError){
			if (!feof($F)){ fgetNrBlank($F,2);}
			if (!feof($F))$paper->Data[$paper->contentKey] = fgetNrBlank($F,3);
			if (!array_key_exists($paper->contentKey, $paper->Data) || $paper->Data[$paper->contentKey] == "") {
				$message .= "Importfehler Artikel Nr. {$importSession->Data['countAll']}: (kein Textkörper):\n";
				foreach ($paper->Data as $kD => $vD) $message .= "$kD:  " . (is_array($vD)? implode(';',$vD):$vD) ."\n";
				$message .= "\n\n\n";
				$importSession->Data['countError']++;
			} else {
				if ($paper->saveMetaData($checkDuplicate)){
					$importSession->Data['count']++;
					if (!$paper->treeTagger()) $message .= $paper->error;
				} else $importSession->Data['dup']++;
				$importSession2 = new Session('import',(isset($session))? $session : false);
				if (array_key_exists('status',$importSession2->Data) && $importSession2->Data['status'] == 'abort') { 
					$importSession->Data['status'] =  'end';
					$message .= "\n\nAbgebrochen\n\n";
					$message .= "{$importSession->Data['count']} Artikel richtig eingelesen\n{$importSession->Data['countError']} Artikel wegen Fehler nicht eingelesen\n{$importSession->Data['dup']} Duplikate Aktualisiert\n";
					$importSession->Data['message'] =  $message;
					$importSession->save();
					exit;
				}
				$importSession->save();
			}
		}else {
			if (!feof($F)){ fgetNrBlank($F,1);}
			if (!feof($F))$paper->Data[$paper->contentKey] = fgetNrBlank($F,3);
		}
		$paper = NULL;
		unset($paper);
		$importSession->Data['message'] =  $message;
	}
	fclose ($F);
	$message .= "{$importSession->Data['count']} Artikel richtig eingelesen\n{$importSession->Data['countError']} Artikel wegen Fehler nicht eingelesen\n{$importSession->Data['dup']} Duplikate Aktualisiert\n";
}
else $message .= "kann Datei nicht lesen $tmpFile\n";
$importSession->Data['message'] =  $message;
$importSession->Data['status'] = 'end';
$importSession->save();
unlinkD($tmpFile);
?>
