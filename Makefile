PHP=docker compose exec php

up:
	docker compose up -d

down:
	docker compose down

bash:
	$(PHP) bash

console:
	$(PHP) php bin/console

composer:
	$(PHP) composer $(CMD)

cache:
	$(PHP) php bin/console cache:clear

test:
	$(PHP) bin/phpunit

test-unit:
	$(PHP) bin/phpunit --testsuite=Unit

test-integration:
	$(PHP) bin/phpunit --testsuite=Integration

test-functional:
	$(PHP) bin/phpunit --testsuite=Functional

test-filter:
	$(PHP) bin/phpunit --filter=$(FILTER)

test-coverage:
	$(PHP) bin/phpunit --coverage-text