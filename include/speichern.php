<?
defined( '_VALID_INGWER' ) or die( 'Restricted access' );

require_once('init.php');

if (array_key_exists('paperId',$_REQUEST) && $_REQUEST['paperId']>=0)
	$paper = new Paper(array('id'=>(int)$_REQUEST['paperId']));
foreach($paper->MType->List as $kM => $vM){
	if ($paper->contentKey != $kM){      //alles Speicher auser content
		$kME = preg_replace('/ /',"_",$kM);
		if (array_key_exists($kME,$_REQUEST)){
			switch ($vM['meta_type']) {
				case 'int': $paper->Data[$kM] = (int) $_REQUEST[$kME];
					break;
				case 'date': 
					$paper->Data[$kM] = parseDate($_REQUEST[$kME]);
					break;
				case 'string': $paper->Data[$kM] = $_REQUEST[$kME];
					break;
				case 'list': if ($_REQUEST[$kME]['list']=='')$paper->Data[$kM] = $_REQUEST[$kME]['neu'];
					else	$paper->Data[$kM] = $_REQUEST[$kME]['list'];
					break;
				case 'multilist': $paper->Data[$kM] = array();
					foreach ($_REQUEST[$kME]['list'] as $kL => $vL){
						if ($_REQUEST[$kME]['list'][$kL]=='')
							{ if ($_REQUEST[$kME]['neu'][$kL]!='') $paper->Data[$kM][] = $_REQUEST[$kME]['neu'][$kL];}
						else	$paper->Data[$kM][] = $_REQUEST[$kME]['list'][$kL];
					}
					break;
			}
		}
	}
}
if (array_key_exists($paper->contentKey,$_REQUEST) && array_key_exists($paper->contentKey.'Check',$_REQUEST) 
	&& $_REQUEST[$paper->contentKey] != $_REQUEST[$paper->contentKey.'Check']){
		$catList = new CategoryList();
		$annoAll= new AnnotationList($paper->Data['id'],False,True);
		$annoAll->delAll($catList->ListReadonly);
		$annoAll=null;
		$paper->Data[$paper->contentKey] = $_REQUEST[$paper->contentKey];
		$paper->saveMetaData();
		$paper->annotationDiff($_REQUEST[$paper->contentKey.'Check']);
		$paper->treeTagger();
	}

else $paper->saveMetaData();
$paper->MType->cleanList();


?>
