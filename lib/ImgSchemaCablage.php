<?php

// Script pour générer les schéma de câblage

// Aide : https://openclassrooms.com/courses/concevez-votre-site-web-avec-php-et-mysql/creer-des-images-en-php

$config_ini = parse_ini_file('../config.ini', true); 

function debug($msg) {
	if (isset($_GET['debug'])) {
		echo '<p class="debug">'.$msg.'</p>';
	}
}

// Inspiré de http://codes-sources.commentcamarche.net/source/35997-centrer-un-texte-dans-une-image-gd
function centrage_texte($chaine,$taillePolice, $widthTotal) {
	// Je calcule le nombre de caractères dans la chaine
	$a = strlen($chaine);
	// Je calcule la taille d'un caractère par rapport à la taille de la police
	$b = imagefontwidth($taillePolice);
	// Je calcule la taille de ma chaine de caractères		
	$c = $a*$b;
	// Je calcule combien il me reste de caractères/espace sur les côtés pour centrer mon texte
	$d = $widthTotal-$c;
	// Je recherche l'emplacement où débutera ma chaine de caractères
	$e = $d/2;	
	// La chaine commencera ... à cet emplacement	
	return $e;		
}
// Inspiré de http://codes-sources.commentcamarche.net/source/35997-centrer-un-texte-dans-une-image-gd
function centrage_img($chaine,$taillePolice, $widthTotal) {
	// Je calcule le nombre de caractères dans la chaine
	$a = strlen($chaine);
	// Je calcule la taille d'un caractère par rapport à la taille de la police
	$b = imagefontwidth($taillePolice);
	// Je calcule la taille de ma chaine de caractères		
	$c = $a*$b;
	// Je calcule combien il me reste de caractères/espace sur les côtés pour centrer mon texte
	$d = $widthTotal-$c;
	// Je recherche l'emplacement où débutera ma chaine de caractères
	$e = $d/2;	
	// La chaine commencera ... à cet emplacement	
	return $e;		
}

