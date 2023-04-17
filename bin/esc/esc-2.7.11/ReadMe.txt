Installation et lancement du Node ESC

I Installation d'un ESC

A Pré-requis

- Avoir un accès sur un serveur mySQL, cet accès devant donner le droit de créer des tables de type "nomDeLESC_%",
  connaître l'ip, port, nom, user et mot de passe.
- Placer ces paramètres dans un fichier de configuration (exemple joins dans le dossier de ce document)
- Posséder le certificat serveur de l'ESC au format PEM (incluant la clé), et l'ajouter au fichier de configuration
- Posséder les clés de signature des documents système au format PEM (Clé RSA)
- Posséder le certificat du client SuperUser au format PEM
- Posséder le certificat permettant de créer des jetons d'horodatage (***)
- Connaître le chemin d'un des point de montages ou seront déposé les fichiers

Toute les libraries externes sont incluses dans le Jar, il n'est donc pas nécessaire
d'ajouter quoi que ce soit de particulier sur le ClassPath.


B Installation

Plusieurs paramètres sont requis : 
-configPath     		Le chemin vers le fichier de configuration contenant les informations 
			    		d'accés aux bases de données de différents nodes
-install 	    		Pour indiquer que l'on est en mode installation et fournir le nom de l'ESC a installer
-superUser 	    		Pour indiquer le chemin vers le certificat pem de l'utilisateur SUPER
-certificatSignature	Pour indiquer le chemin vers le pem contenant les clés permettant de signer les documents du coffre système.
-certificatTimeStamp 	Pour indiquer le chemin vers le certificat pem permettant de créer des jetons d'horodatage
-name 		    		Le nom de cette node d'installation (nom libre, purement indicatif)
-site					Le nom du Site depuis lequel s'effectue l'install
-mountPoint             Le chemin du point de montage par default

Soit :
java -jar EscNode.jar -install "nomEsc" -name "nomNode" -site "nomSite" -configPath "/un/fichier/de/config.xml" /
-superUser "/chemin/certificat/superUser.pem" -certificatSignature "/chemin/certificat/cles.pem" / 
-certificatTimeStamp "/chemin/certificat/tmst.pem" -mountPoint "/mon/point/de/montage"


La base vas alors être construite, la table config recevra les valeurs par défauts nécessaire 
au fonctionnement de l'ESC et de ses Node.

/!\ Par défaut un point de montage est créée dans la base pour l'esc choisit sur le site indiqué.
Ce point est "./Fichiers". Il est conseillé de modifier cette valeur en base.


II Configuration

Pour qu'un node fonctionne il doit absolument recevoir un fichier de configuration.
Ce fichier xml contient les informations d'accés aux différents ESC, ainsi que le certificat SSL du node (sous forme de chaîne de caractère,
 comme dans un pem).
Un exemple est fournis avec cette documentation.

/!\ Attention : la première ligne doit absolument contenir <config xmlns="http://dps.docapost.com/esc/xml/config">


III Utilisation, lancement d'un Node

A Lancement

Pour lancer un Node il faut avoir créé un fichier de configuration contenant les informations nécessaires à l'accés à(aux) ESC.
Le node doit être nommé et ce nom doit être unique, le site doit être précisé.

L'application se lance donc ainsi : 
java -jar EscNode.jar -name "nomNode" -site "LOCAL" -configPath "/un/fichier/de/config.xml"

Les paramètres nécessaires à l'application sont chargés depuis la base de donnée.


B plus d'informations

Lancer le node sans aucun paramètres fera apparaître la liste des commandes acceptées par l'application.
Certain de ces paramètres (tel Port) permettent de remplacer les paramètres par défaut fournis par la base de données. 


usage: node
 -certificatSignature <arg>     Lors de l'installation, insère les clefs
                                servant aux signatures des archives
                                système
 -certificatTimeStamp <arg>     Lors de l'installation, insère le
                                certificat servant à la creation de jeton
                                d'horodatage
 -configPath <arg>              (Obligatoire) Le chemin vers le fichier de
                                configuration
 -debugMode                     Fait passer le Node en mode debug,
                                activant un nombre certain de logs
                                speciaux - ne surtout pas utiliser en
                                production
 -install <arg>                 Permet de creer l'environnement d'une
                                nouvelle installation, le nom de l'ESC a
                                installer doit etre passé en paramètre
 -instantWriteFailureTolerant   Si cette option est presente et qu'une
                                erreur se produit lors de l'ecriture sur
                                un point de montage alors l'ecriture sur
                                ce point de montage sera faite en differée
                                plutot que d'annuler tout le depot
 -logLevel <arg>                Le niveau de debug de l'application
                                (TRACE, DEBUG, INFO, WARN, ERROR, FATAL,
                                OFF...) (defaut : 'INFO')
 -mountPoint <arg>              Lors de l'installation, designe le point
                                de montage par default
 -name <arg>                    (Obligatoire) Le nom de cette node
 -nbDatabaseConnections <arg>   Le nombre maximum de connections a la base
                                de données (1/4 de NbWorker par defaut)
 -nbWorker <arg>                Le nombre de processus traitant les
                                connections entrantes (defaut : '16')
 -nbWorkerBackground <arg>      Le nombre de processus traitant les tache
                                de fond (defaut : '8')
 -port <arg>                    le port http où doit ecouter ce Node (une
                                valeur est prise dans la base de
                                configuration par defaut)
 -site <arg>                    (Obligatoire) Le nom du site sur lequel
                                s'execute cette node
 -superUser <arg>               Lors de l'installation, permet la creation
                                de l'utilisateur SUPER grace au certificat
                                utilisateur au format pem
 -syslogUrl <arg>               Une chaine ip:port indiquant le serveur
                                syslog ou deposer les informations
 -TEST                          Active le mode TEST desactivant certaines
                                protections/fonctionnalité (sauvegarde
                                automatique, verifiation de presence de
                                node "background"... ) - ne surtout pas
                                utiliser en production
 -typeNode <arg>                Precise le type du Node : MIXTE (traite
                                des ordre et des tâches de fond), NODEONLY
                                (traite uniquement des ordres), BACKONLY
                                (traite uniquement des taches de fond)
                                (defaut : 'MIXTE')
 -upgrade <arg>                 Permet de mettre à jour une base de donné
                                pour la rendre compatible avec une
                                nouvelle version de l'applications, le nom
                                de l'ESC à mettre à jour doit etre passé
                                en paramètre
 -enableHttpConnection          Permet de lancer l'ESC avec un mode non
								sécurisé pour atteindre certaines fonctions
								comme nodeList 
								Le port d'écoute pour ce serveur Http doit
								être passé en paramètre 
								(defaut : port SSL +1)
 -portNoSSL                     Port d'écoute pour le serveur Http non
 								sécurisé 								