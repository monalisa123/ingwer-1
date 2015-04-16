<div id="Content">
<script type="text/javascript">
function goPaperId(id){
	window.location.href="?paperId="+id;
}
function goPaperNr(nr){
	window.location.href="?paperNr="+nr;
}
function goNav(nav){
	window.location.href="?nav="+nav;
}

function addMulti(name){
	var nameId = name.replace(/ /g,"_");
	var attr='#'+nameId+'\\[list\\]\\[0\\]';
	var select = $('select'+attr);
	var attr='#'+nameId+'\\[count\\]';
	var count = $('input'+attr);
	var i=count.val();
	var add = '<select id="'+nameId+'[list]['+i+']" onChange="checkDisplay(\''+name+'\','+i+');" name="'+name+'[list]['+i+']">'
		+select.html().replace(/ selected="selected"/,'')
		+'</select>&nbsp;&nbsp;&nbsp;&nbsp; <input name="'+name+'[neu]['+i+']" id="'+nameId+'[neu]['+i+']" type="text" value=""/><br/>';
	i++;
	count.val(i);
	count.before(add);
}

function checkDisplay(name,i){
	var nameId = name.replace(/ /g,"_");
	if (isNaN(i)){
		var attr="input#"+nameId+"\\[neu\\]";
		var inp=$(attr);
		var attr="select#"+nameId+"\\[list\\]";
		if ($(attr).val()=='') inp.css('display','inline');
		else inp.css('display','none');
	}else{
		var attr="input#"+nameId+"\\[neu\\]\\["+i+"\\]";
		var inp=$(attr);
		var attr="select#"+nameId+"\\[list\\]\\["+i+"\\]";
		var select=$(attr);
		if (select.val()=='') inp.css('display','inline');
		else inp.css('display','none');
	}
}

function checkContent(){
	if ($('textarea[name=<?=$content['papercontentKey']?>]').val()!= $('input[name=<?=$content['papercontentKey']?>Check]').val()) 
		return confirm("Beim Verändern des <?=$content['papercontentKey']?>s kann es zu Fehlern bei der Annotationen kommen.\nBitte überprüfen Sie dies nach dem Speichern.\nTrotzdem speichern?");
	return true;
}
</script>
<form action="" method="POST" onSubmit="return checkContent();">
<h1> Lesen / Bearbeiten </h1>
<hr />
<?if (array_key_exists('searchResult',$_SESSION)){
	$result=$_SESSION['searchResult'];
	$countResult = count($result['list']);
} else $countResult=0;
$readonly = array('id','word_count','lemma_count');
$stringType= array('int','string','date');
?>
<table><tr><td width="600px">
	<?if (
		(isset($result) && $countResult >0)
		|| (!isset($result) && $content['paperCount'] >0)):?>
		<input type="hidden" name="paperId" value="<?=$content['paperData']['id']?>"/>
		<input type="hidden" name="cmd" value="Speichern"/>
		<table class="lesen">
		<?foreach($content['paperData'] as $kP=>$kV):?>
		<? if ($kP != $content['papercontentKey']):?>	
			<?if (in_array($kP,$readonly)):?>
				<tr><td class="metaname"><?=htmlspecialchars($kP)?></td><td class="metavalue"><?=(is_array($kV))?htmlspecialchars(implode('; ',$kV)):htmlspecialchars($kV) ?></td></tr>
			<?else:?>
				<?if (in_array($content['paperMeta']->List[$kP]['meta_type'],$stringType)):?>
					<tr><td class="metaname"><?=htmlspecialchars($kP)?></td><td class="metavalue"><input id="<?=preg_replace("/ /",'_',$kP)?>" name="<?=$kP?>" type="text" value="<?=(is_array($kV))?htmlspecialchars(implode('; ',$kV)):htmlspecialchars($kV) ?>"/></td></tr>
				<?elseif ($content['paperMeta']->List[$kP]['meta_type']=='list'):?>
					<tr><td class="metaname"><?=htmlspecialchars($kP)?></td><td class="metavalue">
						<select onChange="checkDisplay('<?=$kP?>');" id="<?=preg_replace("/ /",'_',$kP).'[list]'?>" name="<?=$kP?>[list]">
							<?unset($display);?>
							<option value="">anderer... </option>
							<?foreach($content['paperMeta']->getList($kP) as $kL=>$vL):?>
								<option <?= ($vL['name']==$kV) ? ' selected="selected" ':''?>value="<?=htmlspecialchars($vL['name'])?>"><?=htmlspecialchars($vL['name'])?></option><?if ($vL['name']==$kV) $display=true; ?>
							<?endforeach?>
					</select>&nbsp;&nbsp;&nbsp;&nbsp; <input<?= (isset($display))? ' style="display:none;"':''?> id="<?=preg_replace("/ /",'_',$kP).'[neu]'?>" name="<?=$kP?>[neu]" type="text" value=""/></td></tr>
				<?elseif ($content['paperMeta']->List[$kP]['meta_type']=='multilist'):?>
					<tr><td class="metaname"><?=htmlspecialchars($kP)?></td><td class="metavalue">
					<?$i=0;foreach($kV as $mV):?>
					<select onChange="checkDisplay('<?=$kP?>',<?=$i?>);" name="<?=$kP?>[list][<?=$i?>]" id="<?=(preg_replace("/ /",'_',$kP) . "[list][$i]")?>">
								<?unset($display);?>
								<option value="">anderer... </option>
								<?foreach($content['paperMeta']->getList($kP) as $kL=>$vL):?>
									<option <?= ($vL['name']==$mV) ? ' selected="selected" ':''?>value="<?=htmlspecialchars($vL['name'])?>"><?=htmlspecialchars($vL['name'])?></option><?if ($vL['name']==$mV) $display=true; ?>

								<?endforeach?>
							</select>&nbsp;&nbsp;&nbsp;&nbsp; <input<?= (isset($display))? ' style="display:none;"':''?> id="<?=preg_replace("/ /",'_',$kP)."[neu][$i]"?>" name="<?=$kP?>[neu][<?=$i?>]" type="text" value=""/><br/>
						<?$i++;?>
					<?endforeach?>
					<?if($i==0):?>
					<select onChange="checkDisplay('<?=$kP?>',<?=$i?>);" name="<?=$kP?>[list][<?=$i?>]" id="<?=(preg_replace("/ /",'_',$kP) . "[list][$i]")?>">
								<?unset($display);?>
								<option value="">anderer... </option>
								<?foreach($content['paperMeta']->getList($kP) as $kL=>$vL):?>
									<option value="<?=htmlspecialchars($vL['name'])?>"><?=htmlspecialchars($vL['name'])?></option>

								<?endforeach?>
							</select>&nbsp;&nbsp;&nbsp;&nbsp; <input<?= (isset($display))? ' style="display:none;"':''?> id="<?=preg_replace("/ /",'_',$kP)."[neu][$i]"?>" name="<?=$kP?>[neu][<?=$i?>]" type="text" value=""/><br/>
						<?$i++;?>
					<?endif?>
					<input type="hidden" value="<?=$i?>" name="<?=$kP?>[count]" id="<?=preg_replace("/ /",'_',$kP)."[count]"?>"/>
					<input type="button" onClick="addMulti('<?=$kP?>')" name="add" value="+" />
					</td></tr>
				<?endif;?>
			<?endif;?>
		<?endif;?>
		<?endforeach?>
			<tr><td class="metaname"><input onClick="goNav('Lesen');" type="button" value="Lesen"/></td><td class="metavalue"><input type="submit" value="speichern"/></td></tr>
		</table>
	<?endif;?>
