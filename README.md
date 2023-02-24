# Nyhedslisten
Generate list of new materials for the library used to generate news mails.

## Installation
The repository comes with a complete docker compose setup to run the project.

```bash
docker-compose up -d
docker-compose exec phpfpm composer install
```

### Project installation.

```bash
cp .env .env.local

# Run database migrations
docker-compose exec phpfpm bin/console doctrine:migrations:migrate

# Load fixtures (optional)
docker-compose exec phpfpm bin/console doctrine:fixtures:load
```

You should change the settings in the local env file to ensure that you get
connected to the services requires by the projekt.
