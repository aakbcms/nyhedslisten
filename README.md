# Nyhedslisten
Generate list of new materails for the library used to generate news mails.

## Installation
The repository comes with a complete docker compose setup to run the project.

```bash
docker-compose up -d
docker-compose exec phpfpm composer install
```

### Project installation.

````bash
cp .env .env.local
docker-compose exec phpfpm bin/console doctrine:migrations:migrate
````

You should change the settings in the local env file to ensure that you get
connected to the services requires by the projekt.

### Access the project
````bash
echo "http://0.0.0.0:$(docker-compose port reverse-proxy 80 | cut -d: -f2)"
````
