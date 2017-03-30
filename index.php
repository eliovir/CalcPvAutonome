<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
	<title>[CalcPvAutonome] Calculer/dimensionner son installation photovoltaïque isolé (autonome)</title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link href="./lib/style.css" media="screen" rel="stylesheet" type="text/css" />	
	<meta http-equiv="Pragma" content="no-cache">
	<meta http-equiv="Expires" content="-1">
	<script> 
	<!-- https://www.browser-update.org/ -->
	var $buoop = {vs:{i:10,f:40,o:-8,s:8,c:50},api:4}; 
	function $buo_f(){ 
	 var e = document.createElement("script"); 
	 e.src = "//browser-update.org/update.min.js"; 
	 document.body.appendChild(e);
	};
	try {document.addEventListener("DOMContentLoaded", $buo_f,false)}
	catch(e){window.attachEvent("onload", $buo_f)}
	</script>
	<script src="./lib/jquery-3.1.1.slim.min.js"></script> 
</head>
<body>
	<div id="page-wrap">
		<?php
		$HelpMe='<p style="padding : 5px 10px; background : #FFFF99; border : 1px dotted #FFCC33;">Ce logiciel libre et colaboratif est en recherche de contributeur. N\'hésitez donc pas à donner votre avis sur la méthode de calucl, l\'orthographe, les idée de fonctionnalités qui vous manque, la couleur qui vous pique les yeux ect... Envoyez tout ça par email à : <a href="http://david.mercereau.info/contact/" target="_blank">calcpvautonome(arobase)zici.fr</a> (changer (arobase par le "@") ou directement sur le <a href="https://github.com/kepon85/CalcPvAutonome">dépôt Github</a></p>';
		if (isset($_GET['p']) && $_GET['p'] == 'CalcConsommation') {
			echo '<h1>Calculer ces besoins électriques journalier</h1>';
			echo $HelpMe;
			include('./CalcConsommation.php'); 
		} else {
			echo '<h1>Calculer/dimensionner son installation photovoltaïque isolé (autonome)</h1>';
			echo $HelpMe;
			include('./CalcPvAutonome.php'); 
		}
		?>
		<div id="footer">
            <p class="footer_right">Par <a href="http://david.mercereau.info/">David Mercereau</a> (<a href="https://github.com/kepon85/CalcPvAutonome">Dépôt github</a>)</p>
            <p class="footer_left">CalcPvAutonome version 0.4 est un logiciel libre sous <a href="https://fr.wikipedia.org/wiki/Beerware">Licence Beerware</a></p>
        </div>
	</div>
	<div id="bg">
		<img src="http://calcpvautonome.zici.fr/lib/solar-panel-1393880_1280.png" alt="">
	</div>
	<?php @include_once('./footer.php'); ?>
</body>
</html>
