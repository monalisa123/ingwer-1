<?php

defined( '_VALID_INGWER' ) or die( 'Restricted access' );

require_once('init.php');

class Category {

	var $Data;
	var $Child;
	var $Parent;
	var $ParentPath=null;

	function Category ($name,$id=false){
		global $db, $tbCategory,$tbCategoryParent;

		$this->Child=array();
		$this->Parent=array();

		if ($id) $this->Data=$db->selectOne($tbCategory,'*',"id='%s'",$id);
		else $this->Data=$db->selectOne($tbCategory,'*',"name='%s'",$name);
		
		if (!is_array($this->Data) || !array_key_exists('id',$this->Data)) $this->Data = array('id'=>0,'name'=>$name);
		else if ($this->Data['id']!=0){
			$db->select($tbCategoryParent,'category_id_child',"category_id_parent=%s",$this->Data['id']);
			while (($rC=$db->nextQ('category_id_child'))!==false)$this->Child[]=$rC;
			$db->select($tbCategoryParent,'category_id_parent','category_id_child=%s',$this->Data['id']);
			while (($rC=$db->nextQ('category_id_parent'))!==false) $this->Parent[]=$rC;
			
		}
	}

	function save(){
		global $db, $tbCategory,$tbCategoryParent;
		$update = $this->Data;
		unset($update['id']);

		if ($this->Data['id'] == 0){
			$db->insert($tbCategory,$update);
			$this->Data['id'] = $db->lastId();
		}
		else
			$db->update($tbCategory,$update,"id=%s",$this->Data['id']);

		$db->delete($tbCategoryParent,"category_id_child=%s",$this->Data['id']);
		foreach(array_unique($this->Parent) as $k){
			$db->insert($tbCategoryParent,array('category_id_child'=>$this->Data['id'],'category_id_parent'=>$k));
		}
		$db->delete($tbCategoryParent,"category_id_parent=%s",$this->Data['id']);
		foreach(array_unique($this->Child) as $k){
			$db->insert($tbCategoryParent,array('category_id_parent'=>$this->Data['id'],'category_id_child'=>$k));
		}
	}

	function checkParent(){
		$double = array();
		foreach ($this->Parent as $v) {
			$newCat = new Category('',$v);
			$newCat->checkFamily($this->Data['id'],$v,$double,true);
		}
		$this->Parent = array_diff($this->Parent,$double);
	}

	function checkFamily($id,$startParent,&$double,$parent=true){
		$checkArray = ($parent) ? $this->Parent : $this->Child;
		if (!is_array($checkArray)) return;
		foreach ($checkArray as $v) {
			if ($id == $v) $double[]=$startParent;
			else {
				$newCat = new Category('',$v);
				$newCat->checkFamily($id,$startParent,$double,$parent);
			}
		}
	}
	
	function parentPath($delemiter,$catList,$reload=false){
		if (!$reload && $this->ParentPath !=null) {return $this->ParentPath;}
		$pathList=Array(Array());
		$this->generateParentPath($pathList,$catList);
		$this->ParentPath="";
		$pathStrings= Array();
		foreach($pathList as $p){
			$pathStrings[] = implode($delemiter,$p);
			}
		$this->ParentPath = implode("|",$pathStrings);
		return $this->ParentPath;
		}

	
	function generateParentPath(&$pathList,$catList){
		$current= array_pop($pathList);
		foreach($this->Parent as $p){
			$new=$current;
			if (array_key_exists($p,$catList->List)) {$new[]=$catList->List[$p]->Data['name'];}
			array_push($pathList,$new);
			if (array_key_exists($p,$catList->List)) {$catList->List[$p]->generateParentPath($pathList,$catList);}
		}
	}

