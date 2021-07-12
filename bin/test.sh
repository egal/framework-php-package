#!/bin/bash

if [ -z "${IMAGE}" ]; then
    IMAGE="php:7.4.16-cli-buster";
fi

docker run -it --rm \
    --workdir "/data" \
    --volume "${PWD}:/data:delegated" \
    "${IMAGE}" \
    "vendor/bin/phpunit";

docker run -it --rm \
    --workdir "/data" \
    --entrypoint "vendor/bin/phpcs" \
    --volume "${PWD}:/data" \
    "${IMAGE}" \
    "-p" "--ignore=*/tests/*,*/vendor/*,*/stubs/*" "/data";
