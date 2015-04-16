<?php
defined( '_VALID_INGWER' ) or die( 'Restricted access' );
?>
<?if (array_key_exists('searchResult',$_SESSION)){
	$result=$_SESSION['searchResult'];
	$countResult = count($result['list']);
} else $countResult=0;
if (
	(isset($result) && $countResult >0)
	|| (!isset($result) && $content['paperCount'] >0)):?>
	<?if (isset($result)):?>
		<?if ($countResult>1):?>
		<?$currentResult=array_search($content['paperData']['id'],$result['list']);?>
		<div class="blaettern">
				<form onSubmit="return Category.getPaper(<?=$result['list'][0]?>);" action="" method="GET"><input type="hidden" value="<?=$result['list'][0]?>" name="paperId"/><input  type="submit" value="<<"/></form>
				<form onSubmit="return Category.getPaper(<?=$result['list'][(($currentResult>0)?$currentResult-1:0)]?>);" action="" method="GET"><input type="hidden" value="<?=$result['list'][(($currentResult>0)?$currentResult-1:0)]?>" name="paperId"/><input  type="submit" value="<"/> </form>
				<?=$currentResult+1?> / <?=count($result['list'])?> 
				<form onSubmit="return Category.getPaper(<?=$result['list'][(($currentResult<$countResult-1)?$currentResult+1:$countResult-1)]?>);" action="" method="GET"><input type="hidden" value="<?=$result['list'][(($currentResult<$countResult-1)?$currentResult+1:$countResult-1)]?>" name="paperId"/><input  type="submit" value=">"/></form>
				<form onSubmit="return Category.getPaper(<?=$result['list'][$countResult-1]?>);" action="" method="GET"><input type="hidden" value="<?=$result['list'][$countResult-1]?>" name="paperId"/><input type="submit" value=">>"/></form>
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
		<?endif;?>
	<?endif;?>
<?endif;?>
