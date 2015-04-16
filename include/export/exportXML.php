<?
defined( '_VALID_INGWER' ) or die( 'Restricted access' );


$cat = new CategoryList();
$exportSession = new Session('export',(isset($session))? $session : false);
$form = $exportSession->Data['form'];
$paperId = $exportSession->Data['paperId'];
$exportSession->Data['countAll'] =  count($paperId);
$exportSession->Data['message'] =  "start";
$exportSession->Data['status'] =  "start";
$exportSession->Data['count'] = 0;
$exportSession->save();

$xmlFile = tempnam($uploadPath, "Ing");

$handle = fopen($xmlFile, "w");
fwrite($handle, '<?xml version="1.0" encoding="UTF-8"  standalone="yes"?>' . "\n");
if ($form['xmldata']=="category"){
	fwrite($handle, "<categories>\n");
	foreach ($cat->List as $k=>$c){
		if ((array_key_exists('xmlpos',$form) && $form['xmlpos']==1) 
			|| !in_array($c->Data['id'],$cat->ListReadonly))	{
				$c->xmlCategory($cat,$handle);
			}
	}
	fwrite($handle, "</categories>\n");
} else {
	$del= (array_key_exists('xmlpathchar',$form) && strlen($form['xmlpathchar'])==1)? $form['xmlpathchar']:"/";
	foreach($cat->List as $k=>$c){
		$c->parentPath($del,$cat);
	}
	fwrite($handle, "<meta>\n\t<version>ingwer XML Export Version ".$GLOBALS['version']."</version>\n\t<pathDelimiter>$del</pathDelimiter>\n</meta>\n\n<paperlist>\n");
								
	foreach ($paperId as $vP){
		$paper=new Paper(array('id'=>$vP),true);
		if ($paper->Data['id'] != $vP) continue;
		$paper->xmlPaper($cat,$handle);
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
	fwrite($handle, "</paperlist>\n");
}
fclose($handle);
$message="";
$exportSession->Data['message'] =  $message;
$exportSession->Data['status'] = 'file';
$exportSession->Data['file'] = $xmlFile;
$exportSession->save();
exit(0);
?>
