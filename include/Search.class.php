<?php

defined( '_VALID_INGWER' ) or die( 'Restricted access' );

require_once('initDb.php');
include_once('Limit.class.php');


class SearchTextValue {
	var $val=NULL;
	var $pos=NULL;

	function SearchTextValue(){
	}
}

class SearchTextOperator {
	var $op;
	var $val=array();
	var $posCount=0;
	var $valCount=0;
	var $search=false;
	var $searchPos;

	function SearchTextOperator($op=""){
		$this->op = $op;
	}
}

class SearchText {
	var $splitter=array("UND"=>"and","ODER"=>"or","NICHT"=>"not");
	var $bracketSplitter=array("("=>"(",")"=>")");
	var $posSplitter = "POS=\S+";
	var $posFind = "POS=(\S+)";
	var $splittText=array();
	var $posList=NULL;

	function SearchText($text){
		$this->splitter[$this->posSplitter]="pos";
		$this->_splittSearchText($text);
	}


	function _splittSearchText($text){
		$leftBAll=preg_match_all("/\(/",$text,$ret);
		$rightBAll=preg_match_all("/\)/",$text,$ret);
		$splitter= $this->splitter;
		$splitter[$this->posSplitter]="pos";
		$splittText=array($text);
		#Schluesselwoertert (UND,ODER...)
		foreach(array_keys($splitter) as $k){
			$newSplitText = array();
			foreach ($splittText as $t){
				$newSplitText=array_merge($newSplitText,preg_split("/($k)/",$t,-1,PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY));
			}
			$splittText=$newSplitText;
		}
		$splittText=$newSplitText;
		#Klammern 
		$newSplitText = array();
		foreach ($splittText as $t){
			$leftB=preg_match_all("/\(/",$t,$ret);
			$rightB=preg_match_all("/\)/",$t,$ret);
			if ($leftB!=$rightB){
				if ($leftB<$rightB) $muster="/\)$/D";
				else $muster="/^\(/";
				$count=0;
				do {
					$t=preg_replace($muster,'',trim($t),1,$c);
					$count+=$c;
				} while($c!=0 and $count < abs($leftB-$rightB));
				$brackets=array();
				for ($i=0;$i<$count;$i++){
					if ($leftB<$rightB) $brackets[]=")";
					else $brackets[]="(";
				}
				if ($leftB<$rightB) {
					$newSplitText[]=$t;
					$newSplitText=array_merge($newSplitText,$brackets);
				}else {
					$newSplitText=array_merge($newSplitText,$brackets);
					$newSplitText[]=$t;
				}

			}else $newSplitText[]=$t;
		}

			


		$splittText=$newSplitText;
		#Klammern korrektur
		$leftB=0;
		$rightB=0;
		$newSplitText = array();
		foreach($splittText as $op){
			if ($op=='(') $leftB++;
			if ($op==')') $rightB++;
			}
		if ($leftB != $rightB){
			if ($leftB<$rightB) $muster=')';
			else $muster='(';
			$countB=abs($leftB-$rightB);
			for($i=(count($splittText));$i--;$i>=0){
				if ($muster==trim($splittText[$i])){
					if($i>0){
						$splittText[$i-1].=$splittText[$i];
					}else{
						$newSplitText[0] = $splittText[$i] . $newSplitText[0];
					}
				}else array_unshift($newSplitText,$splittText[$i]);
			}
			$splittText=$newSplitText;
		}
		$splitter= array_merge($this->splitter,$this->bracketSplitter);

		if (count($splittText)>0 and !in_array($splittText[0],array_keys($splitter))) array_push($this->splittText,new SearchTextOperator());
		foreach($splittText as $t){
			$t=trim($t);
			if ($t == "") continue;
			if ( in_array($t,array_keys($splitter)) ){array_push($this->splittText,new SearchTextOperator($splitter[$t]));}
			else {
				$ret = array();
				preg_match("/".$this->posFind."/",$t,$ret);
				$tEnd=end($this->splittText);
				
				$v= end($tEnd->val);
				array_push($tEnd->val,new SearchTextValue());
				$v= end($tEnd->val);
				
				if (count($ret)>1){
					$v->pos=$ret[1];
					$tEnd->posCount++;
					}
				else {
					$v->val=$t;
					$tEnd->valCount++;
				
				}
			}
		}



		return $splittText;	
	}

	function isInPosSearch($paper){
		global $db,$tbCategory,$tbAnnotation,$tbAnnotationPosition;

		#Initalisierung der pos Liste
		$pos=array();
		if (!$this->posList){
			$this->posList=array();
			foreach($this->splittText as $sText){
				foreach ($sText->val as $v) if ($v->pos) $pos[] = "name regexp '^" . $this->regPerlToSql($v->pos) ."$'";
			}
			#$db->select($tbCategory,"id,name","name IN ('".implode("','",$pos)."')");
			$db->select($tbCategory,"id,name",implode(" or ",$pos));
			while (($rs = $db->nextQ())!==false){
				if (isset($rs['id']))  $this->posList[$rs['name']]=$rs['id'];
			}
		}
		#keine pos in Suchanfrage
		if (count($pos) == 0) return true;
		
		$result = $this->contentPos($paper['id'],$paper['content'],$offset,$length,true);
		return $result;

	}

