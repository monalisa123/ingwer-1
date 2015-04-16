<?php
/**
* Tempalte Annotationskategorienbaum
*
* enthaelt den Kategoriebaum des Annotationsfensters (rechte Seite)
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
<div id="categoryList">
<span><input type="checkbox" name="categoryAll" onClick="Category.allDisplay(this)"> Alle Kategorien auswählen</span>
<br />
<span><input type="checkbox" name="categoryAllUse" onClick="Category.allUseDisplay(this)"> Alle verwendeten Kategorien auswählen</span>
<script type="text/javascript">
Category.catList = new Object();
<?foreach ($content['catList']->List as $category):?>
	Category.catList[<?=$category->Data['id']?>]= new Category.Category('<?=$category->Data['id']?>',<?=json_encode($category->Data['name'])?>,<?=json_encode($category->Data['color'])?>,<?=json_encode($category->Data['comment'])?>,new Array('<?=implode("','",$category->Parent)?>'),<?=(in_array($category->Data['id'],$content['catList']->ListReadonly))? 'true':'false'?>);
<?endforeach?>
	Category.catList[0]=new Category.Category('0','Wurzel','','',new Array(''),false);
</script>
<ul class="Category">
	<?foreach ($content['catList']->htmlTree(0) as $k=>$category):?>
	<li name="category_<?=$category['id']?>"><img src="tpl/<?= ($category['childOpen'] > 0) ? "minus.png":"blank.png";?>" class="bullet" onClick="OpenClose(this)" />
	<span class="color" style="background-color:#<?=$category['color']?>;">&nbsp;</span><span name="name" onClick="Category.aktiv(<?=$category['id']?>)"><?=$category['name']?></span><span class="right"><img onClick="Category.editWindow(<?=$category['id']?>)" src="tpl/open.png" /> <input type="checkbox" name="categoryId<?=$category['id']?>"></span>
	<?if ($category['childOpen'] > 0):?>
		<ul class="Category">
	<?else:?>
	</li>
	<?endif?>
	<?for ($i=0; $i<$category['childClose'];$i++):?>
		</ul></li>
	<?endfor?>
<?endforeach?>
</ul>
<br />
<br />
<input class="button" type="button" value='+' onClick="Category.editWindow(-1)"> Kategorie hinzufügen
</div>
