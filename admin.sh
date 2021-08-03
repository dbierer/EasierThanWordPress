#!/bin/bash
DIR=`pwd`
export NAME="test"
export EXT="net"
export URL="$NAME.$EXT"
export CONTAINER="$NAME_$EXT"
export USAGE="Usage: admin.sh up|down|build|init|shell|creds"
export INIT=0
export SWAP=0
if [[ -z "$1" ]]; then
    echo $USAGE
    exit 1
fi
if [[ "$1" = "up" || "$1" = "start" ]]; then
    docker-compose up -d
elif [[ "$1" = "down" || "$1" = "stop" ]]; then
    docker-compose down
    sudo chown -R $USER:$USER *
elif [[ "$1" = "creds" ]]; then
    php $TOOLS_DIR/generate_creds.php $2 $3 $4 $5 $6
elif [[ "$1" = "build" ]]; then
    docker-compose build
elif [[ "$1" = "init" ]]; then
    if [[ -z ${CONTAINER} ]]; then
        echo "Unable to locate running container"
    else
        docker exec $CONTAINER /bin/bash -c "/etc/init.d/mysql start"
        docker exec $CONTAINER /bin/bash -c "/etc/init.d/php-fpm start"
        docker exec $CONTAINER /bin/bash -c "/etc/init.d/httpd start"
    fi
elif [[ "$1" = "shell" ]]; then
    if [[ -z ${CONTAINER} ]]; then
        echo "Unable to locate running container: $CONTAINER"
    else
        docker exec -it $CONTAINER /bin/bash
    fi
else
    echo $USAGE
    exit 1
fi