	function contentPos($paperId,$content,&$offset,&$length,$onlyFind=False){
		global $db,$tbCategory,$tbAnnotation,$tbAnnotationPosition,$globalLimit;

		#Initalisierung der pos Liste
		if (!$this->posList){
			$this->posList=array();
			$pos=array();
			foreach($this->splittText as $sText){
				foreach ($sText->val as $v) if ($v->pos) $pos[] = "name regexp '^" . $this->regPerlToSql($v->pos) ."$'";
			}
			$db->select($tbCategory,"id,name",implode(" or ",$pos));
			while (($rs = $db->nextQ())!==false){
				if (isset($rs['id']))  $this->posList[$rs['name']]=$rs['id'];
			}
		}

		mb_regex_encoding("UTF-8");
		$startPos=0;
		$contentLength=mb_strlen($content);
		do {
			$nextPos=$startPos+1;
			$nextFind=-1;
			$resultText='$result= (';
			$lastOp=0;
			foreach($this->splittText as $sText){ #ueber jeden SearchTextOperator  
				$regExp=array();
				$posList=array();
				$valList=array();
				$pos=0;
				foreach ($sText->val as $v) {  #erzeugen des regulaeren Ausdruecke fuer die Categorien und Teilausdruecke
					if ($v->pos) {
						$regExp[] = "(\w+)";
						$pos++;
						$posList[$pos]=$v->pos;
						$pos++;
					}
					if ($v->val) {
						$regExp[] = "(".$v->val.")";
						$pos++;
						$valList[$pos]=$v->val;
						$pos++;
					}
				}
				
				if (count($regExp) > 0){
					mb_ereg_search_init($content, implode('(\W+)',$regExp));
					mb_ereg_search_setpos($startPos);
					$r = mb_ereg_search();
					if(!$r){
						$sText->search=false;
						}
					else {
						$r = mb_ereg_search_getregs(); //get first result

						do{
							$nullPosition = mb_ereg_search_getpos()-mb_strlen($r[0]);
							$annoList=array();
							$posListKey=array_keys($this->posList);
							foreach ($posList as $pLK=>$pLV){  #ueberpruefung ob Categorie mit gefundenem Wort uebereinstimmt
								$pLVK=array();
								foreach($posListKey as $k) {
									if (mb_ereg_match("".$pLV."" ,$k)){
										$pLVK[]=$this->posList[$k];
									}
								}	
								if (count($pLVK)==0) continue;
								$len=mb_strlen($r[$pLK]);
								$currentString="";
								for ($i=1; $i<$pLK;$i++) $currentString.=$r[$i];
								$currentPos=$nullPosition+mb_strlen($currentString);
								$annoList[] = array($paperId,implode("','",$pLVK),$currentPos,$len);
							}
							$newPosition=$nullPosition+mb_strlen(trim($r[0]));
							$find=true;
							if ($newPosition > 0 && $newPosition< $contentLength) mb_ereg_search_setpos($nullPosition+mb_strlen(trim($r[0])));
							else {
								$find=false;
								break;
							}
							if (count($annoList) != $sText->posCount) $find=false;
							foreach($annoList as $aList){
								$db->select("$tbAnnotation a,$tbAnnotationPosition b","a.id",sprintf(" a.id=b.annotation_id and a.paper_id = %s  and a.category_id IN ('%s') and b.offset=%s and b.length=%s",$aList[0],$aList[1],$aList[2],$aList[3]));
								if ($db->numRows()==0) $find=false;
							}

							#Ausdruck gefunden Positionen merken
							if ($find) {
								$sText->searchPos=mb_ereg_search_getpos();
								if (($sText->searchPos+1)>$nextPos) $nextPos=$sText->searchPos+1;
								if ($nextFind<0 or $nextFind>$nullPosition-50) $nextFind=$nullPosition-50;
							}
							$r = mb_ereg_search_regs();//get next result
						}while($r and !$find);
						$sText->search=$find;
					}
				}
			if ($sText->op == 'not') {
				if ($lastOp != 0 ) $resultText .= " and";
				$resultText .= " !";
			}
			else $resultText .= " {$sText->op} ";
			if ($sText->valCount>0 || $sText->posCount>0) $resultText .= (($sText->search)?"true":"false");

			$lastOp=$sText->valCount+$sText->posCount;
			}
			$resultText .= ");";
			#Gesammete Suchanfrage auswerten.
			eval($resultText);
			if ($result) {
				if ($onlyFind) return $result;
				#Suchanfrage auf möglichst wenig Text am anfang beschraenken, um eventuelle Relevanzen zu finden.
				$offset[]=$nextFind;
				$length[]=100;
				$nextPosMin=$contentLength;
				foreach($this->splittText as $sText){ #kürzeste mögliche Position
					if ($sText->search and $sText->searchPos< $nextPosMin) $nextPosMin=$sText->searchPos;
				}
				$nextPosMinNext=$nextPosMin;
				do {
					$nextPosMin=$nextPosMinNext;
					$nextPosMinNext=$contentLength;
					$resultTextMin='$resultMin=(';
					$lastOp=0;
					foreach($this->splittText as $sText){ #kürzeste mögliche Position
						if ($sText->op == 'not') {
							if ($lastOp != 0 ) $resultTextMin .= " and";
							$resultTextMin .= " !";
						}
						else $resultTextMin .= " {$sText->op} ";
						if ($sText->valCount>0 || $sText->posCount>0) $resultTextMin .= (($sText->search and $sText->searchPos<= $nextPosMin)?"true":"false");
						if ($sText->search and $sText->searchPos> $nextPosMin and $sText->searchPos< $nextPosMinNext) $nextPosMinNext=$sText->searchPos;
						$lastOp=$sText->valCount+$sText->posCount;
					}
					$resultTextMin .= ");";
					eval($resultTextMin);
				} while (!$resultMin and $globalLimit->checkMemoryLimit());
				if (!$globalLimit->checkMemoryLimit()) return false;
				$startPos=$nextPosMin;
			}
			else $startPos=$nextPos;
		}while($result and $startPos<$contentLength and $globalLimit->checkMemoryLimit());
		if (!$globalLimit->checkMemoryLimit()) return false;
		if (count($offset)==1 and $offset[0]==-1) $offset[0]=0;
		return ($result and $globalLimit->checkMemoryLimit());
	
	}

