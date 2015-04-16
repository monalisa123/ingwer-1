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
foreach ($paperId as $vP){
	$paper=new Paper(array('id'=>$vP),true);
	if ($paper->Data['id'] != $vP) continue;
	$paper->lexico3Paper($handle);
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
$exportSession->Data['type'] = 'lexico3';
$exportSession->save();
exit(0);
?>
