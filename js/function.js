var importInterval;
var exportInterval;
var exportFile=true;
var isMessageBox = false;

function htmlResponse(respHtml){
    $('body').replaceWith(respHtml);
}

function Export(respHtml){
	exportFile=true;
	updateProgressBoxExport('Export-Datei wird erzeugt','5','');

	exportInterval = setInterval(exportCheckStatus,'5000');
}

function parseExportStatus (data){
	if (data==null) return;
	if (data.status && data.status == 'start') {
		$('span.messageBox').find('input[type=button]').show();
	}
	if (data.status && data.status == 'file') {
		if (exportFile) {
			updateProgressBoxExport('Download Export-Datei startet',0,'');
			exportFile=false;
			window.location.href = 'index.php?nav=Verwaltung&cmd=ExportFile';
		}
		return;
	}
	if (data.status && data.status == 'end') {
		window.clearInterval(exportInterval);
		messageBoxHide();
		$('textarea#statusExport').val(data.message.replace(/\\n/g, "\n"));
		$.getJSON('indexAjax.php?nav=ExportDeleteStatus');
		return;
	}

	if (data.percent >= 0){
		if (data.cwb)
			updateProgressBoxExport(data.cwb,0,'');
		else updateProgressBoxExport('Verarbeite Artikel',data.percent,data.count + ' / ' + data.countAll);
	}
}

function exportCheckStatus(){
	$.getJSON('indexAjax.php?nav=ExportCheckStatus',parseExportStatus );
}

function importUpload(respHtml){
	updateProgressBox('Datei hochgeladen','5','Analysiere Datei');
	$resp = $(respHtml);
	if ($resp.find('input[name=XMLMap]').length ==1){
		$('div#Content').replaceWith($resp.find('div#Content'));
		messageBoxHide();
		$("#xmlForm").ajaxForm({
		success: importUpload,
			dataType:  'html',
			beforeSubmit: function(arr, $form, options) {
				if($('select[value=1]',$form).length == 1) {
					progressBox('XML-Map wird erstellt','0','Bitte Fenster nicht schließen!');
					$('textarea#status').val('');
					return true;
				}
				alert("Bitte ein umschließendes Tag definieren!");
				return false;
			}

		
});
		return;
	}
	else {
	if ($resp.find('div#Content').length==1) $('div#Content').replaceWith($resp.find('div#Content'));
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
	}

	importInterval = setInterval(importCheckStatus,'5000');
}
 
function parseImportStatus (data){
	if (data==null) return;
	if (data.status && data.status == 'start') {
		$('span.messageBox').find('input[type=button]').show();
	}
	if (data.status && data.status == 'end') {
		window.clearInterval(importInterval);
		messageBoxHide();
		$('textarea#status').val(data.message.replace(/\\n/g, "\n"));
		$.getJSON('indexAjax.php?nav=ImportDeleteStatus');
		return;
	}

	if (data.updateMessage){
		updateProgressBox(data.updateMessage,data.percent,'');
	}else if (data.percent > 0){
		updateProgressBox('Verarbeite Artikel',data.percent,data.countAll + ' / ' + data.countBegin);
	}
}

function importCheckStatus(){
	$.getJSON('indexAjax.php?nav=ImportCheckStatus',parseImportStatus );
}

function OpenClose(T){
	var e = $(T.parentNode);
	if (e == undefined) return;
	var parName= $(T.parentNode.parentNode.parentNode).attr('name');
	if (parName == undefined){
		parName=0;
	}else{
		parName= parseInt(parName.replace(/category_/, ""));
	}

	var name = parseInt(e.attr('name').replace(/category_/, ""));
	
	if (T.src.search(/minus.png$/)>0 && e.children("ul").length > 0) {
		T.src = T.src.replace(/minus.png$/,"plus.png");
		Category.catList[name].setParent(new Parent.Parent(parName,false));
		e.children("ul").slideUp(function (){Annotation.resizeWindow();});
	}
	else {
		Category.catList[name].setParent(new Parent.Parent(parName,true));
		e.children("ul").slideDown(function (){Annotation.resizeWindow();});
		T.src = T.src.replace(/plus.png$/,"minus.png");
	}
}
function abortImport(){
	$.getJSON('indexAjax.php?nav=ImportAbort',parseImportStatus );
}
function abortExport(){
	$.getJSON('indexAjax.php?nav=ExportAbort',parseImportStatus );
}

function updateProgressBoxExport(Title,Percent,Caption){
	$('span.messageBox').html(Title+' <br />'
		+ '<table class="progressBar"><tr><td class="progressBarProgress" style="border:1px solid #000066;width: ' + Percent + '%;"></td><td></td></tr></table>'
		+ Caption + ' <br />'
		+ '<input type="button" value="Abbrechen" onClick="abortExport()"/>');
}

function updateProgressBox(Title,Percent,Caption){
	$('span.messageBox').html(Title+' <br />'
		+ '<table class="progressBar"><tr><td class="progressBarProgress" style="border:1px solid #000066;width: ' + Percent + '%;"></td><td></td></tr></table>'
		+ Caption + ' <br />'
		+ '<input type="button" value="Abbrechen" onClick="abortImport()"/>');
}

function progressBox(Title,Percent,Caption){
	updateProgressBox(Title,Percent,Caption);
	$('span.messageBox').find('input[type=button]').hide();
	messageBoxShow();

}

function resizeOverlay(){
	var Content = $('#Content');
	var Export = $('li.export');
	var pos = Content.offset();
	var heightContent = Content.outerHeight();
	var heightExport = Export.outerHeight();
	if (heightExport > heightContent) heightContent=heightExport;
	var heightBody = window.outerHeight - 100;
	if (!heightBody || heightContent > heightBody) {
		if (heightContent < 700) heightBody = 1000;
		else heightBody = heightContent + 200;
	}
	//heightBody=heightContent;
	var heightSpan = $('#message > span').outerHeight();
	if (heightSpan > heightContent) heightContent=heightSpan;
	$('#message').css({
		top : pos.top,
		height: heightBody});
	$('#message > span').css({
		top : (Math.abs(heightContent - heightSpan) / 2) ,
		left :((Content.width() - $('#message > span').width()) / 2) });
	$('#overlay').height(heightBody);
	$('#overlay').css('top', pos.top);
}

function showWait(){
	if (isMessageBox) return;
	var Content = $('#Content');
	var pos = Content.offset();
	var heightContent = Content.outerHeight();
	var heightBody = window.outerHeight - 100;
	if (!heightBody || heightContent > heightBody) {
		if (heightContent < 700) heightBody = 1000;
		else heightBody = heightContent + 200;
	}
	heightBody -= 500;
	$('#wait').height(heightBody).css('top', pos.top).show(0);
	$('body').css('cursor','progress');
}

function hideWait(){
	if (isMessageBox) return;
	$('#wait').hide(0);
	$('body').css('cursor','auto');
}

function messageBoxShow(){
	isMessageBox = true;
	resizeOverlay();
	$('#overlay').fadeTo('normal',0.8);
	$('#message').fadeTo('fast',0);
	resizeOverlay();
	$('#message').fadeTo('normal',1);
	$('#message').fadeIn();
	$(window).resize(resizeOverlay); 
}

function messageBoxHide(after){
	isMessageBox = false;
	$(window).unbind('resize',resizeOverlay); 
	//$('#paper').show();
	//$('#categoryList ul').show();
	$('#message').fadeOut();
	$('#overlay').fadeTo('normal',0).fadeOut(after);
		window.clearInterval(importInterval);
}
