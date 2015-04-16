<?php
/**
* Haupttempalte Annotation 
*
* enthaelt den Kontentrahmen für das  Annotationfenster mit Blaetterfunktion.
* Ruft das Annotationtemplate fuer das Dokument und den Kategorienbaum auf
*
* LICENSE: 
*
* @category   Template
* @package    Ingwer
* @author     Jochen Koehler <jk@it-devel.de>
* @copyright  2011 semtracks gmbh
* @version    0.8
*/
defined( '_VALID_INGWER' ) or die( 'Restricted access' );
?>
<div id="overlap">
&nbsp;
</div>
<div id="Content">
<h1> Annotation </h1>
<?if (array_key_exists('searchResult',$_SESSION)){
	$result=$_SESSION['searchResult'];
	$countResult = count($result['list']);
} else $countResult=0;
if (
	(isset($result) && $countResult >0)
	|| (!isset($result) && $content['paperCount'] >0)):?>
<script type="text/javascript">
var Resize_start=0;
var ListContainer=0;
$(document).ready(function() {
	Annotation.resizeWindow();
	$('#paperText').bind("mouseup",function(evt,data) {
	Annotation.selectText(evt,data);
	});
	Annotation.initMouseEvent();
	Category.initMouseEvent();
	$('#categoryList ul li[name=category_1] > img').trigger("click");
	Category.loadState();
	Category.changePOSDisplay();
//	$('form[name=blaettern]').ajaxForm({target:"div#paper",url:"indexAjax.php"});
});
$(window).bind("resize",function(evt) {
	Annotation.resizeWindow();
	showWait();
	if (!isMessageBox)
		window.setTimeout("Category.displayAll()", 300);
	evt.stopPropagation();
	evt.stopImmediatePropagation();
});
$(window).bind("unload",function(evt) {
	Category.saveState();
});
</script>
	<?if (isset($result)):?>
		<?if ($countResult>1):?>
		<?$currentResult=array_search($content['paperData']['id'],$result['list']);
		if (!isset($currentResult) || $currentResult===False) $currentResult=0;?>
		<div class="blaettern">
				<form onSubmit="return Category.getPaper(<?=$result['list'][0]?>);" action="" method="GET"><input type="hidden" value="<?=$result['list'][0]?>" name="paperId"/><input  type="submit" value="<<"/></form>
				<form onSubmit="return Category.getPaper(<?=$result['list'][(($currentResult>0)?$currentResult-1:0)]?>);" action="" method="GET"><input type="hidden" value="<?=$result['list'][(($currentResult>0)?$currentResult-1:0)]?>" name="paperId"/><input  type="submit" value="<"/> </form>
				<?=$currentResult+1?> / <?=count($result['list'])?> 
				<form onSubmit="return Category.getPaper(<?=$result['list'][(($currentResult<$countResult-1)?$currentResult+1:$countResult-1)]?>);" action="" method="GET"><input type="hidden" value="<?=$result['list'][(($currentResult<$countResult-1)?$currentResult+1:$countResult-1)]?>" name="paperId"/><input  type="submit" value=">"/></form>
				<form onSubmit="return Category.getPaper(<?=$result['list'][$countResult-1]?>);" action="" method="GET"><input type="hidden" value="<?=$result['list'][$countResult-1]?>" name="paperId"/><input type="submit" value=">>"/></form>
				<form style="margin-left: 50px;"> mit Part of Speech (POS)<input onChange="Category.changePOSDisplayReload()" id="POS" type="checkbox" name="POS" value="1" <?= (array_key_exists('POS',$content) && $content['POS'])?'checked="checked"':""?>></form>
		</div>
		<?else:?>
		<?if (!isset($currentResult) || $currentResult===False) $currentResult=0;?>
		<div class="blaettern">
				<?=$currentResult+1?> / <?=count($result['list'])?> 
				<form style="margin-left: 50px;"> mit Part of Speech (POS)<input onChange="Category.changePOSDisplayReload()" id="POS" type="checkbox" name="POS" value="1" <?= (array_key_exists('POS',$content) && $content['POS'])?'checked="checked"':""?>></form>
		</div>
		<?endif;?>
	<?else:?>
		<?if ($content['paperCount']>0):?>
		<div class="blaettern">
				<form onSubmit="return Category.getPaperNr(0)" name="blaettern" action="" method="GET"><input type="hidden" value="0" name="paperNr"/><input  type="submit" value="<<"/></form>
				<form onSubmit="return Category.getPaperNr(<?=$content['paperNr']-1?>)" name="blaettern" action="" method="GET"><input type="hidden" value="<?=$content['paperNr']-1?>" name="paperNr"/><input  type="submit" value="<"/> </form>
				<?=$content['paperNr']+1?> / <?=$content['paperCount']?> 
				<form onSubmit="return Category.getPaperNr(<?=$content['paperNr']+1?>)" name="blaettern" action="" method="GET"><input type="hidden" value="<?=$content['paperNr']+1?>" name="paperNr"/><input  type="submit" value=">"/></form>
				<form onSubmit="return Category.getPaperNr(<?=$content['paperCount']-1?>)" name="blaettern" action="" method="GET"><input type="hidden" value="<?=$content['paperCount']-1?>" name="paperNr"/><input type="submit" value=">>"/></form>
				<form style="margin-left: 50px;"> mit Part of Speech (POS)<input onChange="Category.changePOSDisplayReload()" id="POS" type="checkbox" name="POS" value="1" <?= (array_key_exists('POS',$content) && $content['POS'])?'checked="checked"':""?>></form>
		</div>
		<?else:?>
		<div class="blaettern">
				<?=$content['paperNr']+1?> / <?=$content['paperCount']?> 
				<form style="margin-left: 50px;"> mit Part of Speech (POS)<input onChange="Category.changePOSDisplayReload()" id="POS" type="checkbox" name="POS" value="1" <?= (array_key_exists('POS',$content) && $content['POS'])?'checked="checked"':""?>></form>
		</div>
		<?endif;?>
	<?endif;?>
	<hr />
	<div id="colorPicker"></div>
	<table id="annotationMain">
	<tr><td>
	<? include('tpl/AnnotationPaper.tpl.php'); ?>
	</td>
	<td id="categoryListContainer">
	<? include('tpl/AnnotationCategory.tpl.php'); ?>
	</td>
	</tr></table>


<?else:?>
	<?if (isset($result)):?>
		Keine Dokumente in Aktueller Suche vorhanden
	<?else:?>
		Keine Dokumente vorhanden
	<?endif;?>
<?endif;?>
</div>
