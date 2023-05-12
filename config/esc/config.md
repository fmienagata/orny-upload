# ESC DEV 

en local dans serveur upload du0-aes-upload.demat.lan 172.29.95.15

URL : https://127.0.0.1:10443<br>
Salle : <b>2f6f2d9fb46c4e558e8873a2ac7899ca</b><br>
Admin : <b>54267662bcd548cbaa8f35dd846922a6</b><br>
Certificat admin : <b>/home/escnode/generationsCertificats/administrateurCLIENT.pem</b><br>
Coffre : <b>c324f337acfd4fa4a93d124f58a6a260</b><br>
Nom du coffre : <b>CoffreDgtt</b><br>
Gestionnaire : <b>8ea44c9fa08446afbde3f4edc25de648</b><br>
Certificat admin : <b>/home/escnode/generationsCertificats/gestionnaireCLIENT.pem</b><br>

## Création de la salle:
Connexion à l'esc <b>ESCNODE_410</b> avec le certificate utilisateurSysteme.pem, pour généré un id de connexion
```
curl -v -X POST -k \
--cert "/home/escnode/generationsCertificats/finalSystem/utilisateurSysteme.pem" https://127.0.0.1:10443/ESCNODE_410/sessionOpen
```
réponse:
```
HTTP/1.1 200 OK
< Date: Tue May 09 18:02:29 CEST 2023
< Server: ESCNODE_410 4.1.0_6c804
< ESC-sessionId: 54a2f00e486941bb8ef92cfea075f970
< ESC-userType: SYSTEM
< ESC-userName: SYSTEM
< ESC-userId: ecf7019998c94e38828cf7fdc0dc86c0
< ESC-timestamp: 2023-05-09T18:02:29.757+02:00
< ESC-escName: ESCNODE_410
< ESC-userIdCmd: ecf7019998c94e38828cf7fdc0dc86c0
< Content-Length: 0
```
L'id de connexion est le suivant: <b>54a2f00e486941bb8ef92cfea075f970</b>

création de la salle de coffre
```
curl -v -X POST -k \
--cert "/home/escnode/generationsCertificats/finalSystem/utilisateurSysteme.pem" \
--header "ESC-sessionId: 54a2f00e486941bb8ef92cfea075f970" \
--data-binary "@salle.pem" \
--header "ESC-label: Vehicle" https://127.0.0.1:10443/ESCNODE_410/roomCreate
```

réponse:
```
HTTP/1.1 100 Continue
* We are completely uploaded and fine
* Mark bundle as not supporting multiuse
  < HTTP/1.1 200 OK
  < Date: Tue May 09 18:05:46 CEST 2023
  < Server: ESCNODE_410 4.1.0_6c804
  < ESC-roomId: 2f6f2d9fb46c4e558e8873a2ac7899ca
  < ESC-timestamp: 2023-05-09T18:05:46.632+02:00
  < ESC-escName: ESCNODE_410
  < ESC-userIdCmd: ecf7019998c94e38828cf7fdc0dc86c0
  < Content-Length: 0
```
On obient un id de la salle : <b>ESC-roomId: 2f6f2d9fb46c4e558e8873a2ac7899ca</b>

## Création de l'administrateur du coffre
A l'aide de l'id de connexion précédemment généré : <b>54a2f00e486941bb8ef92cfea075f970</b>

```
curl -v -X POST -k \
--cert "/home/escnode/generationsCertificats/finalSystem/utilisateurSysteme.pem" \
--header "ESC-sessionId: 54a2f00e486941bb8ef92cfea075f970" \
--header "ESC-nom: Administrateur" \
--data-binary "@/home/escnode/generationsCertificats/finalClient/administrateurCLIENT.pem" https://127.0.0.1:10443/ESCNODE_410/userAddAdmin
```

réponse:
```
HTTP/1.1 100 Continue
* We are completely uploaded and fine
* Mark bundle as not supporting multiuse
  < HTTP/1.1 200 OK
  < Date: Tue May 09 18:08:16 CEST 2023
  < Server: ESCNODE_410 4.1.0_6c804
  < ESC-userId: 54267662bcd548cbaa8f35dd846922a6
  < ESC-timestamp: 2023-05-09T18:08:16.748+02:00
  < ESC-escName: ESCNODE_410
  < ESC-userIdCmd: ecf7019998c94e38828cf7fdc0dc86c0
  < Content-Length: 0
```
On obtient un id utilisateur : <b>ESC-userId: 54267662bcd548cbaa8f35dd846922a6</b>

