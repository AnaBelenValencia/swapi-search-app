# SWStarter - Star Wars Search Application

A full-stack Star Wars search application built with React and Laravel. Search for characters and movies from the Star Wars universe, view detailed information, and track search statistics.

## Features

- Search for People and Movies from the Star Wars universe
- View detailed information for each character including their related films
- View movie details including Opening Crawl text and character list
- Statistics dashboard showing search analytics
- Real-time statistics calculation via background jobs

## Tech Stack

### Frontend
- React 18 with TypeScript
- Vite for fast development and building
- React Router for navigation
- Custom CSS (no UI frameworks)

### Backend
- Laravel 11 (PHP 8.4)
- PostgreSQL database
- SWAPI (swapi.dev) as data source
- Queue system for background jobs
- Scheduled tasks for statistics calculation

### Infrastructure
- Docker and Docker Compose
- Nginx for serving the backend
- PostgreSQL 15

## Project Structure

```
lawnstarter-challenge/
├── backend/                 # Laravel API
│   ├── app/
│   │   ├── Console/        # Scheduled commands
│   │   ├── Http/Controllers/
│   │   ├── Jobs/           # Background jobs
│   │   ├── Models/
│   │   └── Services/       # Business logic
│   ├── database/migrations/
│   ├── routes/api.php
│   └── Dockerfile
├── frontend/               # React SPA
│   ├── src/
│   │   ├── api/           # HTTP client
│   │   ├── components/    # Reusable components
│   │   ├── pages/         # Page components
│   │   └── router/        # Route definitions
│   └── Dockerfile
├── docker/
│   └── nginx/
└── docker-compose.yml
```

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/search` | Search people or films |
| GET | `/api/people/{id}` | Get person details |
| GET | `/api/films/{id}` | Get film details |
| GET | `/api/stats` | Get search statistics |

## Getting Started

### Prerequisites

- Docker Desktop (Windows/Mac) or Docker Engine (Linux)
- Docker Compose v2.0+
- Git

### Quick Start

1. Clone the repository:

```bash
git clone <repository-url>
cd lawnstarter-challenge
```

2. Create the Docker environment file:

```bash
cp backend/.env.docker.example backend/.env.docker
```

> **Note:** After starting the containers, you should generate a new APP_KEY by running:
> ```bash
> docker-compose exec backend php artisan key:generate
> ```

3. Build and start all containers:

```bash
docker-compose up --build -d
```

4. Wait for containers to be ready (first run may take a few minutes):

```bash
docker-compose ps
```

5. The application will be available at:

- Frontend: http://localhost:5173
- Backend API: http://localhost:8000/api
- Database: localhost:5432

### Docker Commands Reference

#### Starting the Project

```bash
# Build and start all services in detached mode
docker-compose up --build -d

# Start without rebuilding (faster)
docker-compose up -d

# Start and view logs in real-time
docker-compose up
```

#### Stopping the Project

```bash
# Stop all containers
docker-compose stop

# Stop and remove containers
docker-compose down

# Stop and remove containers, volumes, and images
docker-compose down -v --rmi all
```

#### Viewing Logs

```bash
# View all logs
docker-compose logs

# View logs for a specific service
docker-compose logs backend
docker-compose logs frontend
docker-compose logs db

# Follow logs in real-time
docker-compose logs -f

# Follow logs for specific service
docker-compose logs -f backend
```

#### Accessing Containers

```bash
# Access backend container shell
docker-compose exec backend sh

# Access database container
docker-compose exec db psql -U swapi_user -d swapi_stats

# Access frontend container shell
docker-compose exec frontend sh
```

#### Laravel Artisan Commands

```bash
# Run migrations
docker-compose exec backend php artisan migrate

# Run migrations fresh (drop all tables and re-run)
docker-compose exec backend php artisan migrate:fresh

# Run database seeders
docker-compose exec backend php artisan db:seed

# Clear all caches
docker-compose exec backend php artisan optimize:clear

# Run scheduled tasks once
docker-compose exec backend php artisan schedule:run

# Run tests
docker-compose exec backend php artisan test

# Run tests with coverage
docker-compose exec backend php artisan test --coverage
```

#### Rebuilding Services

```bash
# Rebuild a specific service
docker-compose build backend
docker-compose build frontend

# Rebuild and restart a service
docker-compose up -d --build backend

# Force rebuild without cache
docker-compose build --no-cache
```

#### Checking Status

```bash
# List running containers
docker-compose ps

# View resource usage
docker stats
```

### Services

The Docker setup includes:

| Service | Description | Port |
|---------|-------------|------|
| db | PostgreSQL 15 database | 5432 |
| backend | Laravel API with php artisan serve | 8000 |
| queue-worker | Processes background jobs | - |
| scheduler | Runs scheduled tasks (stats every 5 min) | - |
| frontend | Vite development server with HMR | 5173 |

### Troubleshooting

#### Port already in use

If you get a port conflict error:

```bash
# Check what's using the port
lsof -i :8000
lsof -i :5173

# Or change ports in docker-compose.yml
```

#### Database connection issues

```bash
# Check if database is ready
docker-compose exec db pg_isready

# View database logs
docker-compose logs db

# Reset database
docker-compose down -v
docker-compose up -d
```

#### Clear Docker cache

```bash
# Remove unused containers, networks, images
docker system prune

# Remove all unused volumes
docker volume prune
```

## Development

### Frontend Development

The frontend uses Vite with hot module replacement. Changes to files in `frontend/src/` will automatically reload in the browser.

### Backend Development

The backend code is mounted as a volume. Changes to PHP files take effect immediately without rebuilding the container.

### Environment Variables

Backend environment variables are configured in `backend/.env.docker`:

- `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` - Database connection
- `SWAPI_BASE_URL` - Star Wars API base URL

Frontend environment variables:

- `VITE_API_BASE_URL` - Backend API URL (default: http://localhost:8000/api)

## Architecture Decisions

1. **Concurrent HTTP Requests**: Related resources (films for a person, characters for a movie) are fetched concurrently using Laravel's `Http::pool()` for better performance.

2. **Search Query Logging**: All searches are logged to the database for analytics purposes.

3. **Precomputed Statistics**: Stats are calculated every 5 minutes by a scheduled job to reduce latency on the stats endpoint.

4. **Service Layer**: Business logic is separated into services (`SwapiSearchService`, `StatsService`) for better testability and maintainability.

## Documentation

For detailed documentation, see:

- [Backend Documentation](./backend/README.md)
- [Frontend Documentation](./frontend/README.md)

## License

This project was created as part of the LawnStarter coding challenge.
