<?php
/**
* Haupttempalte Ingwer 
*
* enthaelt den Htmlrahmen mit einbindung der Stylesheets und javascriptklassen.
* -ruft das  Navigationstemplate ($templatePath.Navigation.tpl.php) auf
* -setzt $content von $p['content'] und ruft das entsprechnde Kontenttemplate auf.
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

echo '<?xml version="1.0" ?>' . "\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?=$p['title']?> - <?=$p['navigation']?></title>
<link rel="shortcut icon" type="image/x-icon" href="http://localhost/semtracks/tpl/Ingwer_32x32.ico"/>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Language" content="de"/>
<meta name="language" content="deutsch"/>
<meta name="revisit-after" content="14 days"/>
<meta name="robots" content="index, follow"/>
<meta name="author" content="Jochen Koehler"/>
<meta name="publisher" content="Jochen Koehler"/>
<meta name="company" content="semtracks gmbh"/>
<link rel="Stylesheet" media="all" type="text/css" href="css/Ingwer.css"/>
<link rel="Stylesheet" media="all" type="text/css" href="css/colorpicker.css"/>
<script type="text/javascript" src="js/jquery-1.5.1.js"></script>
<script type="text/javascript" src="js/jquery.json-2.4.js"></script>
<script type="text/javascript" src="js/jquery.form.js"></script>
<script type="text/javascript" src="js/jquery.cookie.js"></script>
<script type="text/javascript" src="js/colorpicker.js"></script>
<script type="text/javascript" src="js/function.js"></script>
<script type="text/javascript" src="js/json2.js"></script>
<script type="text/javascript" src="js/category.js"></script>
</head>

<body>
<div id="jsActive">Bitte Javascript Aktivieren!</div>
<script type="text/javascript">
document.getElementById("jsActive").style.display = 'none';
</script>
<script type="text/javascript">
/*$(document).ready(function() {
	//	$('a').click(function(evt) { javascriptNavi(evt);});
);
});*/
function javascriptNavi(elem){
	elem.preventDefault();
	var e = $(elem.currentTarget);
	$.ajaxSetup({async: true});
	$('#application').load(e.attr('href')+ ' #application',function(html,status,xhr){$('a').click(function(evt) { javascriptNavi(evt);});});
}

</script>
<div id="application">
<!--Navigation Start-->
<? include($templatePath."Navigation.tpl.php"); ?>
<!--Navigation End-->
	<div id="message" style="display:none;">
		<span class="messageBox">
		</span>
	<div id="colorPicker"></div>
	</div>
	<div id="overlay" style="display:none;">
	</div>
<div id="wait" style="display:none;"></div>
<!--Content Start-->
<?
$content = $p['content']; 
include($templatePath.$p['content']['file'].".tpl.php"); 
?>
 
<!--Content End-->
<!--Footer Start-->
<div id="Footer">&nbsp; </div>
<!--Footer End-->
</div>
</body>
</html>

