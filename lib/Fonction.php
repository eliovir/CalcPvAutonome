<?php
// Formulaire afficher ce qui est en get ou ce qui est dans la config
function valeurRecup($nom) {
	global $config_ini;
	if (isset($_GET[$nom])) {
		echo $_GET[$nom]; 
	} else if ($config_ini['formulaire'][$nom]) {
		echo $config_ini['formulaire'][$nom];
	} else {
		echo '';
	}
}
// Forumaire sur les select mettre le "selected" au bon endroit selon le get ou la config
function valeurRecupSelect($nom, $valeur) {
	global $config_ini;
	if ($_GET[$nom] == $valeur) {
		echo ' selected="selected"'; 
	} else if (empty($_GET[$nom]) && $config_ini['formulaire'][$nom] == $valeur) {
		echo ' selected="selected"'; 
	} else {
		echo '';
	}
}
// Convertie les nombre pour l'affichage ou pour les calculs
function convertNumber($number, $to = null) {
	if ($to == 'print') {
		return number_format($number, 0, ',', ' ');
	} else {
		return number_format($number, 3, '.', '');
	}
}

// Affichage du debug
function debug($msg) {
	if (isset($_GET['debug'])) {
		echo '<span class="debug">'.$msg.'</span>';
	}
}

// Affichage des erreurs du formulaire
function erreurPrint($id, $msg) {
	return '<li>'.$msg.'</li>';
}


// Recherche bonne config régulateur
function chercherRegulateur() {
	
	global $nbRegulateur,$parcPvW,$parcPvV,$parcPvI,$config_ini,$U,$meilleurParcBatterie,$_GET,$batICharge;
	
	$meilleurRegulateur['nom'] = null;
	$meilleurRegulateur['diffRegulateurParcPvW'] = 99999;
	$meilleurRegulateur['diffRegulateurParcPvV'] = 99999;
	$meilleurRegulateur['diffRegulateurParcPvA'] = 99999	;
	
	debug('<ul type="1">');
	
	if ($_GET['ModRegu'] == 'perso') {
		// Mode perso
		
		debug('<li>');
		debug('Test de config avec le régulateur perso ::: ');


		debug($parcPvW.'&lt;'.$_GET['PersoReguPmaxPv'].'W, ');
		debug($parcPvV.'&lt;'.$_GET['PersoReguVmaxPv'].'V, ');
		debug($parcPvI.'&lt;'.$_GET['PersoReguImaxPv'].'A');

		if ($parcPvW < $_GET['PersoReguPmaxPv']
		&& $parcPvV < $_GET['PersoReguVmaxPv']
		&& $parcPvI < $_GET['PersoReguImaxPv']) {
			debug(' | ** ça fonctionne ** ');
			$meilleurRegulateur['nom'] = '(personnalisé)';
			$meilleurRegulateur['Vbat'] = $_GET['U'];
			$meilleurRegulateur['PmaxPv'] = $_GET['PersoReguPmaxPv'];
			$meilleurRegulateur['VmaxPv'] = $_GET['PersoReguVmaxPv'];
			$meilleurRegulateur['ImaxPv'] = $_GET['PersoReguImaxPv'];
			$meilleurRegulateur['Prix'] = $regulateur['Prix'];
		} 
	
	debug('</li>');
	
	} else {
		// Mode auto ou choisie
		foreach ($config_ini['regulateur'] as $idRegulateur => $regulateur) {
			
			// Si un modèle de régulateur à été choisie, on est plus en mode automatique
			if ($_GET['ModRegu'] != 'auto' && $_GET['ModRegu'] != substr($idRegulateur, 0, -3)) {
				continue;
			}
			
			// On conserve uniquement les références supportant la même tension Vbat
			if ($U != $regulateur['Vbat']) {
				continue;
			}
			
			// Debug
			debug('<li>');
			debug('Test de config avec le '.$regulateur['nom'].' ::: ');
			
			debug($parcPvW.'&lt;'.$regulateur['PmaxPv'].'W, ');
			debug($parcPvV.'&lt;'.$regulateur['VmaxPv'].'V, ');
			debug($parcPvI.'&lt;'.$regulateur['ImaxPv'].'A');
			
			if ($parcPvW < $regulateur['PmaxPv']
			&& $parcPvV < $regulateur['VmaxPv']
			&& $parcPvI < $regulateur['ImaxPv']) {
				debug(' | ** ça fonctionne ** ');
			} else {
				continue;
			}
			
			// Différence avec la capacité souhauté
			$diffRegulateurParcPvW=$regulateur['PmaxPv']-$parcPvW;
			$diffRegulateurParcPvV=$regulateur['VmaxPv']-$parcPvV;
			$diffRegulateurParcPvI=$regulateur['ImaxPv']-$parcPvI;
			
			if ($diffRegulateurParcPvW < $meilleurRegulateur['diffRegulateurParcPvW']
			|| $diffRegulateurParcPvV < $meilleurRegulateur['diffRegulateurParcPvV']
			|| $diffRegulateurParcPvI < $meilleurRegulateur['diffRegulateurParcPvA']) {
				debug(' | ** meilleur config **');
				$meilleurRegulateur['diffRegulateurParcPvW'] = $diffRegulateurParcPvW;
				$meilleurRegulateur['diffRegulateurParcPvV'] = $diffRegulateurParcPvV;
				$meilleurRegulateur['diffRegulateurParcPvA'] = $diffRegulateurParcPvI;
				$meilleurRegulateur['nom'] = $regulateur['nom'];
				$meilleurRegulateur['Vbat'] = $regulateur['Vbat'];
				$meilleurRegulateur['PmaxPv'] = $regulateur['PmaxPv'];
				$meilleurRegulateur['VmaxPv'] = $regulateur['VmaxPv'];
				$meilleurRegulateur['ImaxPv'] = $regulateur['ImaxPv'];
				$meilleurRegulateur['Prix'] = $regulateur['Prix'];
			}
			debug('</li>');
		}
	}
	debug('</ul>');

	return $meilleurRegulateur;
	
}

?>
