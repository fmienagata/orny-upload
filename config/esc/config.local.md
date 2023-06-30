# ESC DEV 

en local dans serveur upload du0-aes-upload.demat.lan 172.29.95.15

URL : https://172.22.0.21:4100 ou https://safe-box-esc-local.demat.lan:4100<br> 
Salle : <b>d4dad43ab1de4722ab5dc0a43bf0f215</b><br>
Admin : <b>d1564bc53b6a40cf9fc319e2030a6356</b><br>
Certificat admin : <b>/home/escnode/generationsCertificats/finalClient/administrateurCLIENT.pem</b><br>
Coffre : <b>1014e78d836a4587a5799fb0760fd407</b><br>
Nom du coffre : <b>EscDgtt</b><br>
Gestionnaire : <b>1c9669db21e94ab2a8e38efba0179409</b><br>
Certificat Gestionnaire : <b>/home/escnode/generationsCertificats/finalClient/gestionnaireCLIENT.pem</b><br>
Lecteur : <b>80dcfa032dc24ac5a5bc01986f9928ad</b><br>
Certificat lecteur : <b>/home/escnode/generationsCertificats/finalClient/lecteurCLIENT.pem</b><br>

## Création de la salle:
Connexion à l'esc <b>EscDgtt</b> avec le certificate utilisateurSysteme.pem, pour généré un id de connexion
```
curl -v -X POST -k \
--cert "/home/escnode/generationsCertificats/finalSystem/utilisateurSysteme.pem" https://127.0.0.1:4100/EscDgtt/sessionOpen
```
réponse:
```
HTTP/1.1 200 OK
< Date: Thu Jun 08 12:42:55 CEST 2023
< Server: EscDgtt 4.1.0_6c804
< ESC-sessionId: 41956f1b89014e67b032cbb46467421c
< ESC-userType: SYSTEM
< ESC-userName: SYSTEM
< ESC-userId: 0254a02f751340e6844f9cd4e9612f22
< ESC-timestamp: 2023-06-08T12:42:55.831+02:00
< ESC-escName: EscDgtt
< ESC-userIdCmd: 0254a02f751340e6844f9cd4e9612f22
< Content-Length: 0
```
L'id de connexion est le suivant: <b>54a2f00e486941bb8ef92cfea075f970</b>

création de la salle de coffre
```
curl -v -X POST -k \
--cert "/home/escnode/generationsCertificats/finalSystem/utilisateurSysteme.pem" \
--header "ESC-sessionId: 41956f1b89014e67b032cbb46467421c" \
--data-binary "@salle.pem" \
--header "ESC-label: Vehicle" https://127.0.0.1:4100/EscDgtt/roomCreate
```

réponse:
```
HTTP/1.1 200 OK
< Date: Thu Jun 08 12:47:17 CEST 2023
< Server: EscDgtt 4.1.0_6c804
< ESC-roomId: d4dad43ab1de4722ab5dc0a43bf0f215
< ESC-timestamp: 2023-06-08T12:47:17.632+02:00
< ESC-escName: EscDgtt
< ESC-userIdCmd: 0254a02f751340e6844f9cd4e9612f22
< Content-Length: 0
```
On obient un id de la salle : <b>ESC-roomId: d4dad43ab1de4722ab5dc0a43bf0f215</b>

## Création de l'administrateur du coffre
A l'aide de l'id de connexion précédemment généré : <b>54a2f00e486941bb8ef92cfea075f970</b>

```
curl -v -X POST -k \
--cert "/home/escnode/generationsCertificats/finalSystem/utilisateurSysteme.pem" \
--header "ESC-sessionId: 41956f1b89014e67b032cbb46467421c" \
--header "ESC-nom: Administrateur" \
--data-binary "@/home/escnode/generationsCertificats/finalClient/administrateurCLIENT.pem" https://127.0.0.1:4100/EscDgtt/userAddAdmin
```

