<?php
// Retir les accents
// http://www.weirdog.com/blog/php/supprimer-les-accents-des-caracteres-accentues.html
function wd_remove_accents($str)
{
    $str = htmlentities($str, ENT_NOQUOTES); 
    $str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
    $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
    $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères
    return $str;
}
// Ajoute le ° si c'est une valeur chiffré
function ajoutDegSiAngleChiffre($deg) {
	if (preg_match('#[0-9]+$#',$deg)) {
		return $deg.'°';
	} else {
		return $deg;
	}
}
// Transforme 'sud' en 0°
function nomEnAngle($val) {
	if (!preg_match('#^[0-9]+$#',$val)) {
		switch($val) {
			case 'Sud':
			case 'sud':
				$val='0°';
			break;
			case 'Est':
			case 'est':
				$val='-90°';
			break;
			case 'Ouest':
			case 'ouest':
				$val='90°';
			break;
			case 'Nord':
			case 'nord':
				$val='180°';
			break;
		} 
	}
	return $val;
}
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
function debug($msg, $balise=null) {
	if (isset($_GET['debug'])) {
		if (isset($balise)) {
			echo '<'.$balise.' class="debug">'.$msg.'</'.$balise.'>';
		} else {
			echo $msg;
		}
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
// On cherche le bon câble
function chercherCable_SecionAudessus($sectionMinimum) {
	global $config_ini;
	foreach ($config_ini['cablage'] as $idCable => $cable) {
		debug('Pour une section minimum de '.$sectionMinimum.', on test '.$cable['diametre'], 'p');
		if ($sectionMinimum < $cable['diametre']) {
			$meilleurCable['nom']=$cable['nom'];
			$meilleurCable['diametre']=$cable['diametre'];
			$meilleurCable['prix']=$cable['prix'];
			break;
		}
	}
	return $meilleurCable;
}
function chercherCable_SecionPlusProche($sectionMinimum) {
	global $config_ini;
	$meilleurCable['diffSection']=9999;
	foreach ($config_ini['cablage'] as $idCable => $cable) {
		$diffSection=$sectionMinimum-$cable['diametre'];
		// Si la différence est négative on la met positive pour pouvoir la comparer
		if ($diffSection < 0) {
			$diffSection=$diffSection*-1;
		}
		debug('<p>Pour une section la plus proche de '.$sectionMinimum.', on test '.$cable['diametre'].', il y a une différence de '.$diffSection);
		if ($diffSection <= $meilleurCable['diffSection']) {
			$meilleurCable['nom']=$cable['nom'];
			$meilleurCable['diametre']=$cable['diametre'];
			$meilleurCable['prix']=$cable['prix'];
			$meilleurCable['diffSection']=$diffSection;
			debug(' : *** Nouvelle bonne config');
		}
		debug('</p>');
	}

	return $meilleurCable;
	
}

// On cherche le bon convertisseur
function chercherConvertisseur($U,$Pmax) {
	global $config_ini;
	debug('Tension '.$U, 'p');
	foreach ($config_ini['convertisseur'] as $convertisseur) {
		if ($U == $convertisseur['Vbat']) {
			debug('Test pour le convertisseur '.$convertisseur['nom'], 'p');
			if ($Pmax <= $convertisseur['Pmax']) {
				$meilleurConvertisseur['nom']=$convertisseur['nom'];
				$meilleurConvertisseur['Pmax']=$convertisseur['Pmax'];
				$meilleurConvertisseur['Ppointe']=$convertisseur['Ppointe'];
				$meilleurConvertisseur['VA']=$convertisseur['VA'];
				break;
			}
		}
	}
	return $meilleurConvertisseur;
}
?>
