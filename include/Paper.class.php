<?php

defined( '_VALID_INGWER' ) or die( 'Restricted access' );

require_once('init.php');
	function cmpLength($a, $b)
	{
		    if ($a->Data['length'] == $b->Data['length']) {
				    return 0;
					}
			return ($a->Data['length'] < $b->Data['length']) ? 1 : -1;
	}

	function compareAnnotation($a, $b){
		if ($a->Data['id']==$b->Data['id']) return 0;
		if ($a->Data['id']>$b->Data['id']) return 1;
		return -1;
	}

class Paper {

	var $Data;
	var $Annotation;
	var $MType;
	var $error="";
	var $contentKey;
	var $MaxDepth=0;

	//Override true: uebergebenes Data bleibt erhalten
	function Paper ($Data=array(),$POS=False,$Load=true,$Override=False,$CatList=False){
		global $metaOptions;
		mb_internal_encoding("UTF-8"); 
		$this->Data = $Data;
		$this->MType = new PaperMetaDataTypeList();
		foreach($this->MType->List as $kM=>$vM) {
			if ($vM['meta_type']=='content') {
				$this->contentKey=$kM;
				break;
			}
		}
		if($Load && array_key_exists('id',$this->Data) && $this->Data['id'] > 0) {
			$this->initAnnotation($POS,$CatList);
			$this->initMetaData($Override);
		}
		if( !array_key_exists('id',$this->Data)) $this->Data['id'] = 0;
	}

	function initAnnotation($POS=false,$CatList=False){
		if ($this->Data['id'] == 0) return;
		$this->Annotation = new AnnotationList($this->Data['id'],$CatList,$POS);
	}

	function addAnnotationLocate($Data){
		$cat = new Category('',(int)$Data['categoryId']);
		if ($cat->Data['id'] == 0) return;
		$user = new User('',$Data['user']);
		if ($user->Data['id'] == 0) $user->save();
		if (array_key_exists('name',$user->Data))setcookie('ingwerUser',$user->Data['name'],time()+60*60*24*365);
		$Data['text'] = mb_ereg_replace("\\\'","'",$Data['text'],'p');
		$Data['text'] = mb_ereg_replace('\\\{2,2}','\\',$Data['text'],'p');
		$Data['text'] = mb_ereg_replace('\\\"','"',$Data['text'],'p');
		$Data['text'] = mb_ereg_replace("\r\n","\n",$Data['text'],'p');
		$Data['text'] = mb_ereg_replace("\r","\n",$Data['text'],'p');
		mb_regex_encoding('UTF-8');
		mb_internal_encoding("UTF-8"); 

		if (mb_ereg("c[0-9]+",$Data['textEndId'])==1 ){$end=intval(mb_substr($Data['textEndId'],1));}
		else {$end=false;}
		if (mb_ereg("c[0-9]+",$Data['textStartId'])==1 ){$start=intval(mb_substr($Data['textStartId'],1));}
		else {$start=$end;}
		if ($start===false){
			$sibUp=0;
			$sibDown=0;
			$sibUpLen=0;
			$sibDownLen=0;
			if (isset($Data['textMouseDownSib']) && $Data['textMouseDownSib'] != ""){
				$Data['textMouseDownSib'] = mb_ereg_replace("\\\'","'",$Data['textMouseDownSib'],'p');
				$Data['textMouseDownSib'] = mb_ereg_replace('\\\{2,2}','\\',$Data['textMouseDownSib'],'p');
				$Data['textMouseDownSib'] = mb_ereg_replace('\\\"','"',$Data['textMouseDownSib'],'p');
				$sibDownLen=mb_strlen($Data['textMouseDownSib']);
				if (mb_substr_count( $this->Data[$this->contentKey] ,$Data['textMouseDownSib'] )==1){
					$sibDown = mb_strpos( $this->Data[$this->contentKey] ,$Data['textMouseDownSib']);
				}
			}
			if (isset($Data['textMouseUpSib']) && $Data['textMouseUpSib'] != ""){
				$Data['textMouseUpSib'] = mb_ereg_replace("\\\'","'",$Data['textMouseUpSib'],'p');
				$Data['textMouseUpSib'] = mb_ereg_replace('\\\{2,2}','\\',$Data['textMouseUpSib'],'p');
				$Data['textMouseUpSib'] = mb_ereg_replace('\\\"','"',$Data['textMouseUpSib'],'p');
				$sibUpLen=mb_strlen($Data['textMouseUpSib']);
				if (mb_substr_count( $this->Data[$this->contentKey] ,$Data['textMouseUpSib'] )==1){
					$sibUp = mb_strpos( $this->Data[$this->contentKey] ,$Data['textMouseUpSib']);
				}
			}
			$annoPos = array();
			$annoPos['length'] = mb_strlen($Data['text']);
			if (mb_substr_count( $this->Data[$this->contentKey] ,$Data['text'] )==1){
				$annoPos['offset'] = mb_strpos( $this->Data[$this->contentKey] ,trim($Data['text']));
				$annoPos['offset'] += mb_strpos( $Data['text'],trim($Data['text']));
			}
			elseif (mb_substr_count( $this->Data[$this->contentKey] ,trim($Data['text']) )==1){
				$annoPos['offset'] = mb_strpos( $this->Data[$this->contentKey] ,trim($Data['text']));
			}
			elseif (mb_substr_count( mb_ereg_replace("\s","",$this->Data[$this->contentKey],'p') ,mb_ereg_replace("\s","",$Data['text'],'p') )==1){
				for($i=0;$i < mb_strlen($Data['text']);$i++)
					if (mb_substr_count( $this->Data[$this->contentKey] ,mb_substr($Data['text'],0,mb_strlen($Data['text'])-$i))>0){
						$annoPos['offset'] = mb_strpos( $this->Data[$this->contentKey] ,mb_substr($Data['text'],0,mb_strlen($Data['text'])-$i));
						$i=mb_strlen($Data['text']);
					}
			}
			else {
				//eingrenzen
				if ($sibUpLen!=0 && $sibDownLen!=0){
					$start = ($sibUp < $sibDown)? $sibUp : $sibDown;
					$end = ($sibUp+$sibUpLen < $sibDown+$sibDownLen) ? $sibUp+$sibUpLen : $sibDown+$sibDownLen;
				}
				$annoPosList = array();
				$down = array();
				$i=0;
				while ($i=mb_strpos( $this->Data[$this->contentKey] ,$Data['text'],$i)){
					if (isset($start) && $start <= $i && $end >=$i)$annoPosList[]=$i;
					$i++;
				}
				if (count($annoPosList) == 0) {
					while ($i=mb_strpos( $this->Data[$this->contentKey] ,$Data['text'],$i)){
						$annoPosList[]=$i;
						$i++;
					}
				}
				if (array_key_exists('textMouseDown',$Data) && (int)$Data['textMouseDown']>0){
					$anno = $this->Annotation->List[(int)$Data['textMouseDown']];
					foreach($anno->Position as $kP => $vP){
						if ($vP['length'] > $Data['textStartOffset'])$down[]=$vP['offset']+$Data['textStartOffset'];
						if ($vP['length'] > $Data['textEndOffset'])$down[]=$vP['offset']+$Data['textEndOffset'];
					}
				}
				else {
					if (array_key_exists('textMouseDownPrev',$Data) && (int)$Data['textMouseDownPrev']>0){
						$anno = $this->Annotation->List[(int)$Data['textMouseDownPrev']];
						foreach($anno->Position as $kP => $vP){
							$down[]=$vP['length']+$vP['offset']+$Data['textStartOffset'];
							$down[]=$vP['length']+$vP['offset']+$Data['textEndOffset'];
						}
					}
					else {
						$down[]=$Data['textStartOffset'];
						$down[]=$Data['textEndOffset'];
					}
				}
				if (array_key_exists('textMouseUp',$Data) && (int)$Data['textMouseUp']>0){
					$anno = $this->Annotation->List[(int)$Data['textMouseUp']];
					foreach($anno->Position as $kP => $vP){
						if ($vP['length'] > $Data['textStartOffset'])$up[]=$vP['offset']+$Data['textStartOffset'];
						if ($vP['length'] > $Data['textEndOffset'])$up[]=$vP['offset']+$Data['textEndOffset'];
					}
				}
				else {
					if (array_key_exists('textMouseUpPrev',$Data) && (int)$Data['textMouseUpPrev']>0){
						$anno = $this->Annotation->List[(int)$Data['textMouseUpPrev']];
						foreach($anno->Position as $kP => $vP){
							$up[]=$vP['length']+$vP['offset']+$Data['textStartOffset'];
							$up[]=$vP['length']+$vP['offset']+$Data['textEndOffset'];
						}
					}
					else {
						$up[]=$Data['textStartOffset'];
						$up[]=$Data['textEndOffset'];
					}
				}
				$locate = array();
				foreach($annoPosList as $kA=>$vA){
					if (in_array($vA,$up) && in_array($vA+$annoPos['length'],$down))$locate[]=$vA;
				}
				if (count($locate)==0){
					foreach($annoPosList as $kA=>$vA){
						if (in_array($vA,$up))$locate[]=$vA;
					}
				}
				if (count($locate)==0){
					foreach($annoPosList as $kA=>$vA){
						if (in_array($vA,$down))$locate[]=$vA;
					}
				}
				if (count($locate)>0){
					$annoPos['offset'] = reset($locate);
				}
				else {
					$diff = 1000;
					foreach($annoPosList as $kA=>$vA){
						foreach($up as $vU){
							if (abs($vA-$vU)<$diff){
								$diff =abs($vA-$vU);
								$annoPos['offset']=$vA;
							}
						}
						foreach($down as $vD){
							if (abs($vA-$vD)<$diff){
								$diff =abs($vA-$vD);
								$annoPos['offset']=$vA;
							}
						}
					}
				}
				if (!isset($annoPos['offset'])) $annoPos['offset'] = reset($annoPosList);
			}
		}else {
			
			if ($end!=false and $start > $end){
				$tmp=$start;
				$start=$end;
				$end=$tmp;
			}
			if ($Data['textMouseDblClick']==0) { $start -= mb_strlen($Data['text']);}

			if ($start+mb_strlen($Data['text']) > mb_strlen($this->Data[$this->contentKey])){$start=mb_strlen($this->Data[$this->contentKey])-mb_strlen($Data['text']);}
			if (mb_strpos( $this->Data[$this->contentKey] ,$Data['text'],$start) < $start+2){
				$annoPos['offset']=mb_strpos( $this->Data[$this->contentKey] ,$Data['text'],$start,"UTF-8");
			}else {
				$annoPos['offset']=mb_strpos( $this->Data[$this->contentKey] ,$Data['text'],abs($start-2),"UTF-8");
			}
			if (!$annoPos['offset']){$annoPos['offset']=$start;}
		}
		$annoPos['length'] = mb_strlen($Data['text']);
		$annoPos['category_id']=$cat->Data['id'];
		$annoPos['user_id']=$user->Data['id'];
		if (!isset($annoPos['offset']) || !is_int($annoPos['offset']) ||!isset($annoPos['length'])) return false;
		$this->addAnnotation($annoPos);
		return true;
	}