	function getSQLWhere($col,&$param){
		$where="";
		$opStack=array();
		$lastOp=0;
		foreach($this->splittText as $sText){
			$par="";
			foreach ($sText->val as $v) if ($v->val) $par .= $v->val;
			$posBool= true;
			switch ($sText->op){
				case "not":	$last = array_pop($opStack);
						array_push($opStack,$sText->op);
						foreach ($opStack as $oS) if ($oS == 'not')$posBool= !$posBool;
						//if ($countOp!=1 && !in_array($last,array('and','or','('))) $where .= " and";
						if ($lastOp!=0) $where .= " and";
						if ($sText->posCount==0 and $sText->valCount > 0){
							$where .= " not $col regexp '%s'";
							array_push($param,$this->regPerlToSql($par));
						}elseif ($sText->posCount>0) {
							$where .= " {$sText->op} " . (($posBool)?"true":"false");
						}else {
							$where .= " {$sText->op} ";
						}
						break;

				case "":	
				case "or":	
				case "and":	array_pop($opStack);
				case "(":
						array_push($opStack,$sText->op);
						foreach ($opStack as $oS) if ($oS == 'not')$posBool= !$posBool;
						if ($sText->valCount > 0 && ($posBool || $sText->posCount==0)){
							$where .= " {$sText->op} $col regexp '%s'";
							array_push($param,$this->regPerlToSql($par));
						}elseif ($sText->posCount>0) {
							$where .= " {$sText->op} " . (($posBool)?"true":"false");
						}else {
							$where .= " {$sText->op} ";
						}
						break;
				case ")":	while (count($opStack)>0 && array_pop($opStack) != '(');
						foreach ($opStack as $oS) if ($oS == 'not')$posBool= !$posBool;
						if ($sText->valCount > 0){
							$where .= " {$sText->op} $col regexp '%s'";
							array_push($param,$this->regPerlToSql($par));
						}elseif ($sText->posCount>0) {
							$where .= " {$sText->op} " . (($posBool)?"true":"false");
						}else {
							$where .= " {$sText->op} ";
						}
						break;
			}
			$lastOp=$sText->valCount+$sText->posCount;
		}
		return $where;
	}

	function regPerlToSql($r){
		global $db;
		$perl=array('\d',              '\D',       '\w'            ,'\W'          ,'\s'       ,'\S',		'\b');
		$sql=array('[[:digit:]]','[^[:digit:]]','[_[:alnum:]]','[^_[:alnum:]]','[[:space:]]','[^[:space:]]','([[:<:]]|[[:>:]])');
		$leftB=preg_match_all("/\(/",$r,$ret);
		$rightB=preg_match_all("/\)/",$r,$ret);
		if ($leftB!=$rightB){
			$m=min($leftB,$rightB);
			$leftB=0;
			$rightB=0;
			$rA = preg_split('/(?<!^)(?!$)/u',$r);
			$rANew=array();
			foreach( $rA as $rAS){
				if ($rAS == '('){
					if ($leftB<$m) 	$leftB++;
					else $rAS='\(';
				}
				if ($rAS == ')'){
					if ($rightB<$m and $rightB<$leftB) $rightB++;
					else $rAS='\)';
				}
				$rANew[]=$rAS;
			}
			$r=implode($rANew);

		}
		return $db->escape_string(str_replace($perl,$sql,$r));
	}

}

class Search {

	var $Data=array();
	var $MetaData=array();
	var $Category=array();
	var $List=array();
	var $ResultList;
	var $ResultCount;
	var $WordCount;
	var $LemmaCount;
	var $Start=0;
	var $Order='id';
	var $Direction='asc';
	var $Relevanz=0;
	var $Message="";