réponse:
```
HTTP/1.1 100 Continue
* We are completely uploaded and fine
* Mark bundle as not supporting multiuse
< HTTP/1.1 200 OK
< Date: Thu Jun 08 12:49:47 CEST 2023
< Server: EscDgtt 4.1.0_6c804
< ESC-userId: d1564bc53b6a40cf9fc319e2030a6356
< ESC-timestamp: 2023-06-08T12:49:47.844+02:00
< ESC-escName: EscDgtt
< ESC-userIdCmd: 0254a02f751340e6844f9cd4e9612f22
< Content-Length: 0
```
On obtient un id utilisateur : <b>ESC-userId: d1564bc53b6a40cf9fc319e2030a6356</b>

On donne ensuite les droit à l'utilisateur admin sur la salle de coffre créée plus haut.
```
curl -v -X POST -k \
--cert "/home/escnode/generationsCertificats/finalSystem/utilisateurSysteme.pem" \
--header "ESC-sessionId: 41956f1b89014e67b032cbb46467421c" \
--header "ESC-userId: d1564bc53b6a40cf9fc319e2030a6356" https://127.0.0.1:4100/EscDgtt/d4dad43ab1de4722ab5dc0a43bf0f215/roomRightAdd
```
réponse:
```
Mark bundle as not supporting multiuse
< HTTP/1.1 200 OK
< Date: Thu Jun 08 12:53:30 CEST 2023
< Server: EscDgtt 4.1.0_6c804
< ESC-rightId: 285655dd9f5f4c158f9d2e4c02c45c13
< ESC-timestamp: 2023-06-08T12:53:30.301+02:00
< ESC-escName: EscDgtt
< ESC-userIdCmd: 0254a02f751340e6844f9cd4e9612f22
< Content-Length: 0
```

Le compte admin créé avec les bons droits va servir pour créer et attribuer les droits aux autres utilisateurs et à créer les coffres.

## Création du coffre CoffreDgtt

On commence par se connecter avec l'utilisateur admin pour récupérer un id de session
```
curl -v -X POST -k \
--cert "/home/escnode/generationsCertificats/finalClient/administrateurCLIENT.pem" https://127.0.0.1:4100/EscDgtt/sessionOpen
```
réponse:
```
Mark bundle as not supporting multiuse
< HTTP/1.1 200 OK
< Date: Thu Jun 08 12:54:03 CEST 2023
< Server: EscDgtt 4.1.0_6c804
< ESC-sessionId: 8c28e1e95407426f81bd91f91f37db72
< ESC-userType: ADMIN
< ESC-userName: Administrateur
< ESC-userId: d1564bc53b6a40cf9fc319e2030a6356
< ESC-timestamp: 2023-06-08T12:54:03.248+02:00
< ESC-escName: EscDgtt
< ESC-userIdCmd: d1564bc53b6a40cf9fc319e2030a6356
< Content-Length: 0
```
L'id : <b>ESC-sessionId: 70d5b66007b4491f85982e96a5e5413c</b>

ensuite on lance la création de la salle avec un certificat auto-signé RSA
```
curl -v -X POST -k \
--cert "/home/escnode/generationsCertificats/finalClient/administrateurCLIENT.pem" \
--header "ESC-sessionId: 8c28e1e95407426f81bd91f91f37db72" \
--data-binary "@salle.pem" \
--header "ESC-label: CoffreDgtt" https://127.0.0.1:4100/EscDgtt/d4dad43ab1de4722ab5dc0a43bf0f215/safeCreate
```
réponse:
```
Mark bundle as not supporting multiuse
< HTTP/1.1 200 OK
< Date: Thu Jun 08 12:54:50 CEST 2023
< Server: EscDgtt 4.1.0_6c804
< ESC-safeId: 1014e78d836a4587a5799fb0760fd407
< ESC-archiveId: 1014e78d836a4587a5799fb0760fd407
< ESC-timestamp: 2023-06-08T12:54:50.817+02:00
< ESC-escName: EscDgtt
< ESC-userIdCmd: d1564bc53b6a40cf9fc319e2030a6356
< Content-Length: 0
```
On obtient un id pour le coffre : <b>ESC-safeId: 1014e78d836a4587a5799fb0760fd407</b>

