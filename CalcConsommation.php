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
<p>Pour dimensioner une installation photovoltaïque autonome, réfléchissez comme si c'était l'hiver (le temps d'éclairage est plus long par exemple, le réfrigérateur est peut être débranché du fait qu'il fait déjà froid dehors...) :</p>
<ul>
	<li><a href="http://calcpvautonome.zici.fr/?EquiNom1=Ordinateur%20Portable&EquiPuis1=45&EquiNb1=1&EquiUti1=6&EquiPmax1=1&EquiNom2=Aspirateur%20Classe%20A&EquiPuis2=700&EquiNb2=1&EquiUti2=0.25&EquiNom3=Ampoule%20Led&EquiPuis3=7&EquiNb3=4&EquiUti3=7&EquiNom4=Machine%20%C3%A0%20coudre&EquiPuis4=100&EquiNb4=1&EquiUti4=0.5&EquiNom5=Mini%20cha%C3%AEne%20(musique)&EquiPuis5=16&EquiNb5=1&EquiUti5=6&EquiPmax5=1&EquiNom6=Recharge%20t%C3%A9l%C3%A9phone%20portable&EquiPuis6=6&EquiNb6=1&EquiUti6=2&EquiPmax6=1&EquiNom7=Pompe%20immerg%C3%A9e&EquiPuis7=400&EquiNb7=1&EquiUti7=0.5&EquiPmax7=1&&p=CalcConsommation&equiIncrement=7">Premier exemple sobre</a></li>
	<li><a href="http://calcpvautonome.zici.fr/?EquiNom1=Aspirateur%20Classe%20A&EquiPuis1=700&EquiNb1=1&EquiUti1=0.25&EquiPmax1=1&EquiNom2=Cong%C3%A9lateur%20Bahut%20200L%20Classe%20A&EquiPuis2=370&EquiNb2=1&EquiUti2=24&EquiTotalInput2=800&EquiPmax2=1&EquiNom3=Ampoule%20%C3%A0%20incandescence&EquiPuis3=75&EquiNb3=3&EquiUti3=8&EquiPmax3=1&EquiNom4=Ordinateur%20de%20bureau%20+%20%C3%A9cran&EquiPuis4=140&EquiNb4=1&EquiUti4=0&EquiNom5=T%C3%A9l%C3%A9phone%20fixe&EquiPuis5=2&EquiNb5=1&EquiUti5=24&EquiTotalInput5=48&EquiPmax5=1&EquiNom6=Box%20Internet&EquiPuis6=20&EquiNb6=1&EquiUti6=24&EquiTotalInput6=480&EquiPmax6=1&EquiNom7=T%C3%A9l%C3%A9&EquiPuis7=70&EquiNb7=1&EquiUti7=0&EquiPmax7=1&EquiNom8=Rasoir&EquiPuis8=5&EquiNb8=1&EquiUti8=0.08&&p=CalcConsommation&from=CalcPvAutonome&equiIncrement=8">Deuxième exemple</a></li>
</ul>
<form>
<table>
	<tr>
		<th>Equipement</th>
		<th>Puissance (Watt)</th>
		<th>Allumer<br />simultanément</th>
		<th>Nombre</th>
		<th>Temps d'utilisation<br /> quotidien</th>
		<th>Calcul consommation<br /> automatique</th>
		<th>Consommation <br />quotidienne</th>
		<th>.</th>
	</tr>
	
</table>

<p id="resultatConsoTotal">Vos besoins électriques journaliers : <b><span id="ConsoTotal">0</span> Wh/j</b>
<br />Votre besoin en puissance électrique maximum : <b><span id="PmaxTotal">0</span> W</b> '<a rel="tooltip" class="bulles" title="Somme de la puissance des appareils suceptiblent d`êtres allumés en même temps OU valeur de l'appareil le plus puissant si celui-ci n'est pas branché en même temps que le reste">?</a>
<br /><a href="" id="hrefCalcPvAutonome">Cliquer ici pour indiquer ces valeurs pour le calcul
<br /> de votre installation photovoltaïque autonome</a></p>

<p><input type="button" class="add" value="Ajouter une ligne vide" /> 
<select id="addEquiModele" name="addEquiModele">
<option value="0">Ajouter une ligne selon un modèle...</option>
<?php 
foreach ($config_ini['equipement'] as $equipement) {
	echo '<option value="'.$equipement['conso'].'|'.$equipement['uti'].'|'.$equipement['consoJ'].'">'.$equipement['nom'].'</option>';
}
?>
</select> <a rel="tooltip" class="bulles" title="Les valeurs des modèles sont des estimations indicatives, afin d'être précis nous vous encourageons à vous procurer un Wattmètre afin de mesurer la consommation de chacun de vos appareils">?</a></p>