function shcema_cablage_generer_part($type, $nbSerie, $nbParallele, $batType) {
	global $config_ini;
	if ($type == 'bat') {
		$objW=$config_ini['schemaCablage']['batType'.$batType.'W'];
		$objH=$config_ini['schemaCablage']['batType'.$batType.'H'];
	} else if ($type == 'pv') {
		$objW=$config_ini['schemaCablage']['pvW'];
		$objH=$config_ini['schemaCablage']['pvH'];
	} else {
		exit('Problème dans les paramètres pour générer l\'image');
	}

	$objMarge=$config_ini['schemaCablage']['objMarge'];
	$objTrait=$config_ini['schemaCablage']['objTrait'];
	$objTraitCable=$config_ini['schemaCablage']['objTraitCable'];
	$objPolice=$config_ini['schemaCablage']['objPolice'];

	// Total de l'image 
	if ($type == 'bat') {
		$largeurTotalImage=$nbSerie*$objW+$objMarge*$nbSerie+$objMarge;
		$hauteurTotalImage=$nbParallele*$objH+$objMarge*$nbParallele+$objMarge;
	} else if ($type == 'pv') {
		$largeurTotalImage=$nbSerie*$objW+$objMarge*$nbSerie+$objMarge;
		$hauteurTotalImage=$nbParallele*$objH+$objMarge*$nbParallele+$objMarge+$config_ini['schemaCablage']['reguH']+$objMarge;
	} 

	$image = imagecreate($largeurTotalImage,
						$hauteurTotalImage);
	debug('Total de l\'image : L = '.$largeurTotalImage.' H = '.$hauteurTotalImage);

	// Couleurs
	$blanc = imagecolorallocate($image, 255, 255, 255);
	$rouge = imagecolorallocate($image, 255, 34, 34);
	$noir = imagecolorallocate($image, 0, 0, 0);

	// Fond transparent 
	imagecolortransparent($image, $blanc);

	// Nombre de paralèle
	for ($idParallele = 0; $idParallele < $nbParallele; $idParallele++) {
		// Nombre de série
		for ($idSerie = 0; $idSerie < $nbSerie; $idSerie++) {
			// Déterminer les coordonnées de la batterie
			$x1=$objMarge*$idSerie+($objW*$idSerie)+$objMarge;
			$y1=$objMarge*$idParallele+($objH*$idParallele)+$objMarge;
			$x2=$x1+$objW;
			$y2=$y1+$objH;
			debug('Affichage de l\'objet '.$idSerie.'  de la // '.$idParallele.' en coordonnée x='.$x1.' y='.$y1);
			// On trace le rectangle de base
			imagesetthickness($image, $objTrait);
			imagerectangle ($image, $x1, $y1, $x2, $y2, $noir);
			// Polaritée
			imagestring($image, $objPolice, $x1+($objW/6), $y1+($objH/2)-$objPolice, "+", $rouge);
			imagestring($image, $objPolice, $x2-($objW/4), $y2-($objH/2)-$objPolice, "-", $noir);
						
			// On change le trait pour les câbles
			imagesetthickness($image, $objTraitCable);
			// Câble
			if ($idSerie == 0) {
				imageline ($image, $x1, $y2-($objH/2), $x1-($objMarge/2), $y2-($objH/2), $rouge);
			} else {
				imageline ($image, $x1, $y2-($objH/2), $x1-($objMarge/2), $y2-($objH/2), $noir);
			}
			imageline ($image, $x2, $y2-($objH/2), $x2+($objMarge/2), $y2-($objH/2), $noir);
		}
	}

	if ($type == 'bat') {
		// Câble + général
		imageline ($image,
					$objMarge/2, 
					$objMarge/2, 
					$objMarge/2, 
					($objH*$nbParallele)-($objH/2)+($objMarge*$nbParallele), 
					$rouge);
		imageline ($image, 
					$objMarge/2, 
					$objMarge/2, 
					$largeurTotalImage/2-$objMarge, 
					$objMarge/2, 
					$rouge);
		imageline ($image, 
					$largeurTotalImage/2-$objMarge, 
					$objMarge/2, 
					$largeurTotalImage/2-$objMarge, 
					0, 
					$rouge);
		// Câble - général
		imageline ($image, $largeurTotalImage-$objMarge/2,
					$objMarge/2, 
					$largeurTotalImage-$objMarge/2,
					($objH*$nbParallele)-($objH/2)+($objMarge*$nbParallele),
					$noir);
		imageline ($image, 
					$largeurTotalImage-$objMarge/2,
					$objMarge/2, 
					$largeurTotalImage/2+$objMarge,
					$objMarge/2,
					$noir);
		imageline ($image, 
					$largeurTotalImage/2+$objMarge,
					$objMarge/2,
					$largeurTotalImage/2+$objMarge,
					0,
					$noir);
	} else if ($type == 'pv') {
		// Fin zone PV
		$hauteurZonePv=$hauteurTotalImage-$objMarge-$config_ini['schemaCablage']['reguH'];
		// Affichage Régulateur
		imagesetthickness($image, $objTrait);
		imagerectangle ($image, 
						$largeurTotalImage/2-$config_ini['schemaCablage']['reguW']/2, 
						$hauteurZonePv, 
						$largeurTotalImage/2+$config_ini['schemaCablage']['reguW']/2, 
						$hauteurZonePv+$config_ini['schemaCablage']['reguH'],
						$noir);
		$printRegu='Regulateur de charge';
		imagestring($image, 
			$objPolice,
			centrage_texte($printRegu,$objPolice,$largeurTotalImage), 
			$hauteurZonePv+$config_ini['schemaCablage']['reguH']/2-$objPolice,
			$printRegu, 
			$noir);
		imagesetthickness($image, $objTraitCable);
		// Câble + général
		imageline ($image,
					$objMarge/2, 
					$objMarge+$objH/2, 
					$objMarge/2, 
					$hauteurZonePv-$objMarge/2, 
					$rouge);
		imageline ($image, 
					$objMarge/2, 
					$hauteurZonePv-$objMarge/2, 
					$largeurTotalImage/2-$objMarge, 
					$hauteurZonePv-$objMarge/2, 
					$rouge);
		imageline ($image, 
					$largeurTotalImage/2-$objMarge, 
					$hauteurZonePv-$objMarge/2, 
					$largeurTotalImage/2-$objMarge, 
					$hauteurZonePv, 
					$rouge);
		// Câble - général
		imageline ($image, 
					$largeurTotalImage-$objMarge/2,
					$objMarge+$objH/2, 
					$largeurTotalImage-$objMarge/2,
					$hauteurZonePv-$objMarge/2,
					$noir);
		imageline ($image, 
					$largeurTotalImage-$objMarge/2,
					$hauteurZonePv-$objMarge/2, 
					$largeurTotalImage/2+$objMarge,
					$hauteurZonePv-$objMarge/2,
					$noir);
		imageline ($image, 
					$largeurTotalImage/2+$objMarge,
					$hauteurZonePv-$objMarge/2,
					$largeurTotalImage/2+$objMarge,
					$hauteurZonePv,
					$noir);
		// + après le régulateur 
		imageline ($image, 
					$largeurTotalImage/2-$objMarge, 
					$hauteurTotalImage-$objMarge, 
					$largeurTotalImage/2-$objMarge, 
					$hauteurTotalImage, 
					$rouge);
		// - après le régulateur
		imageline ($image, 
					$largeurTotalImage/2+$objMarge,
					$hauteurTotalImage-$objMarge,
					$largeurTotalImage/2+$objMarge,
					$hauteurTotalImage,
					$noir);
	}
	
	$nomImage = $type.'-'.$nbSerie.'-'.$nbParallele.'.png';
	
	imagepng($image, $config_ini['schemaCablage']['var_dir_file'].'/'.$nomImage);
	imagedestroy($image);
	
	debug($nomImage);
	
	return $nomImage;
}

