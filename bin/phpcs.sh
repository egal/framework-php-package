#!/bin/bash

help() {
    # Display Help
    echo "Usage:    bash bin/phpcs.sh [OPTIONS]"
    echo
    echo "Options:"
    echo "-i,   --image string      Select image."
    echo "      --diffs             Check only diffs with 'main' branch."
    echo "      --report string     Select PHPCS report type."
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
    --diffs)
        DIFFS=TRUE
        shift # past argument
        ;;
    --report)
        REPORT="$2"
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

COMMAND_ADDITIONAL_LINE="-p"

if [ -z "${IMAGE}" ]; then
    IMAGE="php:7.4.16-cli-buster"
fi

if [ -z "${DIFFS}" ]; then
    DIFFS=FALSE
fi

if [ -n "${REPORT}" ]; then
    COMMAND_ADDITIONAL_LINE="${COMMAND_ADDITIONAL_LINE} --report=${REPORT}"
fi

WORKDIR='/data'

if [ $DIFFS == TRUE ]; then
    TEMP="$(git diff --name-only main | grep -E '(.php)$')"
    for i in $(echo "${TEMP[@]}" | tr " " "\n"); do
        FILE="${FILE} ${WORKDIR}/${i}"
    done
else
    FILE="${WORKDIR}"
fi

docker run -it --rm \
    --user "$(id -u):$(id -g)" \
    --workdir "${WORKDIR}" \
    --entrypoint "vendor/bin/phpcs" \
    --volume "${PWD}:${WORKDIR}" \
    "${IMAGE}" \
    "${COMMAND_ADDITIONAL_LINE}" \
    ${FILE}
