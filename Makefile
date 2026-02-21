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