## Création de l'utilisateur Gestionnaire 
C'est l'utilisateur qui permettra de faire les dépôts de fichiers dans le coffre.
On utilise l'id de session de l'administrateur.

```
curl -v -X POST -k \
--cert "/home/escnode/generationsCertificats/finalClient/administrateurCLIENT.pem" \
--header "ESC-sessionId: 8c28e1e95407426f81bd91f91f37db72" \
--header "ESC-type: GEST" \
--header "ESC-nom: Gestionnaire" \
--data-binary "@/home/escnode/generationsCertificats/finalClient/gestionaireCLIENT.pem" https://127.0.0.1:4100/EscDgtt/userAdd
```
réponse:
```
Mark bundle as not supporting multiuse
< HTTP/1.1 100 Continue
* We are completely uploaded and fine
* Mark bundle as not supporting multiuse
< HTTP/1.1 200 OK
< Date: Thu Jun 08 12:57:14 CEST 2023
< Server: EscDgtt 4.1.0_6c804
< ESC-userId: 1c9669db21e94ab2a8e38efba0179409
< ESC-timestamp: 2023-06-08T12:57:14.447+02:00
< ESC-escName: EscDgtt
< ESC-userIdCmd: d1564bc53b6a40cf9fc319e2030a6356
< Content-Length: 0
```
On obtient un id utilisateur : <b>ESC-userId: 1c9669db21e94ab2a8e38efba0179409</b>

on donne ensuite les droits à la salle de coffres
```
curl -v -X POST -k \
--cert "/home/escnode/generationsCertificats/finalClient/administrateurCLIENT.pem" \
--header "ESC-sessionId: 8c28e1e95407426f81bd91f91f37db72" \
--header "ESC-userId: 1c9669db21e94ab2a8e38efba0179409" https://127.0.0.1:4100/EscDgtt/d4dad43ab1de4722ab5dc0a43bf0f215/roomRightAdd
```
réponse:
```
Mark bundle as not supporting multiuse
< HTTP/1.1 200 OK
< Date: Thu Jun 08 13:01:02 CEST 2023
< Server: EscDgtt 4.1.0_6c804
< ESC-rightId: e8550aa7468740ef81bb8b21006e50dd
< ESC-timestamp: 2023-06-08T13:01:02.998+02:00
< ESC-escName: EscDgtt
< ESC-userIdCmd: d1564bc53b6a40cf9fc319e2030a6356
< Content-Length: 0
```

## Création de l'utilisateur lecteur
C'est l'utilisateur qui permettra de faire les dépôts de fichiers dans le coffre.
On utilise l'id de session de l'administrateur.

On commence par se connecter avec l'utilisateur admin pour récupérer un id de session
```
curl -v -X POST -k \
--cert "/home/escnode/generationsCertificats/finalClient/administrateurCLIENT.pem" https://127.0.0.1:4100/EscDgtt/sessionOpen
```
réponse:
```
Mark bundle as not supporting multiuse
< HTTP/1.1 200 OK
< Date: Mon May 15 16:33:40 CEST 2023
< Server: EscDgtt 4.1.0_6c804
< ESC-sessionId: 6f8be98cdd7a432aaf99c7fb5d223fd8
< ESC-userType: ADMIN
< ESC-userName: Administrateur
< ESC-userId: 54267662bcd548cbaa8f35dd846922a6
< ESC-timestamp: 2023-05-15T16:33:40.552+02:00
< ESC-escName: EscDgtt
< ESC-userIdCmd: 54267662bcd548cbaa8f35dd846922a6
< Content-Length: 0
```

Id de session : <b>ESC-sessionId: 6f8be98cdd7a432aaf99c7fb5d223fd8</b>

