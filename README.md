# How run the project

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
