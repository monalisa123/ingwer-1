<?
defined( '_VALID_INGWER' ) or die( 'Restricted access' );
$cat = new CategoryList();
$exportSession = new Session('export',(isset($session))? $session : false);
$form = $exportSession->Data['form'];
if (!array_key_exists('meta',$form) || !is_array($form['meta'])) $form['meta'] = array();
$paperId = $exportSession->Data['paperId'];
$exportSession->Data['countAll'] =  count($paperId);
$exportSession->Data['message'] =  "start";
$exportSession->Data['status'] =  "start";
$exportSession->Data['count'] = 0;
$exportSession->save();

if (array_key_exists('index',$form)) {
	$corpus = (array_key_exists('corpus',$form) && strlen($form['corpus'])>0)?escapeshellcmd(preg_replace('[^0-9a-z]','',$form['corpus'])):'Unbekannt';
	$xmlFile = tempnam($uploadPath, "Ing");

	$handle = fopen($xmlFile, "w");
	fwrite($handle, '<?xml version="1.0" encoding="UTF-8"  standalone="yes"?>' . "\n");

	foreach ($paperId as $vP){
		$paper=new Paper(array('id'=>$vP),true);
		if ($paper->Data['id'] != $vP) continue;
		$paper->cwbPaper($cat,$handle,$form['meta']);
		$exportSession->Data['count']++;
		$exportSession2 = new Session('export',(isset($session))? $session : false);
		if (array_key_exists('status',$exportSession2->Data) && $exportSession2->Data['status'] == 'abort') { 
			$exportSession->Data['status'] =  'end';
			$message = "\n\nAbgebrochen\n\n";
			$exportSession->Data['message'] =  $message;
			$exportSession->save();
			unlinkD($xmlFile);
			exit;
		}
		$exportSession->save();
	}
	fclose($handle);
	$message="";
	if (is_writable("$corpusPath/$corpus")) 
		$message.= shell_exec("rm -r $corpusPath/$corpus 2>&1");

	$message.=shell_exec("mkdir $corpusPath/$corpus 2>&1");
	$argument = "text:0";
	foreach($paper->Data as $kP=>$kV){
		$kPXML = preg_replace(array('/\s/i','/ä/i','/ö/i','/ü/i','/ß/i','/[^a-zA-Z0-9_]/i'),
					array('','ae','oe','ue','ss',''),$kP);
		if ($kP != $paper->contentKey) $argument.="+$kPXML";	
	}
	
	$exportSession->Data['cwb'] =  "Starte CWB encode";
	$exportSession->save();
	$message.=shell_exec("$cwbEncode -f $xmlFile -d $corpusPath/$corpus -R $corpusData/registry/$corpus -c utf8 -xsB -P pos -P lemma -S $argument -S annotation:0+categoryid+categoryname+annotationid 2>&1");
	$exportSession2 = new Session('export',(isset($session))? $session : false);
	if (array_key_exists('status',$exportSession2->Data) && $exportSession2->Data['status'] == 'abort') { 
		unset($exportSession->Data['cwb']);
		$exportSession->Data['status'] =  'end';
		$message = "\n\nAbgebrochen\n\n";
		$exportSession->Data['message'] =  $message;
		$exportSession->save();
		unlinkD($xmlFile);
		exit;
	}
	$exportSession->Data['cwb'] =  "Starte CWB Makeall";
	$exportSession->save();
	$message.=shell_exec("$cwbMakeall -r $corpusData/registry -V $corpus 2>&1");
	unset($exportSession->Data['cwb']);
	$exportSession2 = new Session('export',(isset($session))? $session : false);
	if (array_key_exists('status',$exportSession2->Data) && $exportSession2->Data['status'] == 'abort') { 
		unset($exportSession->Data['cwb']);
		$exportSession->Data['status'] =  'end';
		$message = "\n\nAbgebrochen\n\n";
		$exportSession->Data['message'] =  $message;
		$exportSession->save();
		unlinkD($xmlFile);
		exit;
	}
	$page['content']['messageExport'] = $message;
	unlinkD($xmlFile);
	$exportSession->Data['message'] =  $message;
	$exportSession->Data['status'] = 'end';
	$exportSession->save();
}
else {
	/*header("Content-Type: text/xml; charset=utf-8");
	header("Content-Disposition:attachment;filename=ingwer.xml");*/
	$xmlFile = tempnam($uploadPath, "Ing");

	$handle = fopen($xmlFile, "w");
	fwrite($handle, '<?xml version="1.0" encoding="UTF-8"  standalone="yes"?>' . "\n");

	foreach ($paperId as $vP){
		$paper=new Paper(array('id'=>$vP),true);
		if ($paper->Data['id'] != $vP) continue;
		$paper->cwbPaper($cat,$handle,$form['meta']);
		$exportSession->Data['count']++;
		$exportSession2 = new Session('export',(isset($session))? $session : false);
		if (array_key_exists('status',$exportSession2->Data) && $exportSession2->Data['status'] == 'abort') { 
			$exportSession->Data['status'] =  'end';
			$message = "\n\nAbgebrochen\n\n";
			$exportSession->Data['message'] =  $message;
			$exportSession->save();
			unlinkD($xmlFile);
			exit;
		}
		$exportSession->save();
	}
	fclose($handle);
	$message="";
	$exportSession->Data['message'] =  $message;
	$exportSession->Data['status'] = 'file';
	$exportSession->Data['file'] = $xmlFile;
	$exportSession->save();
	exit(0);
}
?>