On crée l'utilisateur à l'aide de l'id de session admin
```
curl -v -X POST -k \
--cert "/home/escnode/generationsCertificats/finalClient/administrateurCLIENT.pem" \
--header "ESC-sessionId: 8c28e1e95407426f81bd91f91f37db72" \
--header "ESC-type: LECT" \
--header "ESC-nom: Lecteur" \
--data-binary "@/home/escnode/generationsCertificats/finalClient/lecteurCLIENT.pem" https://127.0.0.1:4100/EscDgtt/userAdd
```
réponse:
```
Mark bundle as not supporting multiuse
< HTTP/1.1 100 Continue
* We are completely uploaded and fine
* Mark bundle as not supporting multiuse
< HTTP/1.1 200 OK
< Date: Thu Jun 08 13:02:03 CEST 2023
< Server: EscDgtt 4.1.0_6c804
< ESC-userId: 80dcfa032dc24ac5a5bc01986f9928ad
< ESC-timestamp: 2023-06-08T13:02:03.079+02:00
< ESC-escName: EscDgtt
< ESC-userIdCmd: d1564bc53b6a40cf9fc319e2030a6356
< Content-Length: 0
```

On obtient d'id de l'utilisateur lecteur : <b>ESC-userId: 80dcfa032dc24ac5a5bc01986f9928ad</b>

on donne ensuite les droits à la salle de coffres
```
curl -v -X POST -k \
--cert "/home/escnode/generationsCertificats/finalClient/administrateurCLIENT.pem" \
--header "ESC-sessionId: 8c28e1e95407426f81bd91f91f37db72" \
--header "ESC-userId: 80dcfa032dc24ac5a5bc01986f9928ad" https://127.0.0.1:4100/EscDgtt/d4dad43ab1de4722ab5dc0a43bf0f215/roomRightAdd
```
réponse:
```
Mark bundle as not supporting multiuse
< HTTP/1.1 200 OK
< Date: Thu Jun 08 13:02:40 CEST 2023
< Server: EscDgtt 4.1.0_6c804
< ESC-rightId: c3b125f210f44a7e8053d2a0d00ec2e0
< ESC-timestamp: 2023-06-08T13:02:40.431+02:00
< ESC-escName: EscDgtt
< ESC-userIdCmd: d1564bc53b6a40cf9fc319e2030a6356
< Content-Length: 0
```

## Test de dépôt de fichier
On se connecte avec le certificat gestionnaire
```
curl -v -X POST -k \
--cert "/home/escnode/generationsCertificats/finalClient/gestionaireCLIENT.pem" https://127.0.0.1:4100/EscDgtt/sessionOpen
```
réponse:
```
Mark bundle as not supporting multiuse
< HTTP/1.1 200 OK
< Date: Thu Jun 08 13:03:59 CEST 2023
< Server: EscDgtt 4.1.0_6c804
< ESC-sessionId: c13f4dffc5cb4e979026344146e7d585
< ESC-userType: GEST
< ESC-userName: Gestionnaire
< ESC-userId: 1c9669db21e94ab2a8e38efba0179409
< ESC-timestamp: 2023-06-08T13:03:59.331+02:00
< ESC-escName: EscDgtt
< ESC-userIdCmd: 1c9669db21e94ab2a8e38efba0179409
< Content-Length: 0
```

Dépôt du fichier nomfichier.txt
```
curl -v -X POST -k \
--cert "/home/escnode/generationsCertificats/finalClient/gestionaireCLIENT.pem" \
--header "ESC-sessionId: c13f4dffc5cb4e979026344146e7d585" \
--header "ESC-ucsId;" \
--header "ESC-fileName: nomfichier.txt" \
--header "ESC-archiveLabel: nomexagere" \
--data-binary "@nomfichier.txt" https://127.0.0.1:4100/EscDgtt/d4dad43ab1de4722ab5dc0a43bf0f215/1014e78d836a4587a5799fb0760fd407/archivePutNoControl
```
réponse:
```
Mark bundle as not supporting multiuse
< HTTP/1.1 200 OK
< Date: Thu Jun 08 13:05:08 CEST 2023
< Server: EscDgtt 4.1.0_6c804
< ESC-archiveId: 4999c5b97760456eada60fda7449290f
< ESC-ucsId: 7dd7a83cb2f04018ad244c68cff1bbdd
< ESC-timestamp: 2023-06-08T13:05:08.077+02:00
< ESC-escName: EscDgtt
< ESC-userIdCmd: 1c9669db21e94ab2a8e38efba0179409
< Content-Length: 492
< Content-Type: application/json; charset=UTF-8
< 
* Connection #0 to host 127.0.0.1 left intact
{"id_archive":"4999c5b97760456eada60fda7449290f","nom_fichier":"nomfichier.txt","libelle_archive":"nomexagere","archive_principale":true,"id_ucs":"7dd7a83cb2f04018ad244c68cff1bbdd","id_salle":"d4dad43ab1de4722ab5dc0a43bf0f215","id_coffre":"1014e78d836a4587a5799fb0760fd407","date_depot":"2023-06-08T13:05:07.513+02:00","poids_octet":0,"config_horodatage":"XMLDSIG","hash":"38b060a751ac96384cd9327eb1b1e36a21fdb71114be07434c0cc7bf63f6e1da274edebfe76f65fbd51ad2f14898b95b","hash_type":"SHA384"}
```

