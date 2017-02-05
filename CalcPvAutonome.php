<script src="./lib/jquery-3.1.1.slim.min.js"></script> 
<?php 
include('./lib/Fonction.php');
$config_ini = parse_ini_file('./config.ini', true); 
if (isset($_GET['submit'])) {
?>
<div class="part result">
	<h2 class="titre">Résultat du dimensionnement</h2>
	<p><b>Avertissement</b>: Les résultats sont donnés à titre indicatif. </p>
	<!-- 
		Les PV
	-->
	<h3>Les panneaux photovoltaïques</h3>
	<div id="resultCalcPv" class="calcul">
		<p>On cherche ici la puissance (crête exprimée en W) des panneaux photovoltaïques à installer pour satisfaire vos besoins en fonction de votre situation géographique. La formule est la suivante : </p>
		<p>Pc = Bj / (Rb X Ri X Ej)</p>
		<ul>
			<li>Pc (Wc) : Puissance crête</li>
			<li>Bj (Wh/j) : Besoins journaliers</li>
			<li>Rb : rendement électrique des batteries</li>
			<li>Ri : rendement électrique du reste de l’installation (régulateur de charge…)</li>
			<li>Ej : rayonnement moyen quotidien du mois le plus défavorable dans le plan du panneau (kWh/m²/j)</li>
			<?php 
			if (empty($_GET['Ej']) && isset($_GET['ZoneId'])) {
				$Ej = $config_ini['irradiation']['zone'.$_GET['ZoneId'].'_'.$_GET['Deg']];
				echo '<ul><li>Vous avez sélectionné la Zone '.$_GET['ZoneId'].' avec un angle de '.$_GET['Deg'].'°, nous allons considérer Ej égale à '.$Ej.'</li></ul>';
			} else {
				$Ej = $_GET['Ej'];
			}
			?>
		</ul>
		<p>Dans votre cas ça nous fait : </p>
		<?php 
		$Pc = convertNumber($_GET['Bj'])/(convertNumber($_GET['Rb'])*convertNumber($_GET['Ri'])*convertNumber($Ej));
		?>
		<p><a class="more" id="resultCalcPvHide">Cacher le calcul</a></p>
		<p>Pc = <?= $_GET['Bj'] ?> / (<?= $_GET['Rb'] ?> * <?= $_GET['Ri'] ?> * <?= $Ej ?>) = <b><?= convertNumber($Pc, 'print') ?> Wc</b></p>
	</div>
	
	<p>Vous avez donc besoin d'une puissance de panneau photovoltaïque équivalente à <b><?= convertNumber($Pc, 'print') ?>Wc</b>.</p>
	<p><a id="resultCalcPvShow">Voir, comprendre la démarche, le calcul</a></p>
	<p>Le budget est estimé entre <?= convertNumber($config_ini['prix']['pv_bas']*$Pc, 'print') ; ?>€ et <?= convertNumber($config_ini['prix']['pv_haut']*$Pc, 'print') ; ?>€ (<a rel="tooltip" class="bulles" title="Pour du matériel neuf, avec un coût estimé de <?= $config_ini['prix']['pv_bas'] ?>€/Wc en fourchette basse & <?= $config_ini['prix']['pv_haut'] ?>€/Wc en haute">?</a>)</p>
	<!-- 
		Les batteries
	-->
	<h3>Les batteries</h3>
	<div id="resultCalcBat" class="calcul">
		<p>On cherche ici la capacité nominale des batteries exprimée en ampères heure (Ah)</p>
		<?php 
		// Si le niveau est débutant on choisie pour lui
		if ($_GET['Ni'] == 1) {
			$_GET['Aut'] = 5;
			$_GET['DD'] = 80;
		} 
		// Si la tension U à été mise en automatique ou que le niveau n'est pas expert
		if ($_GET['U'] == 0 || $_GET['Ni'] != 3) {
			if (convertNumber($Pc) < 800) {
				$U = 12;
			} elseif (convertNumber($Pc) > 1600) {
				$U = 48;
			} else {	
				$U = 24;
			}
		} else {
			$U = $_GET['U'];
		}
		?>
		<p>Cap = (Bj x Aut) / (DD x U)</p>
		<ul>
			<li>Cap (Ah) : Capacité nominale des batteries</li>
			<li>Bj (Wh/j) : Besoins journaliers</li>
			<li>Aut : Nombre de jour d'autonomie (sans soleil)</li>
			<li>DD (%) : <a rel="tooltip" class="bulles" title="Avec la technologie AGM il ne faut pas passer sous le seuil critique des 50%">Degré de décharge maximum</a></li>
			<li>U (V) : <a rel="tooltip" class="bulles" title="En mode automatique la tension des batteries sera déduite du besoin en panneaux<br />De 0 à 800Wc : 12V<br />De 800 à 1600 Wc : 24V<br />Au dessus de 1600 Wc : 48V">Tension finale du parc de batterie</a></li>
		</ul>
		<p>Dans votre cas ça nous fait : </p>
		<?php 
		$Cap = (convertNumber($_GET['Bj'])*convertNumber($_GET['Aut']))/(convertNumber($_GET['DD'])*0.01*convertNumber($U));
		?>
		<p><a class="more" id="resultCalcBatHide">Cacher le calcul</a></p>
		<p>Cap = (<?= $_GET['Bj'] ?> x <?= $_GET['Aut'] ?>) / (<?= $_GET['DD']*0.01 ?> x <?= $U ?>) = <b><?= convertNumber($Cap, 'print') ?> Ah</b></p>
	</div>
	<p>Vous avez donc besoin d'un parc de batteries de <b><?= convertNumber($Cap, 'print') ?> Ah en <?= $U ?> V</b>.</p>
	<p><a id="resultCalcBatShow">Voir, comprendre la démarche, le calcul</a></p>	
	<?php 
	// Config batterie : 
	$meilleurParcBatterie['nbBatterieParallele'] = 99999;
	$meilleurParcBatterie['diffCap'] = 99999;
	$meilleurParcBatterie['nom'] = 'Impossible à déterminer';
	$meilleurParcBatterie['V'] = 0;
	$meilleurParcBatterie['Ah'] = 0;
	foreach ($config_ini['batterie'] as $idBat => $batterie) {
		// En mode auto on utilise les batteires 2V si on est au dessus des 550Ah
		if ($_GET['ModBat'] == 'auto') {
			if ($Cap > 550 && $batterie['V'] >= 12) {
				continue;
			} else if ($Cap < 550 && $batterie['V'] < 12) {
				continue;
			}
		// Si on est en mode manuel on fait le calcul uniquement sur le bon modèl 
		} else if ($_GET['ModBat'] != $idBat) {
			continue;
		}
		// Calcul du nombre de batterie nessésaire 
		// ENT(capacité recherché / capcité de la batterie)+1
		$nbBatterie=intval($Cap / $batterie['Ah'])+1;
		// Capacité déduite
		$capParcBatterie=$batterie['Ah']*$nbBatterie;
		// Différence avec la capacité souhauté
		$diffCap=$capParcBatterie-$Cap;
		// Debug
		//echo '<br />';
		//echo 'Pour '.$batterie['nom'].' ::: '.$nbBatterie ;
		//echo ' | total (Ah)'.$capParcBatterie;
		//echo ' | diff = '.$diffCap;
		if ($nbBatterie < $meilleurParcBatterie['nbBatterieParallele']
		|| $nbBatterie == $meilleurParcBatterie['nbBatterieParallele'] && $diffCap <= $meilleurParcBatterie['diffCap']) {
			# Nouvelle meilleur config
			// Debug
			// echo 'nouvelle meilleur config';
			$meilleurParcBatterie['diffCap'] = round($diffCap);
			$meilleurParcBatterie['nom'] = $batterie['nom'];
			$meilleurParcBatterie['V'] = $batterie['V'];
			$meilleurParcBatterie['Ah'] = $batterie['Ah'];
			$meilleurParcBatterie['nbBatterieParallele'] = $nbBatterie;
			$meilleurParcBatterie['nbBatterieSerie'] = $U/$meilleurParcBatterie['V'];
			$meilleurParcBatterie['nbBatterieTotal'] = $meilleurParcBatterie['nbBatterieSerie'] * $meilleurParcBatterie['nbBatterieParallele'];

		}
	}
	if ($_GET['ModBat'] == 'auto') {
		echo '<p>Une hypothèse de câblage serait d\'avoir <b>'.$meilleurParcBatterie['nbBatterieTotal'].' batterie(s)</b> de type <b>'.$meilleurParcBatterie['nom'].'</b> ce qui pousse la capacité du parc à '.$meilleurParcBatterie['Ah']*$meilleurParcBatterie['nbBatterieParallele'].'Ah :</p>';
	} else {
		echo '<p>Vous avez choisi de travailler avec des batterie(s) de type <b>'.$meilleurParcBatterie['nom'].'</b>. Voici une hypothèse de câblage avec <b>'.$meilleurParcBatterie['nbBatterieTotal'].'</b> de ces batteries ce qui pousse la capacité du parc à '.$meilleurParcBatterie['Ah']*$meilleurParcBatterie['nbBatterieParallele'].'Ah :</p>';
	}
	echo '<ul><li>'.$meilleurParcBatterie['nbBatterieParallele'].' en parallèle(s) (<a rel="tooltip" class="bulles" title="Capacité de la batterie ('.$meilleurParcBatterie['Ah'].'Ah) * '.$meilleurParcBatterie['nbBatterieParallele'].' parallèle(s)">pour une la capacité à '.$meilleurParcBatterie['Ah']*$meilleurParcBatterie['nbBatterieParallele'].'Ah</a>) de '.$meilleurParcBatterie['nbBatterieSerie'].' en série(s) (<a rel="tooltip" class="bulles" title="Tension de la batterie ('.$meilleurParcBatterie['V'].'V) * '.$meilleurParcBatterie['nbBatterieSerie'].' série(s)">pour une tension de '.$U.'V</a>) <a rel="tooltip" class="bulles" target="_blank" title="Pour comprendre le câblage des batteries cliquer ici" href="http://www.solarmad-nrj.com/cablagebatterie.html">?</a></li></ul>';
	?>
	<p>Le budget est estimé entre <?= convertNumber($config_ini['prix']['bat'.$meilleurParcBatterie['V'].'V_bas']*$meilleurParcBatterie['Ah']*$meilleurParcBatterie['nbBatterieParallele']*$meilleurParcBatterie['nbBatterieSerie'], 'print') ; ?>€ et <?= convertNumber($config_ini['prix']['bat'.$meilleurParcBatterie['V'].'V_haut']*$meilleurParcBatterie['Ah']*$meilleurParcBatterie['nbBatterieParallele']*$meilleurParcBatterie['nbBatterieSerie'], 'print') ; ?>€ (<a rel="tooltip" class="bulles" title="Pour du matériel neuf, avec un coût estimé de <?= $config_ini['prix']['bat'.$meilleurParcBatterie['V'].'V_bas'] ?>€/Ah en fourchette basse & <?= $config_ini['prix']['bat'.$meilleurParcBatterie['V'].'V_haut'] ?>€/Ah en haute">?</a>)</p>
	<h3>Le reste de l'équipement</h3>
	<p>Il vous reste encore à choisir :</p>
	<ul>
		<li>Le régulateur de charge : il est entre les batteries et les panneaux, c'est lui qui gère la charge des batteries. Pour le choisir il faut connaître le courant et la tension d’arrivée des panneaux ;</li>
		<li><a href="http://www.solarmad-nrj.com/convertisseur.html">Le convertisseur</a> : il est là pour convertir le signal continu des batteries <?= $U ?>V en signal alternatif 230V. Il se choisit avec le voltage d’entrée (ici <?= $U ?>V venus des batteries) et sa puissance maximum en sortie. Pour la puissance maximum de sortie il faut prendre votre appareil qui consomme le plus ou la somme de la puissance des appareils qui seront allumés en même temps ; </li>
		<li>Le câblage, les éléments de protection...</li>
	</ul>
</div>
<script type="text/javascript">
	$( "#resultCalcPvShow" ).click(function() {
		$( "#resultCalcPv" ).show( "slow" );
		$( "#resultCalcPvShow" ).hide( "slow" );
	});
	$( "#resultCalcPvHide" ).click(function() {
		$( "#resultCalcPv" ).hide( "slow" );
		$( "#resultCalcPvShow" ).show( "slow" );
	});
	$( "#resultCalcBatShow" ).click(function() {
		$( "#resultCalcBat" ).show( "slow" );
		$( "#resultCalcBatShow" ).hide( "slow" );
	});
	$( "#resultCalcBatHide" ).click(function() {
		$( "#resultCalcBat" ).hide( "slow" );
		$( "#resultCalcBatShow" ).show( "slow" );
	});
	$( "#resultCalcPvHide" ).click();
	$( "#resultCalcBatHide" ).click();
</script>
<?php
}
?>
<form method="get" action="#" id="formulaireCalcPvAutonome">
	
	<div class="form Ni">
		<label>Votre degré de connaisance en photovoltaïque : </label>
		<select id="Ni" name="Ni">
			<option value="1"<?php echo valeurRecupSelect('Ni', 1); ?>>Débutant</option>
			<option value="2"<?php echo valeurRecupSelect('Ni', 2); ?>>Eclairé</option>
			<option value="3"<?php echo valeurRecupSelect('Ni', 3); ?>>Expert</option>
		</select>
	</div>
	
	<h2 class="titre vous">Votre consommation :</h2>	
			
		<p>C'est l'étape la plus importante pour votre dimensionnement. Si vous ne connaissez pas cette valeur rendez-vous sur notre <b><a href="<?= $config_ini['formulaire']['UrlCalcConsommation'] ?>&from=CalcPvAutonome" id="DemandeCalcPvAutonome">interface de calcul de besoins journaliers</a></b></p>
		
		<div class="form Bj">
			<label>Vos besoins électriques journaliers :</label>
			<input id="Bj" type="number" min="1" max="99999" style="width: 100px;" value="<?php echo valeurRecup('Bj'); ?>" name="Bj" />  Wh/j
		</div>
		<?php
		function ongletActif($id) {
			if ($_GET['Ej'] != '' && $id == 'valeur') {
				echo ' class="actif"';
			} elseif ($_GET['Ej'] == '' && $id == 'carte') {
				echo ' class="actif"';
			}
		}
		?>

	<div class="part pv">
		<h2 class="titre pv">Dimensionnement des panneaux photovoltaïques</h2>
	
		<p>Rayonnement en fonction de votre situation géographique : </p>
		<ul id="onglets">
			<li<?php echo ongletActif('carte'); ?>>Carte par zone (simple)</li>
			<li<?php echo ongletActif('valeur'); ?>>Valeur</li>
		</ul>
		<div id="contenu">
			
			<div class="modeCarte item">
				<div class="form ZoneId">
					<p>Cette simulation simple part du principe que vous êtes orienté plein sud (0°) sans zone d'ombre :</p>
					<label>Sélectionner votre zone (en fonction de la carte ci-après) : </label>
					<select name="ZoneId">
						<option value="1" style="background-color: #98e84f"<?php echo valeurRecupSelect('ZoneId', 1); ?>>Zone 1 : Lille</option>
						<option value="2" style="background-color: #ccee53"<?php echo valeurRecupSelect('ZoneId', 2); ?>>Zone 2 : Paris, Rennes, Strasbourg</option>
						<option value="3" style="background-color: #f9ef58"<?php echo valeurRecupSelect('ZoneId', 3); ?>>Zone 3 : Nantes, Orléans, Besançon</option>
						<option value="4" style="background-color: #f7cd3a"<?php echo valeurRecupSelect('ZoneId', 4); ?>>Zone 4 : Limoges, Clermont-Ferrand</option>
						<option value="5" style="background-color: #ed8719"<?php echo valeurRecupSelect('ZoneId', 5); ?>>Zone 5 : Lyon, Bordeaux, Toulouse</option>
						<option value="6" style="background-color: #e16310"<?php echo valeurRecupSelect('ZoneId', 6); ?>>Zone 6 : Carcasonne, Aubnas</option>
						<option value="7" style="background-color: #c9490c"<?php echo valeurRecupSelect('ZoneId', 7); ?>>Zone 7 : Montpellier, Nimes, Perpignan</option>
						<option value="8" style="background-color: #b61904"<?php echo valeurRecupSelect('ZoneId', 8); ?>>Zone 8 : Marseille</option>
					</select>
				</div>
				
				<div class="form Deg">
					<label>Donner l'inclinaison des panneaux <a rel="tooltip" class="bulles" title="En site isolé on choisie l'inclinaison optimum pour le pire mois de l'année niveau ensoleillement, en France souvent décembre ~65°">(~65° conseillé)</a></label>
					
					<select name="Deg">
						<option value="35"<?php echo valeurRecupSelect('Deg', 35); ?>>35°</option>
						<option value="65"<?php echo valeurRecupSelect('Deg', 65); ?>>65°</option>
					</select>
				</div>
				<p>Pour plus d'options et de précisions, vous pouvez passer en mode valeur.</p>
			</div>
		
			<div class="modeInput item">
				<div class="form Ej">
					<label>Donner la valeur du rayonnement moyen quotidien du mois le plus défavorable dans le plan (l'inclinaison) du panneau :</label>
					<input maxlength="4" size="4" id="Ej" type="number" step="0.01" min="0" max="10" style="width: 100px;" value="<?php echo valeurRecup('Ej'); ?>" name="Ej" /> kWh/m²/j
					<p>Pour obtenir cette valeur rendez vous sur le site de <a href="http://ines.solaire.free.fr/gisesol_1.php" target="_blank">INES</a>, choisir votre ville, l'inclinaison & l'orientation des panneaux puis valider. Il s'agit ensuite de prendre la plus basse valeur de la ligne "Globale (IGP)" (dernière ligne du second tableau) Plus d'informations en bas de cette page : <a href="http://www.photovoltaique.guidenr.fr/cours-photovoltaique-autonome/VI_calcul-puissance-crete.php">Comment obtenir la valeur de Ei, Min sur le site de l'INES ?</a></p>
				</div>
			</div>
			
		</div>
			
		<div class="form Rb">
			<label>Rendement électrique des batteries : </label>
			<input maxlength="4" size="4" id="Rb" type="number" step="0.01" min="0" max="1" style="width: 70px;" value="<?php echo valeurRecup('Rb'); ?>" name="Rb" />
		</div>
		<div class="form Ri">
			<label>Rendement électrique du reste de l’installation (régulateur de charge…) : </label>
			<input maxlength="4" size="4" id="Ri" type="number" step="0.01" min="0" max="1" style="width: 70px;" value="<?php echo valeurRecup('Ri'); ?>" name="Ri" />
		</div>
	</div>
	
	<div class="part bat">
		<h2 class="titre bat">Dimensionnement du parc de batteries</h2>
		<p>Cette application est pré-paramétrée pour des batteries plomb AGM/Gel/OPzV</p>
		<div class="form Aut">
			<label>Nombre de jours d'autonomie : </label>
			<input maxlength="2" size="2" id="Aut" type="number" step="1" min="0" max="50" style="width: 50px" value="<?php echo valeurRecup('Aut'); ?>" name="Aut" />
		</div>
		<div class="form U">
			<label>Tension finale des batteries : </label>
			<select id="U" name="U">
				<option value="0"<?php echo valeurRecupSelect('U', 0); ?>>Auto</option>
				<option value="12"<?php echo valeurRecupSelect('U', 12); ?>>12</option>
				<option value="24"<?php echo valeurRecupSelect('U', 24); ?>>24</option>
				<option value="48"<?php echo valeurRecupSelect('U', 48); ?>>48</option>
			</select> V <a rel="tooltip" class="bulles" title="En mode automatique la tension des batteries sera déduite du besoin en panneaux<br />De 0 à 800Wc : 12V<br />De 800 à 1600 Wc : 24V<br />Au dessus de 1600 Wc : 48V">(?)</a>
		</div>
		<div class="form DD">
			<label>Degré de décharge : </label>
			<input  maxlength="2" size="2" id="DD" type="number" step="1" min="0" max="100" style="width: 70px" value="<?php echo valeurRecup('DD'); ?>" name="DD" /> %
		</div>
		<div class="form ModBat">
			<label>Modèle de batterie : </label>
			<select id="ModBat" name="ModBat">
				<option value="auto">Auto</option>
				<?php 
				foreach ($config_ini['batterie'] as $batModele => $batValeur) {
					echo '<option value="'.$batModele.'"';
					echo valeurRecupSelect('ModBat', $batModele);
					echo '>'.$batValeur['nom'].'</option>';
					echo "\n";
				}
				?>
			</select> <a rel="tooltip" class="bulles" title="En mode automatique, au dessus de 550A, il sera utilisé des batteries GEL OPzV 2V">(?)</a>
		</div>
	</div>
	
	<div class="form End">
		<input id="Reset" type="button" value="Remise à 0" name="reset" />
		<input id="Submit" type="submit" value="Lancer le calcul" name="submit" />
	</div>
</form>

<div id="CarteZone">
	<a href="./lib/Zone-solar-map-fr.png" target="_blank"><img src="./lib/Zone-solar-map-fr.png" /></a>
</div>

<!-- Détection des changement dans le formulaire -->
<input type="hidden" value="0" id="ModificationDuFormulaire" />

<script type="text/javascript">
// Détection des changement dans le formulaire
$( "input" ).change(function () {
	if ($( "#ModificationDuFormulaire" ).val() == 0) {
		$( "#ModificationDuFormulaire" ).val(1);		
	}
});
$( "select" ).change(function () {
	if ($( "#ModificationDuFormulaire" ).val() == 0) {
		$( "#ModificationDuFormulaire" ).val(1);		
	}
});
$('#DemandeCalcPvAutonome').click(function() {
	if ($( "#ModificationDuFormulaire" ).val() == 1) {
		return confirm("Vous avez commencé à remplir ce formulaire, vous allez perdre ces informations en continuant.");
	}
});

/* infobulles http://javascript.developpez.com/tutoriels/javascript/creer-info-bulles-css-et-javascript-simplement-avec-jquery/ */
$(document).ready(function() {
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

// Bouton Submit activation / désactivation
function sumbitEnable() {
	if ($( "#Bj" ).val() > 0) {
		$( "#Submit" ).prop('disabled', false);
	} else {
		$( "#Submit" ).prop('disabled', true);
	}
}
$( "#Bj" ).change(function() {
	sumbitEnable();
});
sumbitEnable();

// Changement de niveau
$( "#Ni" ).change(function () {
	changeNiveau();
});
function changeNiveau() {
	// Debutant (1)
	if ($( "#Ni" ).val() == 1) {
		$( ".form.Ri" ).hide();
		$( ".form.Rb" ).hide();
		$( ".form.AUT" ).hide();
		$( ".form.U" ).hide();
		$( ".form.DD" ).hide();
		$( ".part.bat" ).hide();
		$( ".form.ModBat" ).hide();
	// Eclaire (2)
	} else if  ($( "#Ni" ).val() == 2) {
		$( ".form.Ri" ).hide();
		$( ".form.Rb" ).hide();
		$( ".form.AUT" ).show();
		$( ".form.U" ).hide();
		$( ".form.DD" ).hide();
		$( ".part.bat" ).show();
		$( ".form.ModBat" ).hide();
	// Expert (3)
	} else if ($( "#Ni" ).val() == 3) {
		$( ".form.Ri" ).show();
		$( ".form.Rb" ).show();
		$( ".form.AUT" ).show();
		$( ".form.U" ).show();
		$( ".form.DD" ).show();
		$( ".part.bat" ).show();
		$( ".form.ModBat" ).show();
	}
}
changeNiveau();

// Onglet carte zone
// http://dmouronval.developpez.com/tutoriels/javascript/mise-place-navigation-par-onglets-avec-jquery/
$(function() {
	$('#onglets').css('display', 'block');
	$('#onglets').click(function(event) {
		var actuel = event.target;
		if (!/li/i.test(actuel.nodeName) || actuel.className.indexOf('actif') > -1) {
			//alert(actuel.nodeName)
			return;
		}
		$(actuel).addClass('actif').siblings().removeClass('actif');
		setDisplay();
		$( "#Ej" ).val('');
	});
	function setDisplay() {
		var modeAffichage;
		$('#onglets li').each(function(rang) {
			modeAffichage = $(this).hasClass('actif') ? '' : 'none';
			$('.item').eq(rang).css('display', modeAffichage);
		});
	}
	setDisplay();
});

// Reset form
$( "#Reset" ).click(function() {
	window.location = 'http://<?php echo $_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"] ?>';
});

</script>

