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
<div class="help">
<p>Willkomen bei Ingwer, der Software für Korpusmanagement, Annotation und Analyse!</p>

<h2>Inhaltsverzeichnis</h2>

<ul>
<li><a href="#Übersicht">Übersicht</a></li>
<li><a href="#Suche">Suche</a></li>
<li><a href="#Lesen/Bearbeiten">Lesen/Bearbeiten</a></li>
<li><a href="#Annotation">Annotation</a></li>
<li><a href="#Verwaltung">Verwaltung</a></li>
<li><a href="#Impressum">Impressum</a></li>
<li><a href="#Lizenz">Lizenzbestimmungen</a></li>
</ul>

<h2><a name="Übersicht"></a>Übersicht</h2>

<p>Mit Ingwer können Sie Texte in den Formaten "Text", XML und "Lexico 3" importieren und weiter verarbeiten. Beim Import werden die Texte automatisch mit dem <a href="http://www.ims.uni-stuttgart.de/projekte/corplex/TreeTagger/">TreeTagger</a> POS-annotiert (gegenwärtig werden nur Texte in deutscher Sprache verarbeitet). Zusätzlich zu dieser automatischen Annotation können Sie die Texte mit beliebigen Kategorien manuell annotieren. Für die Recherche und Analyse bietet Ingwer verschiedene Suchmöglichkeiten, mit denen die Texte und deren Annotationen durchsucht werden können. Zudem können die annotierten Texte in verschiedene Formate exportiert werden.</p>

<h2><a name="Suche"></a>Suche</h2>

<p>Auf der Seite "Suche" können Sie nach allen Metadaten und Annotationen, die in den Artikeln vorkommen, suchen. Das + vor der jeweiligen Kategorie bedeutet, dass das gewählte Kriterium im Text vorkommen muss. Mit einem Klick auf das + wird das Zeichen in ein – geändert. Dann darf der Text das gewählte Kriterium nicht enthalten. Wählen Sie also beispielsweise beim Metadatum "Zeitung" eine bestimmte Zeitung aus, werden bei einem + alle Artikel aus der gewählten Zeitung gefunden. Bei einem – hingegen alle Artikel, die nicht in der gewählten Zeitung erschienen sind.</p>

<p>Das Resultat der Suche wird unterhalb des Suchformulars in einer Liste "Ergebnisse" präsentiert. Ein Klick auf eine Ergebniszeile öffnet den Artikel auf der Annotations-Seite.</p>

<p>Sie können eine Suche speichern und zu einem späteren Zeitpunkt wieder laden. Damit ist es möglich, bestimmte Teilkorpora zu bilden.</p>

<h3>Erweiterte Suchmöglichkeiten im Feld "Textkörper"</h3>

<p>Das Suchfeld "Textkörper" bietet weitergehende Suchmöglichkeiten: Sie können
mit regulären Ausdrücken und Bool'schen Operatoren arbeiten, sowie Part-of-Speech-Kategorien in die Suche miteinbeziehen.</p>

<p><b>Bool'sche Operatoren</b>
<table border="0">
<tr><td>UND</td><td><i>Merkel UND CDU</i> Texte, die sowohl <i>Merkel</i> als auch <i>CDU</i> enthalten</td></tr>
<tr><td>ODER</td><td><i>Merkel ODER CDU</i> Texte, die entweder <i>Merkel</i> oder <i>CDU</i> (oder beides) enthalten</td></tr>
<tr><td>UND NICHT</td><td><i>Merkel UND NICHT CDU</i> Texte, die <i>Merkel</i> aber nicht <i>CDU</i> enthalten</td></tr>
</table>
</p>