	function Search($id=false,$name=false){
		global $db, $tbSearch, $tbSearchMetaData, $tbSearchCategory, $tbPaperMetaType,$tbSearchList;

		if (!$id) return;

		if ($name) $this->Data=$db->selectOne($tbSearch,'*',"name='%s'",$name);
		else $this->Data=$db->selectOne($tbSearch,'*',"id=%s",$id);
		if (is_array($this->Data) && array_key_exists('id',$this->Data)){
			$db->select(array("$tbSearchMetaData a", "$tbPaperMetaType b"),"a.*,b.meta_type","a.search_id=%s AND a.paper_meta_data_type_id=b.id",$this->Data['id']);
			while ($rs = $db->nextQ()){
				if ( $rs['meta_type'] == 'list' || $rs['meta_type'] == 'multilist'){
					if (!array_key_exists($rs['paper_meta_data_type_id'],$this->MetaData)){
						$this->MetaData[$rs['paper_meta_data_type_id']]=$rs;
						$this->MetaData[$rs['paper_meta_data_type_id']]['search']=array();
					}
					$this->MetaData[$rs['paper_meta_data_type_id']]['search'][]=$rs['search'];
				}else $this->MetaData[$rs['paper_meta_data_type_id']]=$rs;
			}
			$db->select($tbSearchCategory,"*","search_id=%s",$this->Data['id']);
			while ($rs = $db->nextQ()){
				if (isset($rs['category_id'])) $this->Category[$rs['category_id']]=$rs;
			}
			$db->select($tbSearchList,"*","search_id=%s",$this->Data['id']);
			while ($rs = $db->nextQ()){
				if (isset($rs['paper_id'])) $this->List[]=$rs['paper_id'];
			}

		}
		else {$this->Data= array('id' => -1);}

	}

	function saveList($mark,$name){
		global $db,$tbSearch,$tbSearchCategory,$tbSearchMetaData,$tbSearchList;

		if (array_key_exists('new',$name) && strlen($name['new'])>0) $n=$name['new'];
		elseif (array_key_exists('old',$name) && strlen($name['old'])>0) {
			$this->del($name);
			$n=$name['old'];
		}
		else return;
		
		$update = array();
		$update['name'] = $n;
		$update['type'] = 'list';
		$update['include'] = '1';
		$db->insert($tbSearch,$update);
		$id = $db->lastId();
	 	$vIns = array();
		$vIns['search_id']=$id;
		foreach($mark as $v){
	 		$vIns['paper_id'] = $v;
			$db->insert($tbSearchList,$vIns);
		}
	}

	function save($name){
		global $db,$tbSearch,$tbSearchCategory,$tbSearchMetaData;

		if (array_key_exists('new',$name) && strlen($name['new'])>0) $n=$name['new'];
		elseif (array_key_exists('old',$name) && strlen($name['old'])>0) {
			$n=$name['old'];
			$this->del($name);
		}
		else return;


		$update = $this->Data;
		$update['name'] = $n;
		$db->insert($tbSearch,$update);
		$id = $db->lastId();
		foreach($this->MetaData as $v){
			if (is_array($v['search'])){
	 			$vIns = $v;
				$vIns['search_id']=$id;
				foreach ($v['search'] as $vList){
					$vIns['search']=$vList;
					$db->insert($tbSearchMetaData,$vIns);
				}
			}
			else {
				$v['search_id']=$id;
				$db->insert($tbSearchMetaData,$v);
			}
		}
		foreach($this->Category as $v){
			$v['search_id']=$id;
			$db->insert($tbSearchCategory,$v);
		}

	}
	function del($name){
		global $db,$tbSearch,$tbSearchCategory,$tbSearchMetaData,$tbSearchList;

		if (array_key_exists('old',$name) && strlen($name['old'])>0) $n=$name['old'];
		else return;

		$db->select($tbSearch,'id',"name='%s'",$n);
		$del = array();
		while ($rs = $db->nextQ()){
			if (isset($rs['id'])) $del[]=$rs['id'];
		}
		if (count($del) >0){
			$db->delete($tbSearchCategory,"search_id IN ('".implode("','",$del)."')");
			$db->delete($tbSearchMetaData,"search_id IN ('".implode("','",$del)."')");
			$db->delete($tbSearchList,"search_id IN ('".implode("','",$del)."')");
			$db->delete($tbSearch,"id IN ('".implode("','",$del)."')");
		}
	}