	function getTextForAnnotation($annoId){
		$res=array();
		if (!array_key_exists($annoId,$this->Annotation->List)) return $res;
		foreach($this->Annotation->List[$annoId]->Position as $vA){
			$res[]=mb_substr ( $this->Data[$this->contentKey] , $vA['offset'],$vA['length']);
		}
		return $res;
	}
	function getTextListForAnnotationList($category_id=true){
		$res=array();
		foreach($this->Annotation->List as $kA => $vA){
			if (( $category_id === true) or ( $vA->Data['category_id'] == $category_id)) 
				$res[$kA]=$this->getTextForAnnotation($kA);
		}
		return $res;
	}

	function addAnnotation($Data){
		if (!array_key_exists('Position',$Data)){ 
			$Data['Position'] = array();
			if (array_key_exists('lemma',$Data))
				$Data['Position'][] = array('offset'=>$Data['offset'],'length'=>$Data['length'],'lemma' => $Data['lemma']);
			else 
				$Data['Position'][] = array('offset'=>$Data['offset'],'length'=>$Data['length']);
		}
		$this->Annotation->add($this->Data['id'],$Data);

	}

	function initMetaData($Override){
		global $db, $tbPaper, $tbPaperMetaType,$tbTablePrefix;

		$rs = $db->selectOne($tbPaper,'*',"id=".$db->escape_string($this->Data['id']));
		if (!$rs) return;
		foreach ($this->MType->List as $k => $v){
			if (!$Override && array_key_exists($k,$this->Data)) continue;
			if ($tbTablePrefix.$v['table'] == $tbPaper) $this->Data[$k] = $rs[$v['field_name']];
			else {
				if ($v['meta_type'] == 'list') {
					$rM = $db->selectOne("$tbTablePrefix{$v['table']} a, {$tbPaper}2$tbTablePrefix{$v['table']} b","a.{$v['field_name']}","a.id=b.{$v['table']}_id AND b.paper_id=".$db->escape_string($this->Data['id']));
					$this->Data[$k] = $rM[$v['field_name']];
				} elseif ($v['meta_type'] == 'multilist') {
					$rM = $db->select("$tbTablePrefix{$v['table']} a, {$tbPaper}2$tbTablePrefix{$v['table']} b","a.{$v['field_name']}","a.id=b.{$v['table']}_id AND b.paper_id=".$db->escape_string($this->Data['id']));
					$this->Data[$k] = array();
					while (($rM = $db->nextQ())!==false){
						if (isset($rM[$v['field_name']])) $this->Data[$k][] = $rM[$v['field_name']];
					}
				}
			}
		}
	
	}

