# HomeDuty - Docker Setup

This project runs with Docker Compose using the file `compose.yml`.

## Services

- `app`: Laravel PHP-FPM container
- `nginx`: Web server exposed on `http://localhost:8080`
- `mysql`: MySQL database exposed on `localhost:33060`
- `redis`: Redis exposed on `localhost:63790`
- `queue`: Laravel queue worker
- `scheduler`: Laravel scheduler worker

## Quick Start

1. Start everything:

```bash
make up
```

2. Run migrations:

```bash
make migrate
```

3. Open the app:

- [http://localhost:8080](http://localhost:8080)

## Daily Commands

- `make ps` - show running containers
- `make logs` - follow all logs
- `make app-logs` - follow app logs only
- `make shell` - open shell in app container
- `make artisan c='about'` - run any artisan command
- `make test` - run test suite
- `make pint` - run code formatter

## Stop / Cleanup

- `make down` - stop containers
- `make clean` - stop containers and remove volumes

## Docker Notes

- The app code is mounted into containers, so source code changes are visible immediately.
- MySQL and Redis use named volumes, so data persists across restarts.
- The PHP entrypoint auto-creates `.env` (from `.env.example`) if missing and prepares Laravel storage folders.