<p><b>Reguläre Ausdrücke</b></p>
<p>Die regulären Ausdrücke sind nach Standard <a href="http://pubs.opengroup.org/onlinepubs/009695399/toc.htm">POSIX 1003.2</a> implementiert. Eine kurze Übersicht zur Sprache findet sich <a href="http://www.bubenhofer.com/korpuslinguistik/kurs/index.php?id=regexp.html">hier</a>. In Ergänzung zu den regulären Ausdrücken kann der Operator <i>POS=xxx</i> verwendet werden, um nach Wortarten/Part-of-Speech-Informationen zu suchen.
<table border="0">
<tr><td><nobr>sagt\w+</nobr></td><td><i>sagt</i> mit allen möglichen Endungen</td></tr>
<tr><td><nobr>sagt\w+ POS=ART</nobr></td><td><i>sagt</i> mit allen möglichen Endungen gefolgt von einem Artikel</td></tr>
<tr><td><nobr>sagt\w+ POS=(NN|ART)</nobr></td><td><i>sagt</i> mit allen möglichen Endungen gefolgt von einem Nomen oder einem Artikel</td></tr>
<tr><td><nobr>sagt\w+ POS=[^NA].+</nobr></td><td><i>sagt</i> mit allen möglichen Endungen nicht gefolgt von einer Wortart die mit N oder A beginnt (also keine Artikel, Adjektive, Adverben und Nomen)</td></tr>
<tr><td><nobr>\bsagt\b</nobr></td><td>genau das Wort <i>sagt</i> ohne weitere Zeichen davor oder danach</td></tr>
</table>
</p>

<p><b>Bitte beachten Sie:</b> Die Suchausdrücke im Textkörper-Suchfeld werden prinzipiell als Teilstrings interpretiert. D.h., die Suche nach <i>sagt</i> findet auch <i>versagt, versagte, sagte, sagten</i> etc. Wenn Sie die Suche genau auf das Wort beschränken wollen, müssen Sie den regulären Ausdruck \b verwenden, das für eine Wortgrenze steht: <i>\bsagt\b</i></p>


<h2><a name="Lesen/Bearbeiten"></a>Lesen/Bearbeiten</h2>

<p>Auf der Seite "Lesen/Bearbeiten" sehen Sie alle Texte, die momentan aktiviert sind. Aktiv sind entweder alle Texte in der Datenbank oder das Suchresultat der letzten Suche.</p>

<p>Sie können bestehende Texte und Metadaten verändern. Klicken Sie dazu auf den Knopf "Bearbeiten" unterhalb der Metaangaben. Nun können Sie alle Felder beliebig ändern.</p>

<p>Bitte beachten Sie: Wenn im Artikeltext bereits manuelle Annotationen vorgenommen wurden, kann es bei Veränderungen im Text zu ungewollten Verschiebungen der Annotation kommen. Dies ist insbesondere der Fall, wenn genau die Textpassagen verändert werden, die annotiert sind. Bitte überprüfen Sie deshalb nach einer Änderung am Textkörper, ob die Annotationen noch korrekt sind.</p>

<h2><a name="Annotation"></a>Annotation</h2>

<p>Auf der Seite "Annotation" können Sie nach einem beliebigen Kategorien-System Annotationen vornehmen. Grundsätzlich werden, wie auf der Seite "Lesen/Bearbeiten", alle Texte des letzten Suchresultats dargestellt. Wurde vorher keine Suche gemacht, werden alle Texte dargestellt.</p>

<p>Sie können nun in der rechten Spalte beliebige Kategorien erstellen, mit denen Sie im Text beliebige Passagen auszeichnen wollen. Um eine Kategorie zu erstellen, klicken Sie auf "Kategorie erstellen". Es öffnet sich ein Eigenschaftenfenster, in dem Sie die Bezeichnung der Kategorie, eine Bemerkung dazu, die Position in der Hierarchie der Kategorien und die Farbe festlegen können. Um die Position in der Hierarchie festzulegen, wählen Sie einfach ein Element aus, das Elternelement der aktuellen Kategorie sein soll. Wenn die gewählte Kategorie auf der obersten Ebene angesiedelt werden soll, lassen Sie die Einstellung auf "Wurzel". Eine Kategorie kann Kindelement von beliebig vielen Elementen sein. Sie können also über "weiteres Elternelement" die Zugehörigkeit zu weiteren Elementen festlegen. Sie können das Eigenschaftenfenster zu einer Kategorie jederzeit aufrufen, wenn Sie auf das kleine Dreieck neben der Kategorienbezeichnung klicken.</p>

