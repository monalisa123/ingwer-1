<?php
defined( '_VALID_INGWER' ) or die( 'Restricted access' );
define(DEBUG,0);

/* Authentifizierung fuer Datenbank und Webserververzeichniss */
$dbhost="localhost";
$dbname="IngwerTest";
$dbuser="IngwerTest";
$dbpass="IngwerTest";

$httpUser="ingwer";
$httpPass="rhizom";

/* Tabellen prefix */
$dbTablePrefix= "";


/* Verzeichnisspfade */
$templatePath = "tpl/";
$uploadPath = "tmp/";
$corpusPath = "/var/www/ingwer/cwbcorpora";

/* Programmpfade */
$treeTagger = "/var/local/trt/cmd/tree-tagger-german-utf8";
$lexico2Xml = "/var/www/ingwer/include/lexico2xml.pl";
$perl = "/usr/bin/perl";
$diff = "/usr/bin/diff -i";
$cwbEncode = "/usr/local/bin/cwb-encode";
$cwbMakeall = "/usr/local/bin/cwb-makeall";

/* zu ueberpruefende Zeichensaetze bei Dateiupload, im Progamm alles utf8 */
$charsetList = array("cp1252","latin1","utf8");

include_once("table.php");
?>
