<?php 
$config_ini = parse_ini_file('./config.ini', true); 

echo '<table>';
switch ($_GET['data']) {
    case 'pv':
		echo '<tr>';
			echo '<th>Type</th>';
			echo '<th>Puissance maximum (Pmax)</th>';
			echo '<th>Tension</th>';
			echo '<th>Tension en circuit ouvert (Voc)</th>';
			echo '<th>Courant de court circuit (Isc)</th>';
			echo '<th>Estimation prix</th>';
		echo '</tr>';
		foreach ($config_ini['pv'] as $pvModele => $valeur) {
			echo '<tr>';
				echo '<td>'.ucfirst($valeur['type']).'</td>';
				echo '<td>'.$valeur['W'].' Wc</td>';
				echo '<td>'.$valeur['V'].' V</td>';
				echo '<td>'.$valeur['Vdoc'].' V</td>';
				echo '<td>'.$valeur['Isc'].' A</td>';
				echo '<td>'.round($config_ini['prix']['pv_bas']*$valeur['W']).'
				 - '.round($config_ini['prix']['pv_haut']*$valeur['W']).' €</td>';
			echo '</tr>';
		}	
	break;
	case 'batterie':
		echo '<tr>';
			echo '<th>Nom</th>';
			echo '<th>Capacité (C10)</th>';
			echo '<th>Tension</th>';
			echo '<th>Estimation prix</th>';
		echo '</tr>';
		foreach ($config_ini['batterie'] as $modele => $valeur) {
			echo '<tr>';
				echo '<td>'.ucfirst($valeur['nom']).'</td>';
				echo '<td>'.$valeur['Ah'].' Ah</td>';
				echo '<td>'.$valeur['V'].' V</td>';
				echo '<td>'.round($config_ini['prix']['bat'.$valeur['V'].'V_bas']*$valeur['Ah']).'
				 - '.round($config_ini['prix']['bat'.$valeur['V'].'V_haut']*$valeur['Ah']).' €</td>';
			echo '</tr>';
		}	
	break;
	case 'regulateur':
		echo '<tr>';
			echo '<th>Nom</th>';
			echo '<th>Tension pac batterie</th>';
			echo '<th>Puissance PV max</th>';
			echo '<th>Tension PV max</th>';
			echo '<th>Courant PV max</th>';
			echo '<th>Estimation prix</th>';
		echo '</tr>';
		foreach ($config_ini['regulateur'] as $modele => $valeur) {
			echo '<tr>';
				echo '<td>'.ucfirst($valeur['nom']).'</td>';
				echo '<td>'.$valeur['Vbat'].' V</td>';
				echo '<td>'.$valeur['PmaxPv'].' W</td>';
				echo '<td>'.$valeur['VmaxPv'].' V</td>';
				echo '<td>'.$valeur['ImaxPv'].' A</td>';
				echo '<td>~'.$valeur['Prix'].' €</td>';
			echo '</tr>';
		}	
	break;
	case 'convertisseur':
		echo '<tr>';
			echo '<th>Nom</th>';
			echo '<th>Tension pac batterie</th>';
			echo '<th>Puissance Max (à 25°C)</th>';
			echo '<th>Puissance Pointe</th>';
			echo '<th>Estimation prix</th>';
		echo '</tr>';
		foreach ($config_ini['convertisseur'] as $modele => $valeur) {
			echo '<tr>';
				echo '<td>'.ucfirst($valeur['nom']).'</td>';
				echo '<td>'.$valeur['Vbat'].' V</td>';
				echo '<td>'.$valeur['Pmax'].' W</td>';
				echo '<td>'.$valeur['Ppointe'].' W</td>';
				echo '<td>'.round($config_ini['prix']['conv_bas']*$valeur['VA']).'
				 - '.round($config_ini['prix']['conv_haut']*$valeur['VA']).' €</td>';
			echo '</tr>';
		}	
	break;
	default:
       echo 'no hack';
}
echo '</table>';
?>
