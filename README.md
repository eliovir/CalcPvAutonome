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
  - Déduction automatique de la tension du parc de batteries à utiliser (possibiliter de forcer une valeur en mdoe expert)
  - Déduction automatique d'une configuration du parc de câblage des batteries et du modèle à utiliser (exemple : "2 batteries 220Ah 12V en série") (possibiliter de forcer un modèle de travail en mode expert)
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

  * PHP (5.5-5.6 recomended)
  * Lighttpd/Apache (ou autre serveur web, service d'hébergement mutualisé...)

#### Installation

Télécharger et décompresser le fichier zip du master : https://github.com/kepon85/CalcPvAutonome/archive/master.zip

Le rendre accessible depuis votre serveur http et personnaliser les valeur du fichier config.ini?

### Todos

 - Hypotèse câblage PV. 
 - Prévoir d'autres technologie batterie que AGM
 - Sauvegarde du résultat par URL, envoyer à un amis...
 - Faire un truc plus simple pour déterminer les besoins journaliers
 - Responsive
 - Test sans javascript
 - Récup' valeur Ines directement (sans carte de zone)
 
### Auteur

  - David Mercereau [david #arobase# mercereau #point# info](http://david.mercereau.info/contact/)
	  - Largement inspiré du [tableur posté par lr83](http://forum-photovoltaique.fr/viewtopic.php?p=403856#p403837)

### License

Le code est sous licence BEERWARE : Tant que vous conservez cet avertissement, vous pouvez faire ce que vous voulez de ce truc. Si on se rencontre un jour et que vous pensez que ce truc vaut le coup, vous pouvez me payer une bière en retour. 

L'image de la France : Creative Commons paternité – partage à l’identique 3.0 (non transposée). SolarGIS © 2011 GeoModel Solar s.r.o.

> Written with [StackEdit](https://stackedit.io/).