	function loadFromFormular($form){
		$metaList = new PaperMetaDataTypeList();
		$metaId = array();
		foreach($metaList->List as $mV) {
			$metaId[$mV['id']]=$mV;
			if ($mV['meta_type'] == 'content') $contentId=$mV['id'];
		}
		$categoryList = new CategoryList();

		foreach($form as $kF => $vF){
			if (strpos($kF,'category_')===0){
				if (array_key_exists($vF['id'],$categoryList->List) && $vF['id'] != 0)
					$this->Category[]=array('category_id'=>(int)$vF['id'],'include'=>(((int)$vF['inc'])==0)? 0:1);
			}
			elseif ( ((int)$kF) == $contentId && array_key_exists('id',$vF) && strlen($vF['id'])>0){
				$this->Data['search_text']= $vF['id'];
				$this->Data['include'] = (((int)$vF['inc'])==0) ? 0:1;
			}
			elseif (array_key_exists($kF,$metaId) && array_key_exists('id',$vF) 
				&& ((is_string($vF['id']) && strlen($vF['id'])>0) || (is_array($vF['id']) && count($vF['id'])>0))){
				$m = array('paper_meta_data_type_id'=> (int)$kF);
				$m['include'] = (((int)$vF['inc'])==0)? 0:1;
				if ( array_key_exists('relation',$vF))
						$m['relation']= (int)$vF['relation'];
				switch ($metaId[$kF]['meta_type']){
					case 'date':$m['search']= parseDate($vF['id']);
						if ( array_key_exists('id2',$vF) && strlen($vF['id2'])>0)
							$m['search2']= parseDate($vF['id2']);
						else if ($m['relation'] == 0)
							$m['relation']= -1;
						$this->MetaData[]=$m;
						break;
					case 'multilist': if (count($vF['id']) > 100) continue;
						$m['search']= array(); 
						foreach ($vF['id'] as $v) if ('Bitte wählen'!=substr($v,0,254)) $m['search'][]=substr($v,0,254);
						if ( array_key_exists('id2',$vF) && strlen($vF['id2'])>0)
							(int)$m['search2']= $vF['id2'];
						$this->MetaData[]=$m;
						break;
					case 'list':if (count($vF['id']) > 100) continue;
						$m['search']= array(); 
						foreach ($vF['id'] as $v) $m['search'][]=(int)$v;
						if ( array_key_exists('id2',$vF) && strlen($vF['id2'])>0)
							(int)$m['search2']= $vF['id2'];
						$this->MetaData[]=$m;
						break;
					case 'int':
						if ($vF['relation']==2){
							$search = explode(',',$vF['id']);
							$m['search']=array();
							foreach ($search as $s) $m['search'][]= (int)$s;
							$m['search']= implode(', ',$m['search']);
						}	
						else $m['search']= (int)$vF['id'];
						if ( array_key_exists('id2',$vF) && strlen($vF['id2'])>0)
							(int)$m['search2']= $vF['id2'];
						$this->MetaData[]=$m;
						break;
					default:$m['search']= $vF['id'];
						if ( array_key_exists('id2',$vF) && strlen($vF['id2'])>0)
							$m['search2']= $vF['id2'];
						$this->MetaData[]=$m;
						break;
				}
			}
		}


	}