if (empty($_GET['nbPvS']) || empty($_GET['nbPvP']) || empty($_GET['batType']) || empty($_GET['nbBatP']) || empty($_GET['nbBatS']) || empty($_GET['nbRegu'])) {
	exit('No hack !');
}

if (!isset($_GET['debug'])) {
	header ("Content-type: image/png");
}

$objMarge=$config_ini['schemaCablage']['objMarge'];
$objTrait=$config_ini['schemaCablage']['objTrait'];
$objTraitCable=$config_ini['schemaCablage']['objTraitCable'];
$objPolice=$config_ini['schemaCablage']['objPolice'];

$schemaPv = shcema_cablage_generer_part('pv', $_GET['nbPvS'], $_GET['nbPvP'], null);
$schemaBat = shcema_cablage_generer_part('bat', $_GET['nbBatS'], $_GET['nbBatP'], $_GET['batType']);

list($largeurPv, $hauteurPv) = getimagesize($config_ini['schemaCablage']['var_dir_file'].'/'.$schemaPv);
list($largeurBat, $hauteurBat) = getimagesize($config_ini['schemaCablage']['var_dir_file'].'/'.$schemaBat);

if ($largeurPv*$_GET['nbRegu'] > $largeurBat) {
	$largeurSchema=$largeurPv*$_GET['nbRegu'];
} else {
	$largeurSchema=$largeurBat;
}
debug('Largeur schéma : '.$largeurSchema);
if ($_GET['nbRegu'] == 1) {
	$hauteurSchema=$hauteurPv+$hauteurBat;
} else {
	$hauteurSchema=$hauteurPv+$objMarge+$hauteurBat;
}
debug('Hauteur schéma : '.$hauteurSchema);

