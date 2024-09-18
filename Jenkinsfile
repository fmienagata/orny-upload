pipeline {
    agent any

    environment {
        BASEDIR = "${env.WORKSPACE}"
        VERSION = "${env.BUILD_DISPLAY_NAME}"
        VERSION_MAJOR = "${env.API_VERSION_MAJOR}"
        VERSION_MINOR = "${env.API_VERSION_MINOR}"
        VERSION_PATCH = "${env.API_VERSION_PATCH}"
        BUILD = "${env.BUILD_NUMBER}"
        PHP_PATH = "/opt/remi/php82/root/bin/php"
        RECIPIENTS = 'charles.bezo@docaposte.fr'
    }

    parameters {
        string(name: 'VERSION', defaultValue: '1.0.0', description: 'Version number')
        string(name: 'MINOR_VERSION', defaultValue: '0', description: 'Minor version')
        string(name: 'PATCH_VERSION', defaultValue: '0', description: 'Patch version')
        string(name: 'BUILD_VERSION', defaultValue: '10', description: 'Build version')
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

        stage('Parse Version') {
            steps {
                script {
                    IFS = '.'
                    def versionParts = "${params.VERSION}".split('-')[-1].split('\\.')
                    UPLOAD_VERSION_MAJOR = versionParts[0]
                    UPLOAD_VERSION_MINOR = versionParts.length > 1 ? versionParts[1] : "0"
                    UPLOAD_VERSION_PATCH = versionParts.length > 2 ? versionParts[2] : "0"
                    UPLOAD_VERSION_BUILD = "${params.BUILD_VERSION}"

                    echo "Major: ${UPLOAD_VERSION_MAJOR}, Minor: ${UPLOAD_VERSION_MINOR}, Patch: ${UPLOAD_VERSION_PATCH}, Build: ${UPLOAD_VERSION_BUILD}"

                    // Default to current workspace if not set
                    if (env.WORKSPACE == null || env.WORKSPACE == '') {
                        env.WORKSPACE_DIR = "."
                    }
                }
            }
        }

        stage('Update CHANGELOG.md') {
            steps {
                script {
                    sh '''
                    i=1
                    while IFS= read -r line; do
                        sed -i "s/VERSION_MAJOR/${UPLOAD_VERSION_MAJOR}/g" ${BASEDIR}/CHANGELOG.md
                        sed -i "s/VERSION_MINOR/${UPLOAD_VERSION_MINOR}/g" ${BASEDIR}/CHANGELOG.md
                        sed -i "s/VERSION_PATCH/${UPLOAD_VERSION_PATCH}/g" ${BASEDIR}/CHANGELOG.md
                        sed -i "s/VERSION_BUILD/${UPLOAD_VERSION_BUILD}/g" ${BASEDIR}/CHANGELOG.md
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
                        sed -i "s/upload.version.number: 0.1.0/upload.version.number: ${UPLOAD_VERSION_MAJOR}.${UPLOAD_VERSION_MINOR}.${UPLOAD_VERSION_PATCH}.${UPLOAD_VERSION_BUILD}/g" ${BASEDIR}/config/packages/framework.yaml
                        sed -i "s/upload.version.major: 0/upload.version.major: ${UPLOAD_VERSION_MAJOR}/g" ${BASEDIR}/config/packages/framework.yaml
                        sed -i "s/upload.version.minor: 0/upload.version.minor: ${UPLOAD_VERSION_MINOR}/g" ${BASEDIR}/config/packages/framework.yaml
                        sed -i "s/upload.version.patch: 0/upload.version.patch: ${UPLOAD_VERSION_PATCH}/g" ${BASEDIR}/config/packages/framework.yaml
                        sed -i "s/upload.version.build: 10/upload.version.build: ${UPLOAD_VERSION_BUILD}/g" ${BASEDIR}/config/packages/framework.yaml
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
                        sed -i "s/version: '1.0.0'/version: '${UPLOAD_VERSION_MAJOR}.${UPLOAD_VERSION_MINOR}.${UPLOAD_VERSION_PATCH}.${UPLOAD_VERSION_BUILD}'/g" ${BASEDIR}/config/packages/api_platform.yaml
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
                    -zcvf build/packages/${VERSION}.api.tar.gz .
                '''
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