	function searchPaper($returnOnlyId=false){
		global $db,$tbPaper,$tbAnnotation,$tbAnnotationPosition,$tbJournal,$tbPaperMeta,$globalLimit;
		
		$table = array("$tbPaper a");
		$metaList = new PaperMetaDataTypeList();
		$metaId = array();
		foreach($metaList->List as $mV) {
			$metaId[$mV['id']]=$mV;
			if ($mV['meta_type'] == 'content') $contentId=$mV['id'];
		}
		$categoryList = new CategoryList();
		$where = array();
		$whereList=array();
		$tableCount = 1;
		foreach ($this->MetaData as $vM){
			$metaType=$metaId[$vM['paper_meta_data_type_id']];
			switch($metaType['meta_type']){
					case 'string':
						switch($metaType['table']){
							case 'paper' : $w = "a." . $metaType['field_name'];break;
							case 'paper_meta_data' : $w = "c.".$metaType['field_name']." AND a.id=c.paper_id AND c.paper_meta_data_type_id=". $metaType['meta_type'];break;
							default: $w = "a.". $metaType['table'] . '_id';
						}
						$w .=(($vM['include']==0) ? ' not like ':' like ') . "'%%%s%%'"  ;
						$whereList[] = $vM['search'];
						break;
					case 'content':break;
					case 'multilist':
					case 'list':
						if (count($vM['search']) == 0 || ( count($vM['search'])==1 && $vM['search'][0] === 0)) break;
						switch($metaType['table']){
							case 'paper' : $w = "a." . $metaType['field_name'];break;
							case 'paper_meta_data' : $w =  "a.id=c{$tableCount}.paper_id AND c{$tableCount}.paper_meta_data_type_id=". $metaType['id'] . " AND c{$tableCount}.".$metaType['field_name'];
								$table[] = "$tbPaperMeta c$tableCount";
								$tableCount++;
								break;
							default : $w =  "a.id=c{$tableCount}.paper_id AND c{$tableCount}.{$metaType['table']}_id";
								$table[] = "{$tbPaper}2{$metaType['table']} c$tableCount";
								$tableCount++;
								break;
						}
						$w .=(($vM['include']==0) ? ' NOT':'') . " IN (" ;
						foreach ($vM['search'] as $vLSearch) {
							if ($vLSearch !==0) {
								$whereList[] = $vLSearch;
								$w .= "'%s'," ;
							}
						}
						$w = rtrim($w,",") . ")";
						break;
					case 'int':
						$w =(($vM['include']==0) ? 'NOT ':'');
						switch ($vM['relation']){
							case -1: $w .= "(%s < a.".$metaType['field_name'] . ')';
								$whereList[] = $vM['search'];
								break;
							case 0: 
								$w .= '(a.'.$metaType['field_name']. " BETWEEN %s AND %s)";
								$whereList[] = $vM['search2'];
								$whereList[] = $vM['search'];
								break;
							case 1: $w .= "(%s > a.".$metaType['field_name'] . ')';
								$whereList[] = $vM['search'];
								break;
							case 2: $w .= "(a.".$metaType['field_name'] . ' IN (%s))';
								$whereList[] = $vM['search'];
								break;
						}
						break;
					case 'date':
						$w =(($vM['include']==0) ? 'NOT ':'');
						switch ($vM['relation']){
							case -1: $w .= "('%s' < a.".$metaType['field_name'] . ')';
								$whereList[] = $vM['search'];
								break;
							case 0: 
								$w .= '('.$metaType['field_name']. " BETWEEN '%s' AND '%s')";
								$whereList[] = $vM['search2'];
								$whereList[] = $vM['search'];
								break;
							case 1: $w .= "('%s' > a.".$metaType['field_name'] . ')';
								$whereList[] = $vM['search'];
								break;
						}
			}
			if (isset($w))$where[] =  $w;
			unset($w);

		}
		#TODO regulaere ausdruecke
		$sText=false;
		if (array_key_exists('search_text',$this->Data)&&strlen($this->Data['search_text'])>0){
			$sText = new SearchText($this->Data['search_text']);
			if ($this->Data['include']!=0) $sText = new SearchText($this->Data['search_text']);
			else $sText = new SearchText("NICHT (".$this->Data['search_text'].")");
			$metaType=$metaId[$contentId];
			switch($metaType['table']){
				case 'paper' : $col = "a." . $metaType['field_name'];break;
				case 'paper_meta_data' : $col = "c.".$metaType['field_name']." AND a.id=c.paper_id AND c.paper_meta_data_type_id=". $metaType['meta_type'];
					$table[] = "$tbPaperMeta c";
					break;
				default: $col = "a.". $metaType['table'] . '_id';
			}
			$w = $sText->getSQLWhere("a.content",$whereList);
			if (trim($w) != ''){
				#$w =(($this->Data['include']==0) ? ' ( not (':' (') . $w;
				#$w .=(($this->Data['include']==0) ? ' ))':' )');
				$w = " ( $w ) ";
				if (isset($w))$where[] =  $w;
			}
		}

		$globalLimit->setTimeLimit(100);
		//TODO content allgemein
		//wegen Sortierung in der ausgaben liste
		$journal = array();
		$db->select($tbJournal);
		while (($rs = $db->nextQ())!==false){
			if (isset($rs['id'])) $journal[$rs['id']]=$rs;
		}

		$db->select($table,'a.id',implode(' AND ',$where),$whereList);
		$this->ResultList=array();
		while (($rs = $db->nextQ())!==false){
			if (isset($rs['id'])) $this->ResultList[$rs['id']]=$rs;
		}
		$catId = array();
		foreach($this->Category as $kC=> $vC)
			if ($vC['include'] != 1) {
				$catId[]=$vC['category_id'];
				$categoryList->childList($vC['category_id'],$catId);
			}
		if (count($catId)>0){
			$w = "category_id IN ('" .implode("','",array_unique($catId)) . "')";
			$w .= " AND paper_id IN ('" . implode("','",array_keys($this->ResultList)) . "')";
			$db->select($tbAnnotation,'paper_id',$w,false," group by paper_id");
			while (($rs = $db->nextQ())!==false){
				unset($this->ResultList[$rs['paper_id']]);
			}
		}

		foreach($this->Category as $kC=> $vC)
			if ($vC['include'] == 1){
				$catId=array($vC['category_id']);
				$categoryList->childList($vC['category_id'],$catId);
				if (count($catId)>0){
					$w = "category_id IN ('" .implode("','",array_unique($catId)) . "')";
					$w .= " AND paper_id IN ('" . implode("','",array_keys($this->ResultList)) . "')";
					$db->select($tbAnnotation,'paper_id,category_id',$w);
					$found = array();
					while (($rs = $db->nextQ())!==false){
						$found[$rs['paper_id']]=1;
					}
					foreach($this->ResultList as $kR=> $vR){
						if (!array_key_exists($kR , $found) ) 
								unset($this->ResultList[$kR]);
					}
				}
		}
		
		

		#TODO allgemein
		$resultKeys = array_keys($this->ResultList);
		$this->Relevanz=0;
		if (array_key_exists('type',$this->Data) and $this->Data['type'] == 'list')
			$dbRes=$db->select("$tbPaper a, {$tbPaper}2{$tbJournal} b",'a.id,a.content,a.title,a.release_date,b.journal_id',"a.id=b.paper_id AND a.id IN ('".implode("','",$this->List)."')");
		else 
			$dbRes=$db->select("$tbPaper a, {$tbPaper}2{$tbJournal} b",'a.id,a.content,a.title,a.release_date,b.journal_id',"a.id=b.paper_id AND a.id IN ('".implode("','",$resultKeys)."')");

		//offset + aufnehmen	
		$this->ResultList=array();
		while ($globalLimit->checkAllLimit()  && ($rs = $db->nextQ(False,$dbRes))!==false){
			if (isset($rs['id'])) {
				if (array_key_exists('journal_id',$rs) && array_key_exists($rs['journal_id'],$journal)) $rs['journal'] = $journal[$rs['journal_id']]['name'];
				$rs['offset']=array();
				$rs['length']=array();
				if ($sText)
					$sText->contentPos($rs['id'],$rs['content'],$rs['offset'],$rs['length']);
				if (!$sText || count($rs['offset'])>0){
					$this->ResultList[$rs['id']]=$rs;
				}
			}
		}
		if ( !$globalLimit->checkAllLimit()){ #Timelimit uebrschritten, Ohne relevanz
			$globalLimit->setTimeLimit(100);
			$globalLimit->memoryLimit=true;
			$this->Relevanz=-1;
			if (array_key_exists('type',$this->Data) and $this->Data['type'] == 'list')
				$dbRes=$db->select("$tbPaper a, {$tbPaper}2{$tbJournal} b",'a.id,a.content,a.title,a.release_date,b.journal_id',"a.id=b.paper_id AND a.id IN ('".implode("','",$this->List)."')");
			else 
				$dbRes=$db->select("$tbPaper a, {$tbPaper}2{$tbJournal} b",'a.id,a.content,a.title,a.release_date,b.journal_id',"a.id=b.paper_id AND a.id IN ('".implode("','",$resultKeys)."')");
			//offset + aufnehmen	
			$this->ResultList=array();
			while (($rs = $db->nextQ(False,$dbRes))!==false){
				if (isset($rs['id'])) {
					if (array_key_exists('journal_id',$rs) && array_key_exists($rs['journal_id'],$journal)) $rs['journal'] = $journal[$rs['journal_id']]['name'];
					$rs['offset']=array();
					$rs['length']=array();
					if ($sText && $sText->contentPos($rs['id'],$rs['content'],$rs['offset'],$rs['length'],true)) {
						$rs['offset'][]=0;
						$rs['length'][]=100;
						$this->ResultList[$rs['id']]=$rs;
					}
				}
			}
		}

		
		$w = "a.category_id IN ('" .implode("','",$catId) . "')";
		$w .= " AND a.paper_id IN ('" . implode("','",array_keys($this->ResultList)) . "') AND b.annotation_id=a.id";
		$db->select(array("$tbAnnotation a", "$tbAnnotationPosition b"),'a.id,a.paper_id,b.offset,b.length',$w);
		while (($rs = $db->nextQ())!==false){
			$this->ResultList[$rs['paper_id']]['offset'][]=$rs['offset'];
			$this->ResultList[$rs['paper_id']]['length'][]=$rs['length'];
		}
		$this->ResultCount=0;
		$w = "id IN ('" . implode("','",array_keys($this->ResultList)) . "')";
		$rs = $db->selectOne(array("$tbPaper"),'sum(word_count) as wC,sum(lemma_count) as lC',$w);
		$this->WordCount=$rs['wC'];
		$this->LemmaCount=$rs['lC'];
		$this->Start=0;

		if ($this->Relevanz==0){
			foreach($this->ResultList as $kR => $vR){
				$this->ResultCount += count($vR['offset']);
				if ($this->Relevanz < count($vR['offset']))$this->Relevanz=count($vR['offset']);
				if (count($vR['offset'])==0)$this->ResultCount++;
			}
		}
		else {
			$this->Message= "zu viele Textstellen, Relevanz nicht extra aufgeführt";
			$this->ResultCount = count($this->ResultList);
		}
		if ($returnOnlyId)
			return array_keys($this->ResultList);
		$_SESSION['searchResult'] = array();
		$_SESSION['searchResult']['list'] = array_keys($this->ResultList);
		if (count($_SESSION['searchResult']['list'])>0)$_SESSION['paperId'] = $_SESSION['searchResult']['list'][0];
		else unset($_SESSION['paperId']);
		$_SESSION['searchResult']['catId'] = $catId;
		$_SESSION['searchResult']['start'] = 0;
		$_SESSION['searchResult']['order'] = 'id';
		$_SESSION['searchResult']['direction'] = 'asc';
		$_SESSION['searchResult']['Category'] = $this->Category;
		$_SESSION['searchResult']['MetaData'] = $this->MetaData;
		$_SESSION['searchResult']['Relevanz'] = $this->Relevanz;
		$_SESSION['searchResult']['Message'] = $this->Message;
		$_SESSION['searchResult']['Data'] = $this->Data;

	}