	function checkDuplicate($checksum){
		global $db, $tbPaper;

		$rs = $db->selectOne($tbPaper,'id',"content_checksum='%s'",$checksum);
		if ($rs && isset($rs['id'])) return $rs['id'];
		return false;
	}


	function saveMetaData($checkDuplicate=false){
		global $db, $tbPaper, $tbPaperMetaType,$tbTablePrefix;
		$this->MType = new PaperMetaDataTypeList();
		$noDup = true;
		
		//Update Papertable
		$update = array();
		foreach ($this->MType->List as $k => $v){
			if ($tbTablePrefix.$v['table'] == $tbPaper) { if (array_key_exists($k,$this->Data) ) $update[$v['field_name']] = $this->Data[$k];}
		}
		if (!array_key_exists('content',$update)) $update['content']="";
		$update['change_date'] = date("Y-m-d",time());
		$update['content_checksum'] = sha1($update['content']);
		if ($this->Data['id'] > 0) $db->update($tbPaper,$update,"id=%s",$this->Data['id']);
		else {
			if ($checkDuplicate && ($id=$this->checkDuplicate($update['content_checksum']))){
				$this->Data['id'] = $id;
				$db->update($tbPaper,$update,"id=%s",$this->Data['id']);
				$this->initAnnotation();
				$this->Annotation->delAll();
				if (!array_key_exists('content_checksum',$this->Data) || $update['content_checksum'] != $this->Data['content_checksum']) $this->Annotation->delAll();
				$noDup = false;
			} else {
				$db->insert($tbPaper,$update);
				$this->Data['id'] = $db->lastId();
				$this->initAnnotation();
			}
		}
		$this->Data['change_date'] = $update['change_date']; 
		$this->Data['content_checksum'] = $update['content_checksum']; 

		//Update paper_meta_data Table 
		foreach ($this->MType->List as $k => $v){
			if($v['meta_type'] == 'list') {
				$db->delete("{$tbPaper}2$tbTablePrefix{$v['table']}","paper_id=%s",array($this->Data['id']));
				if (!array_key_exists($k,$this->Data) ) continue;
				if ($this->Data[$k]!='') {
					$value = $db->selectOne($tbTablePrefix.$v['table'],'id',"{$v['field_name']}='%s'",$this->Data[$v['name']]);
					//gegenenfalls Neues Listenelement hinzufuegen
					if (!$value || !isset($value['id']) ){
						$db->insert($tbTablePrefix.$v['table'],array($v['field_name'] => $this->Data[$v['name']]));
						$db->insert("{$tbPaper}2$tbTablePrefix{$v['table']}",array("{$v['table']}_id" => $db->lastId(),"paper_id"=>$this->Data['id']));
					} else 
						$db->insert("{$tbPaper}2$tbTablePrefix{$v['table']}",array("{$v['table']}_id" => $value['id'],"paper_id"=>$this->Data['id']));
				}
			}
			else if($v['meta_type'] == 'multilist') {
				//erst loeschen
				$db->delete("{$tbPaper}2$tbTablePrefix{$v['table']}","paper_id=%s",array($this->Data['id']));
				//dann neue Werte einfuegen
				if (!array_key_exists($k,$this->Data) ) continue;
				$this->Data[$k] = array_unique($this->Data[$k]);
				foreach ($this->Data[$k] as $kM){
					$value = $db->selectOne($tbTablePrefix.$v['table'],'id',"{$v['field_name']}='%s'",$kM);
					//gegenenfalls Neues Listenelement hinzufuegen
					if (!$value || !isset($value['id']) ){
						$db->insert($tbTablePrefix.$v['table'],array($v['field_name'] => $kM));
						$db->insert("{$tbPaper}2$tbTablePrefix{$v['table']}",array("{$v['table']}_id" => $db->lastId(),"paper_id"=>$this->Data['id']));
					} else 
						$db->insert("{$tbPaper}2$tbTablePrefix{$v['table']}",array("{$v['table']}_id" => $value['id'],"paper_id"=>$this->Data['id']));
				}
			}
		}
		return $noDup;
	}

	

	function lexico3Paper($handle=false){
		foreach($this->Data as $kP=>$kV){
			if (!$this->MType->isExport($kP)) continue;
			if (!isset($kV) || $kV=="") $kV = "kA";
			if (is_array($kV) && count($kV)==0) $kV = "kA";

			$kPXML = preg_replace(array('/\s/i','/ä/i','/ö/i','/ü/i','/ß/i','/[^a-zA-Z0-9_]/i'),
						array('_','ae','oe','ue','ss',''),$kP);
			if ($kP != $this->contentKey && $kP != 'Titel' && $kP != 'Untertitel' && $kP != 'word_count' && $kP != 'lemma_count'){	
				if (is_array($kV)){
					if ($handle===false)
						echo "<$kPXML=".preg_replace(array('/\s/i'),array('_'),implode(';',$kV)).">\n";
					else fwrite($handle, "<$kPXML=".preg_replace(array('/\s/i'),array('_'),implode(';',$kV)).">\n");
				}
				else {
					if ($handle===false){
						echo "<$kPXML=".preg_replace(array('/\s/i'),array('_'),$kV).">\n";
						if ($kP == 'Datum') {
							echo "<Monat=".preg_replace(array('/(\d{2,4}-\d{1,2}).*/i'),array('\\1'),$kV).">\n";
							echo "<Jahr=".preg_replace(array('/(\d{2,4}).*/i'),array('\\1'),$kV).">\n";
						}
					}
					else {
						fwrite($handle,"<$kPXML=".preg_replace(array('/\s/i'),array('_'),$kV).">\n");
						if ($kP == 'Datum') {
							fwrite($handle,"<Monat=".preg_replace(array('/(\d{2,4}-\d{1,2}).*/i'),array('\\1'),$kV).">\n");
							fwrite($handle,"<Jahr=".preg_replace(array('/(\d{2,4}).*/i'),array('\\1'),$kV).">\n");
						}
					}
				}
			}
		}
		if ($handle===false){
			echo preg_replace("/\n(\w)/","\n§$1",$this->Data[$this->contentKey]);
			echo "\n\n\n";
		}else{
			fwrite($handle,preg_replace("/\n(\w)/","\n§$1",$this->Data[$this->contentKey]));
			fwrite($handle,"\n\n\n");
		}
	}

