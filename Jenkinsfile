pipeline {
    agent any

    environment {
        PROJECT_PATH = '/home/server2/projects/stackoverflow-devops-demo'
        IMAGE_HOST = 'local.lan'
        IMAGE_NAME_BACKEND = "${IMAGE_HOST}/stackoverflow_backend:${env.GIT_COMMIT}"
        IMAGE_NAME_WEBSERVER = "${IMAGE_HOST}/stackoverflow_ws:${env.GIT_COMMIT}"
        SSH_CREDENTIALS_ID = 'server2-vm-ssh-credentials'
        REMOTE_USER = 'server2'
        REMOTE_HOST = 'server2.in'
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
                            echo  "DB_DATABASE=${DB_DATABASE}" >> .env
                            echo  "DB_USERNAME=${DB_USERNAME}" >> .env
                            echo  "DB_PASSWORD=${DB_PASSWORD}" >> .env
                            echo  "APP_KEY=${APP_KEY}" >> .env
                            cat .env
                        '''
                    }
                }
            }
        }

        stage ("Build Image") {
            steps {
                script {
                    sh 'docker build -t ${IMAGE_NAME_BACKEND} . -f build/Dockerfile'
                    sh 'docker build -t ${IMAGE_NAME_WEBSERVER} . -f build/Dockerfile-nginx'
                }
            }
        }

        stage ("PHP Unit") {
            steps{
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
            post {
                always {
                    junit skipPublishingChecks: true, testResults: 'build/reports/phpunit.xml'
                }
            }
        }

        stage ("Push Image") {
            steps {
                script {
                    sh 'docker push ${IMAGE_NAME_BACKEND}'
                    sh 'docker push ${IMAGE_NAME_WEBSERVER}'
                    
                }
            }
        }

        stage ("Deploy") {
            steps {
                script {
                    def remoteCommands = """
                        cd ${PROJECT_PATH} && 
                        git fetch origin ${env.GIT_BRANCH} &&
                        docker pull ${IMAGE_NAME_BACKEND} && docker pull ${IMAGE_NAME_WEBSERVER} &&
                        docker compose down -v &&
                        git checkout -f ${env.GIT_COMMIT} &&
                        COMMIT_SHA=${env.GIT_COMMIT} docker compose up -d
                    """

                    sshagent([SSH_CREDENTIALS_ID]) {
                        // Connect to the remote server and run git pull
                        sh """
                            ssh -o StrictHostKeyChecking=no ${REMOTE_USER}@${REMOTE_HOST} '${remoteCommands}'
                        """
                    }
                }
            }
        }

       stage ("Migration") {
        steps {
            script {
                def migrationCommand = "docker exec stackoverflow_backend php artisan migrate --force"
                sshagent([SSH_CREDENTIALS_ID]) {
                    // Connect to the remote server and run git pull
                    sh """
                        ssh -o StrictHostKeyChecking=no ${REMOTE_USER}@${REMOTE_HOST} '${migrationCommand}'
                    """
                }
                
            }
        }
       }
    }

    post {
        cleanup {
            script {
                sshagent([SSH_CREDENTIALS_ID]) {
                    // Connect to the remote server and run git pull
                    sh """
                        ssh -o StrictHostKeyChecking=no ${REMOTE_USER}@${REMOTE_HOST} 'docker image prune -a -f'
                    """
                }
            }
        }
    }
}
