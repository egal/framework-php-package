PHP_SERVICE_RUN := docker-compose run --rm --user "$(shell id -u):$(shell id -g)" php

composer/install:
	${PHP_SERVICE_RUN} \
		composer install \
			--dev \
			--no-interaction \
			--no-progress

test/phpunit:
	sh bin/phpunit.sh

test/phpcs:
	${PHP_SERVICE_RUN} vendor/bin/phpcs

phpcbf:
	${PHP_SERVICE_RUN} vendor/bin/phpcbf
