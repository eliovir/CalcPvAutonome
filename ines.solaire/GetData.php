#!/usr/bin/php
<?php
// Script qui récupère les données IGP du mois le plus défavorable 
// Ces données sont récupéré depuis le site : 
// http://ines.solaire.free.fr/gisesol.php

/*
 * 7s par requête
 * 5016 enregistrement par ville
 * 65 villes
 * ... il faut que ça tourne 1 mois pour tout aspirer... OU lancer plusieurs fois le script avec uniquement certain albedo...
 * Remarques : pas plus de 3 script lancé avec la même IP, sinon ça bloque
 */

/* 
 * Juste l'albedo 0.3
 * 		screen -S 0.3 -dm bash -c 'cd /var/www/calcpvautonome.zici.fr/web/dev/ ; while true; do php ines.solaire/GetData.php "0.3"; sleep 60; done'
 * Juste l'albedo 0.3 avec proxy http://www.gatherproxy.com/embed/?t=&p=&c=Germany
 * 		screen -S 0.3 -dm bash -c 'cd /var/www/calcpvautonome.zici.fr/web/dev/ ; while true; do php ines.solaire/GetData.php "0.3" "iporoxy" "portproxy"; sleep 60; done'
 * Tout :
 * screen -S all -dm bash -c 'cd /var/www/calcpvautonome.zici.fr/web/dev/ ; while true; do php ines.solaire/GetData.php; sleep 60; done'
*/

if (php_sapi_name() != 'cli') {
	echo 'Ce script ne peut être lancé qu\'en ligne de comamnde.';
	exit();
}

include('./lib/Fonction.php');
$config_ini = parse_ini_file('./config.ini', true); 
include('./lib/simple_html_dom.php');
include('./ines.solaire/FormData.php');

// Sleep entre 2 requette
$SLEEP_ENTRE_REQUETE=2;


if (isset($argv[1])) {
		
		
	// Pour faire "plus" rapide
	//	$albedos = array ($argv[1]);

	// Par groupe
	/*
	switch ($argv[1]) {
		case 1:
		
		$albedos = array ('0.3',
						'0.7');
		
		break;
		case 2:
		
			$albedos = array ('0.4',
							'0.5');
		
		break;
		case 3:
		
			$albedos = array ('0.8',
							'0.9');
					
		break;
		default :
		exit();
		
	}*/
}


