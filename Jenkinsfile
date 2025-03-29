pipeline {
    agent any

    environment {
        PROJECT_PATH_MAIN = '/home/server2/projects/stackoverflow-devops-demo'
        PROJECT_PATH_STAGING = '/home/server1/projects/stackoverflow-devops-demo'

        IMAGE_HOST = 'local.lan'
        IMAGE_NAME_BACKEND = "${IMAGE_HOST}/stackoverflow_backend:${env.GIT_COMMIT}"
        IMAGE_NAME_WEBSERVER = "${IMAGE_HOST}/stackoverflow_ws:${env.GIT_COMMIT}"

        SSH_CREDENTIALS_ID_MAIN = 'server2-vm-ssh-credentials'
        REMOTE_USER_MAIN = 'server2'
        REMOTE_HOST_MAIN = 'server2.in'
        
        SSH_CREDENTIALS_ID_STAGING = 'server1-vm-ssh-credentials'
        REMOTE_USER_STAGING = 'server1'
        REMOTE_HOST_STAGING = 'server1.in'
    }

    stages {
        stage ("Checkoutscm") {
            steps {
                checkout scm
            }
        }
        stage ("Generate Environmnt") {
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
        stage ("Build Dockerfile") {
            steps {
                script {
                    sh "docker build -t ${IMAGE_NAME_BACKEND} . -f build/Dockerfile"
                    sh "docker build -t ${IMAGE_NAME_WEBSERVER} . -f build/Dockerfile-nginx"
                }
            }
        }
        stage ("PHP Unit") {
            steps {
                sh"""
                    docker create --name ${env.GIT_COMMIT} ${IMAGE_NAME_BACKEND}
                    docker cp ${env.GIT_COMMIT}:/var/www/vendor ./vendor 
                    docker rm ${env.GIT_COMMIT}
                """
                sh"""
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
        stage ("Deploying for server 1 and 2") {
            steps {
                script {
        	    def projectPath = (env.GIT_BRANCH == 'staging') ? '/home/server1/projects/stackoverflow-devops-demo' : '/home/server2/projects/stackoverflow-devops-demo'

                    def remoteCommands = """
                        cd ${projectPath} &&
                        git fetch origin ${env.GIT_BRANCH} &&
                        docker pull ${IMAGE_NAME_BACKEND} && docker pull ${IMAGE_NAME_WEBSERVER} &&
                        docker compose down -v &&
                        git checkout -f ${env.GIT_COMMIT} &&
                        COMMIT_SHA=${env.GIT_COMMIT} docker compose up -d
                    """
                    
                    def sshCredentialsId = (env.GIT_BRANCH == 'staging') ? SSH_CREDENTIALS_ID_STAGING : SSH_CREDENTIALS_ID_MAIN
                    def remoteUser = (env.GIT_BRANCH == 'staging') ? REMOTE_USER_STAGING : REMOTE_USER_MAIN
                    def remoteHost = (env.GIT_BRANCH == 'staging') ? REMOTE_HOST_STAGING : REMOTE_HOST_MAIN


                    sshagent([sshCredentialsId]) {
                        sh """
                            ssh -o StrictHostKeyChecking=no ${remoteUser}@${remoteHost} "${remoteCommands}"
                        """
                    }
                }
            }
        }
        stage ("Migration") {
            steps {
                script {
                    def migrationCommand = "docker exec stackoverflow_backend php artisan migrate --force"
                    if (env.GIT_BRANCH == 'staging') {
                        sshagent([SSH_CREDENTIALS_ID_STAGING]) {
                            sh """
                                ssh -o StrictHostKeyChecking=no ${REMOTE_USER_STAGING}@${REMOTE_HOST_STAGING} '${migrationCommand}'
                            """
                        }
                    } else if (env.GIT_BRANCH == 'main') {
                        sshagent([SSH_CREDENTIALS_ID_MAIN]) {
                            sh """
                                ssh -o StrictHostKeyChecking=no ${REMOTE_USER_MAIN}@${REMOTE_HOST_MAIN} '${migrationCommand}'
                            """
                        }
                    }
                }
            }
        }
        stage("Trigger Automation") {
            when {
                branch 'staging'
            }
            steps {
                build job: "stackoverflow-devops-automation", wait: true
            }
        }
    }

    post {
        cleanup {
        	script {
                if (env.GIT_BRANCH == 'staging') {
                    sshagent([SSH_CREDENTIALS_ID_STAGING]) {
                    sh """
                        ssh -o StrictHostKeyChecking=no ${REMOTE_USER_STAGING}@${REMOTE_HOST_STAGING} 'docker image prune -a -f'
                    """
                    }
                } else if (env.GIT_BRANCH == 'main') {
                    sshagent([SSH_CREDENTIALS_ID_MAIN]) {
                    sh """
                        ssh -o StrictHostKeyChecking=no ${REMOTE_USER_MAIN}@${REMOTE_HOST_MAIN} 'docker image prune -a -f'
                    """
                    }
                }
            }
        }
    }
}