On donne ensuite les droit à l'utilisateur admin sur la salle de coffre créée plus haut.
```
curl -v -X POST -k \
--cert "/home/escnode/generationsCertificats/finalSystem/utilisateurSysteme.pem" \
--header "ESC-sessionId: 54a2f00e486941bb8ef92cfea075f970" \
--header "ESC-userId: 54267662bcd548cbaa8f35dd846922a6" https://127.0.0.1:10443/ESCNODE_410/2f6f2d9fb46c4e558e8873a2ac7899ca/roomRightAdd
```
réponse:
```
Mark bundle as not supporting multiuse
< HTTP/1.1 200 OK
< Date: Tue May 09 18:10:13 CEST 2023
< Server: ESCNODE_410 4.1.0_6c804
< ESC-rightId: 76e7385e902e43d1aa11541afb0a5647
< ESC-timestamp: 2023-05-09T18:10:13.564+02:00
< ESC-escName: ESCNODE_410
< ESC-userIdCmd: ecf7019998c94e38828cf7fdc0dc86c0
< Content-Length: 0
```

Le compte admin créé avec les bons droits va servir pour créer et attribuer les droits aux autres utilisateurs et à créer les coffres.

## Création du coffre CoffreDgtt

On commence par se connecter avec l'utilisateur admin pour récupérer un id de session
```
curl -v -X POST -k \
--cert "/home/escnode/generationsCertificats/finalClient/administrateurCLIENT.pem" https://127.0.0.1:10443/ESCNODE_410/sessionOpen
```
réponse:
```
HTTP/1.1 200 OK
< Date: Tue May 09 18:13:14 CEST 2023
< Server: ESCNODE_410 4.1.0_6c804
< ESC-sessionId: 70d5b66007b4491f85982e96a5e5413c
< ESC-userType: ADMIN
< ESC-userName: Administrateur
< ESC-userId: 54267662bcd548cbaa8f35dd846922a6
< ESC-timestamp: 2023-05-09T18:13:14.185+02:00
< ESC-escName: ESCNODE_410
< ESC-userIdCmd: 54267662bcd548cbaa8f35dd846922a6
< Content-Length: 0
```
L'id : <b>ESC-sessionId: 70d5b66007b4491f85982e96a5e5413c</b>

ensuite on lance la création de la salle avec un certificat auto-signé RSA
```
curl -v -X POST -k \
--cert "/home/escnode/generationsCertificats/finalClient/administrateurCLIENT.pem" \
--header "ESC-sessionId: 70d5b66007b4491f85982e96a5e5413c" \
--data-binary "@salle.pem" \
--header "ESC-label: CoffreDgtt" https://127.0.0.1:10443/ESCNODE_410/2f6f2d9fb46c4e558e8873a2ac7899ca/safeCreate
```
réponse:
```
Mark bundle as not supporting multiuse
< HTTP/1.1 200 OK
< Date: Tue May 09 18:19:23 CEST 2023
< Server: ESCNODE_410 4.1.0_6c804
< ESC-safeId: c324f337acfd4fa4a93d124f58a6a260
< ESC-archiveId: c324f337acfd4fa4a93d124f58a6a260
< ESC-timestamp: 2023-05-09T18:19:23.641+02:00
< ESC-escName: ESCNODE_410
< ESC-userIdCmd: 54267662bcd548cbaa8f35dd846922a6
< Content-Length: 0
```
On obtient un id pour le coffre : <b>ESC-safeId: c324f337acfd4fa4a93d124f58a6a260</b>

## Création de l'utilisateur Gestionnaire 
C'est l'utilisateur qui permettra de faire les dépôts de fichiers dans le coffre.
On utilise l'id de session de l'administrateur.

```
curl -v -X POST -k \
--cert "/home/escnode/generationsCertificats/finalClient/administrateurCLIENT.pem" \
--header "ESC-sessionId: 70d5b66007b4491f85982e96a5e5413c" \
--header "ESC-type: GEST" \
--header "ESC-nom: Gestionnaire" \
--data-binary "@/home/escnode/generationsCertificats/finalClient/gestionaireCLIENT.pem" https://127.0.0.1:10443/ESCNODE_410/userAdd
```
réponse:
```
HTTP/1.1 100 Continue
* We are completely uploaded and fine
* Mark bundle as not supporting multiuse
  < HTTP/1.1 200 OK
  < Date: Tue May 09 18:21:23 CEST 2023
  < Server: ESCNODE_410 4.1.0_6c804
  < ESC-userId: 8ea44c9fa08446afbde3f4edc25de648
  < ESC-timestamp: 2023-05-09T18:21:23.287+02:00
  < ESC-escName: ESCNODE_410
  < ESC-userIdCmd: 54267662bcd548cbaa8f35dd846922a6
  < Content-Length: 0
```
On obtient un id utilisateur : <b>ESC-userId: 8ea44c9fa08446afbde3f4edc25de648</b>

