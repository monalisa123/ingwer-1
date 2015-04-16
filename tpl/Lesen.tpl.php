<div id="Content">
<h1> Lesen / Bearbeiten </h1>
<hr />
<?if (array_key_exists('searchResult',$_SESSION)){
	$result=$_SESSION['searchResult'];
	$countResult = count($result['list']);
} else $countResult=0;?>
<table><tr><td width="600px">
	<?if (
		(isset($result) && $countResult >0)
		|| (!isset($result) && $content['paperCount'] >0)):?>
		<table class="lesen">
		<?foreach($content['paperData'] as $kP=>$kV):?>
		<? if ($kP != $content['papercontentKey']):?>	
			<tr><td class="metaname"><?=$kP?></td><td class="metavalue"><?=(is_array($kV))?implode('; ',$kV):$kV ?></td></tr><?endif?>
		<?endforeach?>
			<tr><td class="metaname"><form action="" method="GET"><input type="hidden" value="Bearbeiten" name="nav"/><input type="submit" value="Bearbeiten"/></form></td><td class="metavalue"></td></tr>
		</table>
	<?endif;?>
</td><td style="padding-top:100px;" align="center">
<?if (isset($result)):?>
	<?if ($countResult>1):?>
	<?$currentResult=array_search($content['paperData']['id'],$result['list']);?>
	<div class="blaettern">
			<form action="" method="GET"><input type="hidden" value="<?=$result['list'][0]?>" name="paperId"/><input  type="submit" value="<<"/></form>
			<form action="" method="GET"><input type="hidden" value="<?=$result['list'][(($currentResult>0)?$currentResult-1:0)]?>" name="paperId"/><input  type="submit" value="<"/> </form>
			<?=$currentResult+1?> / <?=count($result['list'])?> 
			<form action="" method="GET"><input type="hidden" value="<?=$result['list'][(($currentResult<$countResult-1)?$currentResult+1:$countResult-1)]?>" name="paperId"/><input  type="submit" value=">"/></form>
			<form action="" method="GET"><input type="hidden" value="<?=$result['list'][$countResult-1]?>" name="paperId"/><input type="submit" value=">>"/></form>
	</div>
	<?endif;?>
<?else:?>
	<?if ($content['paperCount']>0):?>
	<div class="blaettern">
			<form action="" method="GET"><input type="hidden" value="0" name="paperNr"/><input  type="submit" value="<<"/></form>
			<form action="" method="GET"><input type="hidden" value="<?=$content['paperNr']-1?>" name="paperNr"/><input  type="submit" value="<"/> </form>
			<?=$content['paperNr']+1?> / <?=$content['paperCount']?> 
			<form action="" method="GET"><input type="hidden" value="<?=$content['paperNr']+1?>" name="paperNr"/><input  type="submit" value=">"/></form>
			<form action="" method="GET"><input type="hidden" value="<?=$content['paperCount']-1?>" name="paperNr"/><input type="submit" value=">>"/></form>
	</div>
	<?endif;?>
<?endif;?>
</td></tr></table>
<?if (isset($result)):?>
	<?if ($countResult >0 && is_array($content) && array_key_exists('papercontentKey',$content)):?>
	<br/>
	<?=$content['papercontentKey']?>:<br/>
	<br/>
	<?=preg_replace("/\n/","<br/>",$content['paperData'][$content['papercontentKey']])?>
	<? else: ?>
	Keine Dokumente in Aktueller Suche vorhanden
	<?endif;?>
<?else:?>
	<?if ($content['paperCount'] >0 && is_array($content) && array_key_exists('papercontentKey',$content)):?>
	<br/>
	<?=$content['papercontentKey']?>:<br/>
	<br/>
	<?=preg_replace("/\n/","<br/>",$content['paperData'][$content['papercontentKey']])?>
	<? else: ?>
	Keine Dokumente vorhanden
	<?endif;?>
<?endif;?>

</div>
