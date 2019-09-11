@Library('jenkins-pipeline')_

pipeline {
    agent any
    stages {
        stage('Build and test') {
            parallel {
                stage('PHP') {
                    agent {
                        docker {
                            image 'itkdev/php7.2-fpm:latest' /* 7.2 is used as phan only runs with this version */
                            args '-v /var/lib/jenkins/.composer-cache:/.composer:rw'
                        }
                    }
                    stages {
                        stage('Build') {
                            steps {
                                sh 'composer install'
                            }
                        }
                        stage('PHP7 compatibility') {
                            steps {
                                sh 'vendor/bin/phan --allow-polyfill-parser'

                            }
                        }
                        stage('Coding standards') {
                            steps {
                                sh 'vendor/bin/phpcs --standard=phpcs.xml.dist'
                                sh 'vendor/bin/php-cs-fixer --config=.php_cs.dist fix --dry-run --verbose'
                                sh 'vendor/bin/twigcs lint templates'
                            }
                        }
                    }
                }
            }
        }
        stage('Deployment develop') {
            when {
                branch 'develop'
            }
            steps {
                sh "ansible srvitkphp72stg -m shell -a 'cd /data/www/nyhedslisten_srvitkphp72stg_itkdev_dk/htdocs; git clean -d --force'"
                sh "ansible srvitkphp72stg -m shell -a 'cd /data/www/nyhedslisten_srvitkphp72stg_itkdev_dk/htdocs; git checkout ${BRANCH_NAME}'"
                sh "ansible srvitkphp72stg -m shell -a 'cd /data/www/nyhedslisten_srvitkphp72stg_itkdev_dk/htdocs; git fetch'"
                sh "ansible srvitkphp72stg -m shell -a 'cd /data/www/nyhedslisten_srvitkphp72stg_itkdev_dk/htdocs; git reset origin/${BRANCH_NAME} --hard'"

                // Run composer.
                sh "ansible srvitkphp72stg -m shell -a 'cd /data/www/nyhedslisten_srvitkphp72stg_itkdev_dk/htdocs; APP_ENV=prod composer install --no-dev -o'"

                // Run migrations.
                sh "ansible srvitkphp72stg -m shell -a 'cd /data/www/nyhedslisten_srvitkphp72stg_itkdev_dk/htdocs; APP_ENV=prod php bin/console doctrine:migrations:migrate --no-interaction'"
            }
        }
        stage('Deployment staging') {
            when {
                branch 'release'
            }
            steps {
                echo "release"
            }
        }
        stage('Deployment production') {
            when {
                branch 'master'
            }
            steps {
                timeout(time: 30, unit: 'MINUTES') {
                    input 'Should the site be deployed?'
                }
                steps {
                    echo 'production'
                }
            }
        }
    }
    post {
        always {
            script {
                slackNotifier(currentBuild.currentResult)
            }
        }
    }
}