// Connect DB
try {
	if (preg_match('/^sqlite/', $config_ini['irradiation']['db'])) {
		$dbco = new PDO($config_ini['irradiation']['db']);
	} else {
		$dbco = new PDO($config_ini['irradiation']['db'], $config_ini['irradiation']['dbUser'], $config_ini['irradiation']['dbPass']);
	}
	$dbco->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch ( PDOException $e ) {
	die('Connexion à la base '.$e->getMessage());
}

// Create DB if not exists pour Optimum
try {
	$create = $dbco->query("CREATE TABLE IF NOT EXISTS ".$config_ini['irradiation']['dbTableOptimum']." (
							id INTEGER PRIMARY KEY,
							ville CHAR(24) NOT NULL,
							mois CHAR(20) NOT NULL,
							inclinaison CHAR(11) NOT NULL,
							orientation CHAR(5) NOT NULL,
							albedo CHAR(3) NOT NULL,
							igp NUMERIC(4) NOT NULL);");
} catch ( PDOException $e ) {
	$e->getMessage();
	die();
}

function parseHtmlForIgp() {
	global $html;
	$Igp = array();
	$IgpMonth=0;
	$IGP['igp1']=(float) 99;
	$IGP['igp2']=(float) 99;
	$IGP['igp3']=(float) 99;
	$IGP['igp4']=(float) 99;
	$IGP['igp5']=(float) 99;
	$IGP['igp6']=(float) 99;
	$IGP['igp7']=(float) 99;
	$IGP['igp8']=(float) 99;
	$IGP['igp9']=(float) 99;
	$IGP['igp10']=(float) 99;
	$IGP['igp11']=(float) 99;
	$IGP['igp12']=(float) 99;
	$IgpOptimumPourLaVille=null;
	$IgpOptimumPourLaVilleExplode=null;
	foreach($html->find('td') as $td) {
		// Tant qu'on a pas trouvé les 12 mois d'IGP
		if ($IgpMonth <= 12) {		
			// Si IGP > 1, c'est qu'on est dans la ligne, on enregistre donc les IGP
			if ($IgpMonth > 0) {
				$Igp[$IgpMonth]=(float) str_replace(' ','',$td->plaintext);
				
				$IGP['igp'.$IgpMonth]=$Igp[$IgpMonth];
				
				$IgpMonth++;
			}
			// Si la ligne IGP est trouvé on commence à chercher
			if (preg_match('/(IGP)/',$td->plaintext)) {
				$IgpMonth++;
			}
			
		}
		// Recherche optimum pour la ville :
		if (preg_match('/irradiation globale dans le plan pour le mois le plus/',$td->plaintext)) {
			$IgpOptimumPourLaVille=$td->plaintext;
		} 
	}
	if ($IgpOptimumPourLaVille != null) {
		$IgpOptimumPourLaVilleExplode=explode(" ", $IgpOptimumPourLaVille);
		//print_r($IgpOptimumPourLaVilleExplode);
		$IGP['optimum']['mois'] = substr($IgpOptimumPourLaVilleExplode[16], 0, -1);
		$IGP['optimum']['igp'] = $IgpOptimumPourLaVilleExplode[21];
		$IGP['optimum']['orientation'] = $IgpOptimumPourLaVilleExplode[28];
		if ($IGP['optimum']['orientation'] != 'Ouest' && $IGP['optimum']['orientation'] != 'Est'
		&& $IGP['optimum']['orientation'] != 'Nord' && $IGP['optimum']['orientation'] != 'Sud'
		&& $IGP['optimum']['orientation'] != 'ouest' && $IGP['optimum']['orientation'] != 'est'
		&& $IGP['optimum']['orientation'] != 'nord' && $IGP['optimum']['orientation'] != 'sud') {
			$IGP['optimum']['orientation'] = substr(utf8_encode($IGP['optimum']['orientation']), 0, -2);
		}
		$IGP['optimum']['inclinaison'] = substr($IgpOptimumPourLaVilleExplode[36], 0, -1);
		if ($IGP['optimum']['inclinaison'] != 'horizontale' && $IGP['optimum']['inclinaison'] != 'verticale') {
			$IGP['optimum']['inclinaison'] = substr(utf8_encode($IGP['optimum']['inclinaison']), 0, -2);
		}
	}
	return $IGP;
}

foreach ($villes as $ville) {

	// Create DB if not exists (une / ville)
	$villeTableName=wd_remove_accents(utf8_encode($config_ini['irradiation']['dbTablePrefix'].str_replace(' ','',$ville)));
	
	echo "\n".$villeTableName."\n";
	
	
	try {
		
			$create = $dbco->query("CREATE TABLE IF NOT EXISTS ".$villeTableName." (
									id INTEGER PRIMARY KEY,
									inclinaison CHAR(11) NOT NULL,
									orientation CHAR(5) NOT NULL,
									albedo CHAR(4) NOT NULL,
									igp1 NUMERIC(4) NOT NULL,
									igp2 NUMERIC(4) NOT NULL,
									igp3 NUMERIC(4) NOT NULL,
									igp4 NUMERIC(4) NOT NULL,
									igp5 NUMERIC(4) NOT NULL,
									igp6 NUMERIC(4) NOT NULL,
									igp7 NUMERIC(4) NOT NULL,
									igp8 NUMERIC(4) NOT NULL,
									igp9 NUMERIC(4) NOT NULL,
									igp10 NUMERIC(4) NOT NULL,
									igp11 NUMERIC(4) NOT NULL,
									igp12 NUMERIC(4) NOT NULL);");
		
	} catch ( PDOException $e ) {
		$e->getMessage();
		die();
	}

// Suppression des doublons
	$sql = "delete from ".$villeTableName." where rowid not in (select max(id) from ".$villeTableName." group by inclinaison, orientation, albedo)";
  try {
    $stmt = $dbco->prepare($sql, array(PDO::ATTR_CURSOR, PDO::CURSOR_SCROLL));
    $stmt->execute();
    $stmt = null;
  } 
   catch (PDOException $e) {
    print $e->getMessage();
  }

	foreach ($inclinaisons as $inclinaison) {
		foreach ($orientations as $orientation) {
			foreach ($albedos as $albedo) {
				echo "\n";
				echo "Pour ".$ville." avec un plan de incl=".$inclinaison." ori=".$orientation." alb=".$albedo;
				
				/*
				Content-Type: application/x-www-form-urlencoded
				Content-Length: 84

				ville=Embrun&inclinaison=35%B0&orientation=%2B60%B0&albedo=0.6&choix=kWh%2Fm%B2.jour
				Récupéré via F12, réseau, modifier et renvoyer, corps de a requête
							"ville=Clermont+Ferrand&inclinaison=85%B0&orientation=-135%B0&albedo=0.0&choix=kWh%2Fm%B2.jour");
				*/
				$postFields="ville=".urlencode($ville)."&inclinaison=".urlencode($inclinaison)."&orientation=".urlencode($orientation)."&albedo=".urlencode($albedo)."&choix=kWh%2Fm%B2.jour";
				//"ville=Embrun&inclinaison=35%B0&orientation=%2B60%B0&albedo=0.6&choix=kWh%2Fm%B2.jour"
				//"ville=Clermont+Ferrand&inclinaison=85%B0&orientation=-135%B0&albedo=0.0&choix=kWh%2Fm%B2.jour"

				if ($dbco->query("SELECT COUNT(*) FROM ".$villeTableName." WHERE inclinaison = '".$inclinaison."' AND orientation = '".$orientation."'  AND albedo = '".$albedo."'")->fetchColumn() == 0) {
				
					
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL,"http://ines.solaire.free.fr/gisesol.php");
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);


					
					
					// Définition de l'adresse du proxy
					
					if (isset($argv[2]) && isset($argv[3])) {
						curl_setopt($ch, CURLOPT_PROXY, $argv[2]);
						curl_setopt($ch, CURLOPT_PROXYPORT, $argv[3]);	
					}
					
					
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$server_output = curl_exec ($ch);
					
					// http://nimishprabhu.com/top-10-best-usage-examples-php-simple-html-dom-parser.html
					//$html = file_get_html('/tmp/gisesol.php');
					$html = str_get_html($server_output);
					
					$IGP = parseHtmlForIgp();

					if ($IGP['igp1'] != 99 && $IGP['igp2'] != 99 && $IGP['igp3'] != 99 && $IGP['igp4'] != 99 && $IGP['igp5'] != 99 && $IGP['igp6'] != 99 && $IGP['igp7'] != 99 && $IGP['igp8'] != 99 && $IGP['igp9'] != 99 && $IGP['igp10'] != 99 && $IGP['igp11'] != 99 && $IGP['igp12'] != 99) {
						echo " igp1=".$IGP['igp1'];
						echo " igp2=".$IGP['igp2'];
						echo " igp3=".$IGP['igp3'];
						echo " igp4=".$IGP['igp4'];
						echo " igp5=".$IGP['igp5'];
						echo " igp6=".$IGP['igp6'];
						echo " igp7=".$IGP['igp7'];
						echo " igp8=".$IGP['igp8'];
						echo " igp9=".$IGP['igp9'];
						echo " igp10=".$IGP['igp10'];
						echo " igp11=".$IGP['igp11'];
						echo " igp12=".$IGP['igp12'];
						try {
							$insertcmd = $dbco->prepare("INSERT INTO ".$villeTableName." (inclinaison, orientation, albedo, igp1, igp2, igp3, igp4, igp5, igp6, igp7, igp8, igp9, igp10, igp11, igp12) 
															VALUES (:inclinaison, :orientation, :albedo, :igp1, :igp2, :igp3, :igp4, :igp5, :igp6, :igp7, :igp8, :igp9, :igp10, :igp11, :igp12)");
							$insertcmd->bindParam('inclinaison', $inclinaison, PDO::PARAM_STR);
							$insertcmd->bindParam('orientation', $orientation, PDO::PARAM_STR);
							$insertcmd->bindParam('albedo', $albedo, PDO::PARAM_STR);
							$insertcmd->bindParam('igp1', $IGP['igp1'], PDO::PARAM_STR);
							$insertcmd->bindParam('igp2', $IGP['igp2'], PDO::PARAM_STR);
							$insertcmd->bindParam('igp3', $IGP['igp3'], PDO::PARAM_STR);
							$insertcmd->bindParam('igp4', $IGP['igp4'], PDO::PARAM_STR);
							$insertcmd->bindParam('igp5', $IGP['igp5'], PDO::PARAM_STR);
							$insertcmd->bindParam('igp6', $IGP['igp6'], PDO::PARAM_STR);
							$insertcmd->bindParam('igp7', $IGP['igp7'], PDO::PARAM_STR);
							$insertcmd->bindParam('igp8', $IGP['igp8'], PDO::PARAM_STR);
							$insertcmd->bindParam('igp9', $IGP['igp9'], PDO::PARAM_STR);
							$insertcmd->bindParam('igp10', $IGP['igp10'], PDO::PARAM_STR);
							$insertcmd->bindParam('igp11', $IGP['igp11'], PDO::PARAM_STR);
							$insertcmd->bindParam('igp12', $IGP['igp12'], PDO::PARAM_STR);
							$insertcmd->execute();
						} catch ( PDOException $e ) {
							echo "\nDB insert error :  ", $e->getMessage();
							die();
						}				
					} else {
						echo "\n !!! Erreur !\n\n";
					}

					// Pour l'optimum
					if ($dbco->query("SELECT COUNT(*) FROM ".$config_ini['irradiation']['dbTableOptimum']." WHERE albedo = '".$albedo."' AND ville = '".utf8_encode($ville)."'")->fetchColumn() == 0) {
						try {
							$insertcmd = $dbco->prepare("INSERT INTO ".$config_ini['irradiation']['dbTableOptimum']." (ville, inclinaison, orientation, mois, albedo, igp) 
															VALUES (:ville, :inclinaison, :orientation, :mois, :albedo ,:igp)");
							$insertcmd->bindParam('ville', utf8_encode($ville), PDO::PARAM_STR);
							$insertcmd->bindParam('inclinaison', $IGP['optimum']['inclinaison'], PDO::PARAM_STR);
							$insertcmd->bindParam('orientation', $IGP['optimum']['orientation'], PDO::PARAM_STR);
							$insertcmd->bindParam('mois', utf8_encode($IGP['optimum']['mois']), PDO::PARAM_STR);
							$insertcmd->bindParam('albedo', $albedo, PDO::PARAM_STR);
							$insertcmd->bindParam('igp', $IGP['optimum']['igp'], PDO::PARAM_STR);
							$insertcmd->execute();
						} catch ( PDOException $e ) {
							echo "\nDB insert optimum error :  ", $e->getMessage();
							die();
						}
						echo "\n\t > Avec une albedo de ".$albedo.", le pire moi est le mois de ".$IGP['optimum']['mois']." avec un IGP de ".$IGP['optimum']['igp']." sur une inclinaison de ".$IGP['optimum']['inclinaison']." et une orientation de ".$IGP['optimum']['orientation'].". ";
					}


					curl_close ($ch);
					
					$html->clear();
					unset($html);
					
				} else {
					echo " PRESENT !";
				}

			}
		}
		echo "\n Attente ".$SLEEP_ENTRE_REQUETE."s";
		sleep($SLEEP_ENTRE_REQUETE);
	}
}

?>

