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

switch($form['trennzeichen']){
	case ",": $sep = ",";
		break;
	case ";": $sep = ";";
		break;
	case "t": $sep = "\t";
		break;
	default: $sep = (array_key_exists('eigeneszeichen',$form) && strlen($form['eigeneszeichen'])==1)? $form['eigeneszeichen']: ";";
		break;
}
$newline= (array_key_exists('zeilenschaltung',$form) && $form['zeilenschaltung']==1)?False:True;
$surround= (array_key_exists('inzeichen',$form) && $form['inzeichen']==1)?True:False;
$firstline= (array_key_exists('erstezeile',$form) && $form['erstezeile']==1)?True:False;
foreach ($paperId as $vP){
	$paper=new Paper(array('id'=>$vP),true);
	if ($paper->Data['id'] != $vP) continue;
	if ($firstline) {
		$paper->cvsFirstLinePaper($handle,$sep,$newline,$surround);
		$firstline=False;
	}
	$paper->cvsPaper($handle,$sep,$newline,$surround);
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
$exportSession->Data['type'] = 'csv';
$exportSession->save();
exit(0);
?>