	function _splitSearchText($text,$keyWords){
		$splitText=array($text);
		foreach($keyWords as $k){
			$newSplitText = array();
			foreach ($splitText as $t){
				$newSplitText=array_merge($newSplitText,preg_split("/($k)/",$t,-1,PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY));
			}
			$splitText=$newSplitText;
		}
		return $splitText;	
	}

	function searchPaperFromSession($order=False,$direction=False,$returnOnlyId=False){
		global $db,$tbPaper,$tbAnnotation,$tbAnnotationPosition,$tbJournal,$tbPaperMeta;

		
		$orderAllow = array('id','journal','content','date','relevanz');
		$directionAllow = array('asc','desc');
		if (!array_key_exists('searchResult',$_SESSION)) {
			if ($returnOnlyId)
				return array();
			return;
		}
		if ($returnOnlyId)
			return $_SESSION['searchResult']['list'];

		if ($order && in_array($order,$orderAllow))
			$this->Order = $order;
		else
			$this->Order = $_SESSION['searchResult']['order'];
		if ($direction && in_array($direction,$directionAllow))
			$this->Direction = $direction;
		else
			$this->Direction= $_SESSION['searchResult']['direction'];

		$_SESSION['searchResult']['direction'] = $this->Direction;
		$_SESSION['searchResult']['order'] = $this->Order;
		$catId=$_SESSION['searchResult']['catId'];
		$this->Category = $_SESSION['searchResult']['Category'];
		$this->MetaData = $_SESSION['searchResult']['MetaData'];
		$this->Relevanz = $_SESSION['searchResult']['Relevanz'];
		$relevanz= ($this->Relevanz==-1)?true:false;
		$this->Data = $_SESSION['searchResult']['Data'];
		$journal = array();
		$db->select($tbJournal);
		while (($rs = $db->nextQ())!==false){
			if (isset($rs['id'])) $journal[$rs['id']]=$rs;
		}
		$db->select("$tbPaper a, {$tbPaper}2{$tbJournal} b",'a.id,a.content,a.title,a.release_date,b.journal_id',"a.id=b.paper_id AND a.id IN ('".implode("','",$_SESSION['searchResult']['list'])."')");
		$this->ResultList=array();
		while (($rs = $db->nextQ())!==false){
			if (isset($rs['id'])) $this->ResultList[$rs['id']]=$rs;
		}

		//offsett
		foreach($this->ResultList as $kR => $vR){
			if (array_key_exists('journal_id',$vR) && array_key_exists($vR['journal_id'],$journal)) $vR['journal'] = $journal[$vR['journal_id']]['name'];
			$vR['offset']=array();
			$vR['length']=array();
			if (array_key_exists('search_text',$this->Data) && mb_strlen($this->Data['search_text'],'utf8') > 0){
				$sText = new SearchText($this->Data['search_text']);

				if ($sText) $sText->contentPos($vR['id'],$vR['content'],$vR['offset'],$vR['length'],$relevanz);
			/*	$i=0;
				while ($i=mb_strpos( $vR['content'] ,$this->Data['search_text'],$i,'utf8')){
					if ($i < 50) $vR['offset'][]=0;
					else $vR['offset'][]=$i-50;
					$vR['length'][]=mb_strlen($this->Data['search_text'],'utf8')+100;
					$i++;
				}*/
			}
			$this->ResultList[$kR]=$vR;
		}
		$w = "a.category_id IN ('" .implode("','",$catId) . "')";
		$w .= " AND a.paper_id IN ('" . implode("','",array_keys($this->ResultList)) . "') AND b.annotation_id=a.id";
		$db->select(array("$tbAnnotation a", "$tbAnnotationPosition b"),'a.id,a.paper_id,b.offset,b.length',$w);
		while (($rs = $db->nextQ())!==false){
			$this->ResultList[$rs['paper_id']]['offset'][]=$rs['offset'];
			$this->ResultList[$rs['paper_id']]['length'][]=$rs['length'];
		}
		$this->ResultCount=0;
		foreach($this->ResultList as $kR => $vR){
			$this->ResultCount += count($vR['offset']);
			if (count($vR['offset'])==0)$this->ResultCount++;
		}
		$w = "id IN ('" . implode("','",array_keys($this->ResultList)) . "')";
		$rs = $db->selectOne(array("$tbPaper"),'sum(word_count) as wC,sum(lemma_count) as lC',$w);
		$this->WordCount=$rs['wC'];
		$this->LemmaCount=$rs['lC'];
		$this->orderSearch();
		$_SESSION['searchResult']['list'] = array_keys($this->ResultList);

	}

