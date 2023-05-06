# Upload Bundle

Module d'import de documents

http://dw0-mut-auto10.demat.lan:13033/conversion/v1.0/rest


# Démarrage d'un ESC
## Démarrage de tous les ESC
```
    docker-compose up -d
 ```
## Démarrage de l'ESC spécifique
```
    docker-compose up esc410 -d
 ```

# Création d'un certificat auto-signé dans linux(ou dans le conteneur)
Référence documentaire : https://wiki.csnu.org/index.php/Installation_et_configuration_de_OpenSSL

### Certificat signature
Nous allons créer le certificat qui permettra de signer tous les autres. C'est le certificat racine.

```
cd /home/escnode/generationsCertificats && \
openssl genrsa -out finalSystem/certificateSigning.key 4096 && \
openssl rsa -in finalSystem/certificateSigning.key -out finalSystem/certificateSigning.key && \
openssl req -sha256 -new -key finalSystem/certificateSigning.key -out finalSystem/certificateSigning.csr --config openssl_min_conf.cnf && \
openssl x509 -req -sha256 -days 3650 -extensions req_ext -extfile openssl_min_conf.cnf -in finalSystem/certificateSigning.csr -signkey finalSystem/certificateSigning.key -out finalSystem/certificateSigning.crt && \
cat finalSystem/certificateSigning.crt finalSystem/certificateSigning.key > finalSystem/certificateSigning.pem
```

### Certficat super utilisateur
Ce certificat comme les autres est signé à l'aide du certificat signature créé plus haut.

```
cd /home/escnode/generationsCertificats && \
openssl req -sha256 -new -key finalSystem/certificateSigning.key -out finalSystem/userSystem.csr --config openssl.cnf && \
openssl x509 -req -sha256 -days 3650 -extensions req_ext -extfile openssl_min_conf.cnf -in finalSystem/userSystem.csr -signkey finalSystem/certificateSigning.key -out finalSystem/userSystem.crt && \
cat finalSystem/userSystem.crt finalSystem/certificateSigning.key > finalSystem/userSystem.pem
```

### Certficat de l'horodateur
Ce certificat comme les autres est signé à l'aide du certificat signature créé plus haut.

```
cd /home/escnode/generationsCertificats && \
openssl req -sha256 -new -key finalSystem/certificateSigning.key -out finalSystem/timestampSystem.csr --config openssl.cnf && \
openssl x509 -req -sha256 -days 3650 -extensions req_ext -extfile openssl_min_conf.cnf -in finalSystem/timestampSystem.csr -signkey finalSystem/certificateSigning.key -out finalSystem/timestampSystem.crt && \
cat finalSystem/timestampSystem.crt finalSystem/certificateSigning.key > finalSystem/timestampSystem.pem
```

### Certficat de ssl systeme
Ce certificat comme les autres est signé à l'aide du certificat signature créé plus haut.

```
cd /home/escnode/generationsCertificats && \
openssl req -sha256 -new -key finalSystem/certificateSigning.key -out finalSystem/sslSystem.csr --config openssl.cnf && \
openssl x509 -req -sha256 -days 3650 -extensions req_ext -extfile openssl_min_conf.cnf -in finalSystem/sslSystem.csr -signkey finalSystem/certificateSigning.key -out finalSystem/sslSystem.crt && \
cat finalSystem/sslSystem.crt finalSystem/certificateSigning.key > finalSystem/sslSystem.pem
```

# Procédure d'installation d'un ESC 4.1.0 vierge

### Se connecter en system et récupérer le header 'ESC-sessionId' => \<sessionSystem>
```
curl -v -X POST -k --cert "certificats/finalSystem/utilisateurSysteme.pem" https://127.0.0.1:10443/ESCNODE_410/sessionOpen
```
#### Générer un certificat auto-signé au format RSA/PEM via openssl => \<certificatsalle>

### Créer la salle et récupérer le header 'ESC-roomId' => \<RoomID>
```
curl -v -X POST -k --cert "certificats/finalSystem/utilisateurSysteme.pem" -–header "ESC-sessionId: <sessionSystem>" -–data-binary "@<certificatsalle>.pem" –-header "ESC-label: Salle1" https://127.0.0.1:12713/ESCNODE_2713/roomCreate
```
### Créer un utilisateur de type Administrateur et récupérer le header 'ESC-userId' => \<adminID>
```
curl -v -X POST -k --cert "certificats/finalSystem/utilisateurSysteme.pem" -–header "ESC-sessionId: <sessionSystem>" –-header "ESC-nom: Administrateur" -–data-binary "@certificats/finalClient/administrateurCLIENT.pem" https://127.0.0.1:12713/ESCNODE_2713/userAddAdmin
```
### Ajout des droits d'administration sur la salle \<RoomID>
```
curl -v -X POST -k --cert "certificats/finalSystem/utilisateurSysteme.pem" -–header "ESC-sessionId: <sessionSystem>" –-header "ESC-userId: <adminID>" https://127.0.0.1:12713/ESCNODE_2713/<RoomID>/roomRightAdd
```
### Se connecter en tant qu'Adminitrateur et récupérer le header 'ESC-sessionId' => \<sessionAdmin>
```
curl -v -X POST -k --cert "certificats/finalClient/administrateurCLIENT.pem" https://127.0.0.1:12713/ESCNODE_2713/sessionOpen
```
### Générer un certificat auto-signé au format RSA/PEM via openssl => \<certificat coffre>
### Création du coffre et récupérer le header 'ESC-safeId' => \<safeID>
```
curl -v -X POST -k --cert "certificats/finalClient/administrateurCLIENT.pem" --header "ESC-sessionId: <sessionAdmin>" -–data-binary "@<certificat coffre>.pem" –-header "ESC-label: Coffre1" https://127.0.0.1:12713/ESCNODE_2713/<RoomID>/safeCreate
```
### Création d'un utilisateur gestionnaire et récupérer le header 'ESC-userId' => \<gestionnaireID>
```
curl -v -X POST -k --cert "certificats/finalClient/administrateurCLIENT.pem" -–header "ESC-sessionId: <sessionAdmin>" –-header "ESC-type: GEST" –-header "ESC-nom: Gestionnaire" -–data-binary "@certificats/finalClient/gestionaireCLIENT.pem" https://127.0.0.1:12713/ESCNODE_2713/userAdd
```
### Ajout des droits de gestionnaire sur la salle \<RoomID>
```
curl -v -X POST -k --cert "certificats/finalClient/administrateurCLIENT.pem" -–header "ESC-sessionId: <sessionAdmin>" –-header "ESC-userId: <gestionnaireID>" https://127.0.0.1:12713/ESCNODE_2713/<RoomID>/roomRightAdd
```
###Se connecter en tant que gestionnaire et récupérer le header 'ESC-sessionId' => \<sessionGestionnaire>
```
curl -v -X POST -k --cert "certificats/finalClient/gestionaireCLIENT.pem" https://127.0.0.1:12713/ESCNODE_2713/sessionOpen
```
### Déposer un fichier en tant que gestionnaire
```
curl -v -X POST -k --cert "certificats/finalClient/gestionaireCLIENT.pem" -–header "ESC-sessionId: <sessionGestionnaire>" -–header "ESC-ucsId;" -–header "ESC-fileName: nomfichier.txt" -–header "ESC-archiveLabel: nomexagere" --data-binary "@nomfichier.txt" https://127.0.0.1:12713/ESCNODE_2713/<RoomID>/<safeID>/archivePutNoControl
```