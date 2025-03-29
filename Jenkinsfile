pipeline {
    agent any

    environment {
        IMAGE_HOST = 'local.lan'
        IMAGE_NAME_BACKEND = "${IMAGE_HOST}/stackoverflow_backend:${env.GIT_COMMIT}"
        IMAGE_NAME_WEBSERVER = "${IMAGE_HOST}/stackoverflow_ws:${env.GIT_COMMIT}"
        REMOTE_USER_SERVER1 = 'server1'
        REMOTE_HOST_SERVER1 = 'server1.in'
        REMOTE_PATH_SERVER1 = '/home/server1/projects/stackoverflow-devops-demo'

        REMOTE_USER_SERVER2 = 'server2'
        REMOTE_HOST_SERVER2 = 'server2.in'
        REMOTE_PATH_SERVER2 = '/home/server2/projects/stackoverflow-devops-demo'

        SSH_CREDENTIALS_SERVER1 = 'server1-ssh-credentials'
        SSH_CREDENTIALS_SERVER2 = 'server2-ssh-credentials'
    }

    stages {
        stage ("Checkout") {
            steps {
                checkout scm
            }
        }

        stage ("Generate Environment") {
            steps {
                withCredentials([
                    string(credentialsId: 'DB_DATABASE', variable: 'DB_DATABASE'),
                    string(credentialsId: 'DB_USERNAME', variable: 'DB_USERNAME'),
                    string(credentialsId: 'DB_PASSWORD', variable: 'DB_PASSWORD'),
                    string(credentialsId: 'APP_KEY', variable: 'APP_KEY')
                ]) {
                    script {
                        sh '''
                            cp .env.skeleton .env
                            echo "DB_DATABASE=${DB_DATABASE}" >> .env
                            echo "DB_USERNAME=${DB_USERNAME}" >> .env
                            echo "DB_PASSWORD=${DB_PASSWORD}" >> .env
                            echo "APP_KEY=${APP_KEY}" >> .env
                            cat .env
                        '''
                    }
                }
            }
        }

        stage ("Build Docker Images") {
            steps {
                script {
                    sh 'docker build -t ${IMAGE_NAME_BACKEND} . -f build/Dockerfile'
                    sh 'docker build -t ${IMAGE_NAME_WEBSERVER} . -f build/Dockerfile-nginx'
                }
            }
        }

        stage ("Run Test Cases") {
            steps {
                script {
                    sh """
                        docker create --name ${env.GIT_COMMIT} ${IMAGE_NAME_BACKEND}
                        docker cp ${env.GIT_COMMIT}:/var/www/vendor ./vendor 
                        docker rm ${env.GIT_COMMIT}
                    """
                    sh """
                        docker run --rm \
                        -v ${WORKSPACE}:/var/www \
                        -w /var/www \
                        --entrypoint "" \
                        ${IMAGE_NAME_BACKEND} \
                        ./vendor/bin/phpunit --log-junit build/reports/phpunit.xml
                    """
                }
            }
            post {
                always {
                    junit skipPublishingChecks: true, testResults: 'build/reports/phpunit.xml'
                }
            }
        }



        stage ("Push Docker Images") {
            steps {
                script {
                    sh 'docker push ${IMAGE_NAME_BACKEND}'
                    sh 'docker push ${IMAGE_NAME_WEBSERVER}'
                }
            }
        }

        stage ("Deploy to Appropriate Server") {
            steps {
                script {
                    def remoteHost = (env.BRANCH_NAME == 'staging') ? REMOTE_HOST_SERVER1 : REMOTE_HOST_SERVER2
                    def remoteUser = (env.BRANCH_NAME == 'staging') ? REMOTE_USER_SERVER1 : REMOTE_USER_SERVER2
                    def remotePath = (env.BRANCH_NAME == 'staging') ? REMOTE_PATH_SERVER1 : REMOTE_PATH_SERVER2
                    def sshCredentials = (env.BRANCH_NAME == 'staging') ? SSH_CREDENTIALS_SERVER1 : SSH_CREDENTIALS_SERVER2

                    def deployCommands = """
                        cd ${remotePath} &&
                        git fetch origin ${env.BRANCH_NAME} &&
                        docker pull ${IMAGE_NAME_BACKEND} && docker pull ${IMAGE_NAME_WEBSERVER} &&
                        docker compose down -v &&
                        git checkout -f ${env.GIT_COMMIT} &&
                        COMMIT_SHA=${env.GIT_COMMIT} docker compose up -d
                    """

                    sshagent([sshCredentials]) {
                        sh """
                            ssh -o StrictHostKeyChecking=no ${remoteUser}@${remoteHost} '${deployCommands}'
                        """
                    }
                }
            }
        }

        stage ("Run Migrations on Deployment Server") {
            steps {
                script {
                    def remoteHost = (env.BRANCH_NAME == 'staging') ? REMOTE_HOST_SERVER1 : REMOTE_HOST_SERVER2
                    def remoteUser = (env.BRANCH_NAME == 'staging') ? REMOTE_USER_SERVER1 : REMOTE_USER_SERVER2
                    def sshCredentials = (env.BRANCH_NAME == 'staging') ? SSH_CREDENTIALS_SERVER1 : SSH_CREDENTIALS_SERVER2

                    def migrationCommand = "docker exec stackoverflow_backend php artisan migrate --force"

                    sshagent([sshCredentials]) {
                        sh """
                            ssh -o StrictHostKeyChecking=no ${remoteUser}@${remoteHost} '${migrationCommand}'
                        """
                    }
                }
            }
        }
    }

    post {
        cleanup {
            script {
                def sshCredentials = (env.BRANCH_NAME == 'staging') ? SSH_CREDENTIALS_SERVER1 : SSH_CREDENTIALS_SERVER2
                def remoteHost = (env.BRANCH_NAME == 'staging') ? REMOTE_HOST_SERVER1 : REMOTE_HOST_SERVER2
                def remoteUser = (env.BRANCH_NAME == 'staging') ? REMOTE_USER_SERVER1 : REMOTE_USER_SERVER2

                sshagent([sshCredentials]) {
                    sh """
                        ssh -o StrictHostKeyChecking=no ${remoteUser}@${remoteHost} 'docker image prune -a -f'
                    """
                }
            }
        }
    }
}
