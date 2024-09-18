pipeline {
    agent any

    environment {
        BASEDIR = "${env.WORKSPACE}"
        VERSION_MAJOR = "${env.API_VERSION_MAJOR}"
        VERSION_MINOR = "${env.API_VERSION_MINOR}"
        VERSION_PATCH = "${env.API_VERSION_PATCH}"
        BUILD = "${env.BUILD_NUMBER}"
        TAG = "${params.TAGGING}"
        NEXUS_PUSH = "${params.NEXUS_PUSH}"
        PHP_PATH = "/opt/remi/php82/root/bin/php"
        RECIPIENTS = 'charles.bezo@docaposte.fr'
        SERVER_IP = "172.29.95.15"
        REMOTE_USER = "www-data"
        VERSION = "${env.API_VERSION_MAJOR}.${env.API_VERSION_MINOR}.${env.API_VERSION_PATCH}#${env.BUILD_NUMBER}"  // Pass the version as a parameter to Jenkins
        VERSION_TAR = "${env.API_VERSION_MAJOR}.${env.API_VERSION_MINOR}.${env.API_VERSION_PATCH}_${env.BUILD_NUMBER}"
        VERSION_TAG = "${env.API_VERSION_MAJOR}.${env.API_VERSION_MINOR}.${env.API_VERSION_PATCH}"
        DIR = "/data/sites"  // Define the directory on the remote server
        API_REPO_URL = "https://outils.docapost.tech/gitlab/groupe-pac-p-le-applications-clients/adp/bvrh/backend/bvrh6.2.git"
    }

    parameters {
        booleanParam(name: 'TAGGING', defaultValue: false, description: 'Build must be tagged or not ?')
        booleanParam(name: 'NEXUS_PUSH', defaultValue: false, description: 'Build must be tagged or not ?')
    }

    stages {
        stage('Clean') {
            steps {
                echo 'Cleaning the workspace...'
                sh '''
                rm -rf ${BASEDIR}/build/logs
                find . -name "*.log" -exec rm -f {} +
                find . -name "*.tar.gz" -exec rm -f {} +
                '''
            }
        }

        stage('Prepare') {
            steps {
                echo 'Preparing directories...'
                sh '''
                mkdir -p ${BASEDIR}/build/logs
                mkdir -p ${BASEDIR}/build/packages
                '''
            }
        }

        stage('Update CHANGELOG.md') {
            steps {
                script {
                    sh '''
                    i=1
                    while IFS= read -r line; do
                        sed -i "s/VERSION_MAJOR/${VERSION_MAJOR}/g" ${BASEDIR}/CHANGELOG.md
                        sed -i "s/VERSION_MINOR/${VERSION_MINOR}/g" ${BASEDIR}/CHANGELOG.md
                        sed -i "s/VERSION_PATCH/${VERSION_PATCH}/g" ${BASEDIR}/CHANGELOG.md
                        sed -i "s/VERSION_BUILD/${BUILD}/g" ${BASEDIR}/CHANGELOG.md
                        ((i++))
                        if [[ $i -eq 10 ]]; then break; fi
                    done < ${BASEDIR}/CHANGELOG.md
                    '''
                }
            }
        }

        stage('Check CHANGELOG.md Changes') {
            steps {
                sh '''
                l=1
                while IFS= read -r line; do
                    echo "$line"
                    ((l++))
                    if [[ $l -eq 10 ]]; then break; fi
                done < ${BASEDIR}/CHANGELOG.md
                '''
            }
        }

        stage('Update framework.yaml') {
            steps {
                script {
                    sh '''
                    i=1
                    while IFS= read -r line; do
                        sed -i "s/upload.version.number: 0.1.0/upload.version.number: ${VERSION}/g" ${BASEDIR}/config/packages/framework.yaml
                        sed -i "s/upload.version.major: 0/upload.version.major: ${VERSION_MAJOR}/g" ${BASEDIR}/config/packages/framework.yaml
                        sed -i "s/upload.version.minor: 0/upload.version.minor: ${VERSION_MINOR}/g" ${BASEDIR}/config/packages/framework.yaml
                        sed -i "s/upload.version.patch: 0/upload.version.patch: ${VERSION_PATCH}/g" ${BASEDIR}/config/packages/framework.yaml
                        sed -i "s/upload.version.build: 10/upload.version.build: ${BUILD}/g" ${BASEDIR}/config/packages/framework.yaml
                        ((i++))
                        if [[ $i -eq 22 ]]; then break; fi
                    done < ${BASEDIR}/config/packages/framework.yaml
                    '''
                }
            }
        }

        stage('Check framework.yaml Changes') {
            steps {
                sh '''
                l=1
                while IFS= read -r line; do
                    echo "$line"
                    ((l++))
                    if [[ $l -eq 22 ]]; then break; fi
                done < ${BASEDIR}/config/packages/framework.yaml
                '''
            }
        }

        stage('Update api_platform.yaml') {
            steps {
                script {
                    sh '''
                    i=1
                    while IFS= read -r line; do
                        sed -i "s/version: '1.0.0'/version: '${VERSION}'/g" ${BASEDIR}/config/packages/api_platform.yaml
                        ((i++))
                        if [[ $i -eq 20 ]]; then break; fi
                    done < ${BASEDIR}/config/packages/api_platform.yaml
                    '''
                }
            }
        }

        stage('Check api_platform.yaml Changes') {
            steps {
                sh '''
                l=1
                while IFS= read -r line; do
                    echo "$line"
                    ((l++))
                    if [[ $l -eq 20 ]]; then break; fi
                done < ${BASEDIR}/config/packages/api_platform.yaml
                '''
            }
        }

        stage('Install Vendors') {
            steps {
                echo 'Installing vendors...'
                sh '''
                ${PHP_PATH} -d memory_limit=-1 ${BASEDIR}/composer.phar update --no-plugins --no-scripts --optimize-autoloader --classmap-authoritative
                '''
            }
        }

        stage('Prepare Environment') {
            steps {
                echo 'Preparing environment...'
                sh '''
                ${PHP_PATH} ${BASEDIR}/composer.phar dump-env dev
                ${PHP_PATH} ${BASEDIR}/composer.phar dump-autoload --optimize --classmap-authoritative
                ${PHP_PATH} ${BASEDIR}/bin/console lexik:jwt:generate-keypair --overwrite
                '''
            }
        }

        stage('Package Application') {
            steps {
                echo 'Packaging application...'
                sh '''
                tar --exclude=.git \
                    --exclude=.idea \
                    --exclude=var/cache \
                    --exclude=var/log \
                    --exclude=build \
                    --exclude=build.xml \
                    --exclude=build.deployment.xml \
                    --exclude=backup.api.tar.gz \
                    --exclude=backup.${BUILD}.api.tar.gz \
                    --exclude=${BUILD}.api.tar.gz \
                    --exclude=tests \
                    --exclude=*.tar.gz \
                    --exclude=Jenkinsfile \
                    --exclude=composer* \
                    --exclude=codeception* \
                    --exclude=.php-cs-fixer* \
                    --exclude=.env.* \
                    --exclude=phpunit.xml.dist \
                    -zcvf ${BASEDIR}/build/packages/upload-${VERSION_TAR}.tar.gz .
                '''
            }
        }

        stage('Copy Files to Remote Server') {
            steps {
                script {
                    echo 'Copying files to remote server...'
                    sh '''
                    rsync -e ssh -avz --delete --exclude='build' --exclude='var' --exclude='tests' \
                    --exclude='.env.test' --exclude='phpunit.xml.dist' --exclude='codeception.yml' \
                    --exclude='Jenkinsfile' --exclude='.php-cs-fixer.dist.php' --exclude='.git' \
                    --exclude='build.deployment.dev.xml' --exclude='build.xml' --exclude='.env.local.php' \
                    --exclude='.gitignore' --exclude='bin/PAC.Coffre.1.1.0' \
                    ${BASEDIR}/. ${REMOTE_USER}@${SERVER_IP}:${DIR}/upload-${VERSION}
                    '''
                }
            }
        }

        stage('Prepare Directories on Remote Server') {
            steps {
                script {
                    echo 'Preparing directories on remote server...'
                    sh '''
                    ssh ${REMOTE_USER}@${SERVER_IP} "mkdir -p ${DIR}/upload-${VERSION}/var/cache"
                    ssh ${REMOTE_USER}@${SERVER_IP} "mkdir -p ${DIR}/upload-${VERSION}/var/log"
                    '''
                }
            }
        }

        stage('Set Permissions') {
            steps {
                script {
                    echo 'Setting permissions on remote server...'
                    sh '''
                    ssh ${REMOTE_USER}@${SERVER_IP} "chmod -R 755 ${DIR}/upload-${VERSION}/vendor"
                    ssh ${REMOTE_USER}@${SERVER_IP} "chmod -R 775 ${DIR}/upload-${VERSION}/var"
                    '''
                }
            }
        }

        stage('Run Composer Autoload') {
            steps {
                script {
                    echo 'Running Composer dump-autoload on remote server...'
                    sh '''
                    ssh ${REMOTE_USER}@${SERVER_IP} "cd ${DIR}/upload-${VERSION} && composer dump-autoload --optimize --classmap-authoritative"
                    '''
                }
            }
        }

        stage('Copy .env.local.php') {
            steps {
                script {
                    echo 'Copying .env.local.php to the new release...'
                    sh '''
                    ssh ${REMOTE_USER}@${SERVER_IP} "cp -f ${DIR}/api/.env.local.php ${DIR}/upload-${VERSION}/.env.local.php"
                    '''
                }
            }
        }

        stage('Copy logo-header.svg') {
            steps {
                script {
                    echo 'Copying logo-header.svg to the new release...'
                    sh '''
                    ssh ${REMOTE_USER}@${SERVER_IP} "cp -f ${DIR}/api/templates/icon/ ${DIR}/upload-${VERSION}/.env.local.php"
                    '''
                }
            }
        }

        stage('Update Symlink') {
            steps {
                script {
                    echo 'Updating symbolic link to the new release...'
                    sh '''
                    ssh ${REMOTE_USER}@${SERVER_IP} "ln -sfn ${DIR}/upload-${VERSION} ${DIR}/api"
                    '''
                }
            }
        }

        stage('Flush Redis Cache') {
            steps {
                script {
                    echo 'Flushing Redis cache on the remote server...'
                    sh '''
                    ssh ${REMOTE_USER}@${SERVER_IP} "redis-cli flushall"
                    '''
                }
            }
        }

        stage('Tagging Git API') {
            when {
                expression {
                    return env.MY_ENV_VAR == 'true'
                }
            }
            steps {
                script {
                    withCredentials([gitUsernamePassword(credentialsId: 'GITLAB_GROUP_TOKEN', gitToolName: 'Git')]) {
                        sh """
                            cd ${BASEDIR}
                            rm -rf .git
                            git init
                            git remote add origin ${API_REPO_URL}
                            git fetch
                            git reset --hard origin/master
                            git reset --hard origin/develop
                            git fetch --tags
                            if [ -z \$(git tag | grep ${VERSION_TAG}) ]; then
                                git tag ${VERSION_TAG}
                                git push origin --tags
                            fi
                            git push origin master
                        """
                    }
                }
            }
        }

        stage('Copie et Archivage du livrable dans le repo nexus') {
            when {
                expression {
                    return env.NEXUS_PUSH == 'true'
                }
            }
            steps {
                script {
                    sh """
                        ls -l ${BASEDIR}/build/packages/
                    """
                }
            }
        }
    }

    post {
        always {
            echo 'Cleaning up after the build...'
        }
        success {
            echo 'Build succeeded!'
        }
        failure {
            echo 'Build failed!'
            emailext (
                to: "${env.RECIPIENTS}",
                subject: "Échec du build Jenkins ${currentBuild.fullDisplayName}",
                body: """
                <p>Bonjour,</p>
                <p>Le build <strong>${currentBuild.fullDisplayName}</strong> a échoué.</p>
                <p>Voir les détails du build à l'URL suivante : <a href="${env.BUILD_URL}">${env.BUILD_URL}</a></p>
                <p>Cordialement,<br>Votre serveur Jenkins</p>
                """,
                mimeType: 'text/html'
            )
        }
    }
}