	function replaceSpecialChars($text,$tag=false){
		$Map = array(	'/\s/i'=>'_',
			'/ä/'=>'ae',
			'/ö/'=>'oe',
			'/ü/'=>'ue',
			'/Ä/'=>'Ae',
			'/Ö/'=>'Oe',
			'/Ü/'=>'Ue',
			'/ß/i'=>'ss',
			'/[\x{C8}-\x{CB}]/u'=>'E',
			'/[\x{C0}-\x{C6}]/u'=>'A',
			'/[\x{CC}-\x{CF}]/u'=>'I',
			'/[\x{D2}-\x{D8}]/u'=>'O',
			'/[\x{D9}-\x{DC}]/u'=>'U',
			'/[\x{C7}]/u'=>'C',
			'/[\x{E8}-\x{EB}]/u'=>'e',
			'/[\x{E0}-\x{E6}]/u'=>'a',
			'/[\x{EC}-\x{EF}]/u'=>'i',
			'/[\x{F2}-\x{F8}]/u'=>'o',
			'/[\x{F9}-\x{FC}]/u'=>'u',
			'/[\x{E7}]/u'=>'c',
			'/[^a-zA-Z0-9_]/i'=>''
		);
		if ($tag){
			$Map['/_/']='';
		}
			return preg_replace(array_keys($Map),array_values($Map),$text);
	}

	function cwbPaper($cat=false,$handle=false,$meta=array()){
		$start = "<text";
		foreach($this->Data as $kP=>$kV){

			if (!$this->MType->isExport($kP) && $kP != 'id') continue;
			$kPXML = $this->replaceSpecialChars($kP,true);
			if ($kP != $this->contentKey){	
				if (array_key_exists($this->MType->List[$kP]['id'],$meta)){
					if (is_array($kV)){
						$start .= " ".$this->replaceSpecialChars($kP,true)."=\"".htmlspecialchars($this->replaceSpecialChars(implode('__',$kV)))."\"";
					}
					else {$start .= " ".$this->replaceSpecialChars($kP,true)."=\"".htmlspecialchars($this->replaceSpecialChars($kV))."\"";}
				}else {
					if (is_array($kV)){
						$start .= " $kPXML=\"".htmlspecialchars(implode(';',$kV))."\"";
					}
					else {$start .= " $kPXML=\"".htmlspecialchars($kV)."\"";}
				}
			}
		}
		if ($handle===false)
			echo $start . ">\n";
		else fwrite($handle, $start . ">\n");

		if ($cat===false) $catList = new CategoryList();
		else $catList = $cat;
		$in = array();
		$out = array();
		$catListKeys = array_keys($catList->List);
		//Damit Woerter in Annotation sind erst StartTags->POS->EndTags
		foreach ($this->Annotation->List as $kA) {
			if (!in_array($kA->Data['category_id'],$catList->ListReadonly) && in_array($kA->Data['category_id'],$catListKeys)){
				foreach ($kA->Position as $kP => $vP) {
					//$start = sprintf('<annotation categoryid="%s" categoryname="%s" annotationid="%s" type="start"></annotation>',$kA->Data['category_id'],htmlspecialchars($catList->List[$kA->Data['category_id']]->Data['name']),$kA->Data['id']);
					if (array_key_exists('category',$meta))
						$start = array('categoryid'=>$kA->Data['category_id'],'categoryname'=>htmlspecialchars($this->replaceSpecialChars($catList->List[$kA->Data['category_id']]->Data['name'])),'annotationid'=>$kA->Data['id'],'type'=>'start');
					else 
						$start = array('categoryid'=>$kA->Data['category_id'],'categoryname'=>htmlspecialchars($catList->List[$kA->Data['category_id']]->Data['name']),'annotationid'=>$kA->Data['id'],'type'=>'start');

					if (!array_key_exists($vP['offset'],$in))$in[$vP['offset']]=array($start);
					else $in[$vP['offset']][] = $start;
				}
			}
		}
		//treetagger
		$POS = array();
		foreach ($this->Annotation->List as $kA) {
			if (in_array($kA->Data['category_id'],$catList->ListReadonly)){
				foreach ($kA->Position as $kP => $vP) {
					$POS[]=$vP['offset'];
					$start = array('art'=>$catList->List[$kA->Data['category_id']]->Data['name'],'lemma'=>$vP['lemma'],'length'=>$vP['length']);
					if (!array_key_exists($vP['offset'],$in))$in[$vP['offset']]=array($start);
					else $in[$vP['offset']][] = $start;
				}
			}
		}
		//korrektur fuer Treetagger
		/*if (mb_strlen($this->Data[$this->contentKey])){
			$blank = array(0);
			$whites = array(" ","\n","\t");
			foreach($whites as $vW){
				$i=0;
				while ($i=mb_strpos( $this->Data[$this->contentKey] ,$vW,$i)){
					$blank[]=$i+1;
					$i++;
				}
			}
			$blankDiffs = array_diff($blank,$POS);
			if ((count($POS)>0 && (count($blankDiffs) / count($POS)) > 0.1) || (count($POS)==0 && count($blankDiffs)>0)){
				//treeTagger nicht order zu schlecht gelaufen
				$in = array();
				//Damit Woerter in Annotation sind erst StartTags->POS->EndTags
				foreach ($this->Annotation->List as $kA) {
					if (!in_array($kA->Data['category_id'],$catList->ListReadonly)){
						foreach ($kA->Position as $kP => $vP) {
							//$start = sprintf('<annotation categoryid="%s" categoryname="%s" annotationid="%s" type="start"></annotation>',$kA->Data['category_id'],htmlspecialchars($catList->List[$kA->Data['category_id']]->Data['name']),$kA->Data['id']);
							$start = array('categoryid'=>$kA->Data['category_id'],'categoryname'=>htmlspecialchars($catList->List[$kA->Data['category_id']]->Data['name']),'annotationid'=>$kA->Data['id'],'type'=>'start');

							if (!array_key_exists($vP['offset'],$in))$in[$vP['offset']]=array($start);
							else $in[$vP['offset']][] = $start;
						}
					}
				}
				sort($blankDiffs);
				foreach($blankDiffs as $vB){
					if (mb_strlen ( $this->Data[$this->contentKey])>$vB){
						unset($min);
						foreach($whites as $vW){
							if (mb_strpos( $this->Data[$this->contentKey] ,$vW,$vB+1) !== false
								&& (!isset($min) || $min > mb_strpos( $this->Data[$this->contentKey] ,$vW,$vB+1))
								){
								$min = mb_strpos( $this->Data[$this->contentKey] ,$vW,$vB+1);
							}
						}
						if (isset($min)){
							$word = mb_substr ( $this->Data[$this->contentKey] , $vB , $min-$vB);
							$start = array('art'=>'Blank','lemma'=>$word,'length'=>mb_strlen($word));
							if (!array_key_exists($vB,$in))$in[$vB]=array($start);
							else $in[$vB][] = $start;
						}
						else {
							$word = mb_substr ( $this->Data[$this->contentKey] , $vB);
							$start = array('art'=>'Blank','lemma'=>$word,'length'=>mb_strlen($word));
							if (!array_key_exists($vB,$in))$in[$vB]=array($start);
							else $in[$vB][] = $start;
						}
					}

				}
			}
		}*/

		//Ende Annotationen
		foreach ($this->Annotation->List as $kA) {
			if (!in_array($kA->Data['category_id'],$catList->ListReadonly) && in_array($kA->Data['category_id'],$catListKeys)){
				foreach ($kA->Position as $kP => $vP) {
					//$end = sprintf('<annotation categoryid="%s" categoryname="%s" annotationid="%s" type="end"></annotation>',$kA->Data['category_id'],htmlspecialchars($catList->List[$kA->Data['category_id']]->Data['name']),$kA->Data['id']);
					$end = array('categoryid'=>$kA->Data['category_id'],'categoryname'=>htmlspecialchars($catList->List[$kA->Data['category_id']]->Data['name']),'annotationid'=>$kA->Data['id'], 'type'=>'end');

					if (!array_key_exists(($vP['offset']+$vP['length']),$in))$in[$vP['offset']+$vP['length']]=array($end);
					else $in[$vP['offset']+$vP['length']][] = $end;
				}
			}
		}
		ksort($in);
		$offset=0;
		foreach ($in as $kKI => $vVI){
			foreach ($vVI as $kI => $vI){

				if (array_key_exists('art',$vI)){
					if ($handle===false){
						echo mb_substr ( $this->Data[$this->contentKey] , $kKI ,$vI['length']);
						echo "\t{$vI['art']}\t{$vI['lemma']}\n";
					}
					else {
						fwrite($handle, mb_substr ( $this->Data[$this->contentKey] , $kKI ,$vI['length']));
						fwrite($handle, "\t{$vI['art']}\t{$vI['lemma']}\n");
					}
				}
				else {
					foreach($out as $vOut) {
						if ($handle===false)	echo "</annotation>\n";
						else fwrite($handle, "</annotation>\n");
					}
					if ($vI['type']=='start') $out[$vI['annotationid']]= $vI;
					else unset ($out[$vI['annotationid']]);
					foreach($out as $vOut) {
						$end = sprintf('<annotation categoryid="%s" categoryname="%s" annotationid="%s">',$vOut['categoryid'],$vOut['categoryname'],$vOut['annotationid']);
						if ($handle===false)	echo "$end\n";
						else fwrite($handle, "$end\n");
					}
				}
			}
		}
		foreach($out as $vOut) {
			if ($handle===false)	echo "</annotation>\n";
			else fwrite($handle, "</annotation>\n");
		}
		if ($handle===false) echo "</text>\n\n";
		else fwrite($handle, "</text>\n\n");
	}


