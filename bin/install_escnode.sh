#!/bin/bash

ESC_NAME="ESCNODE_410"
ESC_VERSION="4.1.0"
MYSQL_HOST="localhost"
MYSQL_PORT="3306"
ESC_MYSQL_USER="escClient"
ESC_MYSQL_PASSWORD="escClient1234"
SSL_CERTIFICAT_SYSTEM_PEM=$(</home/escnode/generationsCertificats/finalSystem/sslSysteme.pem)

#JAVA_BIN="/usr/lib/jvm/java-1.8.0-openjdk-amd64/jre/bin/java"
JAVA_BIN="/usr/lib/jvm/jdk1.7.0_80/jre/bin/java"

set -o errexit
set -o nounset
maxcounter=45
counter=1
while ! mysql --host="localhost" -u "$ESC_MYSQL_USER" -p"$ESC_MYSQL_PASSWORD" --port="3306" -e "show databases;" > /dev/null 2>&1; do
    sleep 1
    counter=`expr $counter + 1`
    if [ $counter -gt $maxcounter ]; then
        >&2 echo "We have been waiting for MySQL too long already; failing."
        exit 1
    fi;
done

#echo "$SSL_CERTIFICAT_SYSTEM_PEM"

#Set variables
i=1

#Read in template one line at the time, and replace variables (more
#natural (and efficient) way, thanks to Jonathan Leffler).
while IFS= read -r line
do
    #sed -i '/ESC_MYSQL_PASSWORD(.*)$/'"$ESC_MYSQL_PASSWORD"'(.*)$/' "/home/escnode/esc/config.xml"
    #sed -i '/ESC_MYSQL_USER(.*)$/'"$ESC_MYSQL_USER"'(.*)$/' "/home/escnode/esc/config.xml"
    #sed -i '/ESC_MYSQL_PORT(.*)$/'"$MYSQL_PORT"'(.*)$/' "/home/escnode/esc/config.xml"
    #sed -i '/ESC_MYSQL_HOST(.*)$/'"$MYSQL_HOST"'(.*)$/' "/home/escnode/esc/config.xml"
    #sed -i '/ESC_NODE_NAME(.*)$/'"$ESC_NAME"'(.*)$/' "/home/escnode/esc/config.xml"
    #sed -i '/SSL_CERTIFICAT_SYSTEM_PEM(.*)$/'"$SSL_CERTIFICAT_SYSTEM_PEM"'(.*)$/' "/home/escnode/esc/config.xml"
    ((i++))
    if [[ $i -eq 15 ]]; then
        break
    fi
done < "/home/escnode/esc/config.xml"

cat /home/escnode/esc/config.xml

# Create standard user and grant permissions
if [ $( echo "SHOW DATABASES LIKE '"$ESC_NAME"';" | mysql -N --host="$MYSQL_HOST" -u "$ESC_MYSQL_USER" -p"$ESC_MYSQL_PASSWORD" --port="$MYSQL_PORT" | wc -l ) -eq 0 ]; then
    java -jar /home/escnode/esc/EscNode.jar -install "$ESC_NAME" -name "$ESC_NAME" -site "DOCKER" -configPath "/home/escnode/esc/config.xml" -superUser "/home/escnode/generationsCertificats/finalSystem/userSystem.pem" -certificatSignature "/home/escnode/generationsCertificats/finalSystem/certificateSigning.pem" -certificatTimeStamp "/home/escnode/generationsCertificats/finalSystem/timestampSystem.pem" -mountPoint "/data/esc" -debugMode
fi

java -jar /home/escnode/esc/EscNode.jar -name "$ESC_NAME" -configPath "/home/escnode/esc/config.xml" -portNoSSL 10442 -port 10443 -logLevel TRACE -typeNode MIXTE -site DOCKER -nbWorker 64
