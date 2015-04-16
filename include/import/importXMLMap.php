<?
defined( '_VALID_INGWER' ) or die( 'Restricted access' );


//ini_set('auto_detect_line_endings',true);

$F = fopen($importFile->Data['file'],'r');
$paperMeta = new PaperMetaDataTypeList();
$paper = new Paper();
if (array_key_exists('f',$_REQUEST)) $form = $_REQUEST['f'];
else $importFile->Data['form'];

$overwrite = (array_key_exists('overwrite',$form)) ? true : false;

if (!isset($xmlMap)){
	$xmlMap = array();
	foreach ($form as $kF=>$vF){
		if (array_key_exists($vF,$paperMeta->List) ) $xmlMap[$kF] = $paperMeta->List[$vF];
		if ($vF==1) $xmlMap[$kF] = '1';
	}
}

$importSession->Data['count'] = 0;
$importSession->Data['countError'] = 0;
$importSession->Data['countAll'] = 0;
$importSession->Data['dup'] = 0;
$importSession->Data['countBegin'] = 0;
$importSession->Data['status'] = 'start';
$checkDuplicate = array_key_exists('overwrite',$form);
$titlecontent = "";

if (!array_search('1',$xmlMap)){
	$message .= "Artikelumschließendes XML-Tag wird benötigt\n";
	$page['content']['xmlMap']=$form;
	$page['content']['overwrite']=$overwrite;
	$page['content']['message']=$message;
	$page['content']['file'] = "importXML";
	$paperMeta = new PaperMetaDataTypeList();
	$page['content']['PaperMetaDataType']=$paperMeta->List;
	foreach($paperMeta->List as $kP=>$vP){
		if (!$paperMeta->isImport($kP)) unset($page['content']['PaperMetaDataType'][$kP]);
	}
	//unset($page['content']['PaperMetaDataType']['word_count']);
	//unset($page['content']['PaperMetaDataType']['lemma_count']);
}elseif($F){
	
	//Anzahl der Artikel
	function startAnzahl($parser, $name, $attrs) {
	}

	function endAnzahl($parser, $name) {
		global $xmlMap,$paper,$currentTag,$importSession,$checkDuplicate,$message,$tbPaper;

		$currentTag = "";
		if (array_key_exists($name,$xmlMap) && $xmlMap[$name] == '1'){
			$importSession->Data['countBegin']++;
		}
	}

	$xml_parser = xml_parser_create('utf-8');
	// use case-folding so we are sure to find the tag in $map_array
	xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, true);
	xml_set_element_handler($xml_parser, "startAnzahl", "endAnzahl");
	
	while ($data = fread($F, 4096)) {
		if (!xml_parse($xml_parser, $data, feof($F))) {
			$importSession->Data['countError']++;
			$message .= sprintf("XML error: %s at line %d",	xml_error_string(xml_get_error_code($xml_parser)),
					xml_get_current_line_number($xml_parser));
			}
		}
	xml_parser_free($xml_parser);
	rewind($F);
	$importSession->save();

	function startElement($parser, $name, $attrs) {
		global $xmlMap,$paper,$currentTag,$importSession;

		if (array_key_exists($name,$xmlMap) && $xmlMap[$name] != '1') $currentTag = $name;
		else $currentTag = "";
	}

	function endElement($parser, $name) {
		global $session,$xmlMap,$paper,$currentTag,$importSession,$checkDuplicate,$message,$tbPaper;

		$currentTag = "";
		if (array_key_exists($name,$xmlMap) && $xmlMap[$name] == '1'){
			$importSession->Data['countAll']++;
			foreach ($xmlMap as $kX => $vX){
				if (array_key_exists($vX['name'],$paper->Data) ){
					if ($vX['meta_type']== 'date')  {
						$paper->Data[$vX['name']]=parseDate(trim($paper->Data[$vX['name']]));
					}
					elseif($vX['meta_type'] == 'multilist') {
						$paper->Data[$vX['name']]= preg_split('/[\s]*;[\s]*/',trim($paper->Data[$vX['name']]));
					}
					else{
						$paper->Data[$vX['name']]=trim($paper->Data[$vX['name']]);
						if ($vX['field_name']== 'title' && $vX['table']== $tbPaper) $title= $paper->Data[$vX['name']] ."\n";
						if ($vX['field_name']== 'subtitle' && $vX['table']== $tbPaper) $subtitle= $paper->Data[$vX['name']]."\n";
					}
				}
			}
			$title = (isset($title)) ? $title : "";
			$title .= (isset($subtitle)) ? $subtitle : "";
			if ($title != "") {
				$paper->Data[$paper->contentKey] = $title . "\n" 
				. ((array_key_exists($paper->contentKey,$paper->Data)) ? $paper->Data[$paper->contentKey] : "");
			}
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
			$importSession->Data['message'] =  $message;
			$importSession->save();

			$paper->Data = array('id'=>0);
			$paper->Annotation = NULL;
			/*unset($paper);
			$paper = new Paper();*/
		}
	}

	function characterData($parser, $data){ 
		global $xmlMap,$paper,$currentTag;


		if ($currentTag!="" && array_key_exists($currentTag,$xmlMap)) {
			if (array_key_exists($xmlMap[$currentTag]['name'], $paper->Data)) $paper->Data[$xmlMap[$currentTag]['name']].=$data;
			else $paper->Data[$xmlMap[$currentTag]['name']]=$data;
		}
	}

	$xml_parser = xml_parser_create('utf-8');
	// use case-folding so we are sure to find the tag in $map_array
	xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, true);
	xml_set_element_handler($xml_parser, "startElement", "endElement");
	xml_set_character_data_handler($xml_parser, "characterData");
	
	while ($data = fread($F, 4096)) {
		if (!xml_parse($xml_parser, $data, feof($F))) {
			$importSession->Data['countError']++;
			$message .= sprintf("XML error: %s at line %d",	xml_error_string(xml_get_error_code($xml_parser)),
					xml_get_current_line_number($xml_parser));
			}
		}
	xml_parser_free($xml_parser);
	fclose($F);
	$paper = NULL;
	unset($paper);
				$paper = new Paper();
	$message .= "{$importSession->Data['count']} Artikel richtig eingelesen\n{$importSession->Data['countError']} Artikel wegen Fehler nicht eingelesen\n{$importSession->Data['dup']} Duplikate Aktualisiert\n";
	unlinkD($importFile->Data['file']);
}
else {
	$message .= "kann Datei nicht lesen\n";
	unlinkD($importFile->Data['file']);
}

$importSession->Data['status']='end';
$importSession->Data['message'] =  $message;
$importSession->save();
?>
