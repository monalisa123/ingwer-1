<div id="Content">
<h1> Suchen </h1>
<hr />
<script type="text/javascript">
function changeDisplay(rel){
	if ($('select#rel_'+rel).val()=='0') 
		$('input#befor_'+rel).css('display','inline');
	else	
	$('input#befor_'+rel).css('display','none'); 
	if ($('select#rel_'+rel).val()=='2') 
		$('input#search_'+rel).attr('size','100');
	else	
		$('input#search_'+rel).attr('size','4');
}
function changeInc(T){
	var e = $(T.parentNode);
	if (T.src.search(/minus.png$/)>0) {
		T.src = T.src.replace(/minus.png$/,"plus.png");
		e.children("input:hidden").val('1');
	}
	else {
		e.children("input:hidden").val('0');
		T.src = T.src.replace(/plus.png$/,"minus.png");
	}
}
var categoryCount=<?= (count($content['search']->Category) != 0 )? count($content['search']->Category):1 ?>;

function newCategory(){
	var tr = $('tr#category_'+categoryCount);
	categoryCount++;
	var newCat = '<tr id="category_'+categoryCount+'"><td><img title="include oder exclude" src="tpl/plus.png" onClick="changeInc(this)" /><input type="hidden" name="search[category_'+categoryCount+'][inc]" value="1"/></td>'
			+ '<td class="metaname">Kategorie</td><td class="metavalue">'
			+ '<select name="search[category_'+categoryCount+'][id]">'
			+	'	<option value="0">Bitte wählen</option>'
						<?$tief=0?>
						<?foreach ($content['categoryList']->htmlTree(0) as $kL=>$vL):?>
			+	'					<option value="<?=$vL['id']?>"><?for($i=0;$i<$tief;$i++):?>&nbsp;&nbsp;<?endfor?><?=$vL['name']?></option>'
							<? if ($vL['childOpen']>0) $tief++;
							$tief -= $vL['childClose']; ?>
						<?endforeach?>
			+ '</select>'

			+'</td></tr>';
	tr.after(newCat);
}

function loadSearch(){
		$('span.messageBox').html(
			 ' Suche laden <br /><hr />'
			 +'<table>'
<? if (count($content['searchNames']->List) >0):?>
			+ '<tr><td>laden:</td><td align="left"><select name="oldName">'
<?foreach($content['searchNames']->List as $kV):?>
			+ '<option value="<?=$kV['name']?>"><?=$kV['name']?></option>'
<?endforeach?>
			+'</select></td></tr>'
<?else:?>
			 + '<tr><td></td><td align="left">noch keine Suche gespeichert</td></tr>'
<?endif?>
			+ '</table>'
			+ '<input type="submit" value="Laden" onClick="loadSubmit()"/><input type="button" value="Abbrechen" onClick="messageBoxHide()"/>'
		);
		messageBoxShow();
}
function loadSubmit(){
	$("input#oldName").val($("select[name=oldName]").val());
	$("input[name=searchAction]").val('loadSearch');
	$("form#search").submit();
}
function delSearch(){
		$('span.messageBox').html(
			 ' Suche löschen <br /><hr />'
			 +'<table>'
<? if (count($content['searchNames']->List) >0):?>
			+ '<tr><td>löschen:</td><td align="left"><select name="oldName">'
<?foreach($content['searchNames']->List as $kV):?>
			+ '<option value="<?=$kV['name']?>"><?=$kV['name']?></option>'
<?endforeach?>
			+'</select></td></tr>'
<?else:?>
			 + '<tr><td></td><td align="left">noch keine Suche gespeichert</td></tr>'
<?endif?>
			+ '</table>'
			+ '<input type="submit" value="Löschen" onClick="delSubmit()"/><input type="button" value="Abbrechen" onClick="messageBoxHide()"/>'
		);
		messageBoxShow();
}
function delSubmit(){
	$("input#oldName").val($("select[name=oldName]").val());
	$("input[name=searchAction]").val('deleteSearch');
	$("form#search").submit();
}
function saveList(){
		$('span.messageBox').html(
			 ' Auswahl als Suche speichern <br /><hr />'
			 +'<table>'
			 + '<tr><td>neuer Name:</td><td align="left"><input type="text" name="newName"/></td></tr>'
<? if (count($content['searchNames']->List) >0):?>
			+ '<tr><td>speichern als:</td><td align="left"><select name="oldName">'
<?foreach($content['searchNames']->List as $kV):?>
			+ '<option value="<?=$kV['name']?>"><?=$kV['name']?></option>'
<?endforeach?>
			+'</select></td></tr>'
<?endif?>
			+ '</table>'
			+ '<input type="submit" value="Speichern" onClick="saveListSubmit()"/><input type="button" value="Abbrechen" onClick="messageBoxHide()"/>'
		);
		messageBoxShow();
}
function saveListSubmit(){
	var add = Array();
	add.push({name: "name[new]",value: $("input[name=newName]").val()});
	add.push({name: "name[old]",value: $("select[name=oldName]").val()});
	add.push({name: "searchAction",value:"saveList"});
	$.each($('input[name^=mark]'), function(index, val) {
		if (val.checked){
			add.push({name:val.name,value:1});
		}
		else {
			add.push({name:val.name,value:0});
		}
	});
	window.location.href = window.location.href.replace(/\?.*/,"")+ "?"+$.param(add);
}
function saveSearch(){
		$('span.messageBox').html(
			 ' Suche speichern <br /><hr />'
			 +'<table>'
			 + '<tr><td>neuer Name:</td><td align="left"><input type="text" name="newName"/></td></tr>'
<? if (count($content['searchNames']->List) >0):?>
			+ '<tr><td>speichern als:</td><td align="left"><select name="oldName">'
<?foreach($content['searchNames']->List as $kV):?>
			+ '<option value="<?=$kV['name']?>"><?=$kV['name']?></option>'
<?endforeach?>
			+'</select></td></tr>'
<?endif?>
			+ '</table>'
			+ '<input type="submit" value="Speichern" onClick="saveSubmit()"/><input type="button" value="Abbrechen" onClick="messageBoxHide()"/>'
		);
		messageBoxShow();
}
function saveSubmit(){
	$("input#newName").val($("input[name=newName]").val());
	$("input#oldName").val($("select[name=oldName]").val());
	$("input[name=searchAction]").val('saveSearch');
	$("form#search").submit();
}
function anno(id){
	window.location.href = 'index.php?nav=Annotation&paperId='+id;
}

