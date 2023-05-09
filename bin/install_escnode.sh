#!/bin/bash

ESC_NAME="EscDgtt"
ESC_VERSION="4.1.0"
MYSQL_HOST="localhost"
MYSQL_PORT="3306"
ESC_MYSQL_USER="escClient"
ESC_MYSQL_PASSWORD="escClient1234"
SSL_CERTIFICAT_SYSTEM_PEM=$(</home/escnode/generationsCertificats/finalSystem/sslSysteme.pem)
JAVA="/usr/lib/jvm/java-8-openjdk-amd64/jre/bin/java"

set -o errexit
set -o nounset
maxcounter=45
counter=1
while ! mysql --host="$MYSQL_HOST" -u "$ESC_MYSQL_USER" -p"$ESC_MYSQL_PASSWORD" --port="3306" -e "show databases;" > /dev/null 2>&1; do
    sleep 1
    counter=`expr $counter + 1`
    if [ $counter -gt $maxcounter ]; then
        >&2 echo "We have been waiting for MySQL too long already; failing."
        exit 1
    fi;
done

cat /home/escnode/esc/config.xml

# Create standard user and grant permissions
if [ $( echo "SHOW DATABASES LIKE '"$ESC_NAME"';" | mysql -N --host="$MYSQL_HOST" -u "$ESC_MYSQL_USER" -p"$ESC_MYSQL_PASSWORD" --port="$MYSQL_PORT" | wc -l ) -eq 0 ]; then
    $JAVA -jar /home/escnode/esc/EscNode.jar \
    -install "$ESC_NAME" \
    -name "$ESC_NAME" \
    -site "$ESC_NAME" \
    -configPath "/home/escnode/esc/config.xml" \
    -superUser "/home/escnode/generationsCertificats/finalSystem/utilisateurSysteme.pem" \
    -certificatSignature "/home/escnode/generationsCertificats/finalSystem/signatureSysteme.pem" \
    -certificatTimeStamp "/home/escnode/generationsCertificats/finalSystem/horodatageSysteme.pem" \
    -mountPoint "/data/esc/$ESC_NAME" \
    -debugMode
fi

$JAVA -jar /home/escnode/esc/EscNode.jar \
-name "$ESC_NAME" \
-configPath "/home/escnode/esc/config.xml" \
-portNoSSL 4010 \
-port 4100 \
-logLevel TRACE \
-typeNode MIXTE \
-site "$ESC_NAME" \
-nbWorker 64
