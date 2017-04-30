# CalcPvAutonome

Outil web pour aider à calculer/dimensionner son installation photovoltaïque en site isolé (autonome). 
 
### Démonstration

La démonstration se trouve ici : http://calcpvautonome.zici.fr/ 

### Appel à l'entraide

Pour perfectionner ce logiciel j'ai besoin de vous. donc n'hésitez par à émettre des suggestions sur la méthode de calcul, des idées sur de nouvelles fonctionnalités, des réserves sur les estimations de prix... Tout est bon à prendre, n'[hésitez pas c'est par là](http://david.mercereau.info/contact/).

### Fonctionnalité 

Pour l'utilisateur de base :

  - 3 mode au formulaire (Débutant, Eclairé, Expert)
  - Pour déterminer l'ensoleillement : 
	- (simple) Carte par zone
	- (précis) Valeur du site http://ines.solaire.free.fr/gisesol_1.php (kWh/m²/j)
  - En mode expert, ajuster le degré de décharge, les valeurs de rendement électrique des batteries ou du reste de l'installation, 
  - Déduction automatique du nombre de panneaux, batteries et régulateur nessésaire (possibilité de forcer un modèle type ou de personnaliser ces caractéristiques)
  - Hypothèse de câblage panneaux / régulateur (exemple : 3 panneaux en série sur 2 paralèles branché sur un régulateur)
  - Schéma de câblage
  - Calcul des sections de câblage
  - Déduction automatique de la tension du parc de batteries à utiliser (possibilité de forcer une valeur en mode expert)
  - Estimation d'une fourchette du coût du parc de batterie & photovoltaïque 
  - Explication détailé du calcul pour fait pour parvenir au résultat

Pour les utilisateurs avancés : 

  - Intégration sur votre site web
  - Modifier le fichier config.ini pour changer
	- Les valeurs par défaut du formulaire
	- Les valeurs d'irradiation de la carte par zone
	- La fourchette de prix des panneaux photovoltaïque et des batteries
	- Les modèles de batteries possible pour la détermination d'une configuration

### Installation de l'outil sur mon site :

#### Requis pour le fonctionnement / l'installation du 

  * PHP (5.5-5.6 recomended) + lib gd
  * Lighttpd/Apache (ou autre serveur web, service d'hébergement mutualisé...)

#### Installation

Télécharger et décompresser le fichier zip du master : https://github.com/kepon85/CalcPvAutonome/archive/master.zip

Le rendre accessible depuis votre serveur http et personnaliser les valeur du fichier config.ini?

### Todos

 - BjMax pour déterminer : Convertisseur de tension
 - Pouvoir choisir le type de batterie AGM, GEL, OPvZ...
 - Truc du frigo 24/24 mais qui consomem pas tout le temps... A affiner...
 - Bug info bulle pas bien positionné
 - Pîque de conso qui fait varier le choix de l'onduleur et donc la tension du parc de batterie...
		12V:  de 150 à 1500VA, 24V : de 400 à 5000VA, 48V : 1000 à 10000VA...  (voir mail de guillaume)
	- considère tout les appareils 24/24 + les sélectionné par exemple
 - Prendre en considération l'énergie hybride (éolienne, groupe, hydrolienne...)
 - Mettre un script de bugtrack
 - Prendre en compte l'autodécharge
 - Prévoir d'autres technologie batterie que AGM (
 - Générer un beau dessin avec le câblage
 - Sauvegarde du résultat par URL, envoyer à un amis...
 - Responsive
 - Récup' valeur Ines directement (sans carte de zone)
 - Traduction anglais
 
CalcConsommation : 
 - BUG mettre un arondi 
 - Plus de pédagogie sur le calcul
 - Sauvegarder dans les cookies (il manque pas grand chose)

### Changelog

 - 0.5
	- Calcul des sections de câble partant du régulateur
		http://solarsud.blogspot.fr/2014/11/calcul-de-la-section-du-cable.html
		http://www.plaisance-pratique.com/calcul-de-la-section-des-cables?lang=fr
		http://www.sigma-tec.fr/textes/texte_cables.html
	- Popup d'affichage des modèles
 - 0.4.1
	- Bug FIX plusieurs "auto" appariasse dans le select de tension de batterie en manipulant les régulateurs...
	- Pas plus de 2 bat en paralèle : 
		http://forum-photovoltaique.fr/viewtopic.php?f=84&t=36009#p411019
 - 0.4
	- Schéma de câblage
	- Batteries : prise en compte et personnalisation du courant de charge max (0,2 C)
	- Panneaux : recommandation de pose de boîtier de raccordement au delas de 2 parallèles ( http://forum-photovoltaique.fr/viewtopic.php?p=409170&sid=1ac1384c932b26d382144e0d5c558d04#p409170 )
 - 0.3.2
    - Personnalisation des caractéristiques des batteries de travails
 - 0.3.1
	- Prise en compte du courant de charge max des batteries (dans le régulateur)
	- Ajout de l'angle 0° dans la carte d'iradiation solaire
 - 0.3
	- Déduction automatique du régulateur nécessaire (possibilité de forcer un modèle type ou de personnaliser ces caractéristiques)
	- Déduction automatique du câblage des panneaux (série/parallèle) 
 - 0.2
	- Déduction automatique du nombre de panneaux (possibilité de forcer un modèle de travail)
    - Possibilité de privilégier la technologie monocristalin ou polycristalin 
    - BUG fix CalcConsommation : il y a une virgule dans le tableau de consommation, à l'import dans le calcpvautonome ça passe pas...
    - Vérification valeur du formulaire
 - 0.1
   - 3 mode au formulaire (Débutant, Eclairé, Expert)
   - Pour déterminer l'ensoleillement : 
	 - (simple) Carte par zone
	 - (précis) Valeur du site http://inécessairenes.solaire.free.fr/gisesol_1.php (kWh/m²/j)
   - En mode expert, ajuster le degré de décharge, les valeurs de rendement électrique des batteries ou du reste de l'installation, 
   - Déduction automatique de la tension du parc de batteries à utiliser (possibilité de forcer une valeur en mode expert)
   - Déduction automatique de câblage du parc des batteries et du modèle à utiliser (exemple : "2 batteries 220Ah 12V en série") (possibilité de forcer un modèle de travail en mode expert)
   - Estimation d'une fourchette du coût du parc de batterie & photovoltaïque 
   - Explication détailé du calcul pour fait pour parvenir au résultat
   - Intégration sur votre site web
   - Modifier le fichier config.ini pour changer
 	 - Les valeurs par défaut du formulaire
	 - Les valeurs d'irradiation de la carte par zone
	 - La fourchette de prix des panneaux photovoltaïque et des batteries
	 - Les modèles de batteries possible pour la détermination d'une configuration

### Auteur / contributeur

  - David Mercereau [david #arobase# mercereau #point# info](http://david.mercereau.info/contact/) (auteur)
	  - Largement inspiré du [tableur posté par lr83](http://forum-photovoltaique.fr/viewtopic.php?p=403856#p403837)
  - Guillaume Piton de [SolisION-event](http://solision-event.centerblog.net) (contribution technique)

### License

Le code est sous licence BEERWARE : Tant que vous conservez cet avertissement, vous pouvez faire ce que vous voulez de ce truc. Si on se rencontre un jour et que vous pensez que ce truc vaut le coup, vous pouvez me payer une bière en retour. 

L'image de la France : Creative Commons paternité – partage à l’identique 3.0 (non transposée). SolarGIS © 2011 GeoModel Solar s.r.o.

> Written with [StackEdit](https://stackedit.io/).



