
var Category = {
	catList: new Object(),
	catListSave: new Object(),
	sentenceList: new Array(),
	categoryListWidth: $('#categoryList').css('width'),
	catAktivId: 0,
	changePOSDisplay: function() {
		if ($("input#POS").attr('checked')){
			$('#categoryList ul li[name=category_1]').show();
		}
		else {
			$('#categoryList ul li[name=category_1]').hide();
			}
	},
	changePOSDisplayReload: function() {
		showWait();
		Category.changePOSDisplay();
		var id= $('div#paper > div#paperHidden').find('input[name=selectPaperId]').val();
		Category.getPaper(id);
	},
	changeDisplayClick: function() {
		showWait();
		Category.waitDisplay();
	},
	changeDisplay: function() {
		showWait();
		$("br[name=bruch]").remove();
		if ($("input#changeDisplay").attr('checked')){
			for(var i=1; i <= Category.sentenceList.length; i++){
				var inCat = Category.sentenceList[i-1];
				$("#paperText span[name=sentenceId"+i+"]").css('display','none');
				for(var j=1; j < inCat.length; j++){
					if ($('#categoryList ul li input[name=categoryId'+inCat[j]+']').attr('checked')){
						$("#paperText span[name=sentenceId"+i+"]").css('display','inline').after('<br name="bruch" />');
						break;
					}
				}
			}
		}
		else {
			$("#paperText span[name^=sentenceId]").css('display','inline');
			}
	},
	initMouseEvent: function(){
			$('#categoryList ul li input:checkbox').click(Category.changeCheckedTest);
			$("#categoryList").unbind("mousedown");
			$("#categoryList").bind("mousedown",function(evt,data){
				evt.stopPropagation();
				evt.stopImmediatePropagation();
			});    
			$("#categoryListContainer").unbind("mousedown");
			$("#categoryListContainer").bind("mousedown",function(evt){
				ListContainer=parseInt($("#categoryList").css('width'));
				Resize_start= $("#categoryList").offset().left-1;
				$("#overlap").css("visibility","visible");
				$("#overlap").bind("mouseup mouseleave",function(evt){
					$("#overlap").css("visibility","hidden");
					$("#overlap").unbind("mousemove");
					$("#overlap").unbind("mouseup mouseleave");
					Annotation.resizeWindow();
					showWait();
					if (!isMessageBox)
						window.setTimeout("Category.displayAll()", 300);
					evt.stopPropagation();
					evt.stopImmediatePropagation();
				});
				$("#overlap").bind("mousemove",function(evt){
					var tmp = $("#categoryList").css('width');
					$("#categoryList").css('width',Resize_start-evt.pageX+ListContainer+'px');
					evt.stopPropagation();
					evt.stopImmediatePropagation();
				});
			});    
		},
	displayAll: function(){
		Category.displayBanner();
		Category.waitDisplay();
		Annotation.initMouseEvent();
		},
	changeCheckedTest: function (evt){
		$('#categoryList input[name^="categoryAll"]').attr('checked', false);
		showWait();
		evt.stopPropagation();
		evt.stopImmediatePropagation();
		var cur= $(evt.currentTarget);
		window.setTimeout("Category.changeChecked('"+cur.attr('name')+"',"+cur.attr('checked')+")", 10);
		//window.setTimeout("Category.changeChecked()", 1);
		//Category.changeChecked($(evt.currentTarget));
		       },
	changeChecked: function (id,checked){
		var inp = $('#categoryList ul li input[name='+id+']');
		var inp2 = $('#categoryList ul li input');
		$('#categoryList ul li input[name='+inp.attr('name')+']').attr('checked', checked);
		if (inp.attr('checked')) {
			Category.catList[id.substr(10)].display=true;
			var color = inp.parent().siblings('span.color').css('background-color');
			$('#paperText span.'+inp.attr('name')).css({'background-color':color,'border-top-color':color});
			$('#paperBanner > div[name='+inp.attr('name')+']').show();
		}
		else {
			Category.catList[id.substr(10)].display=false;
			$('#paperText span.'+inp.attr('name')).css({'background-color':'transparent','border-top-color':'transparent'});
			$('#paperBanner > div[name='+inp.attr('name')+']').hide();
		}
		var tmp=$('#categoryList ul li input[name='+inp.attr('name')+']').parent().parent().find('ul li input');
		$('#categoryList ul li input[name='+inp.attr('name')+']').parent().parent().find('ul li input').attr('checked', checked);
		if (inp.attr('checked')) {
			$('#categoryList ul li input[name='+inp.attr('name')+']').parent().parent().find('ul li input').each(function (index,elem){
				var inp = $(elem);
				Category.catList[id.substr(10)].display=true;
				var color = inp.parent().siblings('span.color').css('background-color');
				$('#paperText span.'+inp.attr('name')).css({'background-color':color,'border-top-color':color});
				$('#paperBanner > div[name='+inp.attr('name')+']').show();
		});
		} else {
			$('#categoryList ul li input[name='+inp.attr('name')+']').parent().parent().find('ul li input').each(function (index,elem){
				var inp = $(elem);
				Category.catList[id.substr(10)].display=false;
				$('#paperText span.'+inp.attr('name')).css({'background-color':'transparent','border-top-color':'transparent'});
				$('#paperBanner > div[name='+inp.attr('name')+']').hide();
			});
		}
		Category.changeDisplay();
		Annotation.displayBanner();
		Annotation.indentBanner();
		Annotation.displayBanner();
		hideWait();
	},
	waitDisplay: function (){
		window.setTimeout("Category.waitDisplayDelay()", 10);
	},
	waitDisplayDelay: function (){
		Category.changeDisplay();
		Annotation.displayBanner();
		Annotation.indentBanner();
		Annotation.displayBanner();
		hideWait();
	},
	displayBanner: function (){
		$.each(Category.catList,function (i){
			if (this.display){
				inp = $('#categoryList ul li input[name=categoryId'+i+']');
				var color = inp.parent().siblings('span.color').css('background-color');
				$('#paperText span.'+inp.attr('name')).css({'background-color':color,'border-top-color':color});
				$('#paperBanner > div[name='+inp.attr('name')+']').show();
			}
			/*TEST
			 * else {
				$('#paperText span.'+inp.attr('name')).css('background-color','transparent').css('border-top-color','transparent');
				$('#paperBanner > div[name='+inp.attr('name')+']').hide();
			}*/
		});
	},
	deleteParent: function(evt){ }
		,
	getPaperNr: function (id){
		showWait();
		window.setTimeout("Category.getPaperNrWait("+id+")", 10);
		return false;
	},
	getPaperNrWait: function (id){
		var pos = 0;
		if ($("input#POS").attr('checked')) pos=1;
		$.get("index.php",{
				'nav' : 'getPageNav',
				'POS' : pos,
				'paperNr' : id
		}
			,function (data,stat){
			if (stat!='success') return alert ('konnte nicht zum Artikel gehen');
			$('div.blaettern').replaceWith($(data));
			});
		$.get("indexAjax.php",{
				'nav' : 'getPaperNr',
				'POS' : pos,
				'paperNr' : id
		}
			,function (data,stat){
			if (stat!='success') return alert ('konnte nicht zum Artikel gehen');
			$("#paper").replaceWith(data);
			Annotation.resizeWindow();
			Annotation.initMouseEvent();
			$('#paperText').bind("mouseup",function(evt,data) {
				Annotation.selectText(evt,data);
			});
			Category.displayBanner();
			Category.waitDisplay();
			});
	},
	getPaper: function (id){
		showWait();
		window.setTimeout("Category.getPaperWait("+id+")", 10);
		return false;
	},
	getPaperWait: function (id){
		var pos = 0;
		if ($("input#POS").attr('checked')) pos=1;
		$.get("index.php",{
				'nav' : 'getPageNav',
				'POS' : pos,
				'paperId' : id
		}
			,function (data,stat){
			if (stat!='success') return alert ('konnte nicht zum Artikel gehen');
			$('div.blaettern').replaceWith($(data));
			});
		$.get("indexAjax.php",{
				'nav' : 'getPaper',
				'POS' : pos,
				'paperId' : id
		}
			,function (data,stat){
			if (stat!='success') return alert ('konnte nicht zum Artikel gehen');
			$("#paper").replaceWith(data);
			Annotation.resizeWindow();
			Annotation.initMouseEvent();
			$('#paperText').bind("mouseup",function(evt,data) {
				Annotation.selectText(evt,data);
			});
			Category.displayBanner();
			Category.waitDisplay();
			if ($('#categoryList input[name="categoryAllUse"]').attr('checked')){
				window.setTimeout("Category.allUseDisplayWait(true)", 10);
			}
			});
	},
	aktivWait: function(id){
			var paperHidden= $('div#paper > div#paperHidden');
			var scrollLast = $('div#paper').scrollTop();
			var pos = 0;
			if ($("input#POS").attr('checked')) pos=1;
			$.post("indexAjax.php",{
					'nav':'AddAnnotation',
					'paperId' : paperHidden.find('input[name=selectPaperId]').val(),
					'POS' : pos,
					'selected[categoryId]' : id,
					'selected[text]' : paperHidden.find('input[name=selectedText]').val(),
					'selected[textStartOffset]' : paperHidden.find('input[name=selectedTextStartOffset]').val(),
					'selected[textEndOffset]' : paperHidden.find('input[name=selectedTextEndOffset]').val(),
					'selected[textStartId]' : paperHidden.find('input[name=selectedTextStartId]').val(),
					'selected[textEndId]' : paperHidden.find('input[name=selectedTextEndId]').val(),
					'selected[textMouseUp]' : paperHidden.find('input[name=selectedTextMouseUp]').val(),
					'selected[textMouseUpPrev]' : paperHidden.find('input[name=selectedTextMouseUpPrev]').val(),
					'selected[textMouseUpSib]' : paperHidden.find('input[name=selectedTextMouseUpSib]').val(),
					'selected[textMouseDown]' : paperHidden.find('input[name=selectedTextMouseDown]').val(),
					'selected[textMouseDownPrev]' : paperHidden.find('input[name=selectedTextMouseDownPrev]').val(),
					'selected[textMouseDownSib]' : paperHidden.find('input[name=selectedTextMouseDownSib]').val(),
					'selected[textMouseDblClick]' : paperHidden.find('input[name=selectedTextMouseDblClick]').val(),
					'selected[user]' : $('div#Navigation input[name=user]').val()
					},function (data,stat){
				if (stat!='success') return alert ('konnte Annotation nicht anlegen');
				$('#paper').replaceWith(data);
				Annotation.resizeWindow();
				Annotation.initMouseEvent();
				$('#paperText').bind("mouseup",function(evt,data) {
					Annotation.selectText(evt,data);
				});
				Category.displayBanner();
				Category.waitDisplay();
				$('div#paper').scrollTop(scrollLast);
				});
	},	
	allUseDisplay: function(evt){
		showWait();
		if ($(evt).attr('checked')){
			window.setTimeout("Category.allUseDisplayWait(true)", 10);
		} else {
			window.setTimeout("Category.allUseDisplayWait(false)", 10);
		}
		return false;
	},
	allUseDisplayWait: function(check){
		$('#categoryList > ul li input').attr('checked', false);
		$('#categoryList input[name="categoryAll"]').attr('checked', false);
		Category.allDisplayWait(false);
		$('#categoryList input[name="categoryAllUse"]').attr('checked', check);
		if (check){
			$.each(Annotation.annoListCat,function (index,elem){
				if (!Category.catList[index].readonly && Category.catList[index].name!="$."){
					var inp = $('#categoryList > ul li input[name="categoryId'+index+'"]');
					inp.attr('checked', true);
					Category.catList[index].display=true;
					var color = inp.parent().siblings('span.color').css('background-color');
					$('#paperText span.'+inp.attr('name')).css({'background-color':color,'border-top-color':color});
					$('#paperBanner > div[name='+inp.attr('name')+']').show();
				}
			});
		}else {
			$.each(Annotation.annoListCat,function (index,elem){
				if (!Category.catList[index].readonly && Category.catList[index].name!="$."){
					var inp = $('#categoryList > ul li input[name="categoryId'+index+'"]');
					inp.attr('checked', false);
					Category.catList[index].display=false;
					$('#paperText span.'+inp.attr('name')).css({'background-color':'transparent','border-top-color':'transparent'});
					$('#paperBanner > div[name='+inp.attr('name')+']').hide();
				}
			});
		}
		Category.changeDisplay();
		Annotation.displayBanner();
		Annotation.indentBanner();
		Annotation.displayBanner();
		hideWait();
	},
	allDisplay: function(evt){
		showWait();
		if ($(evt).attr('checked')){
			$('#categoryList > ul li input').attr('checked', true);
			window.setTimeout("Category.allDisplayWait(true)", 10);
		} else {
			$('#categoryList > ul li input').attr('checked', false);
			window.setTimeout("Category.allDisplayWait(false)", 10);
		}
		return false;
	},
	allDisplayWait: function(check){
		$('#categoryList input[name="categoryAllUse"]').attr('checked', false);
		if (check){
			$('#categoryList ul li input').each(function (index,elem){
				var tmp = $('input#POS').attr('checked');
				if ($('input#POS').attr('checked') || (Category.catList[index]!== undefined && !Category.catList[index].readonly)){
					var inp = $(elem);
					Category.catList[inp.attr('name').substr(10)].display=true;
					var color = inp.parent().siblings('span.color').css('background-color');
					$('#paperText span.'+inp.attr('name')).css({'background-color':color,'border-top-color':color});
					$('#paperBanner > div[name='+inp.attr('name')+']').show();
				}
			});
		}else {
			$('#categoryList ul li input').each(function (index,elem){
				var inp = $(elem);
				Category.catList[inp.attr('name').substr(10)].display=false;
				$('#paperText span.'+inp.attr('name')).css({'background-color':'transparent','border-top-color':'transparent'});
				$('#paperBanner > div[name='+inp.attr('name')+']').hide();
			});
		}
		Category.changeDisplay();
		Annotation.displayBanner();
		Annotation.indentBanner();
		Annotation.displayBanner();
		hideWait();
	},
	aktiv: function(id){
		var ulCat = $('div#categoryList ul.Category'); 
		ulCat.find('span.categoryAktiv[name=name]').removeClass('categoryAktiv');
		ulCat.find('li[name=category_'+id+'] > span[name=name]').addClass('categoryAktiv');
		$('td#paperText').one('ajaxError',function (evt){
					alert('konnte Annotation nicht anlegen');
					hideWait();
					evt.stopPropagation();
					});
		Category.catAktivId=id;
		if ($('div#paper > div#paperHidden input[name=selectedText]').val().length > 0){
			showWait();
			window.setTimeout("Category.aktivWait("+id+")", 10);
		}

	}
		,
	deleteDb: function(evt){ },
	OpenClose: function(){ 
		$.each(Category.catList, function(i){
			for (var j=0;j<Category.catList[i].par.length; j++){
				if (Category.catListSave[i]!=undefined){
					Category.catList[i].display=Category.catListSave[i].display;
					var par = Category.catListSave[i].getParent(Category.catList[i].par[j].id);
					if (par.open === false){
						Category.catList[i].setParent(par);
						if (par.id != 0 ) $("li[name='category_"+par.id+"'] ul li[name='category_"+i+"'] > img").trigger("click");
						else $("#categoryList ul li[name='category_"+i+"'] > img").trigger("click");
					}
				}
			}
		});

	},
	saveCategory: function (text,stat){
		if (stat!='success') alert ('konnte nicht Speichern');
		messageBoxHide();
		$('#categoryList').css('width',Category.categoryListWidth);
		Annotation.resizeWindow();
		$.each(Annotation.annoList, function(i){
			var anno = Annotation.annoList[i];
			if (anno.show)
				$('#categoryList ul li input[name=categoryId'+anno.categoryId+']').attr('checked', true);
		});
		$('#categoryList ul li[name=category_1] > img').trigger("click");
		Category.OpenClose();
		Category.changePOSDisplay();
		Category.initMouseEvent();
		Category.displayBanner();
		Category.waitDisplay();

	},
	saveState: function(){
		var state=new Object();
		state.categoryListWidth=$('#categoryList').css('width');
		state.catList=Category.catList;
		$.each(state.catList, function(i){
			if (state.catList[i].readonly == false) {
				state.catList[i].name=undefined;
				state.catList[i].color=undefined;
				state.catList[i].comment=undefined;
				state.catList[i].readonly=undefined;
				state.catList[i].show=undefined;
			}
			else {state.catList[i]=undefined}
			});
		state.user=$('#application input[name="user"]').val();
		var encode=$.toJSON(state);
		$.cookie("state", encode,{ expires: 366 });
	},
	loadState: function(){
		if ($.cookie("state")!=undefined){
			var encode=$.cookie("state");
			var state=$.evalJSON(encode);
			$('#categoryList').css('width',state.categoryListWidth);
			$.each(state.catList, function(i,s){
				Category.catListSave[i]= new Category.Category(s.id,"","","",new Array('1'),false);
				var par = par=new Array();
				for(var j=0;j< s.par.length;j++){
					par[j]=new Parent.Parent(s.par[j].id,s.par[j].open);
				}
				Category.catListSave[i].par= par;
				Category.catListSave[i].display= s.display;
				if (s.display) {
					Category.changeChecked("categoryId"+s.id,true);
				}
			});
			$('#application input[name="user"]').val(state.user);
			Category.OpenClose();
			}
	},
	deleteCategory: function (id,name){
		if ($('li[name=category_'+id+'] li[name^=category_]').length != 0) {
			alert('Kategorie hat noch Unterkategorien, \nund kann deswegen nicht gelöscht werden.');
			return;
		}
		if (!confirm("Wollen Sie Kategorie "+ name +" ("+id+") wirklich löschen?")) return;
		Category.catListSave=Category.catList;
		Category.categoryListWidth=$('#categoryList').css('width');
		$.get("indexAjax.php",{'nav':'DeleteCategory','catId':id},function (data,stat){
			if (stat!='success') return alert ('konnte nicht Löschen');
			$('#categoryList').replaceWith(data);
			$('#categoryList').css('width',Category.categoryListWidth);
			messageBoxHide();
			Annotation.resizeWindow();
			$.each(Annotation.annoList, function(i){
				var anno = Annotation.annoList[i];
				if (anno.show)
					$('#categoryList ul li input[name=categoryId'+anno.categoryId+']').attr('checked', true);
			});
			$('#categoryList ul li[name=category_1] > img').trigger("click");
			Category.OpenClose();
			Category.changePOSDisplay();
			Category.initMouseEvent();
			Category.displayBanner();
			Category.waitDisplay();
			Annotation.initMouseEvent();
			});
	}
		,
	saveDb: function(evt){ },
	abortCategory: function(){ 
		       messageBoxHide();
			Annotation.resizeWindow();
		       }
		,
	getCat: function(id){
		if (id == -1) return new Category.Category(-1,'','ffffff','',new Array('0'),false);
		return Category.catList[id];
		}
		,
	addParentSelect: function (id) {
		var endHtml = $('tr td select[name=parent\\[' + (id-1)+'\\]]').html();
		$('input[name=addParent]').unbind("click").bind("click", function () {Category.addParentSelect(id+1);});
		$('span.messageBox table').append('<tr><td>Elternelement '
				+ id +':</td><td align="left"><select name="parent['+id+']">'+endHtml+'</select></td></tr>');
		
	},
	editWindow: function(id){
		cat = Category.getCat(id);
		var j=1;
		var optString ='<option style="margin-left: 0em" value="0">Wurzel (0)</option>';
		/*$.each(Category.catList, function (i){
			if (i != cat.id
				&& !this.readonly 
				&& $('li[name=category_'+i+']  li[name=category_'+cat.id+']').length == 0
				&& $('li[name=category_'+cat.id+']  li[name=category_'+i+']').length == 0){
				optString += '<option value="'+i + '">'
					+this.name+' (' + i +')</option>';
			}
		});*/
		$('#categoryList li').each( function (j,el){
			var listEl = Category.catList[$(el).attr('name').substring(9)];
			var parents = $(el).parents('ul.Category');
			if (listEl.id != cat.id
				&& !listEl.readonly 
				&& $('li[name=category_'+listEl.id+']  li[name=category_'+cat.id+']').length == 0
				&& $('li[name=category_'+cat.id+']  li[name=category_'+listEl.id+']').length == 0){
				optString += '<option style="margin-left: '+parents.length+'em" value="'+listEl.id + '">'
					+listEl.name+' (' + listEl.id +')</option>';
			}
		});
		var dis="";
		if (cat.readonly) dis=' disabled="disabled"';
		var elString = "";
		for(var i=0;i< cat.par.length;i++){
			elCat = Category.getCat(cat.par[i].id);
			elString += '<tr><td>Elternelement '+ j +':</td><td align="left"><select'+dis+' name="parent['+j+']">'
				+'<option selected="selected" value="'+elCat.id+'">'+elCat.name+' (' + elCat.id +')</option>'
				+optString+'</select></td></tr>';
			j++;
		}

		$('span.messageBox').html('<span class="color" style="background-color:#' + cat.color+'">&nbsp;</span>'
			+ ' Kategorie ' + cat.name+' (' + cat.id + ') <br /><hr />'
			+ '<form action="indexAjax.php" method="GET" id="categoryEditForm">'
			+ '<table>'
			+ '<tr><td>Bezeichnung:</td><td align="left"><input type="text"'+dis+' name="name" value="' + cat.name+'" /></td></tr>'
			+ '<tr><td>Bemerkung:</td><td align="left"><textarea name="comment">' + cat.comment+'</textarea></td></tr>'
			+ elString
			+ '<tr><td>weiteres <br />Elternelement:</td><td align="left"><input'+dis+' name="addParent" type="button" value="+" /></td></tr>'
			+ '<tr><td>Farbe: <span class="pickColor" style="background-color:#' + cat.color+'">&nbsp;</span></td><td align="left">#<input type="text" name="color" value="' + cat.color+'" /></td></tr>'
			+ '</table>'
			+ '<div id="colorpicker"></div>'
			+ '<input type="hidden" value="SaveCategory" name="nav" />'
			+ '<input type="hidden" value="' + cat.id + '" name="catId" />'
			+ '<input type="submit" value="Speichern" /><input type="button" value="Abbrechen" onClick="Category.abortCategory()"/>'
			+ ((cat.id!=-1)?'<input'+dis+' type="button" value="Löschen" onClick="Category.deleteCategory('+ cat.id +',\''+ cat.name +'\')"/>':'')
			+ '</form>');
		messageBoxShow();
		$('input[name=addParent]').bind("click", function () {Category.addParentSelect(j);});
		Category.catListSave=Category.catList;
		Category.categoryListWidth=$('#categoryList').css('width');
		$('#categoryEditForm').ajaxForm({
			success: Category.saveCategory,
			target: '#categoryListContainer'
			});
		Category.initColorPicker();
       	},
	
	initColorPicker : function (){
		$('#colorPicker').ColorPicker({
			flat: true,
			color: $('input[name=color]').val(),	
			onSubmit: function(hsb, hex, rgb) {
				$('span.pickColor').css('backgroundColor', '#' + hex);
				$('input[name=color]').val(hex);
				$('#colorPicker').fadeOut();
				}
			});
		
		$('#colorPicker>div').css('position', 'relative');
		var widt = false;
		$('span.pickColor').add('input[name=color]').bind('click', function() {
			if ( widt) {
				$('#colorPicker').show();
				}
			else {
				$('#colorPicker').hide();
			}
			widt = !widt;
			});
		$('#colorPicker').hide();
			  },

	append: function(selector){
		      $(selector).append('<span name="category' + this.id + '">id:' + this.id + ' Farbe: ' + this.color);
		     },
	Category: function (id,name,color,comment,par,readonly){
	this.id=id;
	this.name=name;
	this.color=color;
	this.comment=comment;
	this.par=new Array();
	for(var i=0;i< par.length;i++){
		this.par[i]=new Parent.Parent(par[i],true);
	}
	this.readonly=readonly;
	this.show=true;
	this.display=false;
	}
	
}
Category.Category.prototype.setParent = function(Par,push){
		for (var i=0; i<this.par.length;i++){
			if (this.par[i].id==Par.id) {
				this.par[i]=Par;
				return true;
			}
		}
		if (push==true) this.par.push(Par);
		return false;
	};

