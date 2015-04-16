<?php
/**
* Ingwer dbupdateList.php  
*
* Wandelt die fehlerhaften Eintraege bei Author in das kompatible list multilistfromat um.
* Die mit ';' separiten Authoren werden in eine Liste von Authoren umgewandelt.
*
* LICENSE: 
*
* @category   Main
* @package    Ingwer
* @author     Jochen Koehler <jk@it-devel.de>
* @copyright  2011 semtracks gmbh
* @version    0.1
*/
define('_VALID_INGWER',"TEST" );
define('DEBUG',"TEST" );
session_start();
require_once('include/init.php');

$level = error_reporting (E_ALL);

// betroffene Authoren
$db->select("author",'id',"name LIKE '%;%'");
$authorList = array();
while ($rS=$db->nextQ()){ $authorList[]=$rS['id'];}

//paper mit diesen autoren
$paperList = array();
$db->select("paper2author",'paper_id',"author_id in ('".implode("','",$authorList)."')");
while ($rS=$db->nextQ()){ $paperList[]=$rS['paper_id'];}

//author in multiliste umwandeln
$db->update("$tbPaperMetaType a",array('a.meta_type'=>'multilist'),"a.table='author'");

foreach ($paperList as $id){
	$paper = new Paper(array('id'=>$id));
	$author = $paper->Data['Autor'];
	$paper->Data['Autor'] = array();
	foreach ($author as $vA){
		foreach (explode(';',$vA) as $vS){
			$paper->Data['Autor'][] = trim($vS);
		}
	}
	$paper->saveMetaData();
}
echo count($paperList)." Artikel aktualisiert.\n ";
if (count($paperList)>0) {
	$paper->MType->cleanList();
	echo " Autorenliste aktualisiert\n";
}

exit(0);
?>
