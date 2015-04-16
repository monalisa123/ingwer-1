<?php

defined( '_VALID_INGWER' ) or die( 'Restricted access' );

require_once('init.php');

class Annotation {

	var $Data;
	var $Position=array();

	function Annotation ($id,$autofill=false){
		global $db, $tbAnnotation,$tbAnnotationPosition;
		
		if ($autofill) return;
		$this->Data=$db->selectOne($tbAnnotation,'*',"id='%s'",$id);
		if (!is_array($this->Data) || !array_key_exists('id',$this->Data)) $this->Data = array('id'=>0);
		else {
			
			$db->select($tbAnnotationPosition,'*',"annotation_id=%s",$this->Data['id'],"order by offset");
			while (($rA=$db->nextQ())!==false)$this->Position[]=$rA;
		}
	}
	
	function userName(){
		if (!array_key_exists('user_id',$this->Data) || $this->Data['user_id']==0) return "";
		$u=new User($this->Data['user_id']);
		if (array_key_exists('name',$u->Data)) return $u->Data['name'];
		return "";
	}	
	function save(){
		global $db, $tbAnnotation,$tbAnnotationPosition;
		$update = $this->Data;
		unset($update['id']);
		$update['last_edit'] = date('Y-m-d');

		if ($this->Data['id'] == 0){
			$db->insert($tbAnnotation,$update);
			$this->Data['id']=$db->lastId();
		}else
			$db->update($tbAnnotation,$update,"id=%s",$this->Data['id']);
		$db->delete($tbAnnotationPosition,"annotation_id=%s",$this->Data['id']);
		foreach(array_unique($this->Position) as $k=>$v){
			$v['annotation_id']=$this->Data['id'];
			$db->insert($tbAnnotationPosition,$v);
		}
	}


	function del(){
		global $db, $tbAnnotation,$tbAnnotationPosition;
		
		$db->delete($tbAnnotation,"id=%s",$this->Data['id']);
		$db->delete($tbAnnotationPosition,"annotation_id=%s",$this->Data['id']);
	}

}


class AnnotationList {
	var $List=array();

	//Liste 
	function AnnotationList($paper_id,$category_id=False,$POS=FALSE){
		global $db, $tbAnnotationPosition,$tbAnnotation;

		$w="a.id = b.annotation_id AND a.paper_id=%s";
		$sentenceId=0;
		if (!$POS) {
			$catList = new CategoryList();
			foreach ($catList->List as $v){
				if ($v->Data['name'] == '$.'){
					$sentenceId= $v->Data['id'];
					break;
				}
			}
			$w .= " AND (a.category_id not in ('" . implode("','",array_values($catList->ListReadonly)). "') or a.category_id=$sentenceId)";
		}
		if($category_id) {
			if (is_array($category_id)){
				$w .= " AND a.category_id IN (%s)";
				$db->select(array("$tbAnnotation a","$tbAnnotationPosition b"),'*',$w,array($paper_id,implode(",",$category_id)),"order by a.id, b.offset");
			}else {
				$w .= " AND a.category_id=%s";
				$db->select(array("$tbAnnotation a","$tbAnnotationPosition b"),'*',$w,array($paper_id,$category_id),"order by a.id, b.offset");
			}
		} else	$db->select(array("$tbAnnotation a","$tbAnnotationPosition b"),'*',$w,$paper_id,"order by a.id, b.offset");

		if (($rs = $db->nextQ())!==false){
			$anno = new Annotation($rs['id'],true);
			$anno->Data=array('id'=>$rs['id'],'paper_id'=>$rs['paper_id'],'category_id'=>$rs['category_id'],'user_id'=>$rs['user_id'],'comment'=>$rs['comment'],'last_edit'=>$rs['last_edit']);
			$anno->Position[]=array('offset'=>$rs['offset'],'length'=>$rs['length'],'lemma'=>((array_key_exists('lemma',$rs)?$rs['lemma']:'')));
			while (($rs = $db->nextQ())!==false){
				if ($rs['id'] != $anno->Data['id']){
					$this->List[$anno->Data['id']] = $anno;
					$anno = new Annotation($rs['id'],true);
					$anno->Data=array('id'=>$rs['id'],'paper_id'=>$rs['paper_id'],'category_id'=>$rs['category_id'],'user_id'=>$rs['user_id'],'comment'=>$rs['comment'],'last_edit'=>$rs['last_edit']);
				}
				$anno->Position[]=array('offset'=>$rs['offset'],'length'=>$rs['length'],'lemma'=>((array_key_exists('lemma',$rs)&& isset($rs['lemma']))?$rs['lemma']:''));
			}
			$this->List[$anno->Data['id']] = $anno;
		}
	}

	function del($id){
		$this->List[$id]->del();
		unset($this->List[$id]);
	
	}

	function delAll($catList=false){
		global $db, $tbAnnotation,$tbAnnotationPosition;

		$id=array();

		if (is_array($catList)){
			foreach ($this->List as $k=>$v)
				if (in_array($v->Data['category_id'],$catList)){
					$id[]= $k;
					unset($this->List[$k]);
				}
		}
		else{
			foreach ($this->List as $k=>$v) {
				$id[]= $k;
				unset($this->List[$k]);
			}
		}

		if (count($id)>0) {
			$db->delete($tbAnnotation,"id IN ('".implode("','",$id) . "')");
			$db->delete($tbAnnotationPosition,"annotation_id IN ('".implode("','",$id) . "')");
		}

	}

	
	function add($paper_id,$Data){
		$a = new Annotation(0);
		$a->Data['paper_id'] = $paper_id;
		if (array_key_exists('category_id',$Data))$a->Data['category_id'] = $Data['category_id'];
		if (array_key_exists('user_id',$Data))$a->Data['user_id'] = $Data['user_id'];
		if (array_key_exists('Position',$Data) && is_array($Data['Position'])){
			foreach($Data['Position'] as $k => $v) $a->Position[]=$v;
		}
		$a->save();
		$this->List[$a->Data['id']]=$a;
	
	}


}


