# How to run the project

###### Set .env
```shell
cp .env.example .env
```

###### You should check that params **HOST_UID** and **HOST_GID** equals the next values
```shell
id -u && id -g
```

###### Build and run docker
```shell
docker-compose build && docker-compose up -d
```

###### How to prepare test env and run tests

```shell
php bin/console doctrine:database:drop --env=test --force;
php bin/console doctrine:database:create --env=test;        
php bin/console doctrine:migrations:migrate --env=test;
php bin/phpunit;
```