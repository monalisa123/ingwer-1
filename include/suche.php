<?
defined( '_VALID_INGWER' ) or die( 'Restricted access' );

require_once('init.php');

$page['content']['paperMetaType'] = new PaperMetaDataTypeList();
$page['content']['categoryList'] = new CategoryList();
foreach ( $page['content']['paperMetaType']->List as $kM => $vM){
	if ($vM['meta_type']=='multilist' || $vM['meta_type']=='list'){
		$list=array();
		if ($vM['table']=="paper_meta_data")
			$db->select($dbTablePrefix . $vM['table'],"*","paper_meta_data_type_id=".$vM['id'],false,"group by " .$vM['field_name']." order by " . $vM['field_name']);
		else
			$db->select($dbTablePrefix . $vM['table'],"*",false,false," order by " . $vM['field_name']);
		while (($rs = $db->nextQ())!==false){
			if (isset($rs['id'])) $list[$rs['id']] = $rs[$vM['field_name']];
		}
		$page['content']['paperMetaType']->List[$kM]['list']=$list;
	}
}
$search = new Search();
$page['content']['search'] = $search;
$page['content']['searchNames'] = new SearchList();
if (array_key_exists('searchAction',$_REQUEST)){
	switch ($_REQUEST['searchAction']){
		case 'search': 
			$search->loadFromFormular($_REQUEST['search']);
			$search->searchPaper();
			$_SESSION['searchMarkIds']= array();
			$page['content']['search'] = $search;
			break;
		case 'saveSearch': 
			$search->loadFromFormular($_REQUEST['search']);
			$search->searchPaper();
			$search->save($_REQUEST['name']);
			$page['content']['search'] = $search;
			break;
		case 'saveList': 
			if (array_key_exists('mark',$_REQUEST)){
				$del = array();
				foreach ($_REQUEST['mark'] as $kM=>$vM){
					if ($vM==0) $del[]=$kM;
					else $_SESSION['searchMarkIds'][]=$kM;
				}
				$_SESSION['searchMarkIds'] = array_unique(array_diff($_SESSION['searchMarkIds'],$del));
			}
			$search->searchPaperFromSession();
			$search->saveList($_SESSION['searchMarkIds'],$_REQUEST['name']);
			$page['content']['search'] = $search;
			break;
		case 'loadSearch': 
			$search = new Search(-1,$_REQUEST['name']['old']);
			$search->searchPaper();
			$page['content']['search'] = $search;
			$_SESSION['searchMarkIds']= array();
			break;
		case 'deleteSearch': 
			$search->searchPaper();
			$search->del($_REQUEST['name']);
			$page['content']['search'] = $search;
			break;
		case 'delList': 
			if (array_key_exists('mark',$_REQUEST)){
				$del = array();
				foreach ($_REQUEST['mark'] as $kM=>$vM){
					if ($vM==0) $del[]=$kM;
					else $_SESSION['searchMarkIds'][]=$kM;
				}
				$_SESSION['searchMarkIds'] = array_unique(array_diff($_SESSION['searchMarkIds'],$del));
			}
			if (array_key_exists('searchMarkIds',$_SESSION) && is_array($_SESSION['searchMarkIds'])){
				foreach($_SESSION['searchMarkIds'] as $kM){
					$paper = new Paper(array('id' => (int) $kM));
					$paper->del();
				}
				if (isset($paper)) {$paper->MType->cleanList();}
			}
			$_SESSION['searchMarkIds']= array();
			//Listen neu einlesen
			$page['content']['paperMetaType'] = new PaperMetaDataTypeList();
			$page['content']['categoryList'] = new CategoryList();
			foreach ( $page['content']['paperMetaType']->List as $kM => $vM){
				if ($vM['meta_type']=='multilist' || $vM['meta_type']=='list'){
					$list=array();
					if ($vM['table']=="paper_meta_data")
						$db->select($dbTablePrefix . $vM['table'],"*","paper_meta_data_type_id=".$vM['id'],false,"group by " .$vM['field_name']." order by " . $vM['field_name']);
					else
						$db->select($dbTablePrefix . $vM['table'],"*",false,false," order by " . $vM['field_name']);
					while (($rs = $db->nextQ())!==false){
						if (isset($rs['id'])) $list[$rs['id']] = $rs[$vM['field_name']];
					}
					$page['content']['paperMetaType']->List[$kM]['list']=$list;
				}
			}
			$search->searchPaperFromSession();
			$search->searchPaper();
			$page['content']['search'] = $search;
			break;
	}
	$page['content']['searchNames'] = new SearchList();
	$page['content']['searchMarkIds']=$_SESSION['searchMarkIds'];
}
else{
	if (!array_key_exists('searchMarkIds',$_SESSION) )$_SESSION['searchMarkIds']= array();
	if (array_key_exists('mark',$_REQUEST)){
		$del = array();
		foreach ($_REQUEST['mark'] as $kM=>$vM){
			if ($vM==0) $del[]=$kM;
			else $_SESSION['searchMarkIds'][]=$kM;
		}
		$_SESSION['searchMarkIds'] = array_unique(array_diff($_SESSION['searchMarkIds'],$del));
	}
	$page['content']['searchMarkIds']=$_SESSION['searchMarkIds'];

	$search->searchPaperFromSession(
		(array_key_exists('order',$_REQUEST))?$_REQUEST['order']:False,	
		(array_key_exists('direction',$_REQUEST))?$_REQUEST['direction']:False
	);
	if (array_key_exists('resultCount',$_REQUEST)){
			$search->Start = (int)$_REQUEST['resultCount'];
			if ($search->Start <0) $search->Start = 0;
			$page['content']['search'] = $search;
			$_SESSION['searchResult']['start'] = $search->Start;
	}
	elseif (array_key_exists('searchResult',$_SESSION))
		$search->Start = $_SESSION['searchResult']['start'];

}
?>
