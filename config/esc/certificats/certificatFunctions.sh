
function signatureSystem # fileName outDir
{
echo "-------Generation de $1"
#Génération clé
openssl genrsa -out $1.key 2048

#Génération request
openssl req -new -key $1.key -out $1.csr -subj /C=FR/ST=IDF/L=Paris/O=Docapost-dps/OU=ESC/CN=ESC-signature/emailAddress=esc@docapost-dps.fr

#Génération certificat
openssl x509 -req -days 365 -in $1.csr -signkey $1.key -out $2/$1.crt
cat $2/$1.crt $1.key > $2/$1.pem
openssl x509 -text -in $2/$1.pem

#suppression fichiers temporaire
rm -f $1.key $1.csr
}

function utilisateurSysteme #fileName outDir
{
echo "-------Generation de $1"
#Génération clé
openssl genrsa -out $1.key 2048

#Génération request
openssl req -new -key $1.key -out $1.csr -subj /C=FR/ST=IDF/L=Paris/O=Docapost-dps/OU=ESC/CN=ESC-UserSysteme

#Génération certificat
openssl x509 -req -days 365 -in $1.csr -signkey $1.key -out $2/$1.crt
cat $2/$1.crt $1.key > $2/$1.pem

#Création du PFX
openssl pkcs12 -export -out $2/$1.pfx -inkey $1.key -in $2/$1.crt -passout pass:
openssl x509 -text -in $2/$1.pem

#suppression fichiers temporaire
rm -f $1.key $1.csr
}

function horodatageSysteme #fileName outDir
{
echo "-------Generation de $1"
PASS=esc1234
#Création de la clé 
openssl genrsa -aes256 -out $1.key -passout pass:$PASS
openssl rsa -in $1.key -out $1.key -passin  pass:$PASS

#Génération request
openssl req -new -key $1.key -out $1.csr -subj /C=FR/ST=IDF/L=Paris/O=Docapost-dps/OU=ESC/CN=ESC-Horodatage/emailAddress=esc@docapost-dps.fr

#Génération du certificat
openssl ca -config ca.cnf -in $1.csr -out $1.pem -extensions esc_tsp -batch -key $PASS
cat $1.pem $1.key > $2/$1.pem
openssl x509 -text -in $2/$1.pem

#suppression fichiers temporaire
rm -f $1.key $1.csr $1.pem
}


function sslSysteme #fileName outDir
{
echo "-------Generation de $1"
#Génération clé
openssl genrsa -out $1.key 2048
#Génération request
openssl req -new -key $1.key -out $1.csr -subj /C=FR/ST=IDF/L=Paris/O=Docapost-dps/OU=ESC/CN=ESC-ssl
#Génération certificat
openssl x509 -req -days 365 -in $1.csr -signkey $1.key -out $1.crt
cat $1.crt $1.key > $2/$1.pem
openssl x509 -text -in $2/$1.pem

#suppression fichiers temporaire
rm -f $1.key $1.csr $1.crt
}


function certificat # fileName outDir pfxOption
{
echo "-------Generation de $1"
#Génération clé
openssl genrsa -out $1.key 2048
#Génération request
openssl req -new -key $1.key -out $1.csr -subj /C=FR/ST=IDF/L=Paris/O=CLIENT/OU=CLIENT/CN=ESC-Signature
#Génération certificat
openssl x509 -req -days 365 -in $1.csr -signkey $1.key -out $2/$1.crt
cat $2/$1.crt $1.key > $2/$1.pem
openssl pkcs12 -export -out $2/$1.pfx -inkey $1.key -in $2/$1.crt -passout pass:
openssl x509 -text -in $2/$1.pem

#suppression fichiers temporaire
rm -f $1.key $1.csr
}






