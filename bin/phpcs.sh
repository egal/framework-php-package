#!/bin/bash

help() {
    # Display Help
    echo "Usage:    bash bin/phpcs.sh [OPTIONS]"
    echo
    echo "Options:"
    echo "-i,   --image string      Select image."
    echo "      --diffs             Check only diffs with 'main' branch."
    echo
}

COMMAND_ADDITIONAL_LINE="-p"

while [[ $# -gt 0 ]]; do
    key="$1"

    case $key in
    --git-diffs)
        GIT_DIFFS=TRUE
        shift # past argument
        ;;
    *)
        COMMAND_ADDITIONAL_LINE="${COMMAND_ADDITIONAL_LINE} ${1}"
        shift # past argument
        ;;
    esac
done

if [ -z "${GIT_DIFFS}" ]; then
    GIT_DIFFS=FALSE
fi

if [ -n "${REPORT}" ]; then
    COMMAND_ADDITIONAL_LINE="${COMMAND_ADDITIONAL_LINE} --report=${REPORT}"
fi

WORKDIR='/data'

if [ $GIT_DIFFS == TRUE ]; then
    TEMP="$(git diff --name-only --diff-filter=dr main | grep -E '(.php)$')"
    for i in $(echo "${TEMP[@]}" | tr " " "\n"); do
        FILE="${FILE} ${WORKDIR}/${i}"
    done
    COMMAND_ADDITIONAL_LINE="${COMMAND_ADDITIONAL_LINE} ${FILE}"
fi

docker-compose run --rm \
    --user "$(id -u):$(id -g)" \
    --entrypoint "vendor/bin/phpcs" \
    php \
    ${COMMAND_ADDITIONAL_LINE}
