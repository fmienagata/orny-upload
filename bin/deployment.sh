#!/usr/bin/env bash

#!/usr/bin/env bash
cd "${WORKSPACE}"

# Copie des fichiers vers le serveur web 172.29.95.125
rsync -e ssh -avz --delete --exclude 'build/packages' --exclude 'var' . www-data@172.29.95.15:"$DIR/upload-${version}"

ssh www-data@172.29.95.15 "mkdir -p $DIR/upload-${version}/var/cache;"
ssh www-data@172.29.95.15 "mkdir -p $DIR/upload-${version}/var/log"
ssh www-data@172.29.95.15 "chmod -R 755 $DIR/upload-${version}/vendor"
ssh www-data@172.29.95.15 "chmod -R 775 $DIR/upload-${version}/var"
ssh www-data@172.29.95.15 "cd $DIR/upload-${version} && composer dump-autoload --optimize --classmap-authoritative"
ssh www-data@172.29.95.15 "cp -f $DIR/api/.env.local.php $DIR/upload-${version}/.env.local.php"
ssh www-data@172.29.95.15 "ln -sfn $DIR/$version $DIR/api"
ssh www-data@172.29.95.15 "redis-cli flushall"