</td><td style="padding-top:100px;" align="center">
<?if (isset($result)):?>
	<?if ($countResult>1):?>
	<?$currentResult=array_search($content['paperData']['id'],$result['list']);?>
	<div class="blaettern">
			<input onClick="goPaperId(<?=$result['list'][0]?>)" type="button" value="<<"/>
			<input onClick="goPaperId(<?=$result['list'][(($currentResult>0)?$currentResult-1:0)]?>)" type="button"  value="<"/> 
			<?=$currentResult+1?> / <?=count($result['list'])?> 
			<input onClick="goPaperId(<?=$result['list'][(($currentResult<$countResult-1)?$currentResult+1:$countResult-1)]?>)" type="button" value=">"/>
			<input onClick="goPaperId(<?=$result['list'][$countResult-1]?>)" type="button" value=">>"/>
	</div>
	<?endif;?>
<?else:?>
	<?if ($content['paperCount']>0):?>
	<div class="blaettern">
			<input onClick="goPaperNr(0)" type="button" value="<<"/>
			<input onClick="goPaperNr(<?=$content['paperNr']-1?>)" type="button"  value="<"/> 
			<?=$content['paperNr']+1?> / <?=$content['paperCount']?> 
			<input onClick="goPaperNr(<?=$content['paperNr']+1?>)" type="button"  value=">"/>
			<input onClick="goPaperNr(<?=$content['paperCount']-1?>)" type="button" value=">>"/>
	</div>
	<?endif;?>
<?endif;?>
</td></tr></table>
<?if (isset($result)):?>
	<?if (is_array($content) && array_key_exists('papercontentKey',$content)):?>
	<br/>
	<?=htmlspecialchars($content['papercontentKey'])?>:<br/>
	<br/>
	<textarea style="width:100%; height:30em;" name="<?=$content['papercontentKey']?>"><?=htmlspecialchars($content['paperData'][$content['papercontentKey']])?></textarea>
	<input type="hidden" name="<?=$content['papercontentKey']?>Check" value="<?=htmlspecialchars($content['paperData'][$content['papercontentKey']])?>"/>
	<? else: ?>
	Keine Dokumente in Aktueller Suche vorhanden
	<?endif;?>
<?else:?>
	<?if (is_array($content) && array_key_exists('papercontentKey',$content)):?>
	<br/>
	<?=htmlspecialchars($content['papercontentKey'])?>:<br/>
	<br/>
	<textarea style="width:100%; height:30em;" name="<?=$content['papercontentKey']?>"><?=htmlspecialchars($content['paperData'][$content['papercontentKey']])?></textarea>
	<input type="hidden" name="<?=$content['papercontentKey']?>Check" value="<?=htmlspecialchars($content['paperData'][$content['papercontentKey']])?>"/>
	<? else: ?>
	Keine Dokumente vorhanden
	<?endif;?>
<?endif;?>
</form>
</div>