<p>Um eine beliebige Textpassage zu annotieren, markieren Sie die gewünschte Textpassage und klicken anschließend auf die Kategorie, mit der die Passage ausgezeichnet werden soll. Wenn das Häkchen neben der Kategorie gesetzt ist, werden die Textpassagen, die mit dieser Kategorie ausgezeichnet sind, im Text farblich markiert. Zudem verweist eine T-Markierung am Seitenrand auf die Position der Annotation.</p>

<p>Mit einem Klick auf die T-Markierung können Sie zur gewählten Annotation eine Bemerkung hinzufügen. Achtung: Im Gegensatz zu einer Bemerkung, die Sie einer Kategorie zuordnen, indem Sie das Eigenschaftenfenster der Kategorie aufrufen, gilt eine Bemerkung zur Annotation nur für die einzelne Annotation im Text.</p>

<p>Im Feld "Bearbeiter" rechts oben kann ein beliebiger Name eingetragen werden. Wenn eine Annotation hinzugefügt wird, wird der dort angegebene Name automatisch in das Eigenschaftenfenster der Annotation übertragen.</p>

<p>Annotationen im Text können sich beliebig überschneiden, teilweise oder ganz überlappen und können beliebige Textmengen umfassen: Buchstaben, Teilwörter, Wörter, Teilsätze, Sätze, Absätze etc. Mit den Häkchen neben den Kategorien in der Kategorienliste rechts können Sie steuern, welche Kategorien im Text angezeigt werden sollen.</p>

<h2><a name="Verwaltung"></a>Verwaltung</h2>

<p>Auf der Seite "Verwaltung" können Sie Texte importieren und exportieren.</p>

<p>Um Texte zu importieren, gehen Sie wie folgt vor: Wählen Sie die gewünschte Datei aus, wählen Sie das Format und klicken Sie auf "importieren". Zu den Formaten:</p>

<ul>
<li class="help">Text strukturiert: Das ist ein projektspezifisches Text-Format</li>
<li class="help">Lexico 3: Format der Korpusanalysesoftware <a href="http://www.tal.univ-paris3.fr/lexico/lexico3.htm">Lexico 3</a></li>
<li class="help">XML: Ein beliebiges XML-Format<br/>Bei diesem Format können Sie im Anschluss wählen, welche XML-Felder importiert und welchen Datenbankfeldern zugeordnet werden sollen.</li>
</ul>

<p>Sie haben zudem die Möglichkeit, bereits in der Datenbank vorhandene Texte zu überschreiben, wenn es sich um Duplikate handelt. Setzen Sie dazu das Häkchen bei der Option "Duplikate überschreiben". Achtung: Dabei gehen auch allenfalls bereits gemachte Annotationen des überschriebenen Textes verloren.</p>

<p>Im Feld "Statusmeldungen" wird angezeigt, ob der Import problemlos verlief, oder ob bestimmte Fehler aufgetreten sind.</p>

<p>Um Texte zu exportieren wählen Sie auf der rechten Seitenhälfte aus, welche Datensätze Sie exportieren wollen. Sie können alle Datensätze, die Datensätze der letzten Suche oder die Datensätze einer vorher gespeicherten Suche exportieren.</p>

<p>Wählen Sie anschließend das Format:</p>