	function cmp($a,$b){
		$faktor= ($this->Direction=='asc')? 1 : -1;
		switch ($this->Order){
			case 'journal': 
				if (!array_key_exists('journal',$a)) return $faktor * 1;
				if (!array_key_exists('journal',$b)) return $faktor * -1;
				return $faktor*strcmp($a['journal'],$b['journal']);
			case 'date': 
				if (!array_key_exists('release_date',$a)) return $faktor * 1;
				if (!array_key_exists('release_date',$b)) return $faktor * -1;
				return $faktor*strcmp($a['release_date'],$b['release_date']);
			case 'relevanz': 
				if (!array_key_exists('offset',$a)) return $faktor * 1;
				if (!array_key_exists('offset',$b)) return $faktor * -1;
				if (count($a['offset']) < count($b['offset'])) return $faktor * 1;
				if (count($a['offset']) == count($b['offset'])) return 0;
				return $faktor*-1;
			case 'content': 
				return $faktor*strcmp(mb_substr($a['content'] , (array_key_exists('0',$a['offset']))?$a['offset'][0]:0, 30),mb_substr($b['content'] , (array_key_exists('0',$b['offset']))? $b['offset'][0]:0, 30));
		}
		return $faktor*strcmp($a['journal'],$b['journal']);
	}

	function orderSearch(){
		$cmpFkt = array('journal'=>'strcmp','content'=>'contentCmp','date'=>'strcmp');
		if ($this->Order=='id') {
			if ($this->Direction=='asc') return;
			$this->ResultList = array_reverse($this->ResultList,true);
			return;
		}

		uasort($this->ResultList,array($this,"cmp"));


	}

}


class SearchList {
	var $List=array();

	//Liste 
	function SearchList(){
		global $db, $tbSearch;

		$db->select($tbSearch);

		while (($rs = $db->nextQ())!==false){
			if (isset($rs['id'])) $this->List[$rs['id']]=$rs;
		}
	}



}

