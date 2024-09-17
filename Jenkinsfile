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

        stage('Build Version') {
            steps {
                echo 'Building version...'
                sh '''
                ${BASEDIR}/bin/build.sh -v ${VERSION_MAJOR}.${VERSION_MINOR}.${VERSION_PATCH} -b ${BUILD}
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
