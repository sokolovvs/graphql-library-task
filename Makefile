install:
	docker-compose build
	docker-compose up -d
	docker-compose run --rm sf_app composer install
	docker-compose run --rm sf_app php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
test:
	php bin/console lint:container
	php bin/console graphql:validate
	php bin/console doctrine:database:drop --env=test --if-exists --force
	php bin/console doctrine:database:create --env=test --if-not-exists
	php bin/console doctrine:migrations:migrate --env="test" --no-interaction
	php bin/phpunit
coverage:
	php bin/console lint:container
	php bin/console graphql:validate
	php bin/console doctrine:database:drop --env=test --if-exists --force
	php bin/console doctrine:database:create --env=test --if-not-exists
	php bin/console doctrine:migrations:migrate --env="test" --no-interaction
	php -d xdebug.mode=coverage bin/phpunit --coverage-html=".codecoverage"
fixtures:
	php bin/console doctrine:fixtures:load