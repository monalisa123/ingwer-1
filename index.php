<?php
/**
* Ingwer Programmstart 
*
* Wertet die Navigation aus und führt die entsprechenden skripte aus, druckt Template aus.
*
* LICENSE: 
*
* @category   Main
* @package    Ingwer
* @author     Jochen Koehler <jk@it-devel.de>
* @copyright  2011 semtracks gmbh
* @version    0.8
*/
define('_VALID_INGWER',"TEST" );
define('DEBUG',"TEST" );
define('CONSTRUCTION',false );
session_start();
require_once('include/init.php');

$level = error_reporting (E_ALL);

if (CONSTRUCTION) {       //Baurbeiten
	$page=array(
		'title'=>'Ingwer '.$version,
		'navigation'=>'Start',
		'content'=>array(
			"file" => "Bauarbeiten"
		)
	);
	$p = new Page($page);
	$p->printPage();
	exit (0);
}

$page=array(
	'title'=>'Ingwer '.$version,
	'navigation'=>'Start',
	'content'=>array(
		"file" => "Start"
	)
);

$POS = (array_key_exists('POS',$_REQUEST) && $_REQUEST['POS'] == 1) ? true:false;
$page['content']['POS'] = $POS;

$nav = (array_key_exists('nav',$_REQUEST)) ? $_REQUEST['nav']:((array_key_exists('nav',$_SESSION))? $_SESSION['nav']:"");
$_SESSION['nav'] = $nav;
switch ($nav){
	case 'Verwaltung': 
		$page['navigation'] = "Verwaltung";
		$page['content']['file'] = "Verwaltung";
		$page['content']['searchNames'] = new SearchList();
		$page['content']['meta'] = array();
		$meta = new PaperMetaDataTypeList();
		foreach($meta->List as $kM => $vM) if ($meta->isExport($kM)) $page['content']['meta'][]=$vM;
		if (isset($httpUser)) $page['content']['httpUser']=$httpUser;
		if (isset($httpPass)) $page['content']['httpPass']=$httpPass;
		if (array_key_exists('cmd',$_REQUEST) && $_REQUEST['cmd']=='ExportFile') {
			$export = new Session('export',session_id());
			if (file_exists($export->Data['file'])) {
				if (array_key_exists('type',$export->Data) && $export->Data['type'] == 'lexico3') {
					header("Content-Type: text/plain; charset=utf-8");
					header("Content-Disposition:attachment;filename=ingwer.txt");

					$handle = fopen($export->Data['file'], "r");
					while ( !feof($handle)) echo preg_replace("/\n/","\r\n",iconv("UTF-8", "ISO-8859-1//TRANSLIT",fgets($handle)));
				} elseif (array_key_exists('type',$export->Data) && $export->Data['type'] == 'csv') {
					header("Content-Type: text/csv; charset=utf-8");
					header("Content-Disposition:attachment;filename=ingwer.csv");

					$handle = fopen($export->Data['file'], "r");
					while ( !feof($handle)) echo fgets($handle);
				} else {
					header("Content-Type: text/xml; charset=utf-8");
					header("Content-Disposition:attachment;filename=ingwer.xml");

					$handle = fopen($export->Data['file'], "r");
					while ( !feof($handle)) echo fgets($handle);
				}
				$export->Data['status']='end';
				$export->save();
				exit(0);
			}
		}elseif (array_key_exists('cmd',$_REQUEST) && $_REQUEST['cmd']=='Export') {
			$export = new Session('export');
			if (array_key_exists('status',$export->Data) && $export->Data['status'] == 'start') 
					$page['content']['progress'] = "progressBoxExport('Überprüfe Status','0','Verbinde...');\nExport('');";
			include('include/export.php');
		}
		else {	
			$import = new Session('import');
			if (array_key_exists('status',$import->Data) && $import->Data['status'] == 'start') 
					$page['content']['progress'] = "progressBox('Überprüfe Status','0','Verbinde...');\nimportUpload('');";
			include('include/import.php');
		}
		break;

	case 'Lesen': include('include/lesen.php');
		$page['navigation'] = "Lesen";
		$page['content']['file'] = "Lesen";
		break;

	case 'Bearbeiten': 
		if (array_key_exists('cmd',$_REQUEST) && $_REQUEST['cmd']=='Speichern') {
			include('include/speichern.php');
		}
		include('include/lesen.php');
		$page['content']['paperMeta'] = $paper->MType;
		$page['navigation'] = "Lesen";
		$page['content']['file'] = "Bearbeiten";
		break;

	case 'Start': 
		$page['navigation'] = "Start";
		$page['content']['file'] = "Start";
		break;
	case 'Suche': include('include/suche.php');
		$page['navigation'] = "Suche";
		$page['content']['file'] = "Suche";
		break;
	case 'Hilfe': 
		$page['navigation'] = "Hilfe";
		$page['content']['file'] = "Hilfe";
		break;
	case 'Annotation': 
		include('include/lesen.php');
		include('include/annotation.php');
		$page['navigation'] = "Annotation";
		if (array_key_exists('ingwerUser',$_COOKIE))$page['user'] = $_COOKIE['ingwerUser'];
		else $page['user'] = "";
		$page['content']['file'] = "Annotation";
		break;
	case 'getPageNav':
		$noPaper = true;
		include('include/lesen.php');
		$page['navigation'] = "Annotation";
		$page['content']['file'] = "Annotation";
		$page['content']['subFile'] = "AnnotationPageNav";
		$p = new Page($page);
		$p->printSubPage();
		exit(0);
		break;
}
$p = new Page($page);
$p->printPage();
?>