	function cvsFirstLinePaper($handle=false,$seperator=";",$newline=True,$surround=True){
		$intList=array("date","int");
		foreach($this->Data as $kP=>$kV){
			if (!$this->MType->isExport($kP)) continue;
			if ($kP != $this->contentKey && $kP != 'id'){	
				if (is_array($kP)) $vG = implode(", ",$kP);
				else $vG = $kP;
				if (!$newline) $vG = preg_replace("/\n/"," ",$vG);
				if (($surround && !in_array($this->MType->List[$kP]['meta_type'],$intList)) || preg_match("/$seperator/",$vG))
					$vG = '"'.preg_replace('/"/','""',$vG).'"';
				if ($handle===false)
					echo "$vG$seperator";
				else
					fwrite($handle, "$vG$seperator");
			}
		}
				
		$vG = $this->contentKey;
		if (!$newline) $vG = preg_replace("/\n/"," ",$vG);
		if ($surround) $vG = '"'.preg_replace('/"/','\"',$vG).'"';
		if ($handle===false)
			echo "$vG\n";
		else
			fwrite($handle, "$vG\n");
	}
	function cvsPaper($handle=false,$seperator=";",$newline=True,$surround=True){
		$intList=array("date","int");
		foreach($this->Data as $kP=>$kV){
			if (!$this->MType->isExport($kP)) continue;
			if ($kP != $this->contentKey && $kP != 'id'){	
				if (is_array($kV)) $vG = implode(", ",$kV);
				else $vG = $kV;
				if (!$newline) $vG = preg_replace("/\n/"," ",$vG);
				if (($surround && !in_array($this->MType->List[$kP]['meta_type'],$intList)) || preg_match("/$seperator/",$vG))
					$vG = '"'.preg_replace('/"/','""',$vG).'"';
				if ($handle===false)
					echo "$vG$seperator";
				else
					fwrite($handle, "$vG$seperator");
			}
		}
				
		$vG = $this->Data[$this->contentKey];
		if (!$newline) $vG = preg_replace("/  /"," ",preg_replace("/\n/"," ",$vG));
		if ($surround) $vG = '"'.preg_replace('/"/','\"',$vG).'"';
		if ($handle===false)
			echo "$vG\n";
		else
			fwrite($handle, "$vG\n");
	}

