<?php
/**
* Tempalte Annotationsdukument
*
* enthaelt das Dokument des Annotationsfensters mit den Annotation (linke Seite)
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
$content['paper']->maxDepth();
?>
<div id="paper">
<div id="paperHidden">
	<input type="hidden" name="selectedText" value="">
	<input type="hidden" name="selectedTextStartOffset" value="">
	<input type="hidden" name="selectedTextStartId" value="">
	<input type="hidden" name="selectedTextEndOffset" value="">
	<input type="hidden" name="selectedTextEndId" value="">
	<input type="hidden" name="selectedTextMouseUp" value="">
	<input type="hidden" name="selectedTextMouseUpPrev" value="">
	<input type="hidden" name="selectedTextMouseUpSib" value="">
	<input type="hidden" name="selectedTextMouseDown" value="">
	<input type="hidden" name="selectedTextMouseDblClick" value="">
	<input type="hidden" name="selectedTextMouseDownSib" value="">
	<input type="hidden" name="selectPaperId" value="<?=$content['paper']->Data['id']?>">
</div>
<script type="text/javascript">
Annotation.annoList = new Object();
<? $annoListCat = array(); ?>
<?foreach ($content['paper']->Annotation->List as $anno):?>
	Annotation.annoList[<?=$anno->Data['id']?>] =  new Annotation.Annotation('<?=$anno->Data['id']?>','<?=$anno->Data['category_id']?>',<?=json_encode($anno->userName())?>,<?=(array_key_exists('comment',$anno->Data))? json_encode($anno->Data['comment']):'""'?>,'<?=(array_key_exists('last_edit',$anno->Data))?$anno->Data['last_edit']:""?>');
<? if (!array_key_exists($anno->Data['category_id'],$annoListCat)) $annoListCat[$anno->Data['category_id']] = array(); 
 $annoListCat[$anno->Data['category_id']][] = $anno->Data['id']; 
?>
<?endforeach?>

Annotation.annoListCat = new Object();
<?foreach ($annoListCat as $k => $List):?>
	Annotation.annoListCat[<?=$k?>] = new Array();
	<?foreach ($List as $v):?>
		Annotation.annoListCat[<?=$k?>].push(<?=$v?>);
	<?endforeach?>
<?endforeach?>
</script>
<?if ($content['paper']):?>
<table class="metadata">
<?$i=0;?>
<?foreach ($content['paper']->Data as $pK => $pV):?>
	<? if ($pK == $content['paper']->contentKey) continue; ?>
	<? if (($i % 2) == 0):?><tr><?endif?>
	<td><span><?=$pK?>: </span><?= (is_array($pV))? implode('; ',$pV):$pV ?></td>
	<? if (($i % 2) == 1):?></tr><?endif?>
	<?$i++;?>
<?endforeach?>
</table>
<input type="checkbox" onChange="Category.changeDisplayClick()" id="changeDisplay" /> nur annotierten Text anzeigen
<div id="paperContent">
<table><tr>
<td id="paperText" <?=($content['paper']->MaxDepth>0)? 'style="line-height: ' . (1+0.2*$content['paper']->MaxDepth) . 'em;"':''?>><?$content['paper']->htmlAnnotatedContent(true)?></td>
<td>
<?foreach ($content['paper']->Annotation->List as $anno):?>
	<?endforeach?>
</td>
<td style="width:30px;">
</td>
<td id="paperBanner">
<?foreach ($content['paper']->Annotation->List as $anno):?>
	<div style="display:none;" class="annotationBanner" name="categoryId<?=$anno->Data['category_id']?>" id="annotationId<?=$anno->Data['id']?>">
		<div class="annotationBannerBalken" style="background-color: red;" name="categoryId<?=$anno->Data['category_id']?>" rel="annotationId<?=$anno->Data['id']?>">
			<div rel="annotationId<?=$anno->Data['id']?>" class="annotationBannerStrich" name="categoryId<?=$anno->Data['category_id']?>">
			</div>
				<div class="annotationBannerBanner"  style="width: <?=75+((strlen($content['catList']->List[$anno->Data['category_id']]->Data['name'])> 16)? (floor(strlen($content['catList']->List[$anno->Data['category_id']]->Data['name']) - 16)*4.5) :0)?>px;"; id="annotationBannerId<?=$anno->Data['id']?>"><?=mb_ereg_replace("\s","&nbsp;",$content['catList']->List[$anno->Data['category_id']]->Data['name'])?></div>
		</div>
	</div>
<?endforeach?>
</td>
</tr></table>
</div>
<?else:?>
Bitte bei Lesen Bearbeiten ein Paper wählen.
<?endif?>
</div>
