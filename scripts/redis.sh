#!/bin/bash

if [[ -z $1 ]]; then 
    echo "Use: $0 [--start | --stop | --status | --cli]" 
    exit 1
fi

case "$1" in 
    --start) 
        echo "Starting Redis..." 
        sudo service redis-server start 
        sudo service redis-server status 
    ;; 
    --stop) 
        echo "Stopping Redis..." 
        sudo service redis-server stop 
    ;; 
    --status) 
        sudo service redis-server status 
    ;; 
    --cli)
        redis-cli
    ;;
    *) 
        echo "Invalid parameter: $1" 
        echo "Valid options: --start, --stop, --status" 
        exit 1 
    ;;
esac    