	function xmlPaper($cat=false,$handle=false){
		if ($handle===false)
			echo "<paper>\n";
		else fwrite($handle, "<paper>\n");

		foreach($this->Data as $kP=>$kV){
			if (!$this->MType->isExport($kP)) continue;
			$kPXML = preg_replace(array('/\s/i','/ä/i','/ö/i','/ü/i','/ß/i','/[^a-zA-Z0-9_]/i'),
						array('','ae','oe','ue','ss',''),$kP);
			if ($kP != $this->contentKey){	
				if (is_array($kV)){
					foreach ($kV as $vG) { 
						if ($handle===false)
							echo "\t<$kPXML>$vG</$kPXML>\n";
						else
							fwrite($handle, "\t<$kPXML>$vG</$kPXML>\n");
					}
				}
				else {
					if ($handle===false)
						echo "<$kPXML>\t$kV</$kPXML>\n";
					else
						fwrite($handle, "<$kPXML>\t$kV</$kPXML>\n");
				}
			}
		}
		$in = array();

		if ($cat===false) $catList = new CategoryList();
		else $catList = $cat;
		$catListKeys = array_keys($catList->List);

		foreach ($this->Annotation->List as $kA) {
			if (in_array($kA->Data['category_id'],$catListKeys)){
				foreach ($kA->Position as $kP => $vP) {
					$start = sprintf('<annotation categoryid="%s" categoryname="%s" annotationid="%s" categorypath="%s" type="start" />',$kA->Data['category_id'],htmlspecialchars($catList->List[$kA->Data['category_id']]->Data['name']),$kA->Data['id'],htmlspecialchars($catList->List[$kA->Data['category_id']]->ParentPath));
					$end = sprintf('<annotation categoryid="%s" categoryname="%s" annotationid="%s" type="end" />',$kA->Data['category_id'],htmlspecialchars($catList->List[$kA->Data['category_id']]->Data['name']),$kA->Data['id']);

					if (!array_key_exists($vP['offset'],$in))$in[$vP['offset']]=array($start);
					else $in[$vP['offset']][] = $start;
					if (!array_key_exists(($vP['offset']+$vP['length']),$in))$in[$vP['offset']+$vP['length']]=array($end);
					else $in[$vP['offset']+$vP['length']][] = $end;
				}
			}
		}
		ksort($in);
		$offset=0;
		if ($handle===false)
			echo "\n<content>";
		else
			fwrite($handle, "\n<content>");
		foreach ($in as $kI => $vI){
			if ($handle===false)
				echo htmlspecialchars(mb_substr ( $this->Data[$this->contentKey] , $offset , $kI-$offset),ENT_XML1,'UTF-8');
			else
				fwrite($handle, htmlspecialchars(mb_substr ( $this->Data[$this->contentKey] , $offset , $kI-$offset),ENT_XML1,'UTF-8'));
			$offset= $kI;
			foreach($vI as $vXml){ 
				if ($handle===false)
					echo $vXml;
				else
					fwrite($handle, $vXml);
			}
		}
		if ($handle===false){
			echo htmlspecialchars(mb_substr ( $this->Data[$this->contentKey] , $offset),ENT_XML1,'UTF-8');
			echo "</content>\n\n";
			echo "</paper>\n\n";
		}else{
			fwrite($handle, htmlspecialchars(mb_substr ( $this->Data[$this->contentKey] , $offset),ENT_XML1,'UTF-8'));
			fwrite($handle, "</content>\n\n");
			fwrite($handle, "</paper>\n\n");
		}
	}


	function maxDepth($force=true){

		if (!$force && $this->MaxDepth > 0) return;
		$catList = new CategoryList();
		$in = array();
		$out = array();
		foreach ($this->Annotation->List as $kA) {
			foreach ($kA->Position as $kP => $vP) {
				$kA->Data['length']=$vP['offset']+$vP['length'];
				if (!array_key_exists($vP['offset'],$in))$in[$vP['offset']]=array($kA);
				else $in[$vP['offset']][] = $kA;
				if (!array_key_exists(($vP['offset']+$vP['length']),$out))$out[$vP['offset']+$vP['length']]=array($kA);
				else $out[$vP['offset']+$vP['length']][] = $kA;
			}
		}
		ksort($in);
		foreach($in as $kI => $vI)usort($in[$kI],"cmpLength");
		ksort($out);
		end($out);
		$to = key($out);
		$current = array();
		$htmlContent='';
		$lastPos=0;
		//maxDepth
		$this->MaxDepth=0;
		for($i=0;$i<=$to;$i++){
			$currentIn=array();
			$closeSpan=0;
			if (array_key_exists($i,$out)) {
				$before=array();
				foreach($out[$i] as $vO){
					if (array_search($vO,$current)!==false) {
						do {
							$pC=array_pop($current);
							if (array_search($pC,$out[$i])!==false) $before[]=$pC;
							else $currentIn[]=$pC;
						}while($pC!=$vO);
					}
					if (array_search($vO,$before)!==false) {
						$closeSpan++;
					}
				}
			}
			$closeSpan += count($currentIn);
			if (array_key_exists($i,$in)) {
				$currentIn = array_merge($currentIn,$in[$i]);
			}
			usort($currentIn,"cmpLength");

			foreach ($currentIn as $v) {
				$current[]=$v;
			}
			$lastPos=$i;
			if ($this->MaxDepth < count($current)-1) $this->MaxDepth = count($current)-1;
		}
		//maxDepth ende
	}