Category.Category.prototype.getParent = function(idPar){
		for (var i=0; i<this.par.length;i++){
			if (this.par[i].id==idPar) return this.par[i];
		}
		return false;
	};

var Parent= {
	Parent: function (id,open){
		this.open = open;
		this.id = id
	}
}

var Annotation = {
	annoList: new Object(),
	annoListCat: new Object(),
	zIndex: new Array(),
	unselectText: function(){
	    },
	initzIndex: function(){
		Annotation.zIndex= new Array();
		var paper = $('#paperText');
		var paperTop= 0;
		if (paper.offset()) paperTop= paper.offset().top;
		var x=paper.height();
		var tmp = document.createElement("span");
		var id = document.createAttribute("id");
		id.nodeValue = "testlineheight";
		tmp.setAttributeNode(id);
		var blank = document.createTextNode("&nbsp;");
		tmp.appendChild(blank);

		    //var y=paper.append('<span id="testlineheight">&nbsp;</span>');
		    var y=paper.append(tmp).find('span#testlineheight').outerHeight();
		    paper.find('span#testlineheight').remove();
		    var lines = Math.abs(x/y)+15;
		   for (var i = 0; i < lines; i++){
			   Annotation.zIndex[i]= new Array();
		   } 
		   var paperB = $('td#paperBanner');
		   while (Annotation.zIndex.length >= lines) {Annotation.zIndex.pop();}
		   $.each(Annotation.annoList, function(i){
			   var anno = Annotation.annoList[i];
			   Annotation.annoList[i].depth=0;
			   if (anno.show){ 
				   for (var j=Math.abs(Math.round(anno.orgTop/y)); j < Math.abs((anno.orgTop + anno.height)/y); j++)
					{
						if (Annotation.zIndex[j])Annotation.zIndex[j].push(i);
					}
				}
		   });
		   for (var i = 0; i < Annotation.zIndex.length; i++){
			   if (Annotation.zIndex[i].length == 1)continue;
			   for (var j= 1; j<Annotation.zIndex[i].length;j++){
				   id = Annotation.zIndex[i][j];
				   if (Annotation.annoList[id].depth < j){
					   Annotation.annoList[id].depth = j;
				   }
			   }
		   }
		   $.each(Annotation.annoList, function(i){
			   var anno = Annotation.annoList[i];
			   if (anno.show){ 
				   var annoId = paperB.find('div#annotationId'+anno.id);
				   var annoTop = (1*annoId.css('top').replace(/[^\,\.\-0-9]/g, ""))-(anno.depth*4);
				   var annoLeft = (anno.depth*4);
				   var annoZ = (255 - anno.depth);
				   annoId.css({'top':annoTop,'left':annoLeft,'z-index':annoZ});
			  }
		   });

		   
	    },
	initMouseEvent: function(){
			var papText=$('td#paperText');
			papText.find('c').bind('mouseup',Annotation.mouseFixUp)
				.bind('mousedown',Annotation.mouseFixDown)
				.bind('dblclick',Annotation.mouseDblClick);
			papText.find('span[name^=sentenceId]').bind('mouseup',Annotation.mouseUp)
				.bind('mousedown',Annotation.mouseDown);
		      $.each(this.annoList, function(i){
			if (Annotation.annoList[i].show){
				papText.find('span[name=annotationId'+Annotation.annoList[i].id+']').click(Annotation.bannerClick)
					.bind('mouseup',Annotation.mouseUp)
					.bind('mousedown',Annotation.mouseDown);
				$('td#paperBanner div[rel=annotationId'+Annotation.annoList[i].id+']').click(Annotation.bannerClick);
				$('td#paperBanner div[id=annotationBannerId'+Annotation.annoList[i].id+']').click(Annotation.bannerClick);
			}
		      });
			papText.bind('mouseup',Annotation.mouseUp)
				.bind('mousedown',Annotation.mouseDown);
		},
	mouseFixUp : function (evt){
			$('div#paper > div#paperHidden').find('input[name=selectedTextMouseDblClick]').val('1');
			if ($(evt.currentTarget).attr('r')){
				$('div#paper > div#paperHidden').find('input[name=selectedTextEndId]').val('c'+$(evt.currentTarget).attr('r'));
			}
		    },
	mouseDblClick : function (evt){
			$('div#paper > div#paperHidden').find('input[name=selectedTextMouseDblClick]').val('0');
			if ($(evt.currentTarget).attr('r')){
				$('div#paper > div#paperHidden').find('input[name=selectedTextStartId]').val('c'+$(evt.currentTarget).attr('r'));
			}
			evt.stopPropagation();
			evt.stopImmediatePropagation();
		    },
	mouseFixDown : function (evt){
			$('div#paper > div#paperHidden').find('input[name=selectedTextMouseDblClick]').val('1');
			if ($(evt.currentTarget).attr('r')){
				$('div#paper > div#paperHidden').find('input[name=selectedTextStartId]').val('c'+$(evt.currentTarget).attr('r'));
			}
		    },
	mouseUp : function (evt){
			Annotation.mouseUpDown(evt,'Up');
			evt.stopPropagation();
		    },
	mouseDown : function (evt){
			Annotation.mouseUpDown(evt,'Down');
			evt.stopPropagation();
		    },
	mouseUpDown : function (evt,upDown){
			var id = "";
			var idPrev = "";
			var idLastTag = "";
			var sib = $(evt.currentTarget);
			if ( evt.originalEvent && evt.originalEvent.explicitOriginalTarget) sib = $(evt.originalEvent.explicitOriginalTarget);
			var sibText = sib.text();
			if ($(evt.currentTarget).attr('name')) id=$(evt.currentTarget).attr('name').substring(12);
			else {
				if (evt.originalEvent.rangeParent){
					var s = $(evt.originalEvent.rangeParent);
					var x = s.prev('span, td');
					while (s.prev().length > 0 && s.prev('span, td').length == 0) s=s.prev();
						if (s.prev('span, td').attr('name')) idPrev=s.prev('span, td').attr('name').substring(12);
				}
			}
			var paperHidden= $('div#paper > div#paperHidden');
			paperHidden.find('input[name=selectedTextMouse'+upDown+']').val(id);
			paperHidden.find('input[name=selectedTextMouse'+upDown+'Prev]').val(idPrev);
			paperHidden.find('input[name=selectedTextMouse'+upDown+'Sib]').val(sibText);
			if (upDown == 'Up')Annotation.selectText(evt);
		    },
	selectText: function(evt,data){
		var paperHidden= $('div#paper > div#paperHidden');
		if (window.getSelection) {
			paperHidden.find('input[name=selectedText]').val(window.getSelection());
			var range = window.getSelection().getRangeAt(0);
			paperHidden.find('input[name=selectedTextStartOffset]').val(range.startOffset);
			paperHidden.find('input[name=selectedTextEndOffset]').val((range.endOffset));
		} else if (document.getSelection) {
			paperHidden.find('input[name=selectedText]').val(document.getSelection());
			var range = document.getSelection().getRangeAt(0);
			paperHidden.find('input[name=selectedTextStartOffset]').val(range.startOffset);
			paperHidden.find('input[name=selectedTextEndOffset]').val((range.endOffset));
		} else if (document.selection) {
			var range = document.selection.createRange()
			paperHidden.find('input[name=selectedText]').val(range.text);
			paperHidden.find('input[name=selectedTextStartOffset]').val(range.startOffset);
			paperHidden.find('input[name=selectedTextEndOffset]').val((range.endOffset));
		}
	    },
	resizeWindow: function(){
		var heightBody = $(window).height()-$('div#Footer').height()-$('table#annotationMain').offset().top-20;
		$('div#paper , div#categoryList').height(heightBody);
	},
	displayBanner: function() {
			var parentCon = $('td#paperText');
			var topParent = parentCon.offset().top;

			$.each(Category.catList, function (i){
				if (Category.catList[i].display && Annotation.annoListCat[i]) { 
					for(var k = 0; k< Annotation.annoListCat[i].length; k++){
						var anno=Annotation.annoList[Annotation.annoListCat[i][k]];

						var annoSpan = parentCon.find('span[name=annotationId'+anno.id+']');
						if (annoSpan.length == 0) {
							Annotation.annoList[Annotation.annoListCat[i][k]].show=false;
						}
						else {
							//TODO if (!Annotation.annoList[i].show) {
							Annotation.annoList[Annotation.annoListCat[i][k]].show=true;
							annoSpan.click(Annotation.bannerClick)
								.bind('mouseup',Annotation.mouseUp)
								.bind('mousedown',Annotation.mouseDown);
							anno.top = "";
							anno.bottom= ""
							$.each(annoSpan,function (i){
								var aEl = $(this);
								if (aEl.offset().top && aEl.height()){
									if (anno.top=="" || aEl.offset().top<anno.top) anno.top=aEl.offset().top;
									if (anno.bottom=="" || (aEl.offset().top + aEl.height())>anno.bottom) anno.bottom = aEl.offset().top+aEl.height();
									}
							});
							anno.height = anno.bottom-anno.top;
							Annotation.annoList[Annotation.annoListCat[i][k]].orgTop=anno.top-topParent;
							Annotation.annoList[Annotation.annoListCat[i][k]].height=anno.height;
							$('td#paperBanner div[rel=annotationId'+anno.id+']').click(Annotation.bannerClick);
							$('td#paperBanner div[id=annotationBannerId'+anno.id+']').click(Annotation.bannerClick);
							var divAnnoId = $('td#paperBanner > div#annotationId'+anno.id);
							divAnnoId.css({'top': 0+'px','height':anno.height+'px'});
							anno.top  = anno.top-divAnnoId.offset().top;
							divAnnoId.css({'top': anno.top+'px','height':anno.height+'px'});
							divAnnoId.find('div[rel=annotationId'+anno.id+'].annotationBannerBalken').css({'height':anno.height+'px','background-color': '#'+Category.catList[anno.categoryId].color});
							divAnnoId.find('div[rel=annotationId'+anno.id+'].annotationBannerStrich').css({'top': (anno.height/2-2)+'px','background-color': '#'+Category.catList[anno.categoryId].color});
							divAnnoId.find('div#annotationBannerId'+anno.id).css({'top': (anno.height/2-12)+'px','background-color': '#'+Category.catList[anno.categoryId].color});
						}
					}
				}
			});

		},
	indentBanner: function() {
			      Annotation.initzIndex();
		      },
	bannerClick: function(evt){
			     	Annotation.mouseUpDown(evt,'Up');
				var sib = $(evt.currentTarget);
				var id= "";
				if (sib.is('div[rel^=annotationId]')) id = sib.attr('rel');
				if (sib.is('span[name^=annotationId]')) id = sib.attr('name');
				id = id.substr(12);
				if (sib.is('div[id^=annotationBannerId]')) {
					id = sib.attr('id');
					id = id.substr(18);
				}
			     var maxBanner = 0;
			      $.each(Annotation.annoList, function(i){
				if (Annotation.annoList[i].show){
					var zindex = $('div#annotationId'+Annotation.annoList[i].id).css('z-index');
					if (Math.abs(zindex) > Math.abs(maxBanner)) maxBanner=zindex;
				}
			      });
			      maxBanner++;
				$('div#annotationId'+id).css('z-index',maxBanner);
				evt.stopPropagation();
				if (sib.is('div[id^=annotationBannerId]') && !isMessageBox) Annotation.editWindow(id);
		     },
	createAnnotation: function(text,offset,length,id){
				  var Artikel = $("#paperText").text();
				  var index = 0;
				  var find = Array();
				  var last =0;
				  while (last != -1){
					  last=Artikel.indexOf(text,last);
					  if (last != -1) {
						  find.push(last);
						  last++;
					  }
				  }
			  },
	abortAnnotation: function(){ 
		       messageBoxHide();
			Annotation.resizeWindow();
		       }
		,
	getAnno: function(id){
		return Annotation.annoList[id];
		},
	saveAnnotation: function (text,stat){
		if (stat!='success') alert ('konnte nicht Speichern');
		if (text.stat) alert ("konnte Annotation " +text.id + ' nicht speichern');
		else Annotation.annoList=text;
		messageBoxHide();
		Annotation.resizeWindow();

	},
	deleteAnnotation: function (id){
		var scrollLast = $('div#paper').scrollTop();
		if (!confirm("Wollen Sie die  Annotation mit der id "+id+" wirklich löschen?")) return;
		$.get("indexAjax.php",{'nav':'DeleteAnnotation','annoId':id},function (data,stat){
			if (stat!='success') return alert ('konnte nicht Löschen');
				$('#paper').replaceWith(data);
			messageBoxHide();
			Annotation.resizeWindow();
			$('#paperText').bind("mouseup",function(evt,data) {
				Annotation.selectText(evt,data);
			});
			Category.displayBanner();
			Category.waitDisplay();
			Annotation.initMouseEvent();
			Annotation.resizeWindow();
			$('div#paper').scrollTop(scrollLast);
			});
	},
	editWindow: function (id){
		anno = Annotation.getAnno(id);
		cat = Category.getCat(anno.categoryId);
		$('span.messageBox').html('<span class="color" style="background-color:#' + cat.color+'">&nbsp;</span>'
			+ ' Annotation id: ' + anno.id+' <br /><hr />'
			+ '<form action="indexAjax.php" method="GET" id="annotationEditForm">'
			+ '<table>'
			+ '<tr><td>id:</td><td align="left">' + anno.id+'</td></tr>'
			+ '<tr><td>Kategorie:</td><td align="left">' + cat.name+'</td></tr>'
			+ '<tr><td>Bearbeiter:</td><td align="left"><input type="text" name="user" value="' + anno.user+'" /></td></tr>'
			+ '<tr><td>Kommentar:</td><td align="left"><textarea name="comment">' + anno.comment+'</textarea></td></tr>'
			+ '<tr><td>Letzte Bearbeitung:</td><td align="left">' + anno.lastEdit+'</td></tr>'
			+ '</table>'
			+ '<input type="hidden" value="SaveAnnotation" name="nav" />'
			+ '<input type="hidden" value="' + anno.id + '" name="annoId" />'
			+ '<input type="submit" value="Speichern" /><input type="button" value="Abbrechen" onClick="Annotation.abortAnnotation()"/>'
			+ '<input type="button" value="Löschen" onClick="Annotation.deleteAnnotation('+ anno.id +')"/>'
			+ '</form>');
		messageBoxShow();
		$('#annotationEditForm').ajaxForm({
			success: Annotation.saveAnnotation,
			dataType: 'json'
			});
       	},
	Annotation : function(id,categoryId,user,comment,lastEdit){
		this.id = id;
		this.categoryId =  categoryId;
		this.user = user;
		this.comment = comment;
		this.lastEdit = lastEdit;
		this.saved = true;
		this.show = false;
		this.zIndex = 255;
		this.orgTop = 255;
		this.height = 255;
		this.depth = 0;
		}
}
