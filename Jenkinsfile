#!groovy
pipeline {
    agent any
    options {
            timeout(time: 1, unit: 'HOURS')
    }
    triggers { pollSCM ('* * * * *') }
    stages {
        stage('Setup') {
            steps {
                sh '''
                    pwd && \
                    whoami && \
                    echo $WORKSPACE && \
                    ls -la
                '''
            }
            post {
                always {
                    step([$class: 'WsCleanup'])
                }
            }
        }
        stage('Browser Tests setup') {
            environment {
                CLOUD_NAME= 'redcomponent'
                API_KEY= '365447364384436'
                API_SECRET='Q94UM5kjZkZIrau8MIL93m0dN6U'
                GITHUB_TOKEN='4d92f9e8be0eddc0e54445ff45bf1ca5a846b609'
                ORGANIZATION='redCOMPONENT-COM'
                REPO='redSHOP'
            }
            parallel {
                stage('Test Stage A') {
                    agent {
                        docker {
                            image 'joomlaprojects/docker-systemtests'
                            args  '--user 0 --privileged=true -v /tmp:/tmp'
                        }
                    }
                    steps {
                        sh 'bash build/jenkins/system-tests.sh'
                    }
                }
                stage('Test Stage B') {
                    agent {
                        docker {
                            image 'joomlaprojects/docker-systemtests'
                            args  '--user 0 --privileged=true'
                        }
                    }
                    steps {
                        sh 'bash build/jenkins/system-tests.sh'
                    }
                }
            }
            post {
                always {
                    step([$class: 'WsCleanup'])
                }
            }
        }
    }
}
