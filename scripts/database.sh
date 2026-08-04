#!/bin/bash

if [[ -z $1 ]]; then 
    echo "Use: $0 [--start | --stop | --status]" 
    exit 1
fi

case "$1" in 
    --start) 
        echo "Starting PostgreSQL..." 
        sudo service postgresql start 
        sudo service postgresql status 
    ;; 
    --stop) 
        echo "Stopping PostgreSQL..." 
        sudo service postgresql stop 
    ;; 
    --status) 
        sudo service postgresql status 
    ;; 
    *) 
        echo "Invalid parameter: $1" 
        echo "Valid options: --start, --stop, --status" 
        exit 1 
    ;;
esac    