function changeMarkAll(){
	$('input[name^=mark]').attr('checked',$('input[name=allMark]').attr('checked'));
	$('span#changeMark').html($('input:checked[name^=mark]').length+exMark);
}
function changeMark(){
	$('input[name=allMark]').attr('checked',false);
	$('span#changeMark').html($('input:checked[name^=mark]').length+exMark);
}

function addMark(obj){
	var add = Array();
	$.each($('input[name^=mark]'), function(index, val) {
		if (val.checked){
			add.push({name:val.name,value:1});
		}
		else {
			add.push({name:val.name,value:0});
		}
	});
	obj.href = obj.href+ "&"+$.param(add);
	return true;
}

function beforeSubmit(){
	if ($('select[name=searchAction]').val()=="delList")
		return confirm("Markierte wirklich löschen?");
	if ($('select[name=searchAction]').val()=="saveList")
		saveList();
	return false;
}

function resetForm(){
	$('select[name^=search]').val(0);
	$('input[name*=id2]').val('').css('display','none');
	$('input[id*=search]').attr('size','4');
	$('select[name*=relation]').val(-1);
	$('input[name^=search]').val('');
	$('img[src*=minus\\.png]').attr('src',"tpl/plus.png");
	$("input[name*=inc]").val('1');
	$("input[name=searchAction]").val('search');
}
</script>
<form action="" method="POST" id="search">
<table class="suchen">
<?foreach($content['paperMetaType']->List as $kP=>$kV):?>
<?if (!$content['paperMetaType']->isSearch($kP)) continue; ?>
<?
unset($meta);
foreach($content['search']->MetaData as $vM){
	if ($vM['paper_meta_data_type_id']==$kV['id']){
		$meta= $vM;
		break;
	}
}?>

