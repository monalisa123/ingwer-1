<?php
define('_VALID_INGWER',"TEST" );
define('DEBUG',"TEST" );
require_once('include/init.php');

$level = error_reporting (E_ALL);
$nav = (array_key_exists('nav',$_REQUEST)) ? $_REQUEST['nav']:((array_key_exists('nav',$_SESSION))? $_SESSION['nav']:"");
$POS = (array_key_exists('POS',$_REQUEST) && $_REQUEST['POS'] == 1) ? true:false;
$page['content']['POS'] = $POS;
if (array_key_exists('session',$_REQUEST)) $session = $_REQUEST['session'];
elseif (array_key_exists(session_name(),$_REQUEST)) $session=$_REQUEST[session_name()];
elseif (array_key_exists(session_name(),$_COOKIE)) $session=$_COOKIE[session_name()];
else $session= false;

switch ($nav){
	case 'SaveAnnotation':
		header('Content-type: application/json');
		$java = array('id'=>(int)$_REQUEST['annoId'], 'stat'=>'Fail');
		$anno = new Annotation((int)$_REQUEST['annoId']);
		if ($anno->Data['id']==0) {
			echo json_encode($java);
			return;
		}
		$anno->Data['comment'] = $_REQUEST['comment'];
		$user = new User('',$_REQUEST['user']);
		if ($user->Data['id']==0) $user->save();
		$anno->Data['user_id'] = $user->Data['id'];
		$anno->save();
		$paper = new Paper(array('id'=>$anno->Data['paper_id']),$POS);
		if ($paper->Data['id']==0) {
			echo json_encode($java);
			return;
		}
		$java = array();
		foreach ($paper->Annotation->List as $anno){
			$java[$anno->Data['id']] = array('id'=>$anno->Data['id'],'categoryId'=>$anno->Data['category_id'],'user' =>$anno->userName(),'comment'=>$anno->Data['comment'],'lastEdit'=>$anno->Data['last_edit']);
		}
		echo json_encode($java);
		break;
	case 'DeleteAnnotation':
		$page['navigation'] = "Annotation";
		$page['content']['file'] = "Annotation";
		$page['content']['subFile'] = "AnnotationPaper";
		$anno = new Annotation((int)$_REQUEST['annoId']);
		if ($anno->Data['id']==0) {
			session_start();
			$paper = new Paper(array('id'=>$_SESSION['paperId']),$POS);
		}
		else {
			$paper = new Paper(array('id'=>$anno->Data['paper_id']),$POS);
			$paper->Annotation->del($anno->Data['id']);
		}
		$page['content']['paper'] = $paper;
		$page['content']['catList'] = new CategoryList();
		$p = new Page($page);
		$p->printSubPage();
		break;
	case 'AddAnnotation':
		$page['navigation'] = "Annotation";
		$page['content']['file'] = "Annotation";
		$page['content']['subFile'] = "AnnotationPaper";
		$paper = new Paper(array('id'=>(int) $_REQUEST['paperId']),$POS);
		if (!$paper->addAnnotationLocate($_REQUEST['selected'])) header("HTTP/1.0 404 Not Found");
		$page['content']['paper'] = $paper;
		$page['content']['catList'] = new CategoryList();
		$p = new Page($page);
		$p->printSubPage();
		break;
	case 'getPaper':
		$page['navigation'] = "Annotation";
		$page['content']['file'] = "Annotation";
		$page['content']['subFile'] = "AnnotationPaper";
		$paper = new Paper(array('id'=>(int) $_REQUEST['paperId']),$POS);
		$page['content']['paper'] = $paper;
		$page['content']['catList'] = new CategoryList();
		$p = new Page($page);
		$p->printSubPage();
		break;
	case 'getPaperNr':
		$rc = $db->selectOne($tbPaper,"count(id) as count");
		$Nr = (int)(array_key_exists('paperNr',$_REQUEST) && $_REQUEST['paperNr']>=0)?$_REQUEST['paperNr']: ((array_key_exists('paperNr',$_SESSION) && $_SESSION['paperNr']>=0)?$_SESSION['paperNr']: 0);
		if ($Nr > $rc['count']-1) $Nr = ($rc['count']<1)? 0 : $rc['count']-1;
		$db->select($tbPaper,"id",False,False,"limit $Nr,1");
		$rP = $db->nextQ();
		$page['navigation'] = "Annotation";
		$page['content']['file'] = "Annotation";
		$page['content']['subFile'] = "AnnotationPaper";
		$paper = new Paper(array('id'=>(int) $rP['id']),$POS);
		$page['content']['paper'] = $paper;
		$page['content']['catList'] = new CategoryList();
		$p = new Page($page);
		$p->printSubPage();
		break;
	case 'blaetterAnnotation':
		$rc = $db->selectOne($tbPaper,"count(id) as count");
		$Nr = (int)(array_key_exists('paperNr',$_REQUEST) && $_REQUEST['paperNr']>=0)?$_REQUEST['paperNr']: 0;
		if ($Nr > $rc['count']-1) $Nr = ($rc['count']<1)? 0 : $rc['count']-1;
		$db->select($tbPaper,"id",False,False,"limit $Nr,1");
		$paper = new Paper($db->nextQ(),$POS);
		$_SESSION['paperId'] = $paper->Data['id'];

		$page['content']['paperCount'] = $rc['count'];
		if($rc['count']>0){
			$page['content']['papercontentKey'] = $paper->contentKey;	
			$page['content']['paperData'] = $paper->Data;
			$page['content']['paperNr'] = $Nr;
		}else
			$page['content']['paperNr'] = 0;
		$page['navigation'] = "Annotation";
		$page['content']['file'] = "Annotation";
		$page['content']['subFile'] = "AnnotationPaper";
		$page['content']['paper'] = $paper;
		$page['content']['catList'] = new CategoryList();
		$p = new Page($page);
		$p->printSubPage();
		break;
	case 'DeleteCategory':
		$page['navigation'] = "Annotation";
		$page['content']['file'] = "Annotation";
		$page['content']['subFile'] = "AnnotationCategory";
		$cat = new Category('',(int) $_REQUEST['catId']);
		$catList = new CategoryList();
		if(count($cat->Child) == 0 && !in_array($cat->Data['id'],$catList->ListReadonly)) $cat->del();
		$page['content']['catList'] = new CategoryList();
		$p = new Page($page);
		$p->printSubPage();
		break;
	case 'SaveCategory':
		include('include/saveCategory.php');
//		include('include/annotation.php');
		$page['navigation'] = "Annotation";
		$page['content']['file'] = "Annotation";
		$page['content']['subFile'] = "AnnotationCategory";
		$page['content']['catList'] = new CategoryList();
		$p = new Page($page);
		$p->printSubPage();
		break;
	case 'ExportAbort':
		$export = new Session('export',$session);
		header('Content-type: text/javascript');
		if ($export->Data['status']=='file') $export->Data['status']='end';
		elseif ($export->Data['status']=='abort') $export->Data['status']='end';
		else $export->Data['status']='abort';
		$export->save();
		exit(0);
		break;
	case 'ExportDeleteStatus':
		$export = new Session('export',$session);
		if (array_key_exists('file',$export->Data) && file_exists($export->Data['file'])) {
			unlinkD($export->Data['file']);
		}
		$export->del();
		exit(0);
		break;
	case 'ExportCheckStatus':
		$export = new Session('export',$session);
		header('Content-type: text/javascript');
		if (!array_key_exists('countAll',$export->Data)) {
			exit(0);
		}
		$export->Data['percent'] = ($export->Data['countAll'] > 0 ) ? round(100*($export->Data['count']/$export->Data['countAll'])):0;
		unset($export->Data['file']);
		unset($export->Data['paperId']);
		unset($export->Data['form']);
		echo json_encode($export->Data);
		exit(0);
		break;
	case 'ExportHintergrundCWB':
		ignore_user_abort(true);	
		set_time_limit(0);
		$exportForm = new Session('export',$session);
		$paperId = $exportForm->Data['paperId'];
		$form=$exportForm->Data['form'];
		include('include/export/exportCWB.php');
		break;
	case 'ExportHintergrundXML':
		ignore_user_abort(true);	
		set_time_limit(0);
		$exportForm = new Session('export',$session);
		$paperId = $exportForm->Data['paperId'];
		$form=$exportForm->Data['form'];
		include('include/export/exportXML.php');
		break;
	case 'ExportHintergrundCSV':
		ignore_user_abort(true);	
		set_time_limit(0);
		$exportForm = new Session('export',$session);
		$paperId = $exportForm->Data['paperId'];
		$form=$exportForm->Data['form'];
		include('include/export/exportCSV.php');
		break;
	case 'ExportHintergrundLexico3':
		ignore_user_abort(true);	
		set_time_limit(0);
		$exportForm = new Session('export',$session);
		$paperId = $exportForm->Data['paperId'];
		$form=$exportForm->Data['form'];
		include('include/export/exportLexico3.php');
		break;
	case 'ExportHintergrundCWB':
		ignore_user_abort(true);	
		set_time_limit(0);
		$exportForm = new Session('exportForm',$session);
		$paperId = $exportForm->Data['paperId'];
		$form=$exportForm->Data['form'];
		include('include/export/exportCWB.php');
		break;
	case 'ImportAbort':
		$import = new Session('import',$session);
		header('Content-type: text/javascript');
		$import->Data['status']='abort';
		$import->save();
		exit(0);
		break;
	case 'ImportDeleteStatus':
		$import = new Session('import',$session);
		$import->del();
		$import = new Session('importForm',$session);
		if (file_exists($import->Data['file'])) unlinkD($import->Data['file']);
		$import->del();
		exit(0);
		break;
	case 'ImportCheckStatus':
		$import = new Session('import',$session);
		header('Content-type: text/javascript');
		if (!array_key_exists('countBegin',$import->Data)) {
			exit(0);
		}
		$import->Data['percent'] = ($import->Data['countBegin'] > 0 ) ? round(100*($import->Data['countAll']/$import->Data['countBegin'])):0;
		echo json_encode($import->Data);
		exit(0);
		break;
	case 'ImportHintergrundText':
		ignore_user_abort(true);	
		set_time_limit(0);
		$message = "Datei hochgeladen\n\n";
		$importFile = new Session('importForm',$session);
		$tmpFile = $importFile->Data['file'];
		$form=$importFile->Data['form'];
		include('include/import/importText.php');
		break;
	case 'ImportHintergrundLexico3':
		ignore_user_abort(true);	
		set_time_limit(0);
		$message = "Datei hochgeladen\n\n";
		$importFile = new Session('importForm',$session);
		$importSession = new Session('import',$session);
		$tmpFile = $importFile->Data['file'];
		$form=$importFile->Data['form'];
		include('include/import/importLexico3.php');
		break;
	case 'ImportHintergrundXML':
		ignore_user_abort(true);	
		set_time_limit(0);
		$importFile = new Session('importForm',$session);
		$importSession = new Session('import',$session);
		$tmpFile = $importFile->Data['file'];
		$message = "Datei hochgeladen\n\n";
		$form=$importFile->Data['form'];
		include('include/import/importXMLMap.php');
		break;
	case 'ImportHintergrundXMLCategory':
		ignore_user_abort(true);	
		set_time_limit(0);
		$importFile = new Session('importForm',$session);
		$importSession = new Session('import',$session);
		$tmpFile = $importFile->Data['file'];
		$message = "Datei hochgeladen\n\n";
		$form=$importFile->Data['form'];
		include('include/import/importXMLCategory.php');
		break;
}
?>