	function htmlAnnotatedContent($print=false,$start='<span  class="categoryId%s" name="annotationId%s" style="border-top-style: solid;border-top-width: %spx;border-top-color: transparent; display: inline;">',$end='</span>'){
		$catList = new CategoryList();
		$in = array();
		$out = array();
		$sentence=new Annotation(0);
		$sentence->Data['sentenceId']= 0;
		$sentence->Data['offset']= 0;
		$sentence->Data['category_id']=0;
		foreach ($catList->List as $v){
			if ($v->Data['name'] == '$.'){
				$sentence->Data['category_id']= $v->Data['id'];
				break;
			}
		}
		foreach ($this->Annotation->List as $kA) {
			foreach ($kA->Position as $kP => $vP) {
				$kA->Data['length']=$vP['offset']+$vP['length'];
				if (!array_key_exists($vP['offset'],$in))$in[$vP['offset']]=array($kA);
				else $in[$vP['offset']][] = $kA;
				if (!array_key_exists(($vP['offset']+$vP['length']),$out))$out[$vP['offset']+$vP['length']]=array($kA);
				else $out[$vP['offset']+$vP['length']][] = $kA;
				if ($kA->Data['category_id'] == $sentence->Data['category_id']) {
					$sentence->Data['sentenceId']++;
					$sentence->Data['length'] = $vP['offset']+$vP['length'] - $sentence->Data['offset'];
					if (!array_key_exists($sentence->Data['offset'],$in))$in[$sentence->Data['offset']]=array(clone $sentence);
					else $in[$sentence->Data['offset']][] = clone $sentence;
					if (!array_key_exists(($vP['offset']+$vP['length']),$out))$out[$vP['offset']+$vP['length']]=array(clone $sentence);
					else $out[$vP['offset']+$vP['length']][] = clone $sentence;
					$sentence->Data['offset']= $vP['offset']+$vP['length'];
				}
			}
		}
		ksort($in);
		foreach($in as $kI => $vI)usort($in[$kI],"cmpLength");
		ksort($out);
		end($out);
		$to = key($out);
		$current = array();
		$htmlContent='';
		$lastPos=0;
		$this->maxDepth(false);
		$sCount=0;
		$sentenceCategory=array();
		$currentSentence=0;
		$charCount=0;
		if($print){
			for($i=0;$i<=$to;$i++){
				$currentIn=array();
				$closeSpan=0;
				if (array_key_exists($i,$out)) {
					$before=array();
					foreach($out[$i] as $vO){
						if (array_search($vO,$current)!==false) {
							do {
								$pC=array_pop($current);
								if (array_search($pC,$out[$i])!==false) $before[]=$pC;
								else $currentIn[]=$pC;
							}while($pC!=$vO);
						}
						if (array_search($vO,$before)!==false) {
							$closeSpan++;
						}
					}
				}
				$closeSpan += count($currentIn);
				if (array_key_exists($i,$in)) {
					$currentIn = array_merge($currentIn,$in[$i]);
				}
				if ($closeSpan > 0 || count($currentIn) > 0) {
					usort($currentIn,"cmpLength");
					echo preg_replace('/\n/','<br />',$this->characterSeparate(mb_substr($this->Data[$this->contentKey],$lastPos,($i-$lastPos)),$charCount));
					for ($iSpan=0; $iSpan<$closeSpan;$iSpan++) {echo $end;}

					foreach ($currentIn as $v) {
						$depth = ($this->MaxDepth -2 - count($current))*3;
						if (array_key_exists('sentenceId',$v->Data)){
							printf('<span  name="sentenceId%s" style="display: inline;">',$v->Data['sentenceId']);
							$currentSentence=$v->Data['sentenceId'];
							if (!array_key_exists($currentSentence,$sentenceCategory))$sentenceCategory[$currentSentence]=array();
						}
						else {
							printf($start,$v->Data['category_id'],$v->Data['id'],$depth);
							if (!in_array($v->Data['category_id'],$catList->ListReadonly)) $sentenceCategory[$currentSentence][]=$v->Data['category_id'];
							foreach ($current as $vI) 
								if (!in_array($vI->Data['category_id'],$catList->ListReadonly)) $sentenceCategory[$currentSentence][]=$vI->Data['category_id'];
						}
						$current[]=$v;
					}
					$lastPos=$i;
				}
			}
			echo preg_replace('/\n/','<br />',$this->characterSeparate(mb_substr($this->Data[$this->contentKey],$lastPos),$charCount));
			echo '<script type="text/javascript">'.
				"\nCategory.sentenceList = new Array(";
				foreach($sentenceCategory as $vSC)
					echo "\nnew Array(" . ((count(array_unique($vSC))>0)?"'',".implode(",",array_unique($vSC)) : implode(",",array_unique($vSC))).'),';
				echo "\nnew Array());\n</script>";
			return;
		}
		for($i=0;$i<=$to;$i++){
			$currentIn=array();
			$closeSpan=0;
			if (array_key_exists($i,$out)) {
				$before=array();
				foreach($out[$i] as $vO){
					if (array_search($vO,$current)!==false) {
						do {
							$pC=array_pop($current);
							if (array_search($pC,$out[$i])!==false) $before[]=$pC;
							else $currentIn[]=$pC;
						}while($pC!=$vO);
					}
					if (array_search($vO,$before)!==false) {
						$closeSpan++;
					}
				}
			}
			$closeSpan += count($currentIn);
			if (array_key_exists($i,$in)) {
				$currentIn = array_merge($currentIn,$in[$i]);
			}
			if ($closeSpan > 0 || count($currentIn) > 0) {
				usort($currentIn,"cmpLength");
				$htmlContent .= preg_replace('/\n/','<br />',$this->characterSeparate(mb_substr($this->Data[$this->contentKey],$lastPos,($i-$lastPos)),$charCount));
				for ($iSpan=0; $iSpan<$closeSpan;$iSpan++) {$htmlContent.=$end;}

				foreach ($currentIn as $v) {
					$depth = ($this->MaxDepth -2 - count($current))*3;
					if (array_key_exists('sentenceId',$v->Data)){
						$htmlContent.=sprintf('<span  name="sentenceId%s" style="display: inline;">',$v->Data['sentenceId']);
						$currentSentence=$v->Data['sentenceId'];
						if (!array_key_exists($currentSentence,$sentenceCategory))$sentenceCategory[$currentSentence]=array();
					}
					else {
						$htmlContent.=sprintf($start,$v->Data['category_id'],$v->Data['id'],$depth);
						if (!in_array($v->Data['category_id'],$catList->ListReadonly)) $sentenceCategory[$currentSentence][]=$v->Data['category_id'];
						foreach ($current as $vI) 
							if (!in_array($vI->Data['category_id'],$catList->ListReadonly)) $sentenceCategory[$currentSentence][]=$vI->Data['category_id'];
					}
					$current[]=$v;
				}
				$lastPos=$i;
			}
		}
		$htmlContent .= preg_replace('/\n/','<br />',$this->characterSeparate(mb_substr($this->Data[$this->contentKey],$lastPos),$charCount));
		$htmlContent .= '<script type="text/javascript">'.
			"\nCategory.sentenceList = new Array(";
			foreach($sentenceCategory as $vSC)
				$htmlContent .= "\nnew Array(" . ((count(array_unique($vSC))>0)?"'',".implode(",",array_unique($vSC)) : implode(",",array_unique($vSC))).'),';
			$htmlContent .= "\nnew Array());\n</script>";

		return $htmlContent;

	}

	function characterSeparate($string,&$cStart){
		mb_regex_encoding('UTF-8');
		mb_internal_encoding("UTF-8"); 
		$stop   = mb_strlen( $string);
		$result = "";

		for( $idx = 0; $idx < $stop; $idx++)
		      {
			      $result .= "<c r='$cStart'>".htmlspecialchars (mb_substr( $string, $idx, 1),ENT_COMPAT,'UTF-8')."</c>";
			      $cStart++;
				     }

		return $result;
	}

	function annotationDiff($oldContent){
		global $uploadPath,$diff;

		//diff starten und Ausgabe einlesen
		$new = preg_replace("/ /","\n",$this->Data[$this->contentKey]);
		$old = preg_replace("/ /","\n",$oldContent);
		$newName=tempnam($uploadPath,"Ing");
		$oldName=tempnam($uploadPath,"Ing");
		$dat = fopen($oldName,"w");
		if ( fwrite($dat,$old) === false) { 
			fclose($dat);
			unlinkD($oldName);
			return false;
		}
		fclose($dat);
		$dat = fopen($newName,"w");
		if ( fwrite($dat,$new) === false) { 
			fclose($dat);
			unlinkD($oldName);
			unlinkD($newName);
			return false;
		}
		fclose($dat);
		$handle = popen ("$diff $oldName $newName", "r");
		$diffOut=array();
		$oldList= explode("\n",$old);
		while (!feof($handle))
			$diffOut[] = fgets($handle);
		fclose($handle);
		unlinkD($oldName);
		unlinkD($newName);

		//Annotations anpassen
		foreach($this->Annotation->List as $kA => $vA){
			foreach($vA->Position as $kP => $pos){
				$offset=0;
				$currentLine=1;
				unset($startLine);
				unset($endLine);
				foreach($oldList as $word){
					if (($offset >= $pos['offset']) && !isset($startLine)) $startLine = $currentLine;
					if (($offset >= ($pos['offset']+ $pos['length'])) && !isset($endLine)) $endLine = $currentLine;
					if (isset($startLine) && isset($endLine)) break;
					$offset += mb_strlen($word)+1;
					$currentLine++;
				}
				foreach ($diffOut as $diff){
					if (mb_strlen($diff)==0) continue;
					if (mb_strpos($diff,">")=== 0){
						if ($line[0] < $startLine) $pos['offset'] +=  (mb_strlen($diff)-2);
						elseif ($line[0] > $endLine) continue;
						else $pos['length'] += (mb_strlen($diff)-2);
					}
					elseif (mb_strpos($diff,'<')=== 0){
						if ($line[0] < $startLine) $pos['offset'] -=  (mb_strlen($diff)-2);
						elseif ($line[0] > $endLine) continue;
						else $pos['length'] -= (mb_strlen($diff)-2);
						$line[0]++;
					}
					elseif (mb_strpos($diff,'-')=== 0){ continue;}
					else{
						$line = mb_split("[a-z,]",$diff);
						if (!isset($endLine) or $line[0]>$endLine){
							break;
						}

					}
				}
				if (($pos['length'] < 1) || ($pos['offset'] < 0))
					unset($this->Annotation->List[$kA]->Position[$kP]);
				else $this->Annotation->List[$kA]->Position[$kP] = $pos;
			}
			if (count($this->Annotation->List[$kA])==0)
				$this->Annotation->del($kA);
			else $this->Annotation->List[$kA]->save();


				
		}
	}