<? if ('content' != $kV['meta_type']):?>

	<tr><td><img title="include oder exclude" src="tpl/<?=(isset($meta) && $meta['include']==0)? 'minus':'plus'?>.png" onClick="changeInc(this)" /><input type="hidden" name="search[<?=$kV['id']?>][inc]" value="<?=(isset($meta) && $meta['include']==0)? '0':'1'?>"/></td><td class="metaname"><?=$kV['name']?></td><td class="metavalue">
		<? switch($kV['meta_type']):
			case 'list':?><select name="search[<?=$kV['id']?>][id][]" multiple size="3">
					<option value="0">Bitte wählen</option>
					<?foreach ($kV['list'] as $kL=>$vL):?>
						<option value="<?=$kL?>"<?=(isset($meta) && !(array_search($kL,$meta['search'])===false))? ' selected="selected"':''?>><?=$vL?></option>
					<?endforeach?>
					</select>
					<?break;?>
		<?	case 'multilist':?><select name="search[<?=$kV['id']?>][id][]" multiple size="3">
					<option value="Bitte wählen">Bitte wählen</option>
					<?foreach ($kV['list'] as $kL=>$vL):?>
						<option value="<?=$kL?>"<?=(isset($meta) && !(array_search($kL,$meta['search'])===false))? ' selected="selected"':''?>><?=$vL?></option>
					<?endforeach?>
					</select>
					<?break;?>
		<?	case 'date':?> <input size="10" id="befor_<?=$kV['id']?>" style="<?=(isset($meta) && $meta['relation']==0)? '':'display:none;'?>" name="search[<?=$kV['id']?>][id2]" type="text" value="<?=(isset($meta) && array_key_exists('search2',$meta))?$meta['search2'] :''?>"/>
				<select id="rel_<?=$kV['id']?>" name="search[<?=$kV['id']?>][relation]" onChange="changeDisplay('<?=$kV['id']?>');">
					<option value="-1"<?=(isset($meta) && $meta['relation']==-1)? ' selected="selected"':''?>>nach</option>
					<option value="1"<?=(isset($meta) && $meta['relation']==1)? ' selected="selected"':''?>>vor</option>
					<option value="0"<?=(isset($meta) && $meta['relation']==0)? ' selected="selected"':''?>>zwischen</option>
				</select>
					<input size="10" name="search[<?=$kV['id']?>][id]" type="text"  value="<?=(isset($meta) && array_key_exists('search',$meta))?$meta['search'] :''?>"/>TT.MM.JJJJ
				<?break;?>
		<?	case 'string':?><input size="100" type="text" name="search[<?=$kV['id']?>][id]" value="<?=(isset($meta) && array_key_exists('search',$meta))?$meta['search'] :''?>"/><?break;?>
		<?	case 'int':?><input id="befor_<?=$kV['id']?>" style="<?=(isset($meta) && $meta['relation']==0)? '':'display:none;'?>"  size="4" name="search[<?=$kV['id']?>][id2]" type="text"  value="<?=(isset($meta) && array_key_exists('search2',$meta))?$meta['search2'] :''?>"/>
				<select id="rel_<?=$kV['id']?>" name="search[<?=$kV['id']?>][relation]" onChange="changeDisplay('<?=$kV['id']?>');">
					<option value="-1"<?=(isset($meta) && $meta['relation']==-1)? ' selected="selected"':''?>>von</option>
					<option value="1"<?=(isset($meta) && $meta['relation']==1)? ' selected="selected"':''?>>bis</option>
					<option value="0"<?=(isset($meta) && $meta['relation']==0)? ' selected="selected"':''?>>zwischen</option>
					<option value="2"<?=(isset($meta) && $meta['relation']==2)? ' selected="selected"':''?>>kommaseparierte Liste</option>
				</select>
					<input type="text" id="search_<?=$kV['id']?>" name="search[<?=$kV['id']?>][id]" size="<?=(isset($meta) && $meta['relation']==2)? '100':'4'?>"  value="<?=(isset($meta) && array_key_exists('search',$meta))?$meta['search'] :''?>"/><?break;?>

		<?endswitch?>
	</td></tr>
<?else:?>
<? $contentKey=$kP;?>
<?endif?>
<?endforeach?>
<?
$inc = (array_key_exists('include', $content['search']->Data))? $content['search']->Data['include'] : '1';
$val = (array_key_exists('search_text', $content['search']->Data))? $content['search']->Data['search_text'] : '';
?>
	<tr><td><img title="include oder exclude" src="tpl/<?=($inc==1)?'plus':'minus'?>.png" onClick="changeInc(this)" /><input type="hidden" name="search[<?=$content['paperMetaType']->List[$contentKey]['id']?>][inc]" value="<?=$inc?>"/></td><td class="metaname"><?=$content['paperMetaType']->List[$contentKey]['name']?></td><td class="metavalue">
		<input size="100" type="text" name="search[<?=$content['paperMetaType']->List[$contentKey]['id']?>][id]" value="<?=$val?>"/>

	</td></tr>
