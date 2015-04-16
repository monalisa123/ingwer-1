<?php
# Test, wo die CWB liegt:
$cwbdirectory;
if (file_exists("cwb/")) {
	$cwbdirectory = "cwb/";
} else if (file_exists("../cwb/")) {
	$cwbdirectory = "../cwb/";
} else {
	$cwbdirectory = "[Corpus Workbench ist nicht installiert]";
}
?>

<div id="Content">
<h1> &nbsp; </h1>
<div id="BigLogo"><img src="images/IngwerLogo.png" alt="" width="450" height="149"><div id="BigLogoVersion"><?=$p['title']?><br/><a href="index.php?nav=Hilfe#Lizenz">Lizenzbestimmungen</a>/<a href="index.php?nav=Hilfe#Impressum">Impressum</a></div></div>

<p>Willkomen bei Ingwer, der Software für Korpusmanagement, Annotation und Analyse!</p>

<ul>
<li><a href="index.php?nav=Verwaltung">Importieren</a> Sie Daten in die Datenbank!</li>
<li><a href="index.php?nav=Suche">Recherchieren</a> Sie in den Daten!</li>
<li><a href="index.php?nav=Lesen">Blättern</a> Sie durch die Daten!</li>
<li>Fügen Sie den Daten <a href="index.php?nav=Annotation">manuelle Annotationen</a> hinzu!</li>
<li>Benutzen Sie die <a href="<? print $cwbdirectory; ?>">Corpus Workbench</a>, um Ihre Daten quantitativ zu analysieren!</li>
<li><a href="index.php?nav=Hilfe">Informieren</a> Sie sich über die weiteren Funktionen von Ingwer!</li>
</ul>

<p class="impressum">Konzeption und Umsetzung: <a href="http://www.semtracks.com">semtracks gmbh</a>, DFG-Projekt "Sprachliche Konstruktionen sozial- und wirtschaftspolitischer Krisen in der BRD seit 1973", <a href="http://www.diskursanalyse.net/wiki.php?wiki=DFG-MeMeDa::DiskursNetz">DiskursNetz</a>; vgl. <a href="index.php?nav=Hilfe#Impressum">Impressum</a>.</p>

</div>
