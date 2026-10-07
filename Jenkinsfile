pipeline{

    agent {
        label 'slave01'
    }

    environment{
        registryProjet = 'registry.pulse.mg/dev/bondy_back'
        app_name_tag = 'bondy_back-0.1'
        commitIdLong = ''
        commitID = ''
        image = ''
        tag = ''
        dockerImage = ''
        PROJECT_NAME = sh(script: 'basename -s .git ${GIT_URL}', returnStdout: true).trim()
        RELEASE_VERSION = "${env.GIT_COMMIT}"
        APP_SERVICE_NAME = 'bondy-back'
    }

    parameters {
        string(name: 'BRANCH_TO_BUILD', defaultValue: 'change-me-to-valid-branch', description: 'Branch to build')
        string(name: 'MAIL_RECEIVERS', defaultValue: 'yvon.benahita@pulse.mg,onyandrianina.ravelomanantsoa@pulse.mg', description: 'Branch to build')
        string(name: 'MAIL_SENDER', defaultValue: 'jenkins.pulse@moov.mg', description: 'Branch to build')
    }

    stages{

        // stage('Manual Checkout') {
        //     parallel {
        //         stage('OWASP') {
        //             when {
        //                 expression { params.BRANCH_TO_BUILD != 'change-me-to-valid-branch' }
        //             }
        //             stages {
        //                 stage('Checkout') {
            
        //                     steps {
        //                         git branch: params.BRANCH_TO_BUILD, url: 'https://gitlab.pulse.mg/aits/bondy-plot-tracking-back', credentialsId: 'gitlab01'
        //                         echo "Building branch: ${params.BRANCH_TO_BUILD}"
        //                     }
        //                 }
        //             }
        //         }
        //     }
        // }

         /////////////////// BUILD APP /////////////////
        stage('Build app'){
            steps{
                script{
                    image = "${registryProjet}:${app_name_tag}"
                    tag = "${app_name_tag}"
                    
                    // env.BRANCH_NAME si null when manual build else
                    sh "echo '======================='"
                    sh "echo 'BRANCH_NAME: ${env.BRANCH_NAME}'"
                    def branch = env.BRANCH_NAME ?: params.BRANCH_TO_BUILD
                    def targetStage = (branch == 'ENV-PULSE-RECETTE') ? 'production' : 'test'

                    docker.withRegistry('https://registry.pulse.mg', 'harbor'){
                            dockerImage = docker.build("$image", "--target ${targetStage} .")
                    }
                }
            }
        }

        stage('Sonar & OWASP') {
            parallel {
                stage('OWASP') {
                    when { branch pattern: "releases/release-sp\\d+|trunk|features/feat-jenkins", comparator: "REGEXP" }
                    stages {
                         ///////////////////OWASP DEPENDENCY CHECK ////////
                        stage('Dependency Check') {
                            steps {
                                script {
                                    dependencyCheck (
                                        odcInstallation: "DP-check",
                                        additionalArguments: "--format XML --scan ${WORKSPACE}"
                                    )
                                }
                            }
                        }
                        ///////////////////OWASP DEPENDENCY PUBLISHER /////
                        stage('Dependency Check Publisher') {
                            steps {
                                script {
                                    step([$class: 'DependencyCheckPublisher', pattern: "**/dependency-check-report.xml", totalThresholdAnalysisExploitable: true, newThresholdAnalysisExploitable: true])
                                }
                            }
                        }
                    }
                }
                stage("Tests & Sonar") {
                    when { branch pattern: "releases/release-sp\\d+|trunk|features/feat-jenkins", comparator: "REGEXP" }
                    stages {
                        stage("Test") {
                            steps {
                                script {
                                    catchError(buildResult: 'SUCCESS', stageResult: 'FAILURE') {
                                        sh "docker-compose -f docker-compose-tu.yaml down --rmi all --remove-orphans -v"
                                        sh "docker-compose -f docker-compose-tu.yaml up -d --build --force-recreate --remove-orphans"
                                        sh "docker exec -i -u root ${env.APP_SERVICE_NAME} composer require --dev allure-framework/allure-phpunit --no-scripts"
                                        sh "docker-compose -f docker-compose-tu.yaml ps"
                                        sh "docker exec -i ${env.APP_SERVICE_NAME} ls -ahl"
                                        sh "docker exec -i -u root ${env.APP_SERVICE_NAME} composer dump-autoload"
                                        sh "docker exec -i -u root ${env.APP_SERVICE_NAME} php bin/console --env test cache:clear"
                                        sh "docker exec -i -u root ${env.APP_SERVICE_NAME} php bin/console --env test doctrine:schema:update --force"
                                        sh "docker exec -i -u root ${env.APP_SERVICE_NAME} php bin/console --env test doctrine:fixtures:load -n"
                                        sh """
                                            docker-compose exec -u root -T ${env.APP_SERVICE_NAME} bash -c "printf 'testuser@example.com\\nmypassword\\nmypassword\\n' | php bin/console app:create-administrator"
                                        """
                                        sh "docker exec -i -u root ${env.APP_SERVICE_NAME} php bin/phpunit tests --log-junit build/test-results/junit.xml --coverage-clover coverage.xml"
                                    }
                                }
                            }

                            post {
                                always {
                                    script {
                                        catchError(buildResult: 'SUCCESS', stageResult: 'FAILURE') {
                                            sh "docker cp ${env.APP_SERVICE_NAME}:/var/www/html/build ."
                                            sh "docker cp ${env.APP_SERVICE_NAME}:/var/www/html/coverage.xml ."
                                            junit(testResults: 'build/test-results/**/*.xml', allowEmptyResults: true)
                                            archiveArtifacts artifacts: 'build/test-results/**/*.xml', fingerprint: true
                                            allure includeProperties: false, jdk: '', results: [[path: 'build/allure-results']]
                                            sh "docker-compose -f docker-compose-tu.yaml down --rmi all --remove-orphans -v"
                                        }
                                    }
                                }

                                failure {
                                    script {
                                        currentBuild.result = 'UNSTABLE'
                                    }
                                }
                            }
                        }


                         /////////////////// SONAR SCANNER /////////////////
                        stage('Review Sonar') {
                            steps {
                                withSonarQubeEnv('sonar-devops') {
                                    // set the analysis mode to incremental
                                    script {
                                        sh "sed -i 's|/var/www/html/||g' coverage.xml"
                                        def scannerHome = tool 'Sonar_Scanner'
                                        sh """
                                            ${scannerHome}/bin/sonar-scanner -X \
                                                -Dsonar.mode=incremental \
                                                -Dsonar.php.tests.reportPath=build/test-results/junit.xml \
                                                -Dsonar.php.coverage.reportPaths=./coverage.xml \
                                                -Dsonar.qualitygate.wait=true \
                                                -Dsonar.projectKey=${PROJECT_NAME} \
                                                -Dsonar.projectVersion=${RELEASE_VERSION} \
                                                -Dsonar.sources=src || true
                                        """
                                    }                    
                                }
                            }
                        }

                    }
                }
            }
        }
        
        stage('Deploy Image'){
            when { branch 'ENV-PULSE-RECETTE' }
            steps{
                script{
                    docker.withRegistry('https://registry.pulse.mg', 'harbor'){
                        dockerImage.push()
                    }
                }
            }
        }
        stage('Deploy App'){
            when { branch 'ENV-PULSE-RECETTE' }
            steps{
                 script{
                     git branch: 'ENV-PULSE-RECETTE', credentialsId: 'gitlab01', url: 'https://gitlab.pulse.mg/pipeline-devops/bondy-plot-tracking-back/playbook.git'
                     sh "ansible-galaxy install --roles-path roles -c -r requirements.yml"
                     ansiblePlaybook(
                         colorized: true,
                         become: true,
                         credentialsId: 'ansible-80',
                         playbook:'install-app.yml',
                         inventory: "env/recette/hosts",
                         extras: "-e ' image=${dockerImage}' -e 'tag=${tag}' -e 'reg=${registryProjet}' -e 'apptag=${app_name_tag}'"
                     )
                }
             }
       }

    }
    post{
        always{
             cleanWs()
        }

        success{
            emailext (
                from: "${params.MAIL_SENDER}",
                to: "${params.MAIL_RECEIVERS}",
                subject: "SUCCESSFUL: Job '${env.JOB_NAME} [${env.BUILD_NUMBER}]'",
                body: """<p>SUCCESSFUL: Job '${env.JOB_NAME} [${env.BUILD_NUMBER}]':</p>
                    <p>Check console output at &QUOT;<a href='${env.BUILD_URL}'>${env.JOB_NAME} [${env.BUILD_NUMBER}]</a>&QUOT;</p>""",
                recipientProviders: [[$class: 'DevelopersRecipientProvider']]
             )
        }

        failure{
            emailext (
                from: "${params.MAIL_SENDER}",
                to: "${params.MAIL_RECEIVERS}",
                subject: "FAILED: Job '${env.JOB_NAME} [${env.BUILD_NUMBER}]'",
                body: """<p>FAILED: Job '${env.JOB_NAME} [${env.BUILD_NUMBER}]':</p>
                    <p>Check console output at &QUOT;<a href='${env.BUILD_URL}'>${env.JOB_NAME} [${env.BUILD_NUMBER}]</a>&QUOT;</p>""",
                recipientProviders: [[$class: 'DevelopersRecipientProvider']]
                )
                steps{
                    script{
                        currentBuild.result = "FAILURE"
                        step([$class: 'InfluxDbPublisher', customData: null, customDataMap: null, customPrefix: null, target: 'devops-metrics'])
                    }
                }
        }
    }
}
