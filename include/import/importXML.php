<?
defined( '_VALID_INGWER' ) or die( 'Restricted access' );


//ini_set('auto_detect_line_endings',true);

$F = fopen($tmpFile,'r');

$paperMeta = new PaperMetaDataTypeList();
$page['content']['PaperMetaDataType']=$paperMeta->List;
foreach($paperMeta->List as $kP=>$vP){
	if (!$paperMeta->isImport($kP)) unset($page['content']['PaperMetaDataType'][$kP]);
}
//unset($page['content']['PaperMetaDataType']['word_count']);
//unset($page['content']['PaperMetaDataType']['lemma_count']);

if($F){
	$xmlMap = array();

	function startElement($parser, $name, $attrs) {
		global $xmlMap;
		$xmlMap[$name]=1;
		}

	function endElement($parser, $name) {}

	function characterData($parser, $data){} 

	$xml_parser = xml_parser_create('utf-8');
	// use case-folding so we are sure to find the tag in $map_array
	xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, true);
	xml_set_element_handler($xml_parser, "startElement", "endElement");
	xml_set_character_data_handler($xml_parser, "characterData");
	
	while ($data = fread($F, 4096)) {
		if (!xml_parse($xml_parser, $data, feof($F))) {
			$message .= sprintf("XML error: %s at line %d",	xml_error_string(xml_get_error_code($xml_parser)),
					xml_get_current_line_number($xml_parser));
			}
		}
	xml_parser_free($xml_parser);

}
else $message .= "kann Datei nicht lesen\n";
$page['content']['xmlMap']=$xmlMap;
$page['content']['overwrite']=$overwrite;
$page['content']['message']=$message;
?>
