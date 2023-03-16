#!/usr/bin/env bash

#!/usr/bin/env bash
cd "${WORKSPACE}"

DIR=/data/sites
version="1.0.0.${BUILD_NUMBER}"
buildToDelete="$(($BUILD_NUMBER-30))"
previuosBuild="$(($BUILD_NUMBER-1))"
versionToDelete="1.0.0.${buildToDelete}"
previousVersion="1.0.0.${previuosBuild}"

VERSION_DATE=`date +%d%m%Y`


# Copie des fichiers vers le serveur web 172.29.95.125
rsync -e ssh -avz --delete . www-data@172.29.95.125:"$DIR/$version"


ssh www-data@172.29.95.15 "mkdir -p $DIR/$version/var/cache;"
ssh www-data@172.29.95.15 "mkdir -p $DIR/$version/var/log"
ssh www-data@172.29.95.15 "chmod -R 755 $DIR/$version/vendor"
ssh www-data@172.29.95.15 "chmod -R 777 $DIR/$version/var"
ssh www-data@172.29.95.15 "cd $DIR/$version && composer dump-autoload --optimize --classmap-authoritative"
ssh www-data@172.29.95.15 "mv $DIR/$previousVersion $DIR/$previousVersion.disabled"
ssh www-data@172.29.95.15 "cp -f $DIR/api/.env.local.php $DIR/$version/.env.local.php"
ssh www-data@172.29.95.15 "rm -f $DIR/api"
ssh www-data@172.29.95.15 "ln -sf $DIR/$version $DIR/api"
ssh www-data@172.29.95.15 "rm -rf $DIR/$versionToDelete.disabled"
