# Ines Solaire 

Ines solaire est un logiciel permettant d'obtenir l'IGP (Irradiation Global dans le plan) qui est une valeur nécessaire pour le calcul de l'installation sur CalcPvAutonome
	Le logiciel Ines Solaire : http://ines.solaire.free.fr/gisesol.php

A l'aide du script GetData.php je récupère toutes les valeurs IGP en testant toutes les possibilités d'orientation, d'inclinaison et d'albedo par ville (contenu dans FormData.php). Ce script rempli la base db.sqlite afin que les données reste en local pour éviter la latence du logiciel Ines Solaire lors de calcul sur CalcPvAutonome.
	
### License

Le code est sous licence BEERWARE : Tant que vous conservez cet avertissement, vous pouvez faire ce que vous voulez de ce truc. Si on se rencontre un jour et que vous pensez que ce truc vaut le coup, vous pouvez me payer une bière en retour. 

> Written with [StackEdit](https://stackedit.io/).