<!-- hidden -->
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
		'\n<tr>', 
			'<td>', 
				'<input type="text" value="Equipement ' + $('#equiIncrement').val() + '" name="EquiNom' + $('#equiIncrement').val() + '" id="EquiNom' + $('#equiIncrement').val() + '" />', 
			'</td>', 
			'<td>', 
				'<input class="Puis" onChange="calcTableau();" type="number"  style="width: 80px;" value="0" min="0" max="99999" name="EquiPuis' + $('#equiIncrement').val() + '" id="EquiPuis' + $('#equiIncrement').val() + '" />W', 
			'</td>', 
			'<td>', 
				'<input class="EquiPmax" onChange="calcTableau();" type="checkbox" name="EquiPmax' + $('#equiIncrement').val() + '" id="EquiPmax' + $('#equiIncrement').val() + '" checked="checked" />',
				'<a rel="tooltip" class="bulles" title="Coché tous les appareils suceptiblent d`êtres allumés en même temps (exemple : un ordinateur, le réfrigérateur, l`ampoule du salon. Par contre la perceuse non, préférez débrancher votre ordinateur si vous avez à l\'allumer">?</a>',
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
				'<input onChange="calcTableau();" class="AutoEquiTotal" type="checkbox" name="AutoEquiTotal' + $('#equiIncrement').val() + '" id="AutoEquiTotal' + $('#equiIncrement').val() + '" checked="checked" />',
				'<a rel="tooltip" class="bulles" title="Coché : le calcul de consommation quotidienne se fera automatiquement<br />Décoché : la consommation quotidienne est à indiquer (pratique pour un réfrigérateur qui est branché 24/24 mais qui ne consomme pas sa puissance 24/24, il s\'alume uniquement si la température monte à l`intérieur">?</a>',
			'</td>', 
			'<td>', 
				'<p>',
					'<input onChange="calcTableau();"  class="EquiTotal" step="0.01" type="number"  style="width: 80px;" value="0" min="0,01" max="99999" name="EquiTotalInput' + $('#equiIncrement').val() + '" id="EquiTotalInput' + $('#equiIncrement').val() + '" />',
					'<span id="EquiTotal' + $('#equiIncrement').val() + '">0</span>', 
					' Wh/j <a rel="tooltip" class="bulles" title="Le calcul est : Puissance (W) * Temps (en heure) * Nombre = Whj (Watt Heure Jour)">?</a>',
				'</p>', 
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
		// Split les data du select
		var ModeleData = $('#addEquiModele').val().split('|');
		// Nom
		$( '#EquiNom'+$('#equiIncrement').val()).val($('#addEquiModele option:selected').html());
		// Conso
		$( '#EquiPuis'+$('#equiIncrement').val()).val(ModeleData[0]);
		// Utilisation
		if (ModeleData[1] != '') {
			$( '#EquiUti'+$('#equiIncrement').val()).val(ModeleData[1]);
		}
		if (ModeleData[2] != '') {
			$('#AutoEquiTotal'+$('#equiIncrement').val()).prop('checked', false)
			$('#EquiTotalInput'+$('#equiIncrement').val()).val(ModeleData[2])
		}
		
		$('#addEquiModele').val(0);
		calcTableau();
	}
});


