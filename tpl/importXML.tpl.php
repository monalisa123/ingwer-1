<div id="Content">
<script type="text/javascript">
$(document).ready(function() {
		$.ajaxSetup({cache:false});
		<?=(array_key_exists('progress',$content))? $content['progress']:""?>
		$("#xmlForm").ajaxForm({
		success: importUpload,
			dataType:  'html',
			beforeSubmit: function(arr, $form, options) {
				if($('input[name=file]',$form).val() != '') {
					progressBox('XML-Map wird erstell','0','Bitte Fenster nicht schließen!');
					$('textarea#status').val('');
					return true;
				}
				return false;
			}

		
});
});
</script>
<h1> Import - XML </h1>
<hr />
<p>Bitte den XML-Tags die Datenbankfelder zuordnen</p>
<form action="" method="GET" id="xmlForm">

		<table class="lesen">
		<? $count=0;?>
		<?foreach($content['xmlMap'] as $kX=>$vX):?>
			<? if($count==0):?>	<tr><?endif?>
				<td class="metaname"><?=$kX?></td><td class="metavalue"><select class="formColor" name="f[<?=$kX?>]">
				<option value="">ignorieren</option>
				<option value="1">Artikelumschließendes Tag</option>
			<?foreach($content['PaperMetaDataType'] as $kP=>$vP):?>
				<option value="<?=$kP?>"<?=($kP==$vX)? ' selected="selected"':''?>><?=$kP?></option>
			<?endforeach?>
			</select>
			</td>
		
			<? if($count==1):?>	</tr><? $count=0;?><?else:?><? $count++;?><?endif;?>
		
		<?endforeach?>
		
		<?while($count<2):?><td></td><?$count++?><?endwhile?>
		</table>
<br />
<input type="submit" name="Importieren" value="Importieren">
<input type="hidden" name="XMLMap" value="1">
<? if ($content['overwrite']):?>
<input type="hidden" name="f[overwrite]" value="1">
<?endif?>
</form>
<br />
	<div class="form">
	Statusmeldungen<br /><textarea readonly="readonly"><?=(array_key_exists('message',$content))? $content['message']:""?></textarea>
	</div>
</div>
