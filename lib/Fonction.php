<?php

function valeurRecup($nom) {
	global $config_ini;
	if (isset($_GET[$nom])) {
		echo $_GET[$nom]; 
	} else if ($config_ini[$nom]) {
		echo $config_ini[$nom];
	} else {
		echo '';
	}
}
function valeurRecupSelect($nom, $valeur) {
	global $config_ini;
	if ($_GET[$nom] == $valeur) {
		echo ' selected="selected"'; 
	} else if (empty($_GET[$nom]) && $config_ini[$nom] == $valeur) {
		echo ' selected="selected"'; 
	} else {
		echo '';
	}
}
function convertNumber($number, $to = null) {
	if ($to == 'print') {
		return number_format($number, 0, ',', ' ');
	} else {
		return number_format($number, 3, '.', '');
	}
}
?>
