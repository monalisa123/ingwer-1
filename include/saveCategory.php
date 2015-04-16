<?php
$cat = new Category('',(int) $_REQUEST['catId']);

$catList = new CategoryList();
$readonly = (in_array($cat->Data['id'],$catList->ListReadonly)) ? true :false;
if (!$readonly) $cat->Data['name']=$_REQUEST['name'];
$cat->Data['comment']=$_REQUEST['comment'];
$cat->Data['color']=$_REQUEST['color'];
if ((int) $_REQUEST['catId']==-1) $cat->save();
elseif (!$readonly) $cat->Parent=array();
if (!$readonly) {
	foreach ($_REQUEST['parent'] as $v)	$cat->Parent[]=(int)$v;
	$cat->checkParent();
}
$cat->save();
?>