<ul>
<li class="help">XML: Die Texte und Annotationen werden in einem generischen XML-Format gespeichert.</li>
<li class="help">Corpus Workbench: Die Texte werden in einem für den Import mit der Corpus Workbench optimierten XML-Format gespeichert.</li>
<li class="help">Corpus Workbench inkl. Indizierung: Die Texte werden exportiert und in die Corpus Workbench importiert (sofern eine CWB-Installation auf dem Server verfügbar ist). Danach kann das Korpus auch in das Web-Interface der CWQ, CQPweb, importiert werden (vgl. Ausführungen unten).</li>
<li class="help">Lexico 3: Die Texte werden für die Weiterverarbeitung mit Lexico 3 gespeichert.</li>
<li class="help">CSV: Die Texte werden im Comma Separated-Values-Format gespeichert, um die Texte und ihre Metadaten z.B. in einer Tabellenkalkulation weiterverarbeiten zu können. Anstelle von Komma kann auch Strichpunkt, Tabulator oder ein beliebiges anderes Zeichen als Feldtrenner gewählt werden. Die Datensätze werden mit den Zeichen-Encoding UTF-8 gespeichert. Wahlweise können die Zeilenschaltungen im Textkörper-Feld entfernt und die Feldnamen in der ersten Zeile mit abgespeichert werden. Im Text vorgenommene Annotationen werden in diesem Format nicht mit exportiert.
</ul>

<h4>Export für die Corpus Workbench/CQPweb</h4>

<p>Werden Texte in das Corpus Workbench-Format exportiert unter Verwendung der Option "inkl. Indizierung" steht das Korpus danach in der CWB auf dem Server zur Verfügung (sofern eine CWB-Installation auf dem Server verfügbar ist). Auf diesem Server ist die <a href="<?php print $cwbdirectory; ?>">Corpus Workbench hier installiert</a>. Hierzu einige Erläuterungen:</p>

<ul>
<li class="help">Wählen Sie als Export-Funktion "Corpus Workbench" mit der Option "inkl. Indizierung" und wählen Sie einen Namen für das Korpus. Der Name darf nur aus Buchstaben und Zahlen in Kleinschreibung bestehen, Umlaute und Sonderzeichen sind nicht erlaubt. <b>Achtung:</b> Besteht bereits ein gleichnamiges CWB-Korpus auf dem Server, wird es überschrieben!</li>
<li class="help">Die Option "einzelne Metadaten und die Kategorien zu kategorialen Daten konvertieren" stellt für die angeklickten Felder sicher, dass die Inhalte so konvertiert werden, dass sie später in CQPweb als kategoriale Daten behandelt werden können: Im Formular "Create metadata table from corpus XML annotations" in CQPweb kann für diese Felder die Kategorie "Classification" statt "Free text" ausgewählt werden.</li>
<li class="help">Nach dem Export kann über die Shell auf das CWB-Korpus zugegriffen werden: 
<pre>cqp -eC
show;</pre>
Auswahl des Korpus:
<pre>TESTKORPUS;</pre>
Suche nach dem Lemma "gehen":
<pre>[lemma="gehen"];</pre>
Vgl. für weiter Informationen zur Suchsyntax die <a href="http://cwb.sourceforge.net/documentation.php">Dokumentation</a>.</li>
<li class="help">Zusätzlich ist es möglich, das indizierte CWB-Korpus in das Web-Interface CQPweb zu importieren. Dazu sind folgende Schritte notwendig:</li>
<ol>
<li class="help">Öffnen Sie die Administrator-Oberfläche von CQPweb: <a href="<?php print $cwbdirectory; ?>adm"><?php print $cwbdirectory; ?>adm</a> (erkundigen Sie sich ggf. beim Administrator dieser Ingwer-Installation über das Passwort).</li>
<li class="help">Wählen Sie links im Menü "Install new corpus".</li>
<li class="help">Klicken Sie ganz oben auf den Link "<a href="<?php print $cwbdirectory; ?>adm/index.php?thisF=installCorpusIndexed&uT=y">Click here to install a corpus you have already indexed in CWB</a>."</li>
<li class="help">Geben Sie in die drei Felder "MySQL name", "full name" und "CWB name" den oben beim Export gewählten Korpusnamen ein. Bei "full name" können Sie auch einen aussagekräftigeren Namen verwenden.</li>
<li class="help">Lassen Sie alle anderen Optionen in der Standardeinstellung und wählen Sie ein beliebiges Stylesheet.</li>
<li class="help">Klicken Sie auf "Install corpus with settings above".</li>
<li class="help">Nach der Meldung "Your corpus has been successfully installed!" können Sie nun über den Link "Design and insert a text-metadata table for the corpus" die Metadaten indizieren. Auf der nun folgenden Seite "Admin tools for managing corpus metadata" scrollen Sie ganz nach unten und wählen den Link "Click here to install metadata from within-corpus XML annotation."</li>
<li class="help">Jetzt werden alle verfügbaren Metadaten auf Textebene dargestellt. Wählen Sie alle aus und entscheiden Sie bei jedem Feld, ob der Typus eine "Classification" oder "Free Text" ist. Die Regeln für "Classification" sind streng und sollten nur für Kategorien wie m/w (männlich/weiblich) oder dergleichen verwendet werden.</li> 
<li class="help">Belassen Sie bei großen Textmengen (über 1000 Texte) die Voreinstellung zum "frequency list setup" auf "No thanks", andernfalls bei "Yes please" und klicken Sie auf "Create metadata table form XML using the settings above".</li>
<li class="help">Falls Sie auf der Seite davor "No thanks" angeklickt haben, müssen Sie nun auf der folgenden Seite die Schaltflächen "Generate CWB text-position records", "Populate word and file counts", "Create CWB frequency table" und "Create frequency tables" unter "other metadata controls" klicken. Das kann bei großen Korpora teilweise lang gehen. Bei sehr großen Korpora können diese Befehle über die Kommandozeile direkt auf dem Server ausgelöst werden (vgl. Hinweise zum Script "../lib/offline-freqlists.php" in der <a href="http://cwb.svn.sourceforge.net/viewvc/cwb/gui/cqpweb/trunk/CQPweb-setup-manual.html">Dokumentation von CQPweb</a> unter "Installing a corpus").</li>
<li class="help">Wählen Sie nun im Menü links unter "Admin tools" noch die Seite "Manage annotation": Hier sollten Sie zuerst unten unter "Annotation metadata" für die Handles "lemma" und "pos" zumindest unter Description eine sprechende Bezeichnung geben, z.B. "Lemma" und "POS" und jeweils in der Spalte "Update?" mit "Go" speichern. Dann wählen Sie oben unter "Annotation setup for CEQL queries..." unter "Primary annotation" den eben so benannten Eintrag "POS" und unter "Secondary annotation" den Eintrag "Lemma" aus und klicken auf "Update annotation settings".</li>
<li class="help">Wählen Sie dann im Menü links "Standard query", um mit den Abfragen beginnen zu können. Informationen zur Bedienung von CQPweb finden Sie auch in <a href="http://www.bubenhofer.com/korpuslinguistik/kurs/index.php?id=cwb_start.html">Noah Bubenhofers Online-Einführung in die Korpuslinguistik</a>.</li>
</ol>
</ul>

