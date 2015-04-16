<?php
defined( '_VALID_INGWER' ) or die( 'Restricted access' );

require_once('constants.php');
require_once('Db.class.php');

if (!isset($db) || get_class($db) != 'db') {
	$db = new db($dbhost,$dbuser,$dbpass,$dbname);
}

?>