on donne ensuite les droits à la salle de coffres
```
curl -v -X POST -k \
--cert "/home/escnode/generationsCertificats/finalClient/administrateurCLIENT.pem" \
--header "ESC-sessionId: 70d5b66007b4491f85982e96a5e5413c" \
--header "ESC-userId: 8ea44c9fa08446afbde3f4edc25de648" https://127.0.0.1:10443/ESCNODE_410/2f6f2d9fb46c4e558e8873a2ac7899ca/roomRightAdd
```
réponse:
```
Mark bundle as not supporting multiuse
< HTTP/1.1 200 OK
< Date: Tue May 09 18:23:04 CEST 2023
< Server: ESCNODE_410 4.1.0_6c804
< ESC-rightId: 8a43c9fd60f04fabb3749d82af6e632c
< ESC-timestamp: 2023-05-09T18:23:04.313+02:00
< ESC-escName: ESCNODE_410
< ESC-userIdCmd: 54267662bcd548cbaa8f35dd846922a6
< Content-Length: 0
```

## Test de dépôt de fichier
On se connecte avec le certificat gestionnaire
```
curl -v -X POST -k \
--cert "/home/escnode/generationsCertificats/finalClient/gestionaireCLIENT.pem" https://127.0.0.1:10443/ESCNODE_410/sessionOpen
```
réponse:
```
Mark bundle as not supporting multiuse
< HTTP/1.1 200 OK
< Date: Tue May 09 18:24:29 CEST 2023
< Server: ESCNODE_410 4.1.0_6c804
< ESC-sessionId: 7dcc896b7f3e425ca94aab0eaa7b52b1
< ESC-userType: GEST
< ESC-userName: Gestionnaire
< ESC-userId: 8ea44c9fa08446afbde3f4edc25de648
< ESC-timestamp: 2023-05-09T18:24:29.372+02:00
< ESC-escName: ESCNODE_410
< ESC-userIdCmd: 8ea44c9fa08446afbde3f4edc25de648
< Content-Length: 0
```

Dépôt du fichier nomfichier.txt
```
curl -v -X POST -k \
--cert "/home/escnode/generationsCertificats/finalClient/gestionaireCLIENT.pem" \
--header "ESC-sessionId: 7dcc896b7f3e425ca94aab0eaa7b52b1" \
--header "ESC-ucsId;" \
--header "ESC-fileName: nomfichier.txt" \
--header "ESC-archiveLabel: nomexagere" \
--data-binary "@nomfichier.txt" https://127.0.0.1:10443/ESCNODE_410/2f6f2d9fb46c4e558e8873a2ac7899ca/c324f337acfd4fa4a93d124f58a6a260/archivePutNoControl
```
réponse:
```
Mark bundle as not supporting multiuse
< HTTP/1.1 200 OK
< Date: Tue May 09 18:30:17 CEST 2023
< Server: ESCNODE_410 4.1.0_6c804
< ESC-archiveId: 231f68c154944a60b2223d3fbb0a75c2
< ESC-ucsId: 3a795d0f832a4559b77aa07b64dfd014
< ESC-timestamp: 2023-05-09T18:30:17.055+02:00
< ESC-escName: ESCNODE_410
< ESC-userIdCmd: 8ea44c9fa08446afbde3f4edc25de648
< Content-Length: 492
< Content-Type: application/json; charset=UTF-8
<
* Connection #0 to host 127.0.0.1 left intact
  {"id_archive":"231f68c154944a60b2223d3fbb0a75c2","nom_fichier":"nomfichier.txt","libelle_archive":"nomexagere","archive_principale":true,"id_ucs":"3a795d0f832a4559b77aa07b64dfd014","id_salle":"2f6f2d9fb46c4e558e8873a2ac7899ca","id_coffre":"c324f337acfd4fa4a93d124f58a6a260","date_depot":"2023-05-09T18:30:17.013+02:00","poids_octet":0,"config_horodatage":"XMLDSIG","hash":"38b060a751ac96384cd9327eb1b1e36a21fdb71114be07434c0cc7bf63f6e1da274edebfe76f65fbd51ad2f14898b95b","hash_type":"SHA384"}
```

preuve de dépôt renvoyée par le coffre
```
{
    "id_archive": "231f68c154944a60b2223d3fbb0a75c2",
    "nom_fichier": "nomfichier.txt",
    "libelle_archive": "nomexagere",
    "archive_principale": true,
    "id_ucs": "3a795d0f832a4559b77aa07b64dfd014",
    "id_salle": "2f6f2d9fb46c4e558e8873a2ac7899ca",
    "id_coffre": "c324f337acfd4fa4a93d124f58a6a260",
    "date_depot": "2023-05-09T18:30:17.013+02:00",
    "poids_octet": 0,
    "config_horodatage": "XMLDSIG",
    "hash": "38b060a751ac96384cd9327eb1b1e36a21fdb71114be07434c0cc7bf63f6e1da274edebfe76f65fbd51ad2f14898b95b",
    "hash_type": "SHA384"
}
```