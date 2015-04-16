<div id="Content">
<script type="text/javascript">
$(document).ready(function() {
	$.ajaxSetup({
		cache:false
		<?=(array_key_exists('httpUser',$content))? ",username: '".$content['httpUser']."'":""?>
		<?=(array_key_exists('httpPass',$content))? ",password: '".$content['httpPass']."'":""?>
	});
		<?=(array_key_exists('progress',$content))? $content['progress']:""?>
		$("#uploadForm").ajaxForm({
		success: importUpload,
			dataType:  'html',
			beforeSubmit: function(arr, $form, options) {
				if($('input[name=file]',$form).val() != '') {
					progressBox('Datei wird hochgeladen','0','Bitte Fenster nicht schließen!');
					$('textarea#status').val('');
					return true;
				}
				return false;
			}

		
});
		$("#exportForm").ajaxForm({
		success: Export,
			dataType:  'html',
			beforeSubmit: function(arr, $form, options) {
				if ($('li.export input#exportCwb').attr('checked') && $('li.export input#exportIndex').attr('checked') && 
					($('li.export input#exportCorpus').val()=='' || $('li.export input#exportCorpus').val().search(/[^0-9a-z]/)>0)) {
					alert("Bitte Korpusbezeichnung eingeben\nnur Buchstaben und Zahlen , keine Umlaute, Kleinschreibung");
					return false;
				}
				progressBox('Export wird vorbereitet','0','Bitte Fenster nicht schließen!');
				$('textarea#statusExport').val('');
				return true;
			}

		
});
});
</script>
<ul class="administration">
	<li class="import">
		<h1> Import </h1>
		<hr />
		<form action="" method="POST" enctype="multipart/form-data" id="uploadForm">
		<table class="form">
			<tr><td>Quelle</td><td><input class="formColor" type="file" name="file"/></td></tr>
			<tr><td>Format</td><td><input class="formColor" type="radio" name="f[format]" checked="checked" value="text" id="importText"/> <label for="importText">Text strukturiert</label><br />
						<input class="formColor" type="radio" name="f[format]" value="lexico3" id="importLexico3"/> <label for="importLexico3">Lexico3</label> <br />
						<input class="formColor" type="radio" name="f[format]" value="xml" id="importXml"/> <label for="importXml">XML</label></br>
						<input class="formColor" type="radio" name="f[format]" value="xmlcategory" id="importXmlCategory"/> <label for="importXmlCategory">XML Kategorien</label><br />
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input class="formColor" type="checkbox" name="f[overwritecategory]" value="1" id="importOverwriteCategory"/><label for="importOverwriteCategory"> alte Kategorien löschen</label>
						</td></tr>
			<tr><td><label for="importOverwrite">Duplikate <br/>&uuml;berschreiben</label></td><td><input class="formColor" type="checkbox" value='1' name="f[overwrite]" checked="checked" id="importOverwrite"/></td></tr>
			<tr><td><input class="formColor" name="f[submit]" type="submit" value="Importieren"/></td><td></td></tr>
			<tr><td colspan="2">Statusmeldungen<br /><textarea id="status" readonly="readonly"><?=(array_key_exists('message',$content))? $content['message']:""?></textarea></td></tr>
		</table>
		<input type="hidden" name="nav" value="Verwaltung"/>
		<input type="hidden" name="cmd" value="Import"/>
		</form>
	</li>
	<li class="export">
		<h1> Export </h1>
		<hr />
		<form action="" id="exportForm">
		<table class="form">
			<tr><td><input id="exportAll" class="formColor" type="radio" checked="checked" name="f[data]" value="all"/></td><td><label for="exportAll">alle Datensätze</label></td><td></td></tr>
			<tr><td><input id="exportCurrentSearch" class="formColor" type="radio" name="f[data]" value="currentSearch"/></td><td><label for="exportCurrentSearch">aktuelles Suchresultat</label></td><td></td></tr>
			<tr><td><input id="exportSaveSearch" class="formColor" type="radio" name="f[data]" value="saveSearch"/></td><td><label for="exportSaveSearch">gespeicherte Suche</label></td><td>
				<select name="f[search]">
					<option>Bitte wählen</option>
			<? if (count($content['searchNames']->List) >0):?>
				<?foreach($content['searchNames']->List as $kV):?>
					<option value="<?=$kV['name']?>"><?=$kV['name']?></option>
				<?endforeach?>
			<?endif?>
				</select></td></tr>
			<tr><td colspan="3"><b>Format</b></td></tr>
			<tr><td><input id="exportXml" class="formColor" checked="checked" type="radio" name="f[format]" checked="checked" value="xml"/></td><td><label for="exportXml">XML</label></br>
				<table class="form">
					<tr><td><input id="exportXmlData" class="formColor" type="radio" name="f[xmldata]" checked="checked" value="data"/></td><td><label for="exportXmlData">Datensätze</label></br>Trennzeichen für Kategorienpfad: <input type="text" name="f[xmlpathchar]" value="/" size="1" maxlength="1" /></td></tr>
					<tr><td><input id="exportXmlCategory" class="formColor" type="radio" name="f[xmldata]" value="category"/></td><td><label for="exportXmlCategory">Kategorien</label></br><input id="exportXMLPos" type="checkbox" name="f[xmlpos]" value="1"/><label for="exportXMLPOS">inkl. POS</label></td></tr>
				</table>
				</td><td></td></tr>
			<tr><td><input id="exportCwb" class="formColor" type="radio" name="f[format]" value="cwb"/></td><td><label for="exportCwb">Corpus Workbench</label></td><td></td></tr>
			<tr><td></td><td>Korpusbezeichnung<br/>(nur Buchstaben und Zahlen, <br/>keine Umlaute, Kleinschreibung)</td><td><input id="exportCorpus" type="text" name="f[corpus]" /></td></tr>
			<tr><td></td><td><input id="exportIndex" type="checkbox" name="f[index]" value="1"/><label for="exportIndex">inkl. Indizierung</label></td><td></td></tr>
			<tr><td></td><td colspan="2"><b>einzelne Metadaten und die Kategorien zu kategorialen Daten konvertieren:</b></td></tr>
			<? $line=true?>
				<?foreach($content['meta'] as $kM):?>
					<? if ($line):?>
			<tr class="metaauswahl"><td></td><td><input type="checkbox" value="1" id="exportMeta<?=$kM['id']?>" name="f[meta][<?=$kM['id']?>]" \><label for="exportMeta<?=$kM['id']?>"> <?=$kM['name']?></label></td>
					<? $line=false?>
					<?else:?>
			<td><input type="checkbox" value="1" id="exportMeta<?=$kM['id']?>" name="f[meta][<?=$kM['id']?>]" \><label for="exportMeta<?=$kM['id']?>"> <?=$kM['name']?></label></td></tr>
					<? $line=true?>
					<?endif?>
				<?endforeach?>
				<? if (!$line):?>
			<td></td></tr>
				<?endif?>
			<tr class="metaauswahl"><td></td><td><input type="checkbox" value="1" id="exportMetaCategory" name="f[meta][category]" \><label for="exportMetaCategory"> Kategorien</label></td><td></td></tr>
			<tr><td><input id="exportLexico3" class="formColor" type="radio" name="f[format]" value="lexico3"/></td><td><label for="exportLexico3">Lexico 3</label></td><td></td></tr>
			<tr><td><input id="exportCsv" class="formColor" type="radio" name="f[format]" value="cvs"/></td><td><label for="exportCsv">CSV</label></td><td></td></tr>
			<tr><td></td><td><label for="exportTrennzeichen">Trennzeichen: </label><select id="exportTrennzeichen" name="f[trennzeichen]">
				<option value=";">;</option>
				<option value=",">,</option>
				<option value="t">Tabulator</option>
				<option value="0">wählen</option>
			</select></td><td><label for="exportEigenesZeichen"> Zeichen wählen: </label><input id="exportEigenesZeichen" size="1" type="text" name="f[eigeneszeichen]" /></td></tr>
			<tr><td></td><td><input id="exportInZeichen" type="checkbox" name="f[inzeichen]" value="1" checked="checked" /><label for="exportInZeichen">Felder in Anführungszeichen</label></td>
			<td><input id="exportZeilenschaltung" type="checkbox" name="f[zeilenschaltung]" value="1"/><label for="exportZeilenschaltung">Zeilenschaltung entfernen</label></td></tr>
			<tr><td></td><td><input id="exportErsteZeile" type="checkbox" name="f[erstezeile]" value="1"/><label for="exportErsteZeile">Feldnamen in die erste Zeile setzen</label></td><td></td></tr>
			<tr><td colspan="3"><input class="formColor" name="f[submit]" type="submit" value="Exportieren"/></td></tr>
			<tr><td colspan="3">Statusmeldungen<br /><textarea id="statusExport" readonly="readonly"><?=(array_key_exists('messageExport',$content))? $content['messageExport']:""?></textarea></td></tr>
		</table>
		<input type="hidden" name="nav" value="Verwaltung"/>
		<input type="hidden" name="cmd" value="Export"/>
		</form>
	</li>
</ul>
</div>