$imageSchema = imagecreate($largeurSchema, $hauteurSchema);
$blanc = imagecolorallocate($imageSchema, 255, 255, 255);
$rouge = imagecolorallocate($imageSchema, 255, 34, 34);
$noir = imagecolorallocate($imageSchema, 0, 0, 0);

$nbSchemaPv=0;
$imageSchemaPv = imagecreatefrompng($config_ini['schemaCablage']['var_dir_file'].'/'.$schemaPv);
if ($_GET['nbRegu'] == 1) {
	imagecopymerge($imageSchema, $imageSchemaPv, $largeurSchema/2-$largeurPv/2+$largeurPv*$nbSchemaPv, 0, 0, 0, $largeurPv, $hauteurPv, 60);
} else {
	while ($nbSchemaPv < $_GET['nbRegu']) {		
		imagecopymerge($imageSchema, $imageSchemaPv, $largeurPv*$nbSchemaPv, 0, 0, 0, $largeurPv, $hauteurPv, 60);
		$nbSchemaPv++;
	}
}

$imageSchemaBat = imagecreatefrompng($config_ini['schemaCablage']['var_dir_file'].'/'.$schemaBat);

$txtParcPanneau='Parc de panneau(x)';
imagestring($imageSchema, 
	$objPolice,
	centrage_texte($txtParcPanneau,$objPolice,$largeurSchema),
	$objMarge/6, 
	$txtParcPanneau, 
	$noir);
$txtParcBatterie='Parc de batterie(s)';
imagestring($imageSchema, 
	$objPolice,
	centrage_texte($txtParcBatterie,$objPolice,$largeurSchema),
	$hauteurSchema-$objMarge/1.3, 
	$txtParcBatterie, 
	$noir);

if ($_GET['nbRegu'] == 1) {
	imagecopymerge($imageSchema, $imageSchemaBat, $largeurSchema/2-$largeurBat/2, $hauteurPv, 0, 0, $largeurBat, $hauteurBat, 60);
} else {
	imagecopymerge($imageSchema, $imageSchemaBat, $largeurSchema/2-$largeurBat/2, $hauteurPv+$objMarge, 0, 0, $largeurBat, $hauteurBat, 60);
	// relier les images : 
	imagesetthickness($imageSchema, $objTraitCable);
	imageline ($imageSchema, 
				$largeurSchema/2+$objMarge,
				$hauteurPv-$objMarge/4, 
				$largeurSchema/2+$objMarge,
				$hauteurPv+$objMarge,
				$noir);
	imageline ($imageSchema, 
				$largeurPv/2+$objMarge,
				$hauteurPv-$objMarge/4, 
				$largeurPv*$_GET['nbRegu']-$largeurPv/2+$objMarge,
				$hauteurPv-$objMarge/4, 
				$noir);
	imageline ($imageSchema, 
				$largeurPv/2-$objMarge,
				$hauteurPv+$objMarge-$objMarge/4, 
				$largeurPv*$_GET['nbRegu']-$largeurPv/2-$objMarge,
				$hauteurPv+$objMarge-$objMarge/4, 
				$rouge);
	for ($idRegu = 0; $idRegu <= $_GET['nbRegu']; $idRegu++) {
		imageline ($imageSchema, 
				$largeurPv/2-$objMarge+$largeurPv*$idRegu,
				$hauteurPv, 
				$largeurPv/2-$objMarge+$largeurPv*$idRegu,
				$hauteurPv+$objMarge-$objMarge/4, 
				$rouge);
	}
}

// Ménage 
unlink($config_ini['schemaCablage']['var_dir_file'].'/'.$schemaBat);
unlink($config_ini['schemaCablage']['var_dir_file'].'/'.$schemaPv);

		
if (!isset($_GET['debug'])) {
	imagepng($imageSchema);
}


?>