	function del(){
		global $db, $tbCategory,$tbCategoryParent;
		
		$db->delete($tbCategoryParent,"category_id_child=%s",$this->Data['id']);
		$db->delete($tbCategoryParent,"category_id_parent=%s",$this->Data['id']);

		$db->delete($tbCategory,"id=%s",$this->Data['id']);
	}
	function xmlCategory($catList,$handle){
		fwrite($handle, '<category id="' .$this->Data['id'].'">'. "\n");
		fwrite($handle, "\t<name>{$this->Data['name']}</name>\n");
		fwrite($handle, "\t<parents>\n");
		foreach ($this->Parent as $k=>$p){
			fwrite($handle, "\t\t<parent>$p</parent>\n");
		}
		fwrite($handle, "\t</parents>\n");
		fwrite($handle, "\t<comment>{$this->Data['comment']}</comment>\n");
		fwrite($handle, "\t<color>{$this->Data['color']}</color>\n");
		fwrite($handle, "</category>\n");
	}

}


class CategoryList {
	var $List=array();
	var $ListReadonly=array();
	var $htmlTreex=array();
	var $parentHistory=array();

	//Liste nach mit id als key
	function CategoryList(){
		global $db, $tbCategory;


		$db->select($tbCategory,"*",False,False,"order by name,id asc");
		while ($rs = $db->nextQ()){
			if (isset($rs['id'])) {
				$this->List[$rs['id']]="";
				if ($rs['name']=='POS') {
					$this->ListReadonly[]=$rs['id'];
					$posId=$rs['id'];
				}
			}
		}
		foreach($this->List as $k => $v) {
			$this->List[$k] = new Category("",$k);
			if (in_array($posId,$this->List[$k]->Parent))
				$this->ListReadonly[]=$this->List[$k]->Data['id'];
		}
	}

	function del($id){
		$this->List[$id]->del();
		unset($this->List[$id]);
	
	}

	function cleanAnnotations(){
		global $db, $tbAnnotationPosition,$tbAnnotation;
		
		$db->delete($tbAnnotationPosition,"annotation_id in (SELECT id FROM `annotation` WHERE category_id not IN (select id from category) and category_id <> 0)");
		$db->delete($tbAnnotation,"category_id not IN (select id from category) and category_id <> 0");
	}

	function add($name){
		$C = new Category($name);
		$C->save();
		$this->List[$C->Data['id']]=$C;
		return $C->Data['id'];
	}
	
	function Tree($parent){
		$child=array();
		foreach ($this->List as $k=>$v){
			if ($parent!= $v->Data['id'] && !in_array($v->Data['id'],$this->parentHistory) && isset($v->Parent) && is_array($v->Parent) && in_array($parent,$v->Parent)){
				array_push($this->parentHistory,$parent);
				$child[$v->Data['id']] = $this->Tree($k);
				array_pop($this->parentHistory);

			}
		}
		if ($parent==0 && array_key_exists(1,$child)) {
			$v =$child[1];
			unset($child[1]);
			$child[1]=$v;
		}
		return $child;
	}

	function childList($parent,&$child){
		foreach ($this->List as $k=>$v){
			if ($parent!= $v->Data['id'] && !in_array($v->Data['id'],$this->parentHistory) && isset($v->Parent) && is_array($v->Parent) && in_array($parent,$v->Parent)){
				array_push($this->parentHistory,$parent);
				$child[] = $v->Data['id'];
				$this->childList($k,$child);
				array_pop($this->parentHistory);

			}
		}

	}
	
	function htmlTree($parent){
		$tree = $this->Tree($parent);
		
		$hTree = array();
		$this->walkTree($tree,$hTree);
		return $hTree;
	}

	function walkTree($tree,&$hTree){
		foreach ($tree as $k=>$v){
			$hTree[]= array('id'=>$k,'name'=>$this->List[$k]->Data['name'],'color'=>$this->List[$k]->Data['color'],'childOpen' => count($v), 'childClose' => 0 );
			if (is_array($v) && count($v)>0){
				$this->walkTree($v,$hTree);
				$hTree[count($hTree)-1]['childClose']++;
			}
		}
	}




}


