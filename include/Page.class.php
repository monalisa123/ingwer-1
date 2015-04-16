<?php
/**
* Templatesystem 
*
* Sehr einfaches Templatesystem 
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

require_once('init.php');


/**
* Seitenklasse
*
* ruft das entsprechende Template auf
*
* @category   Template
* @package    Ingwer
* @author     Jochen Koehler <jk@it-devel.de>
* @copyright  2011 semtracks gmbh
* @version    0.8
*/

class Page {

	/**
	 * ein array der die Information fuer die Seite enthaelt, content enthaelt die Infos fuer den Kontent
	 * z.B.: array(	
	 * 	'title'=>'Ingwer 0.8',	
	 * 	'navigation'=>'Verwaltung',	
	 * 	'content'=>array(                  
	 * 		"file" => "Verwaltung",
	 * 		"zusatzVar" => new SearchList()
	 * 		)
	 * 	)
	)
	 */
	var $Data;

	function Page ($Data){
		$this->Data = $Data;
	}

	/**
	 * Druckt Seitet
	 */
	function printPage(){
		global $templatePath;

		$p = $this->Data;
		include($templatePath.'Frame.tpl.php');
	}

	/**
	 * Druckt nur content der Seite
	 */
	function printSubPage(){
		global $templatePath;

		$content = $this->Data['content']; 
		include($templatePath.$content['subFile'].".tpl.php"); 
	}

}
?>
