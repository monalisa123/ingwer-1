<div id="Navigation">
<ul>
	<li class="<?= ($p['navigation']=="Start") ? "navActiv": "navPassiv"?>"><a href="?nav=Start">Start</a></li>  
	<li class="<?= ($p['navigation']=="Suche") ? "navActiv": "navPassiv"?>"><a href="?nav=Suche">Suche</a></li>  
	<li class="<?= ($p['navigation']=="Lesen") ? "navActiv": "navPassiv"?>"><a href="?nav=Lesen">Lesen/Bearbeiten</a></li>  
	<li class="<?= ($p['navigation']=="Annotation") ? "navActiv": "navPassiv"?>"><a href="?nav=Annotation">Annotation</a></li>  
	<li class="<?= ($p['navigation']=="Verwaltung") ? "navActiv": "navPassiv"?>"><a href="?nav=Verwaltung">Verwaltung</a></li>  
	<li class="<?= ($p['navigation']=="Hilfe") ? "navActiv": "navPassiv"?>"><a href="?nav=Hilfe">Hilfe/Impressum</a></li> 
</ul>
<?if(isset($p['user'])):?> 
<span class="userInput"> Bearbeiter: <input type="text" size="15" name="user" value="<?=$p['user']?>"/></span>
<?endif?>
</div>