	function treeTagger(){
		global $treeTagger;

		$this->error = "";

		$descriptorspec = array(
		   0 => array("pipe", "r"),  // STDIN 
		   1 => array("pipe", "w"),  // STDOUT
		   2 => array("pipe", "w") // STDERR 
	   	);
		
		if (!is_executable($treeTagger)) {
			$this->error = "treeTagger($treeTagger) kann nicht gestartet werden\n";
			return false;
		}

		$process = proc_open($treeTagger, $descriptorspec, $pipes, NULL, NULL);

		$error_proc ="";
		if (is_resource($process)) {
		    $fl = fwrite($pipes[0], $this->Data[$this->contentKey]);
		    fclose($pipes[0]);

			

		    $this->Data['word_count']=0;
		    $this->Data['lemma_count']=0;
		    $currentPos = 0;


		    while (!feof($pipes[1])){
			    $line = trim(fgets($pipes[1]));
			    $posList=explode("\t",$line);
			    if (count($posList)==3){
				    if (mb_strlen($posList[0])>1) $this->Data['word_count']++;
				    if (mb_strlen($posList[2])>1) $this->Data['lemma_count']++;
				    else $posList[2] ="";
				    $cat = new Category($posList[1]);
				    if (!in_array(1,$cat->Parent)) {
					    $cat->Parent[]=1;
					    $cat->save();
				    }
				    $Data = array(
					    'category_id'=>$cat->Data['id'], 
					    'length'=>mb_strlen($posList[0]),
					    'lemma'=>$posList[2],
					    'offset'=> mb_strpos($this->Data[$this->contentKey],$posList[0],$currentPos)
				    );
				    $currentPos = $Data['offset'] + $Data['length'];
				    $this->addAnnotation($Data);
			    }
		    }
 		    fclose($pipes[1]);
		    while (!feof($pipes[2])){
			    $this->error .= fgets($pipes[2]);
		    }
		    fclose($pipes[2]);


		    $this->saveMetaData();

		    // Es ist wichtig, alle Pipes zu schließen bevor 
		    // proc_close aufgerufen wird, um Deadlocks zu vermeiden
		    if (proc_close($process) == 0) return true;
		    return false;
		}
	}

	function del(){
		global $db, $tbPaper, $tbTablePrefix;

		if ($this->Data['id'] < 1) return;
		
		#Alle Annotationen loeschen nicht nur die lesbaren
		$annoAll= new AnnotationList($this->Data['id'],False,True);
		$annoAll->delAll($catList->ListReadonly);
		$annoAll=null;

		foreach ($this->MType->List as $k => $v){
			if($v['meta_type'] == 'list' ||$v['meta_type'] == 'multilist') {
				//loeschen
				$db->delete("{$tbPaper}2".$tbTablePrefix.$v['table'],"paper_id=%s",$this->Data['id']);
			}
		}
		$db->delete($tbPaper,"id=%s",array($this->Data['id']));
	}	



}

class PaperMetaDataType {
	var $Data;

	function PaperMetaDataType($id) {}
}

class PaperMetaDataTypeList {
	var $List=array();
	var $metaOptions=array("search"=>1,"import"=>2,"export"=>4);

	//Liste nach mit name als key
	function PaperMetaDataTypeList(){
		global $db, $tbPaperMetaType;

		$db->select($tbPaperMetaType,"*",False,False,"order by id");
		while (($rs = $db->nextQ())!==false){
			if (isset($rs['name'])) $this->List[$rs['name']] = $rs;
		}
	}

	function cleanList(){
		global $db, $tbPaper, $tbTablePrefix;

		foreach ($this->List as $kL => $vL){
			if($vL['meta_type'] == 'list' || $vL['meta_type'] == 'multilist'){ 
				$q ="SELECT a.id FROM $tbTablePrefix{$vL['table']} a LEFT OUTER JOIN {$tbPaper}2$tbTablePrefix{$vL['table']} b ON a.id = b.{$vL['table']}_id 
				WHERE b.paper_id IS NULL";
				$db->query($q);
				$emp = array();
				while (($rs = $db->nextQ())!==false){
					if (isset($rs['id'])) $emp[] = $rs['id'];
				}
				if (count($emp)>0)
					$db->delete($tbTablePrefix.$vL['table'],"id IN ('".implode("','",$emp)."')");
			}

		}
	}

	function getList($name){
		global $db, $tbPaper, $tbTablePrefix;

		if (array_key_exists($name,$this->List)){
			$vL = $this->List[$name];
			if($vL['meta_type'] == 'list' || $vL['meta_type'] == 'multilist'){ 
				$q ="SELECT a.id,a.{$vL['field_name']} as name FROM $tbTablePrefix{$vL['table']} a";
				$db->query($q);
				$ret = array();
				while (($rs = $db->nextQ())!==false){
					if (isset($rs['id'])) $ret[] = $rs;
				}
				return $ret;
			}

		}
		return false;
	}

	function isImport($name){
		return ( ($this->List[$name]['options'] & $this->metaOptions['import']) > 0);
	}

	function isExport($name){
		return ( ($this->List[$name]['options'] & $this->metaOptions['export']) > 0);
	}

	function isSearch($name){
		return ( ($this->List[$name]['options'] & $this->metaOptions['search']) > 0);
	}

	function getMetaDataTypeByName($name){}


}


