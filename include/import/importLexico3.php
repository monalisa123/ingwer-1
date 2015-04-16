<?
defined( '_VALID_INGWER' ) or die( 'Restricted access' );


//ini_set('auto_detect_line_endings',true);

$importFile->Data['file'] = tempnam($uploadPath, "Ing");
$importFile->save();

$xmlLexico3Map = array (
	'DOCUMENT'=>'1',
	'ZEITUNG'=>'Zeitung',
	'TITLE'=>'Titel',
	'SEITE'=>'Seite',
	'RUBRIK'=>'Ressort',
	'AUTOR'=>'Autor',
	'DATUM'=>'Datum',
	'DOMINANZ'=>'Dominanz',
	'ERSCHEINT'=>'Erscheint',
	'SUBTITLE'=>'Untertitel',
        'PRESSETEXTSORTE'=>'Pressetextsorte',
        'GEOPOLITISCHERBEZUG'=>'Geopolitischer Bezug',
	'SUCHWORT'=>'Suchwort',
	'SERIE'=>'Serie',
	'TEXT'=>'Textkörper'
);

$paperMeta = new PaperMetaDataTypeList();
$xmlMap = array ();
foreach($xmlLexico3Map as $kX=>$vX){
	if (array_key_exists($vX,$paperMeta->List)) $xmlMap[$kX] = $paperMeta->List[$vX];
	if ($vX==1) $xmlMap[$kX] = '1';
}

$P = popen("$perl $lexico2Xml --encoding utf8 $tmpFile", "r");
$F = fopen($importFile->Data['file'],'w');

if($F && $P ){
	while (!feof($P)) fwrite($F,fgets($P));

	if (($e=pclose($P))!= 0) $message .= "$lexico2Xml Fehler : $e\n";
	unlinkD($tmpFile);

	if (fclose($F)) {
		if ($e==0) {
			include("include/import/importXMLMap.php");
		}
	}
	else $message .= "Schreibfehler bei {$importFile->Data['file']} \n";
}
else $message .= "kann Datei nicht lesen oder $perl $lexico2Xml starten\n";
$importSession->Data['message']=$message;
$importSession->save();
?>
