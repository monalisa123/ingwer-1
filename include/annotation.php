<?
defined( '_VALID_INGWER' ) or die( 'Restricted access' );

require_once('init.php');

$page['content']['catList'] = new CategoryList();
if (isset($_SESSION['paperId'])) $page['content']['paper'] = new Paper(array('id'=>$_SESSION['paperId']),$POS);
else $page['content']['paper'] = false;
$content =  $page['content'];
?>
