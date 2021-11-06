@echo off
SET USAGE="Usage: init.sh up|down|build|ls|init|shell"
SET CONTAINER="file_cms"
SET INIT=0

IF "%~1"=="" GOTO :done

IF "%1"=="up" GOTO :up
IF "%1"=="start" GOTO :up
GOTO :opt2
:up
docker-compose up -d %2
GOTO:EOF

:opt2
IF "%1" =="down" GOTO :down
IF "%1"=="stop" GOTO :down
GOTO :opt3
:down
docker-compose down
takeown /R /F *
del 1
GOTO:EOF

:opt3
IF "%1"=="build" GOTO :build
GOTO :opt4
:build
docker-compose build %2
GOTO:EOF

:opt4
IF "%1"=="ls" GOTO :ls
GOTO :opt5
:ls
docker container ls
GOTO:EOF

:opt5
IF "%1"=="init" GOTO :init
GOTO :opt6
:init
docker exec %CONTAINER% /bin/bash -c "/etc/init.d/mysql restart"
docker exec %CONTAINER% /bin/bash -c "/etc/init.d/php-fpm restart"
docker exec %CONTAINER% /bin/bash -c "/etc/init.d/httpd restart"
GOTO:EOF

:opt6
IF "%1"=="shell" GOTO :shell
GOTO :done
:shell
IF "%2"=="" GOTO :usage
docker exec -it %CONTAINER% /bin/bash
GOTO:EOF

:done
echo "Done"
echo %USAGE%
echo "You entered %1 and %1"
GOTO:EOF
