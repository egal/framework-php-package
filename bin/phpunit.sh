#!/bin/bash

help() {
    # Display Help
    echo "Usage:    bash bin/phpunit.sh [OPTIONS]"
    echo
    echo "Options:"
    echo "-i,   --image string      Select image."
    echo
}

while [[ $# -gt 0 ]]; do
    key="$1"

    case $key in
    -i | --image)
        IMAGE="$2"
        shift # past argument
        shift # past value
        ;;
    *) # unknown option
        echo "Unknown parameter '$1'!"
        echo
        help
        exit 1
        ;;
    esac
done

if [ -z "${IMAGE}" ]; then
    IMAGE="php:7.4.16-cli-buster";
fi

docker run -it --rm \
    --workdir "/data" \
    --volume "${PWD}:/data:delegated" \
    "${IMAGE}" \
    "vendor/bin/phpunit";
