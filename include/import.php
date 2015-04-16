<?
defined( '_VALID_INGWER' ) or die( 'Restricted access' );
require_once('init.php');



if ( array_key_exists('file',$_FILES) && array_key_exists('f',$_POST)) {
	$file = $_FILES['file'];
	
	if ($file['error'] == 0 && is_uploaded_file($file['tmp_name'])) {
		$form = $_POST['f'];
		$overwrite = (array_key_exists('overwrite',$form)) ? true : false;
		switch ($form['format']){
			case 'text': 
				$tmpFile = copyToUtf8($file['tmp_name']);
				$message = "Datei hochgeladen\n\n";
				$importSession = new Session('import');
				$importSession->del();
				$importFile = new Session('importForm');
				$importFile->Data['file'] = $tmpFile;
				$importFile->Data['form'] = $form;
				$importFile->save();
				if (!backGroundCall('ImportHintergrundText')){ 
                			$session= session_id();
                			$importSession = new Session('import',$session);
					include('include/import/importText.php');
				}

				break;
			case 'lexico3': 
				$tmpFile = copyToUtf8($file['tmp_name']);
				$message = "Datei hochgeladen\n\n";
				$importSession = new Session('import');
				$importSession->del();
				$importFile = new Session('importForm');
				$importFile->Data['file'] = $tmpFile;
				$importFile->Data['form'] = $form;
				$importFile->save();
				if (!backGroundCall('ImportHintergrundLexico3')) {
                			$session= session_id();
                			$importSession = new Session('import',$session);
					include('include/import/importLexico3.php');
				}
				break;
			case 'xmlcategory': 
				$tmpFile = tempnam($uploadPath, "Ing");
				move_uploaded_file ($file['tmp_name'] , $tmpFile );	
				$message = "Datei hochgeladen\n\n";
				$importSession = new Session('import');
				$importSession->del();
				$importFile = new Session('importForm');
				$importFile->Data['file'] = $tmpFile;
				$importFile->Data['form'] = $form;
				$importFile->save();
				if (!backGroundCall('ImportHintergrundXMLCategory')){ 
                			$session = session_id();
                			$importSession = new Session('import',$session);
					include('include/import/importXMLCategory.php');
				}
				break;
			case 'xml':
				$tmpFile = tempnam($uploadPath, "Ing");
				move_uploaded_file ($file['tmp_name'] , $tmpFile );	
				$message = "Datei hochgeladen\n\n";
				$importSession = new Session('import');
				$importSession->del();
				$importFile = new Session('importForm');
				$importFile->Data['file'] = $tmpFile;
				$importFile->Data['form'] = $form;
				$importFile->save();
				$page['content']['file'] = "importXML";
				include('include/import/importXML.php');
				break;
		}
		$page['content']['message'] = $message;
	}
	else $page['content']['message'] = "uploadFehler";
}
elseif ( array_key_exists('f',$_REQUEST) && array_key_exists('XMLMap',$_REQUEST)) {
	$message="";
	$importFile = new Session('importForm');
	$importFile->Data['form'] = $_REQUEST['f'];
	$importFile->save();
	if (!backGroundCall('ImportHintergrundXML')) 
                $session= session_id();
                $importSession = new Session('import',$session);
		include('include/import/importXMLMap.php');
	$page['content']['message'] = $message;
}
?>