<hr/>

<h3><a name="Impressum"></a>Impressum</h3>

<p>Ingwer ist eine Software der semtracks gmbh. Die Software wurde entwickelt in Zusammenarbeit mit dem DFG-Projekt "<a href="http://www.uni-trier.de/index.php?id=41375">Sprachliche Konstruktionen sozial- und wirtschaftspolitischer 'Krisen' in der BRD von 1973 bis heute</a>" (Leitung: Martin Wengeler und Alexander Ziem) und <a href="http://www.diskursanalyse.net/wiki.php?wiki=DFG-MeMeDa::DiskursNetz">DiskursNetz</a> (vertreten durch Johannes Angermüller) unter Mitarbeit von Kristin Kuck, David Römer, Ronny Scholz und Alexander Ziem.</p>

<p>Finanzierung: DFG – Deutsche Forschungsgemeinschaft; Universität Mainz</p>

<h4>Lizenzbestimmungen</h4>

<p>Die Software wird über die Server der semtracks gmbh gegen Registrierung vertrieben und darf nicht über andere Vertriebskanäle an die Projektmitglieder verteilt werden. Die Software darf zudem ohne Einwilligung der semtracks gmbh nicht an Dritte weiterverkauft oder kostenlos weitergegeben werden. Der Quellcode der Software darf ohne Einwilligung der semtracks gmbh nicht verändert werden.</p>

</div>
</div>