// Re-calcule le tableau
function calcTableau() {
	var ConsoTotal = 0;
	var PmaxTotal = 0;
	var PmaxNbEqui = 0;
	var PmaxEquiRecord = 0;
	for (var idEqui = 1; idEqui <= parseInt($('#equiIncrement').val(),10); idEqui++) {
		var ConsoEqui = 0;
		if ($( '#EquiNom'+idEqui).length) {
			// Consommation 
			ConsoEqui=parseInt($('#EquiPuis'+idEqui).val(),10)*parseInt($('#EquiNb'+idEqui).val(),10)*$('#EquiUti'+idEqui).val(),10;
			// Automatique
			if ($('#AutoEquiTotal'+idEqui).is(':checked')) {
				$( '#EquiTotalInput'+idEqui).hide();
				$( '#EquiTotal'+idEqui).show();
				$( '#EquiTotalInput'+idEqui).val(ConsoEqui);
				$( '#EquiTotal'+idEqui).text(ConsoEqui);
				$( '#EquiNb'+idEqui).prop('disabled',false);
				$( '#EquiUti'+idEqui).prop('disabled',false);
			// Manuel
			} else {
				//console.log('Equipement ' + idEqui + ' Mode Manuel');
				$('#EquiTotalInput'+idEqui).show();
				$('#EquiTotal'+idEqui).hide();
				$('#EquiTotal'+idEqui).text(ConsoEqui);
				$('#EquiNb'+idEqui).prop('disabled',true);
				$('#EquiUti'+idEqui).prop('disabled',true);
				ConsoEqui=parseInt($('#EquiTotalInput'+idEqui).val());
			}
			//console.log('Equipement ' + idEqui + ' conso = ' + ConsoEqui);
			ConsoTotal = ConsoTotal + ConsoEqui;
			// Si 24/24, on coche alumage simultané
			if ($('#EquiUti'+idEqui).val() == '24') {
				$('#EquiPmax'+idEqui).prop('checked', true);
			}
			// alumage simultané coché : : 
			if ($('#EquiPmax'+idEqui).is(':checked')) {
				//console.log('Puissance Max coché !');
				PmaxTotal = PmaxTotal + parseInt($('#EquiPuis'+idEqui).val(),10);
				PmaxNbEqui++;
			}
			// Nouveau record de consommation ?
			if (PmaxEquiRecord < parseInt($('#EquiPuis'+idEqui).val(),10)) {
				PmaxEquiRecord = parseInt($('#EquiPuis'+idEqui).val(),10)
			}
		}
	}
	//console.log('Conso total : ' + ConsoTotal);
	//console.log('Puissance Max total : ' + PmaxTotal);
	$( '#ConsoTotal').text(ConsoTotal);
	if (PmaxNbEqui == 0 || PmaxTotal < PmaxEquiRecord) {
		PmaxTotal = PmaxEquiRecord
	}
	$( '#PmaxTotal').text(PmaxTotal);
	if ($('#goCalcPvAutonome').val().indexOf('\?') == '-1') {
		$('#hrefCalcPvAutonome').attr('href', $('#goCalcPvAutonome').val()+'?Bj='+Math.round(ConsoTotal)+'&Pmax='+Math.round(PmaxTotal));
	} else {
		$('#hrefCalcPvAutonome').attr('href', $('#goCalcPvAutonome').val()+'&Bj='+Math.round(ConsoTotal)+'&Pmax='+Math.round(PmaxTotal));
	}
}

// Bouton de partage
$('#share').on('click', function() {
	// Liste le formulaire 
	var URLconstruction = '?';
	var nbPourDeVrai=0;
	for (var idEqui = 1; idEqui <= parseInt($('#equiIncrement').val(),10); idEqui++) {
		if ($('#EquiNom'+idEqui).length) {
			nbPourDeVrai++;
			URLconstruction=URLconstruction + 'EquiNom'+nbPourDeVrai+'=' + $('#EquiNom'+idEqui).val()+'&EquiPuis'+nbPourDeVrai+'=' + $('#EquiPuis'+idEqui).val()+'&EquiNb'+nbPourDeVrai+'=' + $('#EquiNb'+idEqui).val()+'&EquiUti'+nbPourDeVrai+'=' + $('#EquiUti'+idEqui).val()+'&';
			if (!$('#AutoEquiTotal'+idEqui).is(':checked')) {
				URLconstruction=URLconstruction + 'EquiTotalInput'+nbPourDeVrai+'=' + $('#EquiTotalInput'+idEqui).val()+'&';
			}
			if (!$('#EquiPmax'+idEqui).is(':checked')) {
				URLconstruction=URLconstruction + 'EquiPmax'+nbPourDeVrai+'=0&';
			}
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
	URLconstruction=URLconstruction+'&equiIncrement='+nbPourDeVrai;
	
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
				if (preg_match('#^EquiTotalInput#', $getkey)) {
					// On cherche l'ID de l'équipement
					preg_match("/[0-9]+$/", $getkey, $idEqui);
					echo '$("#AutoEquiTotal'.$idEqui[0].'").prop(\'checked\', false);';
					echo '//console.log("#'.$idEqui[0].'");';
				}
				if (preg_match('#^EquiPmax#', $getkey)) {
					// On cherche l'ID de l'équipement
					preg_match("/[0-9]+$/", $getkey, $idEqui);
					echo '$("#EquiPmax'.$idEqui[0].'").prop(\'checked\', false);';
				}
			}
		}
	}
	?>
	calcTableau();

}); 



</script>