preuve de dépôt renvoyée par le coffre
```
{
	"id_archive":"4999c5b97760456eada60fda7449290f",
	"nom_fichier":"nomfichier.txt",
	"libelle_archive":"nomexagere",
	"archive_principale":true,
	"id_ucs":"7dd7a83cb2f04018ad244c68cff1bbdd",
	"id_salle":"d4dad43ab1de4722ab5dc0a43bf0f215",
	"id_coffre":"1014e78d836a4587a5799fb0760fd407",
	"date_depot":"2023-06-08T13:05:07.513+02:00",
	"poids_octet":0,
	"config_horodatage":"XMLDSIG",
	"hash":"38b060a751ac96384cd9327eb1b1e36a21fdb71114be07434c0cc7bf63f6e1da274edebfe76f65fbd51ad2f14898b95b",
	"hash_type":"SHA384"
}
```

## Test de téléchargement de fichier
On se connecte avec le certificat gestionnaire
```
curl -v -X POST -k \
--cert "/home/escnode/generationsCertificats/finalClient/lecteurCLIENT.pem" https://127.0.0.1:4100/EscDgtt/sessionOpen
```
réponse:
```
Mark bundle as not supporting multiuse
< HTTP/1.1 200 OK
< Date: Thu Jun 08 13:07:49 CEST 2023
< Server: EscDgtt 4.1.0_6c804
< ESC-sessionId: bc4e0cd94d6441a79ae759dca73d2db0
< ESC-userType: LECT
< ESC-userName: Lecteur
< ESC-userId: 80dcfa032dc24ac5a5bc01986f9928ad
< ESC-timestamp: 2023-06-08T13:07:49.074+02:00
< ESC-escName: EscDgtt
< ESC-userIdCmd: 80dcfa032dc24ac5a5bc01986f9928ad
< Content-Length: 0
```

Téléchargement du fichier
```
curl -v -X POST -k \
--cert "/home/escnode/generationsCertificats/finalClient/lecteurCLIENT.pem" \
--header "ESC-sessionId: bc4e0cd94d6441a79ae759dca73d2db0" \
--header "ESC-archiveId: 4999c5b97760456eada60fda7449290f" \
https://127.0.0.1:4100/EscDgtt/d4dad43ab1de4722ab5dc0a43bf0f215/1014e78d836a4587a5799fb0760fd407/archiveGet
```

réponse:
```
Mark bundle as not supporting multiuse
< HTTP/1.1 200 OK
< Date: Thu Jun 08 13:08:33 CEST 2023
< Server: EscDgtt 4.1.0_6c804
< ESC-fileName: nomfichier.txt
< ESC-label: nomexagere
< ESC-archiveSize: 0
< ESC-hashType: SHA384
< ESC-archiveId: 4999c5b97760456eada60fda7449290f
< ESC-hash: 38b060a751ac96384cd9327eb1b1e36a21fdb71114be07434c0cc7bf63f6e1da274edebfe76f65fbd51ad2f14898b95b
< ESC-timestamp: 2023-06-08T13:08:33.760+02:00
< ESC-escName: EscDgtt
< ESC-userIdCmd: 80dcfa032dc24ac5a5bc01986f9928ad
< Content-Length: 0
< Content-Type: application/octet-stream
```