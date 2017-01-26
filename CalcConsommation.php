<script src="./lib/jquery-3.1.1.slim.min.js"></script> 
<?php 
include('./lib/Fonction.php');
$config_ini = parse_ini_file('./config.ini', true); 
?>

<p>Pour connaître la consommation (en Watt) de vos appareils électriques vous pouvez : 
<ul>
	<li>Regarder sur la notice, sur l'appareil lui même parfois ;</li>
	<li>Vous procurer un Wattmètre (~10€) il se branche entre la prise et votre appareil et vous indique précisément la consommation ;</li>
</ul></p>
<p>Pour dimensioner une installation photovoltaïque autonome, réfléchissez comme si c'était l'hiver (le temps d'éclairage est plus long par exemple...) :</p>
<form>
<table>
	<tr>
		<th>Equipement</th>
		<th>Puissance (Watt)</th>
		<th>Nombre</th>
		<th>Temps d'utilisation quotidien</th>
		<th>Consommation quotidienne</th>
		<th>.</th>
	</tr>
	
</table>

<p id="resultatConsoTotal">Consommation quotidienne totale : <b><span id="ConsoTotal">0</span> Wh/j</b>
<br /><a href="" id="hrefCalcPvAutonome">Indiquer cette valeur comme "Besoins journaliers" pour le <br />
calcul de votre installation photovoltaïque autonome</a></p>

<p><input type="button" class="add" value="Ajouter une ligne vide" /> 
<select id="addEquiModele" name="addEquiModele">
<option value="0">Ajouter une ligne selon un modèle...</option>
<?php 
foreach ($config_ini['equipement'] as $equipement) {
	echo '<option value="'.$equipement['conso'].'">'.$equipement['nom'].'</option>';
}
?>
</select> <a rel="tooltip" class="bulles" title="Les valeurs des modèles sont des estimations indicatives, afin d'être précis nous vous encourageons à vous procurer un Wattmètre afin de mesurer la consommation de chacun de vos appareils">?</a></p>

<input type="hidden" value="0" name="equiIncrement" id="equiIncrement" />

<p>
	<input type="button" value="Partager/Mémoriser ce tableau" id="share" />
	<!--<input type="submit" value="Sauvegarder ce tableau" id="save" />-->
</p>
</form>

<?php
// Si la provenance est du formulaire de CalPvAutonome 
$goCalcPvAutonome=$config_ini['formulaire']['UrlCalcPvAutonome'];
if (isset($_GET['from']) && $_GET['from'] == 'CalcPvAutonome' && isset($_SERVER['HTTP_REFERER'])) {
	$goCalcPvAutonome = $_SERVER['HTTP_REFERER'];
	// On retir le Bj (besoin journalier de l'URL Refer si il est présent
	if (preg_match('#Bj=[0-9]+#', $_SERVER['HTTP_REFERER'])) {
		$goCalcPvAutonome=preg_replace('/Bj=[0-9]+/', '', $_SERVER['HTTP_REFERER']);
	}	
}
echo '<input type="hidden" value="'.$goCalcPvAutonome.'" id="goCalcPvAutonome" />';
?>

<script type="text/javascript">
function ajoutUneLigne() {
	$('#equiIncrement').val(parseInt($('#equiIncrement').val(),10)+1);
	$('table').append( 
        [
		'<tr>', 
			'<td>', 
				'<input type="text" value="Equipement ' + $('#equiIncrement').val() + '" name="EquiNom' + $('#equiIncrement').val() + '" id="EquiNom' + $('#equiIncrement').val() + '" />', 
			'</td>', 
			'<td>', 
				'<input class="Puis" onChange="calcTableau();" type="number"  style="width: 80px;" value="0" min="0" max="99999" name="EquiPuis' + $('#equiIncrement').val() + '" id="EquiPuis' + $('#equiIncrement').val() + '" />W', 
			'</td>', 
			'<td>', 
				'<input class="Nb" onChange="calcTableau();"  type="number" style="width: 60px;" value="1"  min="1" max="99" name="EquiNb' + $('#equiIncrement').val() + '" id="EquiNb' + $('#equiIncrement').val() + '" />', 
			'</td>', 
			'<td>', 
				'<select class="Uti" onChange="calcTableau();"  id="EquiUti' + $('#equiIncrement').val() + '" name="EquiUti' + $('#equiIncrement').val() + '">', 
					'<option value="0">0</option>',
					'<option value="0.08">5 m</option>', 
					'<option value="0.25">15 m</option>', 
					'<option value="0.5">30 m</option>', 
					'<option value="0.75">45 m</option>', 
					'<option value="1">1 H</option>', 
					'<option value="1.5">1 H 30</option>', 
					'<option value="2">2 H</option>', 
					'<option value="2.5">2 H 30</option>', 
					'<option value="3">3 H</option>', 
					'<option value="4">4 H</option>', 
					'<option value="5">5 H</option>', 
					'<option value="6">6 H</option>', 
					'<option value="7">7 H</option>', 
					'<option value="8">8 H</option>', 
					'<option value="9">9 H</option>', 
					'<option value="10">10 H</option>', 
					'<option value="11">11 H</option>', 
					'<option value="12">12 H</option>', 
					'<option value="16">16 H</option>', 
					'<option value="20">20 H</option>', 
					'<option value="24">24 H</option>', 
				'</select>', 
			'</td>', 
			'<td>', 
				'<p><span id="EquiTotal' + $('#equiIncrement').val() + '">0</span> Wh/j <a rel="tooltip" class="bulles" title="Le calcul est : Puissance (W) * Temps (en heure) * Nombre = Whj (Watt Heure Jour)">?</a></p>', 
			'</td>', 
			'<td>', 
				'<img src="./lib/trash.png" width="30" class="remove" />', 
			'</td>', 
		'</tr>'
        ].join('') //un seul append pour limiter les manipulations directes du DOM
    );  
    
}

// Ajout d'une ligne dans le tableau
$('.add').on('click', function() {    
	ajoutUneLigne();
	calcTableau();
});
// Suppression d'une ligne dans le tableau
$('table').on('click', '.remove', function() {
   var $this = $(this);
   $this.closest('tr').remove();   
   calcTableau(); 
});

// Ajout d'un modèle d'équipement
$('#addEquiModele').change(function() {
	if ($('#addEquiModele').val() != 0) {
		ajoutUneLigne();
		$( '#EquiNom'+$('#equiIncrement').val()).val($('#addEquiModele option:selected').html());
		$( '#EquiPuis'+$('#equiIncrement').val()).val($('#addEquiModele').val());
		$('#addEquiModele').val(0);
	}
});


// Re-calcule le tableau
function calcTableau() {
	var ConsoTotal = 0;
	for (var idEqui = 1; idEqui <= parseInt($('#equiIncrement').val(),10); idEqui++) {
		var ConsoEqui = 0;
		if ($( '#EquiNom'+idEqui).length) {
			ConsoEqui=parseInt($('#EquiPuis'+idEqui).val(),10)*parseInt($('#EquiNb'+idEqui).val(),10)*$('#EquiUti'+idEqui).val(),10;
			$( '#EquiTotal'+idEqui).text(ConsoEqui);
			//console.log('Equipement ' + idEqui + ' conso = ' + ConsoEqui);
			ConsoTotal = ConsoTotal + ConsoEqui;
		}
	}
	//console.log('Conso total : ' + ConsoTotal);
	$( '#ConsoTotal').text(ConsoTotal);
	if ($('#goCalcPvAutonome').val().indexOf('\?') == '-1') {
		$('#hrefCalcPvAutonome').attr('href', $('#goCalcPvAutonome').val()+'?Bj='+ConsoTotal);
	} else {
		$('#hrefCalcPvAutonome').attr('href', $('#goCalcPvAutonome').val()+'&Bj='+ConsoTotal);
	}
}

// Bouton de partage
$('#share').on('click', function() {
	// Liste le formulaire 
	var URLconstruction = '?';
	var nbPourDeVrai=0;
	for (var idEqui = 1; idEqui <= parseInt($('#equiIncrement').val(),10); idEqui++) {
		if ($( '#EquiNom'+idEqui).length) {
			nbPourDeVrai++;
			URLconstruction=URLconstruction + 'EquiNom'+nbPourDeVrai+'=' + $('#EquiNom'+idEqui).val()+'&EquiPuis'+nbPourDeVrai+'=' + $('#EquiPuis'+idEqui).val()+'&EquiNb'+nbPourDeVrai+'=' + $('#EquiNb'+idEqui).val()+'&EquiUti'+nbPourDeVrai+'=' + $('#EquiUti'+idEqui).val()+'&';
		}
	}
	
	<?php
	if (isset($_GET['p'])) {
		echo 'URLconstruction=URLconstruction+\'&p='.$_GET['p'].'\';';
	}
	if (isset($_GET['from']) && $_GET['from'] == 'CalcPvAutonome') {
		echo 'URLconstruction=URLconstruction+\'&from=CalcPvAutonome\';';
	}
	?>
	URLconstruction=URLconstruction+'&equiIncrement='+parseInt($('#equiIncrement').val(),10);
	
	prompt('Copier l\'adresse internet ci-après gardez là précieusement ou partager là...', window.location.protocol+'//'+window.location.hostname+window.location.pathname+encodeURI(URLconstruction));
});

// init
$(document).ready(function() {
	// Ajout de la première ligne
	ajoutUneLigne();
	
	<?php
	// Si il y a du get...
	if (isset($_GET['equiIncrement'])) {
		for ($i = 1; $i < $_GET['equiIncrement']; $i++) {
			echo 'ajoutUneLigne();';
		}
		foreach ($_GET as $getkey => $getval) {
			if (preg_match('#^Equi#', $getkey)) {
				echo '$("#'.$getkey.'").val("'.$getval.'");';
			}
		}
	}
	?>
	calcTableau();
	
	/* infobulles http://javascript.developpez.com/tutoriels/javascript/creer-info-bulles-css-et-javascript-simplement-avec-jquery/ */
	
    // Sélectionner tous les liens ayant l'attribut rel valant tooltip
    $('a[rel=tooltip]').mouseover(function(e) {
		// Récupérer la valeur de l'attribut title et l'assigner à une variable
		var tip = $(this).attr('title');   
		// Supprimer la valeur de l'attribut title pour éviter l'infobulle native
		$(this).attr('title','');
		// Insérer notre infobulle avec son texte dans la page
		$(this).append('<div id="tooltip"><div class="tipBody">' + tip + '</div></div>');    
		// Ajuster les coordonnées de l'infobulle
		$('#tooltip').css('top', e.pageY + 10 );
		$('#tooltip').css('left', e.pageX + 20 );
		// Faire apparaitre l'infobulle avec un effet fadeIn
	}).mousemove(function(e) {
		// Ajuster la position de l'infobulle au déplacement de la souris
		$('#tooltip').css('top', e.pageY + 10 );
		$('#tooltip').css('left', e.pageX + 20 );
	}).mouseout(function() {
		// Réaffecter la valeur de l'attribut title
		$(this).attr('title',$('.tipBody').html());
		// Supprimer notre infobulle
		$(this).children('div#tooltip').remove();
	});
}); 



</script>