<?if (count($content['search']->Category) != 0 ):?>
<?$i=1;
	foreach($content['search']->Category as $vC):?>
		<tr id="category_<?=$i?>"><td><img title="include oder exclude" src="tpl/<?=($vC['include']==1)? 'plus':'minus'?>.png" onClick="changeInc(this)" /><input type="hidden" name="search[category_<?=$i?>][inc]" value="<?=$vC['include']?>"/></td>
			<td class="metaname">Kategorie</td><td class="metavalue">
				<select name="search[category_<?=$i?>][id]">
						<option value="0">Bitte wählen</option>
						<?$tief=0?>
						<?foreach ($content['categoryList']->htmlTree(0) as $kL=>$vL):?>
							<option value="<?=$vL['id']?>"<?=($vC['category_id']==$vL['id']) ? ' selected="selected"':''?>><?for($i=0;$i<$tief;$i++):?>&nbsp;&nbsp;<?endfor?><?=$vL['name']?></option>
							<? if ($vL['childOpen']>0) $tief++;
							$tief -= $vL['childClose']; ?>
						<?endforeach?>
				</select>

		</td></tr>
<?$i++?>
	<?endforeach?>
<?else:?>
	<tr id="category_1"><td><img title="include oder exclude" src="tpl/plus.png" onClick="changeInc(this)" /><input type="hidden" name="search[category_1][inc]" value="1"/></td>
		<td class="metaname">Kategorie</td><td class="metavalue">
			<select name="search[category_1][id]">
					<option value="0">Bitte wählen</option>
						<?$tief=0?>
						<?foreach ($content['categoryList']->htmlTree(0) as $kL=>$vL):?>
							<option value="<?=$vL['id']?>"><?for($i=0;$i<$tief;$i++):?>&nbsp;&nbsp;<?endfor?><?=$vL['name']?></option>
							<? if ($vL['childOpen']>0) $tief++;
							$tief -= $vL['childClose']; ?>
						<?endforeach?>
			</select>

	</td></tr>
<?endif?>
	<tr><td></td>
		<td colspan="2" class="metaname"><input type="button" value="+" onClick="newCategory()" /> weitere Kategorie hinzufügen</td><td class="metavalue">
	</td></tr>
</table>
<input type="hidden" name="searchAction" value="search"/>
<input type="hidden" name="name[old]" id="oldName"/>
<input type="hidden" name="name[new]" id="newName"/>
<input type="submit" value="Suchen"/>
<input type="button" value="Suche speichern" onClick="saveSearch()"/>
<input type="button" value="Suche laden" onClick="loadSearch()"/>
<input type="button" value="Suche löschen" onClick="delSearch()"/>
<input type="button" value="Formular zurücksetzen" onClick="resetForm()"/>

</form>
<br/>
<br/>
<p>Ergebnisse: <b><?=$content['search']->Message?></b></p>
<? $list = $content['search']->ResultList?>
<? $mark = $content['searchMarkIds']?>
<script type="text/javascript">
var exMark=<?= count($mark)?>;
</script>
<? $maxCount=25?>
<? $cLength=40?>
<?if ($content['search']->ResultCount>$maxCount):?>
<div class="blaettern">
		<a onClick="addMark(this)" href="?resultCount=0"><input  type="submit" value="<<"/></a>
		<a onClick="addMark(this)" href="?resultCount=<?=$content['search']->Start-$maxCount?>"><input  type="button" value="<"/></a>
		<?=floor($content['search']->Start / $maxCount)+1?> / <?=floor($content['search']->ResultCount / $maxCount)+1?> 
		<a onClick="addMark(this)" href="?resultCount=<?=
		(((floor($content['search']->ResultCount / $maxCount))*$maxCount)<($content['search']->Start+$maxCount))? ((floor($content['search']->ResultCount / $maxCount))*$maxCount) : ($content['search']->Start+$maxCount)
		?>"><input  type="submit" value=">"/></a>
		<a onClick="addMark(this)" href="?resultCount=<?=(floor($content['search']->ResultCount / $maxCount))*$maxCount?>" ><input type="submit" value=">>"/></a>
</div>
<?endif;?>
<?$relevanz = ($content['search']->Relevanz>0) ? true :false;?>
<form action="" method="GET" onSubmit="return beforeSubmit();">
<table id="searchResult"> 
	<tr>
		<th><a onClick="addMark(this)" class="sortSearch" href="?order=id&direction=<?=($content['search']->Direction=="asc")?'desc':'asc'?>">id
			<?if($content['search']->Order=="id"):?><img title="<?=($content['search']->Direction=="asc")?'aufsteigend':'absteigend'?>" src="images/<?=$content['search']->Direction?>.png"/><?endif?></a></th>
		<th><a onClick="addMark(this)" class="sortSearch" href="?order=journal&direction=<?=($content['search']->Direction=="asc")?'desc':'asc'?>">Zeitung
			<?if($content['search']->Order=="journal"):?><img title="<?=($content['search']->Direction=="asc")?'aufsteigend':'absteigend'?>" src="images/<?=$content['search']->Direction?>.png"/><?endif?></a></th>
		<th><a onClick="addMark(this)" class="sortSearch" href="?order=date&direction=<?=($content['search']->Direction=="asc")?'desc':'asc'?>">Datum
			<?if($content['search']->Order=="date"):?><img title="<?=($content['search']->Direction=="asc")?'aufsteigend':'absteigend'?>" src="images/<?=$content['search']->Direction?>.png"/><?endif?></a></th>
		<th><a onClick="addMark(this)" class="sortSearch" href="?order=content&direction=<?=($content['search']->Direction=="asc")?'desc':'asc'?>">Textbeginn
			<?if($content['search']->Order=="content"):?><img title="<?=($content['search']->Direction=="asc")?'aufsteigend':'absteigend'?>" src="images/<?=$content['search']->Direction?>.png"/><?endif?></a></th>
