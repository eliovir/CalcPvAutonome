<?php 
// Les fonctions
include('./lib/Fonction.php');
// Les données du site InesSolaire
include('./ines.solaire/FormData.php'); 
// Le fichier de config
$config_ini = parse_ini_file('./config.ini', true); 

// Les bules d'aides 
$aideInclinaison='(<a onClick="window.open(\'http://ines.solaire.free.fr/pages/inclinaison.htm\',\'Z\',\'status=no ,scrollbars=no,width=350,height=350,top=50,left=50\')">?</a>)';
$aideOrientation='(<a onClick="window.open(\'http://ines.solaire.free.fr/pages/orientation.htm\',\'Z\',\'status=no ,scrollbars=no,width=350,height=350,top=50,left=50\')">?</a>)';
$aideAlbedo='(<a onClick="window.open(\'http://ines.solaire.free.fr/pages/albedo.htm\',\'Albedo\',\'status=no ,scrollbars=no,width=450,height=700,top=0,left=50\')">?</a>)';

/*
 * ####### Résultat #######
*/

if (isset($_GET['submit'])) {
	echo '<div class="part result">';
	// Détection des erreurs de formulaires
	$erreurDansLeFormulaire['nb']=0;
	
	$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('Bj', 'Le besoin journalier n\'est pas correct car < 0');
	$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('Pmax', 'Le besoin en puissance maximum n\'est pas correct car < 0');
	if ($_GET['ModPv'] == 'perso') {
		$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('PersoPvV', 'La tension du panneau personnalisé n\'est pas correct car < 0');
		$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('PersoPvW', 'La puissance du panneau personnalisé n\'est pas correct car < 0');
		$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('PersoPvVdoc', 'La tension en circuit ouvert (Vdoc) du panneau personnalisé n\'est pas correct car < 0');
		$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('PersoPvIsc', 'Le courant de court circuit (Isc) du panneau personalisé n\'est pas correct car < 0');
	}
	if ($_GET['ModBat'] == 'perso') {
		$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('PersoBatV', 'La tension de la batterie personnalisée n\'est pas correct car < 0');
		$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('PersoBatAh', 'La capacité de la batterie personnalisée n\'est pas correct car < 0');
	} elseif ($_GET['ModBat'] == 'auto') {
		// Assure la compatibilité avant cette fonctionnalitée
		if (empty($_GET['TypeBat'])) {
			$_GET['TypeBat'] = 'auto';
		}
	}
	if ($_GET['ModRegu'] == 'perso') {
		$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('PersoReguVmaxPv', 'La tension du régulateur personnalisé n\'est pas correct car < 0');
		$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('PersoReguPmaxPv', 'La puissance du régulateur personnalisé n\'est pas correct car < 0');
		$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('PersoReguImaxPv', 'Le courant de court-circuit du régulateur personnalisé n\'est pas correct car < 0');
	}
	$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('Aut', 'Le nombre de jours d\'autonomie n\'est pas correct car < 0');
	$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('Rb', 'Le rendement électrique des batteries n\'est pas correct car < 0');
	$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('Ri', 'Le rendement électrique de l\'installation n\'est pas correct car < 0');
	$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('DD', 'Le degré de décharge n\'est pas correct car < 0');
	$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('reguMargeIcc', 'La marge de sécurité Icc du régulateur de charge n\'est pas correcte car < 0');
	$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('distancePvRegu', 'La distance entre les panneaux et le régulateur n\'est pas correcte car < 0');
	$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('distanceReguBat', 'La distance entre le régulateur et les batteries n\'est pas correcte car < 0');
	$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('cablageRho', 'La résistivité du conducteur n\'est pas correcte car < 0');
	$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('cablagePtPourcent', 'La chute de tension tolérable n\'est pas correcte car < 0');
	// Assure la compatibilité avant cette fonctionnalitée
	if (empty($_GET['cablageRegleAparMm'])) {
		$_GET['cablageRegleAparMm'] = $config_ini['formulaire']['cablageRegleAparMm'];
	}
	// S'il faut utilise la base INES Solaire (en mode automatique
	if (empty($_GET['Ej'])) {
		// Connect DB
		try {
			if (preg_match('/^sqlite/', $config_ini['irradiation']['db'])) {
				$dbco = new PDO($config_ini['irradiation']['db']);
			} else {
				$dbco = new PDO($config_ini['irradiation']['db'], $config_ini['irradiation']['dbUser'], $config_ini['irradiation']['dbPass']);
			}
			$dbco->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch ( PDOException $e ) {
			die('Impossible de se connecter à la base de données de rayonnement solaire (Ines Solaire), merci de <a href="http://david.mercereau.info/contact/">le signaler</a>. Vous pouvez tout de même utiliser le mode manuel pour faire votre simulation.');
		}
		if (isset($_GET['InesSolaireAuto'])) {
			// Choix optimum 
			$InesSolaireAuto = null;
			try {
				$InesSolaireAuto=$dbco->query("SELECT mois, inclinaison, orientation, igp FROM ".$config_ini['irradiation']['dbTableOptimum']." WHERE ville = \"".$_GET['InesVille']."\"")->fetch();
			} catch ( PDOException $e ) {
				$erreurDansLeFormulaire['nb']++;
				$erreurDansLeFormulaire['msg']=$erreurDansLeFormulaire['msg'].erreurPrint('InesSolaireAuto', 'Impossible de choisir l\'orientation et l\'inclinaison la plus favorable pour cette ville. Elle n\'a pas été trouvée dans la base (INES Solaire), merci de <a href="http://david.mercereau.info/contact/">le signaler</a>. Vous pouvez tout de même utiliser le mode manuel pour faire votre simulation. <br />Détail de l\'erreur :.'.$e);
			}
			if ($InesSolaireAuto != null) {
				$InesSolaireAuto['inclinaison']=ajoutDegSiAngleChiffre($InesSolaireAuto['inclinaison']);
				$InesSolaireAuto['orientation']=ajoutDegSiAngleChiffre($InesSolaireAuto['orientation']);
			}
		} else {
			// Orientation & inclinaison sélectionné
			$villeTableName=wd_remove_accents($config_ini['irradiation']['dbTablePrefix'].str_replace(' ','',$_GET['InesVille']));
			$InesSolaire = null;
			$Rqt="SELECT igp1, igp2, igp3, igp4, igp5, igp6, igp7, igp8, igp9, igp10, igp11, igp12 FROM ".$villeTableName." WHERE inclinaison = \"".$_GET['InesInclinaison']."\" AND orientation = \"".$_GET['InesOrientation']."\" AND albedo = \"".$_GET['InesAlbedo']."\"";
			try {
				$InesSolaire=$dbco->query($Rqt)->fetch();
			} catch ( PDOException $e ) {
				$erreurDansLeFormulaire['nb']++;
				$erreurDansLeFormulaire['msg']=$erreurDansLeFormulaire['msg'].erreurPrint('InesSolaire', 'Impossible de trouver le rayonnement avec les informations indiqué dans la base de données (INES Solaire), merci de <a href="http://david.mercereau.info/contact/">le signaler</a>. Vous pouvez tout de même utiliser le mode manuel pour faire votre simulation. <br />Voici la requête qui a échoué '.$Rqt.' <br />Détail de l\'erreur :.'.$e);
			}
		}
	} else if ($_GET['Ej'] < 0) {
		$erreurDansLeFormulaire['nb']++;
		$erreurDansLeFormulaire['msg']=$erreurDansLeFormulaire['msg'].erreurPrint('Ej', 'Le rayonnement moyen quotidien n\'est pas correct car < 0');
	}
	if ($erreurDansLeFormulaire['nb'] != 0) {
		echo '<div class="erreurForm">';
		echo '<p>Il y a des erreurs dans le formulaire qui empêche de continuer, merci de corriger ::</p>';
		echo '<ul>'.$erreurDansLeFormulaire['msg'].'</ul>';
		echo '</div>';
	} else {
	// Pas d'erreur
	debug('Cette couleur représente le mode transparent / debug','p');
	?>

	<h2 class="titre">Résultat du dimensionnement</h2>
	<p><b>Avertissement :</b> Les résultats sont donnés à titre indicatif, nous vous conseillons de vous rapprocher d'un professionnel pour l'achat du matériel, celui-ci pourra valider votre installation. </p>
	<!-- 
		Les PV
	-->
	<h3 id="resultatPv">Panneau photovoltaïque</h3>
	<div id="resultCalcPv" class="calcul">
		<p>On cherche ici la puissance (crête exprimée en W) des panneaux photovoltaïques à installer pour satisfaire vos besoins en fonction de votre situation géographique. La formule est la suivante : </p>
		<p>Pc = Bj / (Rb X Ri X Ej)</p>
		<ul>
			<li>Pc (Wc) : puissance crête (recherché)</li>
			<li>Bj (Wh/j) : besoins journaliers = <?= $_GET['Bj'] ?></li>
			<li>Rb : rendement électrique des batteries = <?= $_GET['Rb'] ?></li>
			<li>Ri : rendement électrique du reste de l’installation (régulateur de charge…) = <?= $_GET['Ri'] ?></li>
			<li>Ej : rayonnement moyen quotidien du mois le plus défavorable dans le plan du panneau (kWh/m²/j)</li>
			<?php 
			// S'il faut utilise la base INES Solaire (en mode automatique)
			if (empty($_GET['Ej'])) {	
				if (isset($_GET['InesSolaireAuto'])) {
					// Choix optimum 
					$Ej=$InesSolaireAuto['igp'];
					echo '<ul><li>'.$Ej.' pour '.$_GET['InesVille'].' selon le logiciel <a href="ines.solaire.free.fr/gisesol.php" target="_blank">Ines Solaire</a>, avec comme paramètre : ';
					echo '<ul>';
					echo '<li>Autonomie complète de Janvier à Décembre</li>';
					echo '<li>Orientation : '.$InesSolaireAuto['orientation'].' '.$aideOrientation.' </li>';
					echo '<li>Inclinaison : '.$InesSolaireAuto['inclinaison'].' '.$aideInclinaison.'</li>';
				} else {
					// Orientation & inclinaison sélectionné
					// InesPeriode=partielle&InesPeriodeDebut=10&InesPeriodeFin=2&
					if (isset($_GET['InesPeriode']) && $_GET['InesPeriode'] == 'partielle') {
						$moiEnCours=0;
						while($moiEnCours != $_GET['InesPeriodeFin']) {
							if ($moiEnCours == 0){
								$moiEnCours = $_GET['InesPeriodeDebut'];
							} else {
								if ($moiEnCours == 12){
									$moiEnCours=1;
								} else {
									$moiEnCours++;
								}
							}
							$InesPeriode[]=$moiEnCours;
						}
					}
					$Ej=9999;
					debug('<ul>');
					debug('On ne gardera l\'IGP du mois le plus défavorable :', 'li');
					for ($igpNb = 1; $igpNb <= 12; $igpNb++) {
						// pour l'autonomie partielle
						if (isset($_GET['InesPeriode']) && $_GET['InesPeriode'] == 'partielle' && !in_array($igpNb, $InesPeriode)) {
							continue;
						}
						debug('Pour '.$_GET['InesVille'].', au mois de '.utf8_encode($mois[$igpNb]).' l\'IGP est de '.$InesSolaire['igp'.$igpNb], 'li');
						if ($InesSolaire['igp'.$igpNb] < $Ej) {
							$Ej=$InesSolaire['igp'.$igpNb];
						}
					}
					debug('</ul>');

					echo '<ul>';
					echo '<li>'.$Ej.' pour '.$_GET['InesVille'].' selon le logiciel <a href="ines.solaire.free.fr/gisesol.php" target="_blank">Ines Solaire</a>, avec comme paramètre : ';
					echo '<ul>';
					if (isset($_GET['InesPeriode']) && $_GET['InesPeriode'] == 'partielle') {
						echo '<li>Autonomie partiel sur la période de '.utf8_encode($mois[$_GET['InesPeriodeDebut']]).' à '.utf8_encode($mois[$_GET['InesPeriodeFin']]).'</li>';
					} else {
						echo '<li>Autonomie complète de Janvier à Décembre</li>';
					}
					echo '<li>Orientation : '.ajoutDegSiAngleChiffre($_GET['InesOrientation']).' '.$aideOrientation.' </li>';
					echo '<li>Inclinaison : '.ajoutDegSiAngleChiffre($_GET['InesInclinaison']).' '.$aideInclinaison.'</li>';
				}
				echo '<li>Albédo : '.$_GET['InesAlbedo'].' '.$aideAlbedo.'. </li></ul> ';
				echo '</ul>';
			} else {
				$Ej = $Ej = $_GET['Ej'];
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
	
	<p>Les panneaux photovoltaïques produisent de l'électricité à partir des rayonnements du soleil.
	<?php
	if (empty($_GET['Ej']) && isset($_GET['InesSolaireAuto'])) {
		// Choix optimum 
		echo 'Afin d\'être autonome toute l\'année il convient de positionner les panneaux de façon optimum pour le mois le plus défavorable. Pour '.$_GET['InesVille'].', le mois le plus défavorable en ensoleillement est le mois de '.$InesSolaireAuto['mois'].'. Il vous est donc conseillé d\'orienter vos panneaux <b>'.$InesSolaireAuto['orientation'].'</b> '.$aideOrientation.' avec une inclinaison de <b>'.$InesSolaireAuto['inclinaison'].'</b> '.$aideInclinaison.'.';
	} else if (empty($_GET['Ej'])) {
		echo 'Vous avez choisi, pour la ville de '.$_GET['InesVille'].', une orientation de vos panneaux <b>'.ajoutDegSiAngleChiffre($_GET['InesOrientation']).'</b> '.$aideOrientation.' ainsi qu\'une inclinaison <b>'.ajoutDegSiAngleChiffre($_GET['InesInclinaison']).'</b> '.$aideInclinaison.'.';
	}
	?>
	</p>
	<p>Dans ces conditions vous auriez besoin d'une puissance de panneau photovoltaïque équivalente à <b><?= convertNumber($Pc, 'print') ?>Wc</b> afin de satisfaire vos besoins journaliers de <?= $_GET['Bj'] ?>Wh/j.</p>
	<p><a id="resultCalcPvShow">Voir, comprendre la démarche, le calcul</a></p>
	
	<?php	
	
	/*
	 * ####### Recherche d'une Config panneux : #######
	*/
	debug('<div>');
	debug('Recherche une hypothèse pour avoir le minimum de panneaux au plus proche des besoins de '.convertNumber($Pc,'print').' : ','span');
	/* Personnaliser */
	if ($_GET['ModPv'] == 'perso') {
		debug('Un panneau personnalisé à été indiqué, nous allons travailler avec celui-ci uniquement.', 'span');
		// Combien de panneau ?
		$nbPv=intval($Pc / $_GET['PersoPvW'])+1;
		// Capacité déduite
		$PcParcPv=$_GET['PersoPvW']*$nbPv;
		// Différence avec la capacité souhauté
		$diffPcParc=$PcParcPv-$Pc;
		$meilleurParcPv['nbPv'] = $nbPv;
		$meilleurParcPv['diffPcParc'] = round($diffPcParc);
		$meilleurParcPv['W'] = $_GET['PersoPvW'];
		$meilleurParcPv['V'] = $_GET['PersoPvV'];
		$meilleurParcPv['Vdoc'] = $_GET['PersoPvVdoc'];
		$meilleurParcPv['Isc'] = $_GET['PersoPvIsc'];
	/* Automatique selon les info's */
	} else {
		// Juste pour le debug :
		if ($_GET['TypePv'] != 'auto') {
			debug('Le type de panneau est forcé en '.$_GET['TypePv'],'p');
		}
		if ($_GET['ModPv'] == 'auto' ) {
			debug('On teste tous les <a onclick="window.open(\''.$config_ini['formulaire']['UrlModeles'].'&data=pv\',\'Les modèles de panneaux\',\'directories=no,menubar=no,status=no,location=no,resizable=yes,scrollbars=yes,height=500,width=600,fullscreen=no\');">modèle de panneau</a> de ce type possibles', 'span');
		}
		if ($_GET['ModPv'] != 'auto') {
			debug('Il a été forcé le choix de travailler avec le modèle type '.$_GET['ModPv'].'W','p');
		}
		$meilleurParcPv['nbPv'] = 99999;
		$meilleurParcPv['diffPcParc'] = 99999;
		$meilleurParcPv['W'] = 0;
		debug('<ul type="1">');
		foreach ($config_ini['pv'] as $idPv => $pv) {
			// Gestion du mode automatique dans le type :
			if ($_GET['ModPv'] == 'auto' && $_GET['TypePv'] != 'auto') {
				if ($_GET['TypePv'] != $pv['type']) {
					continue;
				}
			}
			if ($_GET['ModPv'] != 'auto' && $_GET['ModPv'] != $idPv) {
				continue;
			}
			// Calcul du nombre de panneaux nessésaire 
			$nbPv=intval($Pc / $pv['W'])+1;
			// Capacité déduite
			$PcParcPv=$pv['W']*$nbPv;
			// Différence avec la capacité souhauté
			$diffPcParc=$PcParcPv-$Pc;
			// Debug
			debug('<li>');
			debug('Avec le panneau de '.$pv['W'].'Wc : il en faudrait '.$nbPv.' pour une puissance total de : '.$PcParcPv.'W. La différence avec le besoin est de '.convertNumber($diffPcParc,'print').'W', 'span');
			
			$savMeilleurParcPv = false;

			$diffNbPvAvecMeilleurParc=$meilleurParcPv['nbPv']-$nbPv;
			
			// Si la différence de puissance & que le nombre de PV est inférieur 
			if ($diffPcParc <= $meilleurParcPv['diffPcParc'] && $nbPv < $meilleurParcPv['nbPv']
			// Si la différence dans le nombre de panneaux avec la meilleur config n'est pas un (même critère que précédent)
			 || $diffNbPvAvecMeilleurParc != 1 && $diffPcParc <= $meilleurParcPv['diffPcParc']
			 || $diffNbPvAvecMeilleurParc != 1 && $nbPv < $meilleurParcPv['nbPv']) {
				$savMeilleurParcPv = true;
			}
			
			if ($savMeilleurParcPv) {
				# Nouvelle meilleur config
				// Debug
				debug('<ul>');
				debug('<b>Meilleure configuration</b> pour le moment (soit nb pv < à l\'hypothèse précédente, soit moins de différence avec le besoin','li');
				debug('</ul>');
				$meilleurParcPv['nbPv'] = $nbPv;
				$meilleurParcPv['diffPcParc'] = round($diffPcParc);
				$meilleurParcPv['W'] = $pv['W'];
				$meilleurParcPv['V'] = $pv['V'];
				$meilleurParcPv['Vdoc'] = $pv['Vdoc'];
				$meilleurParcPv['Isc'] = $pv['Isc'];
				$meilleurParcPv['type'] = $pv['type'];
				$meilleurParcPv['nbPv'] = $nbPv;
			}
			debug('</li>');
		}
		debug('</ul>');
	}
	debug('</div>');
	if ($_GET['ModPv'] == 'auto') {
		echo '<p>Une hypothèse serait d\'avoir <b>'.$meilleurParcPv['nbPv'].' panneau(x)</b> '.$meilleurParcPv['type'].' de <b>'.$meilleurParcPv['W'].'Wc</b> chacun en '.$meilleurParcPv['V'].'V (<a rel="tooltip" class="bulles" title="Caractéristique du panneau : <br />P = '.$meilleurParcPv['W'].'W<br />U = '.$meilleurParcPv['V'].'V<br />Vdoc ='.$meilleurParcPv['Vdoc'].'V<br />Isc = '.$meilleurParcPv['Isc'].'A">?</a>) ce qui pousse la capacité du parc à '.$meilleurParcPv['W']*$meilleurParcPv['nbPv'].'W :</p>';
	}elseif ($_GET['ModPv'] == 'perso') {
		echo '<p>Avec votre panneau personnalisé (<a rel="tooltip" class="bulles" title="Caractéristique du panneau : <br />P = '.$meilleurParcPv['W'].'W<br />U = '.$meilleurParcPv['V'].'V<br />Vdoc ='.$meilleurParcPv['Vdoc'].'V<br />Isc = '.$meilleurParcPv['Isc'].'A">détail ici</a>) l\'hypothèse serait d\'avoir <b>'.$meilleurParcPv['nbPv'].' panneau(x)</b> de <b>'.$meilleurParcPv['W'].'Wc</b> chacun en '.$meilleurParcPv['V'].'V ce qui pousse la capacité du parc à '.$meilleurParcPv['W']*$meilleurParcPv['nbPv'].'W :</p>';
	} else {
		echo '<p>Avec le panneau '.$meilleurParcPv['type'].' sélectionné de <b>'.$meilleurParcPv['W'].'Wc</b> en '.$meilleurParcPv['V'].'V , une hypothèse serait d\'avoir <b>'.$meilleurParcPv['nbPv'].' de ces panneau(x)</b> (<a rel="tooltip" class="bulles" title="Caractéristique du panneau : <br />P = '.$meilleurParcPv['W'].'W<br />U = '.$meilleurParcPv['V'].'V<br />Vdoc ='.$meilleurParcPv['Vdoc'].'V<br />Isc = '.$meilleurParcPv['Isc'].'A">?</a>) ce qui pousse la capacité du parc à '.$meilleurParcPv['W']*$meilleurParcPv['nbPv'].'W :</p>';
	}
	?>
	<!-- 
		Les batteries
	-->
	<h3 id="resultatBat">Batterie</h3>
	<div id="resultCalcBat" class="calcul">
		<p>On cherche ici la capacité nominale des batteries exprimée en ampères heure (Ah, donné en <a href="http://www.batterie-solaire.com/batterie-delestage-electrique.htm" target="_blank">C10</a>)</p>
		<?php 
		// Si la tension U à été mise en automatique ou que le niveau n'est pas expert
		if ($_GET['U'] == 0 || $_GET['Ni'] != 3) {
			debug('Pour la tension finale du parc de batterie. Automatiquement : si Pc <500Wc on reste en 12V, entre 500 et 1500 on passe en 24V et au delà des 1500Wc on passe en 48V', 'span');
			if (convertNumber($Pc) < 500) {
				$U = 12;
				debug('Ici on est donc en '.$U.'V  car le besoin en panneaux est < à 500Wc', 'span');
			} elseif (convertNumber($Pc) > 1500) {
				$U = 48;
				debug('Ici on est donc en '.$U.'V  car le besoin en panneaux est > à 1500Wc', 'span');
			} else {	
				$U = 24;
				debug('Ici on est donc en '.$U.'V  car le besoin en panneaux est entre 500 et 1500Wc', 'span');
			}
		} else {
			$U = $_GET['U'];
			debug('La tension finale du parc de batterie est forcée à '.$U.'V', 'span');
		}
		?>
		<p>Cap = (Bj x Aut) / (DD x U)</p>
		<ul>
			<li>Cap (Ah) : capacité nominale des batteries (en <a href="http://www.batterie-solaire.com/batterie-delestage-electrique.htm" target="_blank">C10</a>))</li>
			<li>Bj (Wh/j) : besoins journaliers = <?= $_GET['Bj'] ?></li>
			<li>Aut : nombre de jours d'autonomie (sans soleil) = <?= $_GET['Aut'] ?></li>
			<li>DD (%) : <a rel="tooltip" class="bulles" title="Avec des batteries au plomb il ne faut pas passer sous le seuil critique des 50% de degré de décharge, 20% est idéal">degré de décharge maximum</a> = <?= $_GET['DD'] ?></li>
			<li>U (V) : <a rel="tooltip" class="bulles" title="En mode automatique la tension des batteries sera déduite du besoin en panneaux<br />De 0 à 500Wc : 12V<br />De 500 à 1500 Wc : 24V<br />Au dessus de 1500 Wc : 48V">Tension finale du parc de batterie</a> = <?= $U ?></li>
		</ul>
		<p>Dans votre cas ça nous fait : </p>
		<?php 
		$Cap = (convertNumber($_GET['Bj'])*convertNumber($_GET['Aut']))/(convertNumber($_GET['DD'])*0.01*convertNumber($U));
		?>
		<p><a class="more" id="resultCalcBatHide">Cacher le calcul</a></p>
		<p>Cap = (<?= $_GET['Bj'] ?> x <?= $_GET['Aut'] ?>) / (<?= $_GET['DD']*0.01 ?> x <?= $U ?>) = <b><?= convertNumber($Cap, 'print') ?> Ah</b></p>
	</div>
	<p>Les batteries servent à stocker l'énergie électrique produite par les panneaux. Vous auriez besoin d'un parc de batteries de <b><?= convertNumber($Cap, 'print') ?> Ah en <?= $U ?> V</b>.</p>
	<p><a id="resultCalcBatShow">Voir, comprendre la démarche, le calcul</a></p>	
	
	<?php
	$CourantDechargBesoinPmax=$_GET['Pmax']/$U;
	$CourantDechargeMax = $Cap*$_GET['IbatDecharge']/100;
	// Si le courant de décharge n'est pas respecté par rapport à la taille de la batterie
	if ($CourantDechargBesoinPmax > $CourantDechargeMax) {
		echo '<p>Le courant de décharge du parc batterie ne doit pas dépasser '.$_GET['IbatDecharge'].'%, ce qui fait <a rel="tooltip" class="bulles" title="'.convertNumber($Cap, 'print').'Ah * '.$_GET['IbatDecharge'].'/100">'.number_format($CourantDechargeMax, 1, ',', ' ').'A</a> dans notre cas. Hors avec un besoin en puissance max de '.$_GET['Pmax'].'W de panneau le courant de décharge est de <a rel="tooltip" class="bulles" title="'.$_GET['Pmax'].'W / '.$U.'V">'.number_format($CourantDechargBesoinPmax, 1, ',', ' ').'A</a>. Pour répondre au besoin de puissance maximum de '.$_GET['Pmax'].'W, il vous faut augmenter le parc de batterie à ';
		$Cap=$CourantDechargBesoinPmax*100/$_GET['IbatDecharge'];
		echo '<b>'.convertNumber($Cap, 'print').'Ah</b>.</p>';
	} else {
		debug('On a testé que le courant de décharge du parc batterie et on ne dépasse pas les '.$_GET['IbatDecharge'].'%.','p');
	}
	$CourantChargeDesPanneaux=$meilleurParcPv['W']*$meilleurParcPv['nbPv']/$U;
	$CourantChargeMax = $Cap*$_GET['IbatCharge']/100;
	// Si le courant de charge n'est pas respecté par rapport à la taille de la batterie
	if ($CourantChargeDesPanneaux > $CourantChargeMax) {
		echo '<p>Le courant de charge du parc batterie ne doit pas dépasser '.$_GET['IbatCharge'].'%, ce qui fait <a rel="tooltip" class="bulles" title="'.convertNumber($Cap, 'print').'Ah * '.$_GET['IbatCharge'].'/100">'.number_format($CourantChargeMax, 1, ',', ' ').'A</a> dans notre cas. Hors avec '.$meilleurParcPv['W']*$meilleurParcPv['nbPv'].'Wc de panneau le courant de charge est de <a rel="tooltip" class="bulles" title="'.$meilleurParcPv['W']*$meilleurParcPv['nbPv'].'W / '.$U.'V">'.number_format($CourantChargeDesPanneaux, 1, ',', ' ').'A</a>. Si votre régulateur le permet vous pouvez le brider ou augmenter votre parc de batterie à ';
		$Cap=$CourantChargeDesPanneaux*100/$_GET['IbatCharge'];
		echo '<b>'.convertNumber($Cap, 'print').'Ah</b>. Nous allons partir sur l\'augmentation du parc de batterie.</p>';
	} else {
		debug('On a testé que le courant de charge du parc batterie et on ne dépasse pas les '.$_GET['IbatCharge'].'%.','p');
	}
	?>

	<?php 
	/*
	 * ####### Recherche d'une Config batterie : #######
	*/
	debug('<div>');
	$meilleurParcBatterie['nbBatterieParalle'] = 99999;
	$meilleurParcBatterie['diffCap'] = 99999;
	$meilleurParcBatterie['nom'] = 'Impossible à déterminer';
	$meilleurParcBatterie['V'] = 0;
	$meilleurParcBatterie['Ah'] = 0;
	// Choix de la technologie en fonction de la capacitée (pour le mode automatique)
	if ($Cap < 50) {
		$BatType = 'AGM';
	} elseif ($Cap < 500) {
		$BatType = 'GEL';
	} else {
		$BatType = 'OPzV';
	}
	if ($_GET['TypeBat'] == 'auto') {
		debug('En mode automatique, la technologie choisie est AGM si Cap < à 50Ah, GEL si < à 500Ah & OPzv si > à 500Ah, ici on a donc choisi '.$BatType,'p');
	} else {
		debug('Vous avez choisie de privilégier la technologie '.$_GET['TypeBat']);
	}
	debug('Recherche une hypothèse parmi <a onclick="window.open(\''.$config_ini['formulaire']['UrlModeles'].'&data=batterie\',\'Les modèles de batteries\',\'directories=no,menubar=no,status=no,location=no,resizable=yes,scrollbars=yes,height=500,width=600,fullscreen=no\');">les modèles</a> sur le nombre batterie à mettre en paralèle (noté //). Il faut avoir le minimum de batterie au plus proche des besoins de '.convertNumber($Cap,'print').'Ah : ','span');
	debug('<ul type="1">');
	foreach ($config_ini['batterie'] as $idBat => $batterie) {
		// En mode personnalisé on force et on stop la boucle à la fin 
		if ($_GET['ModBat'] == 'perso') {
			debug('Une batterie personnalisée à été indiquée, nous allons travailler avec celle-ci uniquement.', 'span');
			// plus loin, la même condition avec un break
			$batterie['Ah'] = $_GET['PersoBatAh'];
			$batterie['V'] = $_GET['PersoBatV'];
		// En mode auto on utilise le type de batterie préféré (GEL par défaut)
		} else if ($_GET['ModBat'] == 'auto') {
			if ($_GET['TypeBat'] == 'auto') {
				if ($batterie['type'] != $BatType) {
					continue;
				}
			} elseif ($_GET['TypeBat'] != $batterie['type']) {
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
		debug('<li>');
		debug('Avec une batterie '.$batterie['nom'].' ,il en faudrait '.$nbBatterie.' pour une capacité de '.$capParcBatterie.'Ah. La différence avec le besoin est de '.convertNumber($diffCap,'print').'Ah', 'span');
		if ($nbBatterie <= 2) {
			// + de 2 paralèles n'est pas souhaitable			
			if ($_GET['ModBat'] == 'perso' 
			|| $nbBatterie < $meilleurParcBatterie['nbBatterieParalle']
			|| $nbBatterie == $meilleurParcBatterie['nbBatterieParalle'] && $diffCap <= $meilleurParcBatterie['diffCap']) {
				# Nouvelle meilleur config
				// Debug
				debug('<ul>');
				debug('<b>Meilleure configuration</b> pour le moment car nb < 2 (pas plus de 2 //) & soit nb < hypothèse précédente, soit moins de différence avec le besoin','li');
				debug('</ul>');
				$meilleurParcBatterie['diffCap'] = round($diffCap);
				$meilleurParcBatterie['nom'] = $batterie['nom'];
				$meilleurParcBatterie['V'] = $batterie['V'];
				$meilleurParcBatterie['Ah'] = $batterie['Ah'];
				$meilleurParcBatterie['type'] = $batterie['type'];
				$meilleurParcBatterie['nbBatterieParalle'] = $nbBatterie;
				$meilleurParcBatterie['nbBatterieSerie'] = $U/$meilleurParcBatterie['V'];
				$meilleurParcBatterie['nbBatterieTotal'] = $meilleurParcBatterie['nbBatterieSerie'] * $meilleurParcBatterie['nbBatterieParalle'];
			}
		}
		debug('</li>');
		// En mode personnalisé stop la boucle après avoir forcé 
		if ($_GET['ModBat'] == 'perso') {
			break;
		}
	}
	debug('</ul>');
	debug('Le nombre de batterie à mettre en série est déterminé en fonction de la tension du parc (ici '.$U.'V) et de la tension de la batterie choisie (ici '.$meilleurParcBatterie['V'].'V), il faut donc '.$meilleurParcBatterie['nbBatterieSerie'].' batterie(s) '.$meilleurParcBatterie['nom'].' pour attendre cette tension de '.$U.'V','p');
	debug('</div>');
	if ($meilleurParcBatterie['nbBatterieParalle'] != 99999) {
		if ($_GET['ModBat'] == 'auto') {
			echo '<p>Une hypothèse de câblage serait d\'avoir <b>'.$meilleurParcBatterie['nbBatterieTotal'].' batterie(s)</b> de type <b>'.$meilleurParcBatterie['nom'].'</b> ce qui pousse la capacité du parc à '.$meilleurParcBatterie['Ah']*$meilleurParcBatterie['nbBatterieParalle'].'Ah.</p>';
		} else if ($_GET['ModBat'] == 'perso') {
			echo '<p>Vous avez choisi de travailler avec des batteries personnalisées à '.$meilleurParcBatterie['Ah'].'Ah en '.$meilleurParcBatterie['V'].'V. Voici une hypothèse de câblage avec <b>'.$meilleurParcBatterie['nbBatterieTotal'].'</b> de ces batteries ce qui pousse la capacité du parc à '.$meilleurParcBatterie['Ah']*$meilleurParcBatterie['nbBatterieParalle'].'Ah.</p>';
		} else {
			echo '<p>Vous avez choisi de travailler avec des batteries de type <b>'.$meilleurParcBatterie['nom'].'</b>. Voici une hypothèse de câblage avec <b>'.$meilleurParcBatterie['nbBatterieTotal'].'</b> de ces batteries ce qui pousse la capacité du parc à '.$meilleurParcBatterie['Ah']*$meilleurParcBatterie['nbBatterieParalle'].'Ah.</p>';
		}
			echo '<ul><li><b>'.$meilleurParcBatterie['nbBatterieSerie'].' batterie(s) en série</b> (<a rel="tooltip" class="bulles" title="Tension de la batterie ('.$meilleurParcBatterie['V'].'V) * '.$meilleurParcBatterie['nbBatterieSerie'].' série(s)">pour une tension de '.$U.'V</a>) ';
			if ($meilleurParcBatterie['nbBatterieParalle'] != 1) {
				echo 'sur <b>'.$meilleurParcBatterie['nbBatterieParalle'].' parallèle(s)</b> (<a rel="tooltip" class="bulles" title="Capacité de la batterie ('.$meilleurParcBatterie['Ah'].'Ah) * '.$meilleurParcBatterie['nbBatterieParalle'].' parallèle(s)">pour une la capacité à '.$meilleurParcBatterie['Ah']*$meilleurParcBatterie['nbBatterieParalle'].'Ah</a>)';
			} 
			echo '<a rel="tooltip" class="bulles" target="_blank" title="Pour comprendre le câblage des batteries cliquer ici" href="http://www.solarmad-nrj.com/cablagebatterie.html">?</a></li></ul>';
	} else {
		echo '<p>Désolé nous n\'avons pas réussi à faire une hypothèse de câblage pour les batteries. </p>';
		if ($_GET['ModBat'] != 'auto') {
			echo '<p>Nous vous conseillons de repasser en mode automatique, un câblage n\'est peut être pas préférable avec ce modèle.</p>';
		}
	}
	?>
	
	<p>Vous pouvez simuler l'état de vos batteries grâce à <a href="http://re.jrc.ec.europa.eu/pvgis/apps4/pvest.php?lang=fr&map=europe" target="_blank">PVGIS</a> : <a id="aidePvgisShow">aide à la simulation</a></p>
	<div id="aidePvgis" class="calcul">
		<a class="more" id="aidePvgisHide">Cacher l'aide</a>
		<ul>
			<li>Cliquer sur ce lien : <a href="http://re.jrc.ec.europa.eu/pvgis/apps4/pvest.php?lang=fr&map=europe" target="_blank">http://re.jrc.ec.europa.eu/pvgis/apps4/pvest.php?lang=fr&map=europe</a></li>
			<li>Indiquer la ville d'implantation à gauche au dessus de la carte</li>
			<li>Cliquer sur l'onglet "PV hors-réseau" à droite</li>
			<li>Puis indiquer les valeurs :</li>
			<ul>
				<li>Puissance PV crête : <?=  $meilleurParcPv['W']*$meilleurParcPv['nbPv'] ?></li>
				<li>Voltage de la batterie : <?= $U ?></li>
				<?php if ($meilleurParcBatterie['nbBatterieParalle'] != 99999) { ?>
					<li>Capacité de la batterie : <?= $meilleurParcBatterie['Ah']*$meilleurParcBatterie['nbBatterieParalle'] ?></li>
				<?php } else { ?>
					<li>Capacité de la batterie : <?= $Cap ?> en </li>
				<?php } ?>
				<li>Consommation journalière : <?= $_GET['Bj'] ?></li>
				<?php 
				if (isset($_GET['InesSolaireAuto'])) {
					echo '<li>Inclinaison du module : '.$InesSolaireAuto['inclinaison'].'</li>';
					echo '<li>Orientation : '.nomEnAngle($InesSolaireAuto['orientation']).'</li>';
				} else {
					echo '<li>Inclinaison du module : '.ajoutDegSiAngleChiffre($_GET['InesInclinaison']).'</li>';
					echo '<li>Orientation : '.nomEnAngle(ajoutDegSiAngleChiffre($_GET['InesOrientation'])).'</li>';
				}
				?>
			</ul>
			<li>Puis cliquer sur calculer</li>
		</ul>
		<p>Pour maximiser la durée de vie de vos batteries il est conseillé de ne pas descendre sous les 80% de charge (donc 20% de décharge) trop fréquement.</p>
	</div>
	<!-- 
		Régulateur
	-->
	<h3 id="resultatRegu">Régulateur de charge</h3>
	<p>Le régulateur de charge est entre les batteries et les panneaux, c'est lui qui gère la charge des batteries en fonction de ce que peuvent fournir les panneaux. </p>
	<?php 
	/*
	 * ####### Recherche d'une Config régulateur : #######
	*/
	// Courant de charge max avec les batteries
	$batICharge = $meilleurParcBatterie['Ah']*$meilleurParcBatterie['nbBatterieParalle'] * $_GET['IbatCharge'] / 100;
	debug('On considère le courant de charge max du parc de batteries '.$meilleurParcBatterie['Ah'].'Ah à '.$_GET['IbatCharge'].'%. Ce qui nous fait '.number_format($batICharge, 2, ',', ' ').'A.','p');
	
	debug('Recherche une hypothèse de câblage des panneaux (au maximum en série pour éviter les pertes/grosses sections de câble) avec tous les <a onclick="window.open(\''.$config_ini['formulaire']['UrlModeles'].'&data=regulateur\',\'Les modèles de batteries\',\'directories=no,menubar=no,status=no,location=no,resizable=yes,scrollbars=yes,height=500,width=600,fullscreen=no\');">les modèles de régulateurs</a>, en '.$U.'V : ','span');
	// D'abord on test avec 1 régulateur
	// Ensuite on test tout en série
	// Si on trouve pas, on divise en parallèle
	// Si ça marche toujours pas on test avec plusieurs régulateur (10  max)
	for ($nbRegulateur = 1; $nbRegulateur <= 10; $nbRegulateur++) {	
		// On check toutes les possibilités en série puis en divisant en parallèles
		if ($meilleurParcPv['nbPv'] == 1) {
			$nbPvConfigFinal=1;
		} else {
			$nbPvConfigFinal=round($meilleurParcPv['nbPv']/$nbRegulateur);
		}
		$nbPvSerie = $nbPvConfigFinal;
		$nbPvParalele = 1;
		while ($nbPvSerie >= 1) {
			debug('En considérant '.$nbRegulateur.' régulateur, on teste avec '.$nbPvSerie.' panneaux en série sur '.$nbPvParalele.' parallèle :', 'p');
			$VdocParcPv=$meilleurParcPv['Vdoc']*$nbPvSerie;
			$IscParcPv=$meilleurParcPv['Isc']*$nbPvParalele;
			$parcPvW = $nbPvSerie*$nbPvParalele * $meilleurParcPv['W'];
			$parcPvV = $VdocParcPv;
			$parcPvI = $IscParcPv*$_GET['reguMargeIcc']/100+$IscParcPv;
			
			$meilleurRegulateur = chercherRegulateur();
			
			// Solution trouvée
			if ($meilleurRegulateur['nom']) {
				break;
			}
			
			// Pour la suite 
			if ($nbPvSerie != 1) {
				$nbPvSerie=round($nbPvSerie/2);
				$nbPvParalele =round($nbPvConfigFinal / $nbPvSerie);
			} else {
				$nbPvSerie = 0;
			}
		}
		// Solutaion trouvé
		if ($meilleurRegulateur['nom']) {
			debug('Une hypothèse est émise, on s\'arrête là', 'p');
			break;
		}
	}

	if (!$meilleurRegulateur['nom']) {
		echo '<p>Désolé nous n\'avons pas réussi à faire une hypothèse de câblage panneaux/régulateur. ';
		if ($_GET['ModRegu'] != 'auto') {
			echo 'Nous vous encourageons à passer le modèle du régulateur et/ou les panneaux en automatique. ';
		}
		echo '</p>';
	} else {
		if ($meilleurParcPv['nbPv'] != $nbPvSerie*$nbPvParalele*$nbRegulateur) {
			echo '<p><i>Attention : pour cette hypothèse nous sommes passé à '.$nbPvSerie*$nbPvParalele*$nbRegulateur.' panneaux</i></p>';
		}
		if ($_GET['ModRegu'] == 'perso') {
			echo '<p>Avec votre régulateur personélisé, une ';
		} else if ($_GET['ModRegu'] != 'auto') {
			echo '<p>Vous forcé la sélection du régulateur '.$meilleurRegulateur['nom'].', une ';
		} else {
			echo '<p>Une ';
		}
		if ($nbRegulateur != 1) {
			echo 'hypothèse de câblage serait d\'avoir <b>'.$nbRegulateur.' régulateur type '.$meilleurRegulateur['nom'].'</b> (<a rel="tooltip" class="bulles" title="Avec caractéristiques similaires : <br />Tension de la batterie : '.$meilleurRegulateur['Vbat'].'V<br />Puissance maximale PV : '.$meilleurRegulateur['PmaxPv'].'W<br />Tension PV circuit ouvert : '.$meilleurRegulateur['VmaxPv'].'V<br />Courant PV court circuit : '.$meilleurRegulateur['ImaxPv'].'A">?</a>) et sur chacun d\'entre eux connecter <b>'.$nbPvSerie.' panneau(x) en série';
			if ($nbPvParalele != 1) {
				echo ' sur '.$nbPvParalele.' parallèle(s)</b></p>';
			} else {
				echo '</b></p>';
			}
		} else {
			echo 'hypothèse de câblage serait d\'avoir un <b>régulateur type '.$meilleurRegulateur['nom'].'</b> (<a rel="tooltip" class="bulles" title="Avec caractéristiques similaires : <br />Tension de la batterie : '.$meilleurRegulateur['Vbat'].'V<br />Puissance maximale PV : '.$meilleurRegulateur['PmaxPv'].'W<br />Tension PV circuit ouvert : '.$meilleurRegulateur['VmaxPv'].'V<br />Courant PV court circuit : '.$meilleurRegulateur['ImaxPv'].'A">?</a>) sur lequel serait connecté ';
			if ($nbPvSerie == 1 && $nbPvParalele == 1) {
				echo '<b>'.$nbPvSerie.' panneau';
			} else {
				echo '<b>'.$nbPvSerie.' panneau(x) en série';
				if ($nbPvParalele != 1) {
					echo ' sur '.$nbPvParalele.' parallèle(s)';
				} 
			}
			echo '</b></p>';
		}
		
		?>
		<div id="resultCalcRegu" class="calcul">
			<p>Un régulateur type <?= $meilleurRegulateur['nom'] ?>, avec un parc de batterie(s) en <b><?= $meilleurRegulateur['Vbat'] ?>V</b>, accepte  : </p>
			<ul>
				<li><b><?= $meilleurRegulateur['PmaxPv'] ?>W</b> de puissance maximum de panneaux : </li>
					<ul><li>Avec un total de <?= $nbPvSerie*$nbPvParalele ?> panneau(x) en <?= $meilleurParcPv['W'] ?>W, on monte à <b><?= $meilleurParcPv['W']*$nbPvParalele*$nbPvSerie ?>W</b> (<a rel="tooltip" class="bulles" title="<?= $meilleurParcPv['W'] ?>W x <?= $nbPvParalele*$nbPvSerie ?> panneau(x) ">?</a>)</li></ul>
				<li><b><?= $meilleurRegulateur['VmaxPv'] ?>V</b> de tension PV maximale de circuit ouvert : </li>
					<ul><li>Avec <?= $nbPvSerie ?> panneau(x) en série ayant une tension (Vdoc) de <?= $meilleurParcPv['Vdoc'] ?>V, on monte à <b><?= $nbPvSerie*$meilleurParcPv['Vdoc'] ?>V</b> (<a rel="tooltip" class="bulles" title="<?= $meilleurParcPv['Vdoc'] ?>V (Vdoc) x <?= $nbPvSerie ?> panneau(x) en série">?</a>)</li></ul>
				<li><b><?= $meilleurRegulateur['ImaxPv'] ?>A</b> de courant de court-circuit PV maximal : </li>
					<ul><li>Avec <?= $nbPvParalele ?> panneau(x) en parallèle(s) ayant une intensité (Isc) de <?= $meilleurParcPv['Isc'] ?>A et une marge de sécurité de <?= $_GET['reguMargeIcc'] ?>%, on monte à <b><?= $nbPvParalele*($meilleurParcPv['Isc']+$meilleurParcPv['Isc']*$_GET['reguMargeIcc']/100) ?>A</b> (<a rel="tooltip" class="bulles" title="(<?= $meilleurParcPv['Isc'] ?>A d'Isc * <?= $_GET['reguMargeIcc'] ?>/100 de marge + <?= $meilleurParcPv['Isc'] ?>A d'Isc) x <?= $nbPvParalele ?> panneau(x) en parallèle(s)">?</a>)</li></ul>
			</ul>
			<p>Note : la mise en série additionne la tension (V) et la mise en parallèle additionne l'intensité (I)</p>
			<p>Toutes ces caractéristiques sont disponibles dans la fiche technique du produit. Vous pouvez personnaliser les caractéristiques de votre régulateur en mode <i>Expert</i>.</p>
			<p><a class="more" id="resultCalcReguHide">Cacher la démarche</a></p>
			<p> </p>
		</div>
		<p><a id="resultCalcReguShow">Voir, comprendre la démarche</a></p>	
		<?php
		if ($nbPvParalele > 1) {
			echo 'Quand il y a des parallèles il est recommandé de poser un boitier de raccordement avec des fusibles sur chaque branche pour protéger les panneaux contre un courant inverse.';
		}
	}
	?>
	<h3 id="resultatSchema">Schéma de câblage</h3>
	
	<?php 
	if (empty($meilleurRegulateur['nom']) || $meilleurParcBatterie['nbBatterieParalle'] == 99999) {
		echo '<p>Les hypothèses de câblages n\'ont pas toutes abouties, il n\'est donc pas possible de présenter un schéma de câblage.</p>';
	} else {
		$batType=2;
		if ($meilleurParcBatterie['V'] == 12) {
			$batType=1;
		}	
		$SchemaUrl='./lib/ImgSchemaCablage.php?nbPvS='.$nbPvSerie.'&nbPvP='.$nbPvParalele.'&batType='.$batType.'&nbBatS='.$meilleurParcBatterie['nbBatterieSerie'].'&nbBatP='.$meilleurParcBatterie['nbBatterieParalle'].'&nbRegu='.$nbRegulateur;
		$widthImage=20;
		if ($nbPvSerie > 1 || $meilleurParcBatterie['nbBatterieSerie'] > 1) {
			$widthImage=40;
		}
		if ($nbPvSerie > 3 || $meilleurParcBatterie['nbBatterieSerie'] > 3) {
			$widthImage=70;
		}
		if ($nbPvSerie > 5 || $meilleurParcBatterie['nbBatterieSerie'] > 5) {
			$widthImage=100;
		}
		echo '<p>Un schéma de câblage a été établi en fonction des hypothèses panneau/régulateur/batterie émises précédemment :</p>';
		echo '<p><a target="_blank" href="'.$SchemaUrl.'"><img width="'.$widthImage.'%"  src="'.$SchemaUrl.'" /></a></p>';
	}
	?>
	
	<h3  id="resultatConv">Convertisseur</h3>
	<p>Le convertisseur est là pour transformer le courant continue (ici <?= $U ?>V) des batteries en courant alternatif assimilable par les appareils standard du marché. Il vous faut un convertisseur capable de délivrer les <?= $_GET['Pmax'] ?>W de puissance électrique maximum dont vous avez besoin.</p>
	<?php
	debug('On recherche, parmis <a onclick="window.open(\''.$config_ini['formulaire']['UrlModeles'].'&data=convertisseur\',\'Les modèles de Convertisseur\',\'directories=no,menubar=no,status=no,location=no,resizable=yes,scrollbars=yes,height=500,width=600,fullscreen=no\');">les modèles de convertisseurs</a> un convertisseur supportant une tension d\'entrée de '.$U.' et qui soit capable de délivrer une puissance maximum de '.$_GET['Pmax'].'W  : ','p');
	$meilleurConvertisseur=chercherConvertisseur($U,$_GET['Pmax']);
	if ($meilleurConvertisseur['nom'] == '') {
		echo '<p>Désolé nous n\'avons pas réussi à trouver un convertisseur pour une telle puissance.</p> ';
	} else {
		// Annoncer limite batterie
		$CourantDechargeMaxParcBatterieHypothetique=$meilleurParcBatterie['Ah']*$meilleurParcBatterie['nbBatterieParalle']*$_GET['IbatDecharge']/100;
		$PuissanceMaxDechargeBatterie=$CourantDechargeMaxParcBatterieHypothetique*$U;
		echo '<p>Une hypothèse serait d\'opter pour un <b>convertisseur type '.$meilleurConvertisseur['nom'].'</b> qui monte en puissance maximum de sortie à '.$meilleurConvertisseur['Pmax'].'W avec des pointes possible à '.$meilleurConvertisseur['Ppointe'].'W.';
		if ($PuissanceMaxDechargeBatterie < $meilleurConvertisseur['Pmax']) {
			echo 'Ceci dit, pour ne pas endommager vos batteries, vous ne pourrez aller au delà des '.$PuissanceMaxDechargeBatterie.'W <a rel="tooltip" class="bulles" title="('.$meilleurParcBatterie['Ah']*$meilleurParcBatterie['nbBatterieParalle'].'Ah de batterie * '.$_GET['IbatDecharge'].'/100 de courant max de décharge des batterie) * '.$U.'V">?</a>';
		}
		echo '</p>';
	}
	?>
	
	<h3 id="resultatBatControleur">Contrôleur de batterie</h3>
	<p>Il vous est conseillé d'avoir un contrôleur de batterie afin de connaître l'état de charge de votre parc de batteries.
	<?php if ($Cap > 100 || $meilleurParcBatterie['Ah']*$meilleurParcBatterie['nbBatterieParalle'] > 100) {  
		// type BMV
		$BudgetBatControleur = 150;
	 } else {  
		// type voltmètre
		$BudgetBatControleur = 15; 
		echo 'Ceci étant dit, au vu de votre petite installation, un voltmètre parait plus raisonnable / approprié. Grâce à un <a href="https://www.solariflex.com/smartblog/19/comment-interpreter-voltage-batteries.html">tableau de correspondance</a> vous pouvez, de façon grossière et incertaine, déterminer le pourcentage de charge .';
	} ?>
	</p>
	<h3 id="resultatCablage">Le câblage</h3>
	<?php $BudgetCable=0; ?>
	<p>Le choix (<a href="http://solarsud.blogspot.fr/2014/11/calcul-de-la-section-du-cable.html" target="_blank">calcul</a>) des sections de câbles est important pour éviter les pertes :</p>
	<ul>
		<?php
			$PT=($nbPvSerie*$meilleurParcPv['Vdoc'])*$_GET['cablagePtPourcent']/100;
			# formule de calcul avec la distance et la chute de tension
			$cableDistancePvRegu_Calc=round($_GET['cablageRho']*($_GET['distancePvRegu']*2)*($nbPvParalele*$meilleurParcPv['Isc'])/$PT,2);
			# règle des 5A par mm² 
			$cableDistancePvRegu_AparMm=round(($nbPvParalele*$meilleurParcPv['Isc'])/$_GET['cablageRegleAparMm'],2);
			if ($cableDistancePvRegu_Calc < $cableDistancePvRegu_AparMm) {
				$cableDistancePvRegu_Final=$cableDistancePvRegu_AparMm;
			} else {
				$cableDistancePvRegu_Final=$cableDistancePvRegu_Calc;
			}
		?>
		<li>Entre les panneaux et le régulateur, pour une distance de <?= $_GET['distancePvRegu'] ?>m, il vous est conseillé un câble d'une section de <?= $cableDistancePvRegu_Final ?>mm² 
		<a id="resultCalcCablePvReguShow">(voir, comprendre la démarche)</a></li>
		<div id="resultCalcCablePvRegu" class="calcul">
			<p><a class="more" id="resultCalcCablePvReguHide">Cacher la démarche</a></p>
			<p>La formule pour calculer une section de câble pour éviter les pertes est :</p>
			<p>S = Rho x L x I / PT</p>
			<ul>
				<li>S (mm²) : Section du conducteur</li>
				<li>Rho (ohm) : <a href="https://fr.wikipedia.org/wiki/R%C3%A9sistivit%C3%A9" target="_blank">Résistivité</a> du conducteur (0,017ohm pour le cuivre)</li>
				<li>L (m) : Longueur aller + retour du conducteur</li>
				<li>I (A) : L’intensité (ici l'intensité des panneaux x le nombre de paralèle)</li>
				<li>PT (V) : Perte de tension acceptée au niveau des câbles (<?= $_GET['cablagePtPourcent']?>% de la tension)</li>
					<ul><li>(La tension des panneaux x le nombre de série) x <?= $_GET['cablagePtPourcent']?>/100</li></ul>
			</ul>
			<p>Dans votre cas ça nous fait : </p>
			<p>S = <?php echo $_GET['cablageRho'].' x ('.$_GET['distancePvRegu'].'x2) x '.$nbPvParalele*$meilleurParcPv['Isc'].' / '.$PT.' = <b>'.$cableDistancePvRegu_Calc.'</b>'; ?>mm²</p>
			<?php 
			if ($cableDistancePvRegu_Calc < $cableDistancePvRegu_AparMm) {
				echo '<p>Mais cette section ne respecte pas la règle des '.$_GET['cablageRegleAparMm'].'A/mm² qui permet de se prémunir des échauffements. ';
				echo 'Pour respecter cette règle, il faudrait s\'approcher d\'une section de <b>'.$cableDistancePvRegu_Final.'</b>mm² <a rel="tooltip" class="bulles" title="'.$nbPvParalele*$meilleurParcPv['Isc'].'A / '.$_GET['cablageRegleAparMm'].'A/mm² = '.$cableDistancePvRegu_Final.'mm²">?</a></p>';
			}
			?>
		</div>
		<ul>
		<?php
		if ($cableDistancePvRegu_Calc < $cableDistancePvRegu_AparMm) {
			$meilleurCable = chercherCable_SecionPlusProche($cableDistancePvRegu_Final); 
		} else {
			$meilleurCable = chercherCable_SecionAudessus($cableDistancePvRegu_Final); 
		}
		if (empty($meilleurCable)) {
			echo '<li>Impossible de proposer une section de câble réaliste. Vous deviez peut être envisager de diminuer la distance entre les appareils.';
		} else { 
			$BudgetCable=$BudgetCable+$_GET['distancePvRegu']*$meilleurCable['prix'];
			?>
			<li>Section de câble la plus proche proposée : <b><?= $meilleurCable['nom'] ?></b>, pour un coût d'environ <?= $_GET['distancePvRegu']*$meilleurCable['prix'] ?>€</li>
		<?php } ?>
		</ul>
		<?php
			$PT=$U*$_GET['cablagePtPourcent']/100;
			# formule de calcul avec la distance et la chute de tension
			$cableDistanceReguBat_Calc=round($_GET['cablageRho']*($_GET['distanceReguBat']*2)*($parcPvW/$U)/$PT,2);
			# règle des 5A par mm² 
			$cableDistanceReguBat_AparMm=round(($parcPvW/$U)/$_GET['cablageRegleAparMm'],2);
			if ($cableDistanceReguBat_Calc < $cableDistanceReguBat_AparMm) {
				$cableDistanceReguBat_Final=$cableDistanceReguBat_AparMm;
			} else {
				$cableDistanceReguBat_Final=$cableDistanceReguBat_Calc;
			}
		?>
		<li>Entre le régulateur et les batteries, pour une distance de <?= $_GET['distanceReguBat'] ?>m, il vous est conseillé un câble d'une section de <?= $cableDistanceReguBat_Final ?>mm²
		<a id="resultCalcCableReguBatShow">(voir, comprendre la démarche)</a></li>
		<div id="resultCalcCableReguBat" class="calcul">
			<p><a class="more" id="resultCalcCableReguBatHide">Cacher la démarche</a></p>
			<p>La formule pour calculer une seciton de câble pour éviter les pertes est :</p>
			<p>S = Rho x L x I / PT</p>
			<ul>
				<li>S (mm²) : Section du conducteur</li>
				<li>Rho (ohm) : <a href="https://fr.wikipedia.org/wiki/R%C3%A9sistivit%C3%A9" target="_blank">Résistivité</a> du conducteur (0,017ohm pour le cuivre)</li>
				<li>L (m) : LocableDistanceReguBat_Calcngueur aller + retour du conducteur</li>
				<li>I (A) : L’intensité (ici la puissance des panneaux / la tension des batteries)</li>
				<li>PT (V) : Perte de tension acceptée au niveau des câbles (<?= $_GET['cablagePtPourcent']?>% de la tension)</li>
					<ul><li>La tension des batteries (soit <?= $U ?>V) x <?= $_GET['cablagePtPourcent']?>/100</li></ul>
			</ul>
			<p>Dans votre cas ça nous fait : </p>
			<p>S = <?php echo $_GET['cablageRho'].' x ('.$_GET['distanceReguBat'].'x2) x ('.$parcPvW.' / '.$U.') / '.$PT.' = <b>'.$cableDistanceReguBat_Calc.'</b>'; ?>mm²</p>
			<?php 
			if ($cableDistanceReguBat_Calc < $cableDistanceReguBat_AparMm) {
				echo '<p>Mais cette section ne respecte pas la règle des '.$_GET['cablageRegleAparMm'].'A/mm² qui permet de se prémunir des échauffements. ';
				echo 'Pour respecter cette règle, il faudrait s\'approcher d\'une section de <b>'.$cableDistanceReguBat_Final.'</b>mm² <a rel="tooltip" class="bulles" title="'.$parcPvW.'W / '.$U.'V = '.$parcPvW/$U.'A<br />'.$parcPvW/$U.'A  / '.$_GET['cablageRegleAparMm'].'A/mm² = '.$cableDistanceReguBat_Final.'mm²">?</a></p>';
			}
			?>
		</div>
		<ul>
		<?php
		if ($cableDistanceReguBat_Calc < $cableDistanceReguBat_AparMm) {
			$meilleurCable = chercherCable_SecionPlusProche($cableDistanceReguBat_Final); 
		} else {
			$meilleurCable = chercherCable_SecionAudessus($cableDistanceReguBat_Final); 
		}
		if (empty($meilleurCable)) {
			echo '<li>Impossible de proposer une section de câble réaliste. Vous deviez peut être envisager de diminuer la distance entre les appareils.';
		} else { 
			$BudgetCable=$BudgetCable+$_GET['distanceReguBat']*$meilleurCable['prix'];
			?>
			<li>Section de câble la plus proche proposée : <b><?= $meilleurCable['nom'] ?></b>, pour un coût d'environ <?= $_GET['distanceReguBat']*$meilleurCable['prix'] ?>€</li>
		<?php } ?>
		</ul>
	</ul>
	<p>Un autre calculateur (plus complet) de sections de câbles est disponible sur <a href="http://www.sigma-tec.fr/textes/texte_cables.html" target="_blank">sigma-tec</a>.</p>
	<h3 id="resultatBudget">Budget</h3>
	<p>Ceci est une estimation grossière pour du matériel neuf, elle ne fait en aucun cas office de devis ;
	<ul>
		<?php
		$BudgetPvBas=$config_ini['prix']['pv_bas']*$meilleurParcPv['W']*$meilleurParcPv['nbPv'];
		$BudgetPvHaut=$config_ini['prix']['pv_haut']*$meilleurParcPv['W']*$meilleurParcPv['nbPv'];
		echo '<li>Panneaux photovoltaïque : entre '.convertNumber($BudgetPvBas, 'print').'€ et '.convertNumber($BudgetPvHaut, 'print').'€ (<a rel="tooltip" class="bulles" title="Coût estimé de '.$config_ini['prix']['pv_bas'] .'€/Wc en fourchette basse & '.$config_ini['prix']['pv_haut'] .'€/Wc en haute">?</a>)</li>';
		if ($meilleurParcBatterie['nbBatterieParalle'] != 99999) { 
			$BudgetBarBas=$config_ini['prix']['bat_'.$meilleurParcBatterie['type'].'_bas']*$meilleurParcBatterie['Ah']*$meilleurParcBatterie['V']*$meilleurParcBatterie['nbBatterieParalle']*$meilleurParcBatterie['nbBatterieSerie'];
			$BudgetBarHaut=$config_ini['prix']['bat_'.$meilleurParcBatterie['type'].'_haut']*$meilleurParcBatterie['Ah']*$meilleurParcBatterie['V']*$meilleurParcBatterie['nbBatterieParalle']*$meilleurParcBatterie['nbBatterieSerie'];
		} else { 
			$BudgetBarBas=$config_ini['prix']['bat_'.$BatType.'_bas']*$Cap*$U;
			$BudgetBarHaut=$config_ini['prix']['bat_'.$BatType.'_haut']*$Cap*$U;
		} 
		echo '<li>Batterie : entre '.convertNumber($BudgetBarBas, 'print').'€ et '.convertNumber($BudgetBarHaut, 'print').'€ (<a rel="tooltip" class="bulles" title="Avec un coût estimé de '.$config_ini['prix']['bat_'.$meilleurParcBatterie['type'].'_bas']*$meilleurParcBatterie['V'] .'€/Ah en fourchette basse & '.$config_ini['prix']['bat_'.$meilleurParcBatterie['type'].'_haut']*$meilleurParcBatterie['V'] .'€/Ah en haute">?</a>)</li>';
		if (!$meilleurRegulateur['nom']) {
			$budgetRegulateur=0;
			echo '<li>Régulateur : désolé nous n\'avons pas réussi à faire une hypothèse pour le régulateur.</li>';
		} else {
			$budgetRegulateur=$meilleurRegulateur['Prix']*$nbRegulateur;
			echo '<li>Régulateur : environ '.convertNumber($budgetRegulateur, 'print') .'€</li>';
		}
		if ($meilleurConvertisseur['nom'] == '') {
			$budgetConvertisseurBas=0;
			$budgetConvertisseurHaut=0;
			echo '<li>Convertisseur : désolé nous n\'avons pas réussi à faire une hypothèse pour un convertisseur.</li> ';
		} else {
			$budgetConvertisseurBas=$config_ini['prix']['conv_bas']*$meilleurConvertisseur['VA'];
			$budgetConvertisseurHaut=$config_ini['prix']['conv_haut']*$meilleurConvertisseur['VA'];
			echo '<li>Convertisseur : entre '.convertNumber($budgetConvertisseurBas, 'print').'€ et '.convertNumber($budgetConvertisseurHaut, 'print').'€ (<a rel="tooltip" class="bulles" title="Avec un coût estimé de '.$config_ini['prix']['conv_bas'].'€/VA en fourchette basse & '.$config_ini['prix']['conv_haut'].'€/VA en haute">?</a>)</li>';
		}
		echo '<li>Contrôleur de batterie : environ '.convertNumber($BudgetBatControleur, 'print') .'€</li>';
		echo '<li>Câblage : environ '.convertNumber($BudgetCable, 'print') .'€</li>';
		$budgetTotalBas=$BudgetPvBas+$BudgetBarBas+$budgetRegulateur+$budgetConvertisseurBas+$BudgetCable+$BudgetBatControleur;
		$budgetTotalHaut=$BudgetPvHaut+$BudgetBarHaut+$budgetRegulateur+$budgetConvertisseurHaut+$BudgetCable+$BudgetBatControleur;
		?>
	</ul>
	<p>Ce qui nous fait un budget total <b>entre <?= convertNumber($budgetTotalBas, 'print') ?> et <?= convertNumber($budgetTotalHaut, 'print') ?>€</b>. A ça il faut ajouter le prix des supports de panneau, du câblage/cosse ainsi des éléments de protections (fusible, coup batterie...).</p>
	<h3 id="resultatDon">Soutenir, contribuer</h3>
	<p>Si ce logiciel vous a été utile et/ou que vous voulez exprimer de la reconnaissance :  </p>
	<ul>
		<li><a href="http://david.mercereau.info/contact/"  target="_blank">Dites merci !</a> (ça fait toujours plaisir)</li>
		<li><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=MBDD2TG6D4TPC&lc=FR&item_name=CalcPvAutonome&item_number=calcpvautonome&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted"  target="_blank">Soutener en faisant un don</a> (sécurisé)</li>
		<li><a href="" target="_blank">Contribuer / améliorer le logiciel</a> si vous avez des compétences en programmation</li>
	</ul>
	<!-- Afficher ou non les informations complémentaire du formulaire -->
	<script type="text/javascript">
		$( "#aidePvgisShow" ).click(function() {
			$( "#aidePvgis" ).show( "slow" );
			$( "#aidePvgisShow" ).hide( "slow" );
		});
		$( "#aidePvgisHide" ).click(function() {
			$( "#aidePvgis" ).hide( "slow" );
			$( "#aidePvgisShow" ).show( "slow" );
		});
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
		$( "#resultCalcReguShow" ).click(function() {
			$( "#resultCalcRegu" ).show( "slow" );
			$( "#resultCalcReguShow" ).hide( "slow" );
		});
		$( "#resultCalcReguHide" ).click(function() {
			$( "#resultCalcRegu" ).hide( "slow" );
			$( "#resultCalcReguShow" ).show( "slow" );
		});
		$( "#resultCalcCablePvReguShow" ).click(function() {
			$( "#resultCalcCablePvRegu" ).show( "slow" );
			$( "#resultCalcCablePvReguShow" ).hide( "slow" );
		});
		$( "#resultCalcCablePvReguHide" ).click(function() {
			$( "#resultCalcCablePvRegu" ).hide( "slow" );
			$( "#resultCalcCablePvReguShow" ).show( "slow" );
		});
		$( "#resultCalcCableReguBatShow" ).click(function() {
			$( "#resultCalcCableReguBat" ).show( "slow" );
			$( "#resultCalcCableReguBatShow" ).hide( "slow" );
		});
		$( "#resultCalcCableReguBatHide" ).click(function() {
			$( "#resultCalcCableReguBat" ).hide( "slow" );
			$( "#resultCalcCableReguBatShow" ).show( "slow" );
		});
		$( "#aidePvgisHide" ).click();
		<?php if (empty($_GET['debug'])) { ?>
		$( "#resultCalcPvHide" ).click();
		$( "#resultCalcBatHide" ).click();
		$( "#resultCalcReguHide" ).click();
		$( "#resultCalcCablePvReguHide" ).click();
		$( "#resultCalcCableReguBatHide" ).click();
		<?php } ?>
	</script>
	<?php
	} 
	echo '</div>';
}

/*
 * ####### Formulaire #######
*/
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
	
	<div class="conseil debutant">
		<p><a href="http://david.mercereau.info/formation-pv/" target="_blank"><img style="float: right; padding: 10ppx" width="100	" src="./lib/FormationPv.png" alt="" /></a><b>Suggestion</b> : regarder la petite <a href="http://david.mercereau.info/formation-pv/" target="_blank">formation vidéo sur l'autonomie électrique photovoltaîque</a> pour un meilleur usage de ce calculateur.</p>
	</div>
	
	<h2 class="titre vous">Votre consommation :</h2>	
			
		<p>C'est l'étape la plus importante pour votre dimensionnement. Si vous ne connaissez pas cette valeur rendez-vous sur notre <b><a href="<?= $config_ini['formulaire']['UrlCalcConsommation'] ?>?from=CalcPvAutonome" id="DemandeCalcPvAutonome">interface de calcul de besoins journaliers</a></b></p>
		
		<div class="form Bj">
			<label>Vos besoins électriques journaliers :</label>
			<input id="Bj" type="number" min="1" max="99999" style="width: 100px;" value="<?php echo valeurRecup('Bj'); ?>" name="Bj" />  Wh/j
		</div>
		
		<div class="form Pmax">
			<label>Votre besoin en puissance électrique maximum :</label>
			<input id="Pmax" type="number" min="1" max="99999" style="width: 100px;" value="<?php echo valeurRecup('Pmax'); ?>" name="Pmax" />  W <a rel="tooltip" class="bulles" title="Il s'agit de la somme des puissances des appareils branché au même moment. <br />Par exemple si vous aviez un réfrégirateur de (70W), une scie sauteuse (500W) et une ampoule (7W) qui sont suceptibles d'être allumés en même temps votre besoin en puissance max est de 577W (70+500+7)">?</a>
		</div>
		
		<?php
		function ongletActif($id) {
			if ($_GET['Ej'] != '' && $id == 'valeur') {
				echo ' class="actif"';
			} elseif ($_GET['Ej'] == '' && $id == 'auto') {
				echo ' class="actif"';
			}
		}
		?>

	<div class="part pv">
		<h2 class="titre pv">Dimensionnement des panneaux photovoltaïques</h2>
	
		<p>Rayonnement en fonction de votre situation géographique : </p>
		<ul id="onglets">
			<li<?php echo ongletActif('auto'); ?>>Automatique</li>
			<li<?php echo ongletActif('valeur'); ?> id="EjOnglet">Manuel</li>
		</ul>
		<div id="contenu">
			
			<div class="modeInesSolaire item">				
				<p>Les données de rayonnement sont celles du site de  <a href="http://ines.solaire.free.fr/gisesol_1.php" target="_blank">INES</a>.</p>
				
				<div class="form InesVille">
					<label>Sélectionner la ville la plus proche de chez vous :</label>
					<select name="InesVille">
						<?php 
						foreach ($villes as $ville) { 
							$ville=utf8_encode($ville);
							echo '<option value="'.$ville.'"';
							valeurRecupSelect('InesVille', $ville);
							echo '>'.$ville.'</option>';
						}
						?>
					</select>
				</div>
				
				<div class="form InesSolaireAuto">
					<input type="checkbox" id="InesSolaireAuto" name="InesSolaireAuto"
					<?php
					if (empty($_GET['submit']) || isset($_GET['InesSolaireAuto'])) {
						echo ' checked="checked"'; 
					}
					?>
					/> Choisir l'orientation et l'inclinaison la plus favorable dans ma ville
				</div>
				
				<div class="form InesManuel InesInclinaison">
					<label>Inclinaison des panneaux :</label>
					<select name="InesInclinaison">
						<?php 
						foreach ($inclinaisons as $inclinaison) { 
							$inclinaison=utf8_encode($inclinaison);
							echo '<option value="'.$inclinaison.'"';
							valeurRecupSelect('InesInclinaison', $inclinaison);
							echo '>'.$inclinaison.'°</option>';
						}
						?>
					</select>
					<?= $aideInclinaison ?>
					
					
				</div>
				<div class="form InesManuel InesOrientation">
					<label>Orientation des panneaux :</label>
					<select name="InesOrientation">
						<?php 
						foreach ($orientations as $orientation) { 
							$orientation=utf8_encode($orientation);
							echo '<option value="'.$orientation.'"';
							echo valeurRecupSelect('InesOrientation', $orientation);
							echo '>'.$orientation.'°</option>';
						}
						?>
					</select>
					<?= $aideOrientation ?>
				</div>
				<div class="form InesAlbedo">
					<label>Albédo du sol :</label>
					<select name="InesAlbedo">
						<?php 
						foreach ($albedos as $albedo) { 
							$albedo=utf8_encode($albedo);
							echo '<option value="'.$albedo.'"';
							echo valeurRecupSelect('InesAlbedo', $albedo);
							echo '>'.$albedo.'</option>';
						}
						?>
					</select>
					<?= $aideAlbedo ?>
				</div>
				<div class="form InesPeriode">
					<label>Autonomie souhaité :</label>
					<select name="InesPeriode" id="InesPeriode">
						<option value="complete"<?php echo valeurRecupSelect('InesPeriode', 'complete'); ?>>Annuelle / complète</option>
						<option value="partielle"<?php echo valeurRecupSelect('InesPeriode', 'partielle'); ?>>Saisonnière / partielle</option>
					</select>
					<div class="form InesPeriodeDebutFin">
						<label>Sélectiononer la période :</label>
						<select name="InesPeriodeDebut">
							<?php
							foreach ($mois as $moisId=>$moisNom) { 
								$moisNom=utf8_encode($moisNom);
								echo '<option value="'.$moisId.'"';
								valeurRecupSelect('InesPeriodeDebut', $moisId);
								echo '>'.$moisNom.'</option>';
							}
							?>
						</select>
						<select name="InesPeriodeFin">
							<?php
							foreach ($mois as $moisId=>$moisNom) { 
								$moisNom=utf8_encode($moisNom);
								echo '<option value="'.$moisId.'"';
								valeurRecupSelect('InesPeriodeFin', $moisId);
								echo '>'.$moisNom.'</option>';
							}
							?>
						</select>
					</div>
				</div>
				
			</div>
			<div class="modeInput item">
				<div class="form Ej">
					<label>Donner la valeur du rayonnement moyen quotidien du mois le plus défavorable dans le plan (l'inclinaison) du panneau :</label>
					<input maxlength="4" size="4" id="Ej" type="number" step="0.01" min="0" max="10" style="width: 100px;" value="<?php echo valeurRecup('Ej'); ?>" name="Ej" /> kWh/m²/j
					<p>Pour obtenir cette valeur rendez vous sur le site de <a href="http://ines.solaire.free.fr/gisesol_1.php" target="_blank">INES</a>, choisir votre ville, l'inclinaison & l'orientation des panneaux puis valider. Il s'agit ensuite de prendre la plus basse valeur de la ligne "Globale (IGP)" (dernière ligne du second tableau) Plus d'informations en bas de cette page : <a href="http://www.photovoltaique.guidenr.fr/cours-photovoltaique-autonome/VI_calcul-puissance-crete.php">Comment obtenir la valeur de Ei, Min sur le site de l'INES ?</a></p>
				</div>
			</div>
			
		</div>
		
		<div class="form ModPv">
			<label><a onclick="window.open('<?= $config_ini['formulaire']['UrlModeles'] ?>&data=pv','Les modèles de panneaux','directories=no,menubar=no,status=no,location=no,resizable=yes,scrollbars=yes,height=500,width=600,fullscreen=no');">Modèle de panneau</a> : </label>
			<select id="ModPv" name="ModPv">
				<option value="auto">Automatique</option>
				<option value="perso" style="font-weight: bold"<?php echo valeurRecupSelect('ModPv', 'perso'); ?>>Personnaliser</option>
				<?php 
				foreach ($config_ini['pv'] as $pvModele => $pvValeur) {
					echo '<option value="'.$pvModele.'"';
					echo valeurRecupSelect('ModPv', $pvModele);
					echo '>'.ucfirst($pvValeur['type']).' '.$pvValeur['W'].'Wc en '.$pvValeur['V'].'V</option>';
					echo "\n";
				}
				?>
			</select> 
		</div>
		<div class="form TypePv">
			<label>Technologie préféré de panneau : </label>
			<select id="TypePv" name="TypePv">
				<option value="monocristalin"<?php echo valeurRecupSelect('TypePv', 'monocristalin'); ?>>Monocristalin</option>
				<option value="polycristallin"<?php echo valeurRecupSelect('TypePv', 'polycristallin'); ?>>Polycristallin</option>
			</select> 
		</div>
		
		<div class="form PersoPv">
			<p>Vous pouvez détailler les caractéristiques techniques de votre panneau : </p>
			<ul>
				<li>
					<label>Puissance maximum (Pmax)  : </label>
					<input type="number" min="1" max="9999" style="width: 70px;" value="<?php echo valeurRecup('PersoPvW'); ?>"  name="PersoPvW" />Wc
				</li>
				<li>
					<label>Tension : </label>
					<input type="number" min="1" max="999" style="width: 70px;" value="<?php echo valeurRecup('PersoPvV'); ?>" name="PersoPvV" />V
				</li>
				<li>
					<label>Tension en circuit ouvert (Voc) </label>
					<input type="number" step="0.01" min="1" max="99" style="width: 70px;" value="<?php echo valeurRecup('PersoPvVdoc'); ?>"  name="PersoPvVdoc" />V
				</li>
				<li>
					<label>Courant de court circuit (Isc)</label>
					<input type="number" step="0.01" min="0,01" max="99" style="width: 70px;" value="<?php echo valeurRecup('PersoPvIsc'); ?>"  name="PersoPvIsc" />A
				</li>
			</ul>
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
		<p>Cette application est pré-paramétrée pour des batteries plomb (AGM/Gel/OPvS/OPzV)</p>
		<div class="form Aut">
			<label>Nombre de jours d'autonomie : </label>
			<input maxlength="2" size="2" id="Aut" type="number" step="1" min="0" max="50" style="width: 50px" value="<?php echo valeurRecup('Aut'); ?>" name="Aut" />
		</div>
		<div class="form U">
			<label>Tension finale du parc de batteries : </label>
			<select id="U" name="U">
				<option value="0"<?php echo valeurRecupSelect('U', 0); ?>>Auto</option>
				<option value="12"<?php echo valeurRecupSelect('U', 12); ?>>12</option>
				<option value="24"<?php echo valeurRecupSelect('U', 24); ?>>24</option>
				<option value="48"<?php echo valeurRecupSelect('U', 48); ?>>48</option>
			</select> V <a rel="tooltip" class="bulles" title="En mode automatique la tension des batteries sera déduite du besoin en panneaux<br />De 0 à 500Wc : 12V<br />De 500 à 1500 Wc : 24V<br />Au dessus de 1500 Wc : 48V">(?)</a>
		</div>
		<div class="form DD">
			<label>Degré de décharge limite : </label>
			<input maxlength="2" size="2" id="DD" type="number" step="1" min="0" max="100" style="width: 70px" value="<?php echo valeurRecup('DD'); ?>" name="DD" /> %
		</div>
		<div class="form ModBat">
			<label><a onclick="window.open('<?= $config_ini['formulaire']['UrlModeles'] ?>&data=batterie','Les modèles des batteries','directories=no,menubar=no,status=no,location=no,resizable=yes,scrollbars=yes,height=500,width=600,fullscreen=no');">Modèle de batterie</a> (<a href="http://www.batterie-solaire.com/batterie-delestage-electrique.htm" target="_blank">donné en C10</a>) : </label>
			<select id="ModBat" name="ModBat">
				<option value="auto">Automatique</option>
				<option value="perso" style="font-weight: bold"<?php echo valeurRecupSelect('ModBat', 'perso'); ?>>Personnaliser</option>
				<?php 
				foreach ($config_ini['batterie'] as $batModele => $batValeur) {
					echo '<option value="'.$batModele.'"';
					echo valeurRecupSelect('ModBat', $batModele);
					echo '>'.$batValeur['nom'].'</option>';
					echo "\n";
				}
				?>
			</select> <a rel="tooltip" class="bulles" title="En mode automatique, au dessus de 500A, il sera utilisé des batteries GEL OPzV 2V">(?)</a>
		</div>
		<div class="form TypeBat">
			<label>Technologie de batteries préféré : </label>
			<select id="TypeBat" name="TypeBat">
				<option value="auto"<?php echo valeurRecupSelect('TypeBat', 'auto'); ?>>Auto.</option>
				<option value="AGM"<?php echo valeurRecupSelect('TypeBat', 'AGM'); ?>>AGM</option>
				<option value="GEL"<?php echo valeurRecupSelect('TypeBat', 'GEL'); ?>>Gel</option>
				<option value="OPzV"<?php echo valeurRecupSelect('TypeBat', 'OPzV'); ?>>OPzV</option>
				<option value="OPzS"<?php echo valeurRecupSelect('TypeBat', 'OPzS'); ?>>OPzS</option>
			</select> 
		</div>
		<div class="form PersoBat">
			<p>Vous pouvez détailler les caractéristiques techniques de votre batterie : </p>
			<ul>
				<li>
					<label>Capacité (C10) : </label>
					<input type="number" min="1" max="9999" style="width: 70px;" value="<?php echo valeurRecup('PersoBatAh'); ?>"  name="PersoBatAh" />Ah
				</li>
				<li>
					<label>Tension : </label>
					<select id="PersoBatV" name="PersoBatV">
						<option value="2"<?php echo valeurRecupSelect('PersoBatV', 2); ?>>2</option>
						<option value="4"<?php echo valeurRecupSelect('PersoBatV', 4); ?>>4</option>
						<option value="6"<?php echo valeurRecupSelect('PersoBatV', 6); ?>>6</option>
						<option value="12"<?php echo valeurRecupSelect('PersoBatV', 12); ?>>12</option>
					</select> V
				</li>
			</ul>
		</div>
		<div class="form IbatCharge">
			<label>Capacité de courant de charge max : </label>
			<input maxlength="2" size="2" id="IbatCharge" type="number" step="1" min="0" max="100" style="width: 70px" value="<?php echo valeurRecup('IbatCharge'); ?>" name="IbatCharge" /> %
		</div>
		<div class="form IbatDecharge">
			<label>Capacité de courant de décharge max : </label>
			<input  maxlength="2" size="2" id="IbatDecharge" type="number" step="1" min="0" max="100" style="width: 70px" value="<?php echo valeurRecup('IbatDecharge'); ?>" name="IbatDecharge" /> %
		</div>
	</div>
	
	<div class="part regu">
		<h2 class="titre regu">Regulateur de charge</h2>
		<div class="form ModRegu">
			<label><a onclick="window.open('<?= $config_ini['formulaire']['UrlModeles'] ?>&data=regulateur','Les modèles de régulateur','directories=no,menubar=no,status=no,location=no,resizable=yes,scrollbars=yes,height=500,width=670,fullscreen=no');">Modèle de régulateur</a> : </label>
			<select id="ModRegu" name="ModRegu">
				<option value="auto">Automatique</option>
				<option value="perso" style="font-weight: bold"<?php echo valeurRecupSelect('ModRegu', 'perso'); ?>>Personnaliser</option>
				<?php 
				$ReguModeleDoublonCheck[]=null;
				foreach ($config_ini['regulateur'] as $ReguModele => $ReguValeur) {
					if (!in_array(substr($ReguModele, 0, -3), $ReguModeleDoublonCheck)) {	
						echo '<option value="'.substr($ReguModele, 0, -3).'"';
						echo valeurRecupSelect('ModRegu', substr($ReguModele, 0, -3));
						echo '>'.$ReguValeur['nom'].'</option>';
						echo "\n";
						$ReguModeleDoublonCheck[]=substr($ReguModele, 0, -3);
					}
				}
				?>
			</select>
		</div>
		<div class="form PersoRegu">
			<p>Vous pouvez détailler les caractéristiques techniques de votre régulateur solair : </p>
			<ul>
				<li>
					<label>Tension finale des batteries : <a rel="tooltip" class="bulles" title="Cette valeur se change dans 'Dimensionnement du parc batteries'"><span id="PersoReguVbat"></span>V</a></label>
				</li>
				<li>
					<label>Puissance maximale PV : </label>
					<input type="number" min="1" max="9999" style="width: 70px;" value="<?php echo valeurRecup('PersoReguPmaxPv'); ?>"  name="PersoReguPmaxPv" />W
				</li>
				<li>
					<label>Tension PV maximale de circuit ouvert : </label>
					<input type="number" min="1" max="9999" style="width: 70px;" value="<?php echo valeurRecup('PersoReguVmaxPv'); ?>" name="PersoReguVmaxPv" />V
				</li>
				<li>
					<label>Max. PV courant (Puissance / Tension) :</label>
					<input type="number" step="0.01" min="0,01" max="999" style="width: 70px;" value="<?php echo valeurRecup('PersoReguImaxPv'); ?>"  name="PersoReguImaxPv" />A
				</li>
			</ul>
		</div>
		<div class="form reguMargeIcc">
			<label>Marge de sécurité du courant de court-circuit Icc des panneaux : </label>
			<input maxlength="2" size="2" id="reguMargeIcc" type="number" step="1" min="0" max="100" style="width: 70px" value="<?php echo valeurRecup('reguMargeIcc'); ?>" name="reguMargeIcc" /> %
		</div>
	</div>
	
	<div class="part cable">
		<h2 class="titre cable">Câblage</h2>
		<p>On considère un câblage solaire souple en cuivre.</p>
		<div class="form cablePvRegu">
			<label>Distance (aller simple) entre les panneaux et le régulateur : </label>
			<input maxlength="2" size="2" id="distancePvRegu" type="number" step="0.5" min="0" max="100" style="width: 70px" value="<?php echo valeurRecup('distancePvRegu'); ?>" name="distancePvRegu" /> m
		</div>
		<div class="form cableReguBat">
			<label>Distance (aller simple) entre le régulateur et les batteries : </label>
			<input maxlength="2" size="2" id="distanceReguBat" type="number" step="0.5" min="0" max="100" style="width: 70px" value="<?php echo valeurRecup('distanceReguBat'); ?>" name="distanceReguBat" /> m
		</div>
		<div class="form cablageRho">
			<label>La résistivité du conducteur (rhô) mm²/m  : </label>
			<input maxlength="4" size="4" id="cablageRho" type="number" step="0.001" min="0" max="10" style="width: 70px" value="<?php echo valeurRecup('cablageRho'); ?>" name="cablageRho" /> ohm
		</div>
		<div class="form cablagePtPourcent">
			<label>Chute de tension tolérable : </label>
			<input maxlength="2" size="2" id="cablagePtPourcent" type="number" step="0.1" min="0" max="100" style="width: 70px" value="<?php echo valeurRecup('cablagePtPourcent'); ?>" name="cablagePtPourcent" /> %
		</div>
		<div class="form cablageRegleAparMm">
			<label>Ratio pour se pérmunir de l'échauffement du câble : </label>
			<input maxlength="2" size="2" id="cablageRegleAparMm" type="number" step="0.1" min="0" max="100" style="width: 70px" value="<?php echo valeurRecup('cablageRegleAparMm'); ?>" name="cablageRegleAparMm" /> A/mm²
		</div>
	</div>
		
	<div class="form End">
		
		<input id="Reset" type="button" value="Remise à 0" name="reset" />
		<input id="Submit" type="submit" value="Lancer le calcul" name="submit" />
		<img style="margin-left: 150px; vertical-align:bottom;" src="https://www.paypalobjects.com/fr_FR/FR/i/btn/btn_donate_SM.gif" border="0" alt="Faire un don"  onclick="location.href='https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=MBDD2TG6D4TPC&lc=FR&item_name=CalcPvAutonome&item_number=calcpvautonome&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted';" />
	</div>
	<div class="form ModeDebug"><input type="checkbox" name="debug" <?php if (isset($_GET['debug'])) echo 'checked="checked"'; ?> />Activer le mode transparent/debug pour mieux comprendre le fonctionnement</div>
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

$( "#InesPeriode" ).change(function () {
	InesPeriodeChange();
});
$( "#InesSolaireAuto" ).change(function () {
	InesSolaireAutoChange();
});
$( "#ModPv" ).change(function () {
	modPvChange();
});
$( "#ModBat" ).change(function () {
	modBatChange();
});
$( "#ModRegu" ).change(function () {
	modReguChange();
});
$( "#U" ).change(function () {
	$( "#PersoReguVbat" ).text($( "#U" ).val());
});

// Bouton Submit activation / désactivation
function sumbitEnable() {
	if (($( "#Bj" ).val() > 0) && $( "#Pmax" ).val() > 0) {
		$( "#Submit" ).prop('disabled', false);
	} else {
		$( "#Submit" ).prop('disabled', true);
	}
}
$( "#Bj" ).change(function() {
	sumbitEnable();
});
$( "#Pmax" ).change(function() {
	sumbitEnable();
});

// INES
function InesPeriodeChange() {
	if ($( "#InesPeriode" ).val() == 'partielle') {
		$( ".InesPeriodeDebutFin" ).show();
	} else {
		$( ".InesPeriodeDebutFin" ).hide();
	}
}
function InesSolaireAutoChange() {
	if ($("#InesSolaireAuto").is(':checked')) {
		$( ".InesManuel" ).hide();
		$( ".InesPeriode" ).hide();
	} else {
		$( ".InesManuel" ).show();
		$( ".InesPeriode" ).show();
	}
}
// Changement de modèle de PV
function modPvChange() {
	if ($( "#ModPv" ).val() == 'auto') {
		$( ".form.TypePv" ).show();
		$( ".form.PersoPv" ).hide();
	} else if ($( "#ModPv" ).val() == 'perso') {
		$( ".form.TypePv" ).hide();
		$( ".form.PersoPv" ).show();
	} else {
		$( ".form.TypePv" ).hide();
		$( ".form.PersoPv" ).hide();
	}
}
// Changement de modèle de batterie
function modBatChange() {
	if ($( "#ModBat" ).val() == 'auto') {
		$( ".form.TypeBat" ).show();
		$( ".form.PersoBat" ).hide();
	} else if ($( "#ModBat" ).val() == 'perso') {
		$( ".form.TypeBat" ).hide();
		$( ".form.PersoBat" ).show();
	} else {
		$( ".form.TypeBat" ).hide();
		$( ".form.PersoBat" ).hide();
	}
}
// Changement modèle régulateur
function modReguChange() {
	if ($( "#ModRegu" ).val() == 'auto') {
		$( ".form.TypeRegu" ).show();
		$( ".form.PersoRegu" ).hide();
		if ($("#U option").length == 3) {
			$("#U").append('<option value="0">Auto</option>');
		}
	} else if ($( "#ModRegu" ).val() == 'perso') {
		$( ".form.TypeRegu" ).hide();
		$( ".form.PersoRegu" ).show();
		$("#U option[value='0']").remove();
		$( "#PersoReguVbat" ).text($( "#U" ).val());
	} else {
		$( ".form.TypeRegu" ).hide();
		$( ".form.PersoRegu" ).hide();
		if ($("#U option").length == 3) {
			$("#U").append('<option value="0">Auto</option>');
		}
	}
	
}

// Changement de niveau
$( "#Ni" ).change(function () {
	changeNiveau();
});
function changeNiveau() {
	// Debutant (1)
	if ($( "#Ni" ).val() == 1) {
		$( ".conseil.debutant" ).show();
		$( "#EjOnglet" ).hide();
		$( ".form.Ri" ).hide();
		$( ".form.Rb" ).hide();
		$( ".form.AUT" ).hide();
		$( ".form.U" ).hide();
		$( ".form.DD" ).hide();
		$( ".part.bat" ).hide();
		$( ".part.regu" ).hide();
		$( ".form.ModBat" ).hide();
		$( ".form.IbatCharge" ).hide();
		$( ".form.IbatDecharge" ).hide();
		$( ".form.ModPv" ).hide();
		$( ".form.TypePv" ).hide();
		$( ".part.cable" ).hide();
		$( ".form.InesAlbedo" ).hide();
		<?php if (isset($_GET['InesSolaireAuto'])) { ?>
			$("#InesSolaireAuto").prop('checked', true);
		<?php } ?>
		$( ".form.ModeDebug" ).hide();
	// Eclaire (2)
	} else if  ($( "#Ni" ).val() == 2) {
		$( ".conseil.debutant" ).show();
		$( "#EjOnglet" ).show();
		$( ".form.Ri" ).hide();
		$( ".form.Rb" ).hide();
		$( ".form.AUT" ).show();
		$( ".form.U" ).hide();
		$( ".form.DD" ).hide();
		$( ".part.bat" ).show();
		$( ".part.regu" ).hide();
		$( ".form.ModBat" ).hide();
		$( ".form.IbatCharge" ).hide();
		$( ".form.IbatDecharge" ).hide();
		$( ".form.ModPv" ).hide();
		$( ".form.TypePv" ).show();
		$( ".part.cable" ).show();
		$( ".form.cablageRho" ).hide();
		$( ".form.cablagePtPourcent" ).hide();
		$( ".form.cablageRegleAparMm" ).hide();
		$( ".form.InesAlbedo" ).hide();
		<?php if (empty($_GET['InesSolaireAuto'])) { ?>
			$("#InesSolaireAuto").prop('checked', false);
		<?php } ?>
		$( ".form.ModeDebug" ).hide();
	// Expert (3)
	} else if ($( "#Ni" ).val() == 3) {
		$( ".conseil.debutant" ).hide();
		$( "#EjOnglet" ).show();
		$( ".form.Ri" ).show();
		$( ".form.Rb" ).show();
		$( ".form.AUT" ).show();
		$( ".form.U" ).show();
		$( ".form.DD" ).show();
		$( ".part.bat" ).show();
		$( ".part.regu" ).show();
		$( ".form.ModBat" ).show();
		$( ".form.IbatCharge" ).show();
		$( ".form.IbatDecharge" ).show();
		$( ".form.ModPv" ).show();
		$( ".form.TypePv" ).show();
		$( ".part.cable" ).show();
		$( ".form.cablageRho" ).show();
		$( ".form.cablagePtPourcent" ).show();
		$( ".form.cablageRegleAparMm" ).show();
		$( ".form.InesAlbedo" ).show();
		<?php if (empty($_GET['InesSolaireAuto'])) { ?>
			$("#InesSolaireAuto").prop('checked', false);
		<?php } ?>
		$( ".form.ModeDebug" ).show();
	}
	InesSolaireAutoChange();
}

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

$(document).ready(function() {
	// Init formulaire 
	changeNiveau();
	modPvChange(); 
	modBatChange();
	modReguChange(); 
	InesPeriodeChange();
	sumbitEnable();	
}); 


</script>

