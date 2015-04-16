<?
defined( '_VALID_INGWER' ) or die( 'Restricted access' );
require_once('init.php');


if (array_key_exists('f',$_REQUEST) ) {
	$form = $_REQUEST['f'];
	switch ($form['data']){
		case 'all':
			$paperId= array();
			$db->select($tbPaper,'id');
			while (($rs = $db->nextQ())!==false){
				if (isset($rs['id'])) $paperId[]=$rs['id'];
			}
			break;
		case 'currentSearch':
			$search = new Search();
				$paperId = $search->searchPaperFromSession(false,false,true);
			break;
		case 'saveSearch':
			$search = new Search(-1,$form['search']);
			if ($search->Data['id'] > 0 )
				$paperId=$search->searchPaper(true);
			else $paperId = array();
			break;
	}

	$export = new Session('export');
	$export->Data['paperId'] = $paperId;
	$export->Data['form'] = $form;
	$export->save();

	switch ($form['format']){
		case 'cwb': 
			if (!backGroundCall('ExportHintergrundCWB')) 
                		$session= session_id();
				include('include/export/exportCWB.php');
			break;
		case 'lexico3': 
			if (!backGroundCall('ExportHintergrundLexico3')) 
                		$session= session_id();
				include('include/export/exportLexico3.php');
			break;
		case 'xml':
			if (!backGroundCall('ExportHintergrundXML')) 
                		$session= session_id();
				include('include/export/exportXML.php');
			break;
		case 'cvs':
			if (!backGroundCall('ExportHintergrundCSV')) 
                		$session= session_id();
				include('include/export/exportCSV.php');
			break;
}
}
?>