<? if ($relevanz):?>
		<th><a onClick="addMark(this)" class="sortSearch" href="?order=relevanz&direction=<?=($content['search']->Direction=="asc")?'desc':'asc'?>">Relevanz
			<?if($content['search']->Order=="relevanz"):?><img title="<?=($content['search']->Direction=="asc")?'aufsteigend':'absteigend'?>" src="images/<?=$content['search']->Direction?>.png"/><?endif?></a></th>
<?endif?>
		<th>&nbsp;</th></tr>
<? $i=0?>
<? $startItem=0?>
<?if (is_array($list)):?>
	<?foreach($list as $vL):?>
		<?if ($startItem<$content['search']->Start):?>
			<?$startItem++?>	
		<?else:?>
			<tr class="<?=(($i % 2)==1)?"dark":"bright"?>"><td onClick="anno(<?=$vL['id']?>)"><?=$vL['id']?></td><td onClick="anno(<?=$vL['id']?>)"><?=(array_key_exists('journal',$vL))?$vL['journal']:"&nbsp;"?></td><td onClick="anno(<?=$vL['id']?>)"><?=$vL['release_date']?></td><td onClick="anno(<?=$vL['id']?>)"><?=mb_substr($vL['content'] , (array_key_exists(0,$vL['offset']))?$vL['offset'][0]:0, (array_key_exists(0,$vL['length']))?$vL['length'][0]:$cLength,"utf-8")?>...</td><? if ($relevanz):?><td><?=count($vL['offset'])?></td><?endif?><td><input type="checkbox" <?=in_array($vL['id'],$mark)?' checked="checked"':''?>value="1" name="mark[<?=$vL['id']?>]" onChange="changeMark()" /></td></tr>
			<?$i++?>
			<?if ($i>=$maxCount) break;?>
		<?endif?>
		<?for($j=1;$j<count($vL['offset']);$j++):?>
			<?if ($startItem<$content['search']->Start):?>
				<?$startItem++?>	
			<?else:?>
				<tr class="<?=(($i % 2)==1)?"dark":"bright"?>"><td onClick="anno(<?=$vL['id']?>)">&nbsp;</td><td onClick="anno(<?=$vL['id']?>)">&nbsp;</td><td onClick="anno(<?=$vL['id']?>)">&nbsp;</td><td onClick="anno(<?=$vL['id']?>)"><?=mb_substr($vL['content'] , $vL['offset'][$j], $vL['length'][$j],"utf-8")?>...</td><td></td><td>&nbsp;</td></tr>
				<?$i++?>
				<?if ($i>=$maxCount) break(2);?>
			<?endif?>
		<?endfor?>
	<?endforeach?>
<?endif?>
<tr class="<?=(($i % 2)==1)?"dark":"bright"?>"><td></td><td></td><td></td><td>Alle auswählern / Auswahl entfernen</td><td><input type="checkbox" name="allMark" onChange="changeMarkAll()" /></td><? if ($relevanz):?><td></td><?endif?></tr>
<tr class="foot"><td colspan="3">Anzahl Datensätze: <?=count($list)?></td><td colspan="2">Markierte Datensätze: <span id="changeMark"><?=count($mark)?></span> <select name="searchAction"><option value="">Bitte wählen</option><option value="delList">Löschen</option><option value="saveList">Speichern</option></select> <input type="submit" value="OK"/> &nbsp;&nbsp;Anzahl Wörter: <?=$content['search']->WordCount?>&nbsp;&nbsp;Anzahl Lemma: <?=$content['search']->LemmaCount?></td><? if ($relevanz):?><td></td><?endif?></tr>
</table> 
</form>
</div>
<script type="text/javascript">
exMark=exMark-$('input:checked[name^=mark]').length;
</script>
