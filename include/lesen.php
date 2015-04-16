<?
defined( '_VALID_INGWER' ) or die( 'Restricted access' );

require_once('init.php');

$rc = $db->selectOne($tbPaper,"count(id) as count");
$Nr = (int)(array_key_exists('paperNr',$_REQUEST) && $_REQUEST['paperNr']>=0)?$_REQUEST['paperNr']: ((array_key_exists('paperNr',$_SESSION) && $_SESSION['paperNr']>=0)?$_SESSION['paperNr']: 0);
if ($Nr > $rc['count']-1) $Nr = ($rc['count']<1)? 0 : $rc['count']-1;
$db->select($tbPaper,"id",False,False,"limit $Nr,1");
$rP = $db->nextQ();
if (array_key_exists('paperId',$_REQUEST) && $_REQUEST['paperId']>=0)
	$paper = new Paper(array('id'=>(int)$_REQUEST['paperId']));
elseif (array_key_exists('paperNr',$_REQUEST)) 
	$paper = new Paper(array('id'=>(int)$rP['id']));
elseif (array_key_exists('paperId',$_SESSION) && $_SESSION['paperId']>0)
	$paper = new Paper(array('id'=>(int)$_SESSION['paperId']));
else 
	$paper = new Paper(array('id'=>(int)$rP['id']));
$_SESSION['paperId'] = $paper->Data['id'];
$_SESSION['paperNr'] = $Nr;

$page['content']['paperCount'] = $rc['count'];
if($rc['count']>0){
	$page['content']['papercontentKey'] = $paper->contentKey;	
	$page['content']['paperData'] = $paper->Data;
	$page['content']['paperNr'] = $Nr;
}else
	$page['content']['paperNr'] = 0;

?>
