#!/bin/bash

help() {
    # Display Help
    echo "Usage:    bash bin/phpunit.sh [OPTIONS]"
    echo
}

while [[ $# -gt 0 ]]; do
    key="$1"

    case $key in
    *)
        COMMAND_ADDITIONAL_LINE="${COMMAND_ADDITIONAL_LINE} ${1}"
        shift # past argument
        ;;
    esac
done

docker-compose down -t 0 -v &> /dev/null
docker-compose build -q
docker-compose up -d postgres &> /dev/null

if docker-compose run --rm php -r "\$tries = 0; while (true) { try { \$tries++; if (\$tries > 60) { throw new RuntimeException('PostgreSQL never became available'); } sleep(1); new PDO(getenv('DB_CONNECTION').':host='.getenv('DB_HOST').';port='.getenv('DB_PORT').';dbname='.getenv('DB_NAME').'', getenv('DB_USERNAME'), getenv('DB_PASSWORD'), [PDO::ATTR_TIMEOUT => 3]); break; } catch (PDOException \$e) {} }"; then
    if docker-compose run --rm php "vendor/bin/phpunit" ${COMMAND_ADDITIONAL_LINE}; then
        docker-compose down -t 0 -v &> /dev/null
    else
        docker-compose down -t 0 -v &> /dev/null
        exit 1
    fi
else
    docker-compose logs
    docker-compose down -t 0 -v &> /dev/null
    exit 1
fi
