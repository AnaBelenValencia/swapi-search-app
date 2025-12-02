# LawnStarter SWAPI Challenge – Backend Documentation

This backend project implements a service layer that connects to SWAPI (swapi.dev), supports resource-based search (people/films), stores query history, and generates aggregated statistics. The system is built with clarity, extensibility, and maintainability in mind, fully aligned with the requirements defined in the challenge specification.

This document explains the architecture, technical decisions, data model, endpoints, tests, and overall reasoning behind each implementation choice.

---

## 1. Project Overview

The goal of this backend is to provide a clean API layer that sits between the frontend and SWAPI.  
The service supports:

- Searching SWAPI resources (people and films) with pagination and filtering.
- Fetching detail views for individual people and films with related data.
- Loading related resources (movies for a person, characters for a film) using concurrent HTTP requests.
- Persisting query history for analytics.
- Generating aggregated statistics from historical data.
- Updating statistics periodically using a scheduled job.

The backend runs inside Docker and uses PostgreSQL for persistent storage.

---

## 2. Architecture and Technical Stack

### 2.1 Framework: Laravel
Laravel was chosen because it offers:

- A well-organized MVC structure.
- Built-in support for external API consumption through the HTTP client.
- First-class testing tools.
- A comprehensive scheduling and queue system (used for statistics calculation).
- Eloquent ORM for expressive database interactions.

### 2.2 Database: PostgreSQL
PostgreSQL was selected due to:

- Strong support for date/time operations.
- Easy aggregation for statistical queries.
- JSON column support for flexible storage of computed statistics.

### 2.3 Containerization: Docker + Docker Compose
The backend is fully containerized:

- PHP-FPM container handles the Laravel application.
- Nginx container handles routing and HTTP serving.
- PostgreSQL container provides persistent data storage.

This ensures reproducible environments and prevents dependency conflicts with the host machine.

---

## 3. Folder Structure

```
backend/
  app/
    Http/Controllers/
    Services/
    Jobs/
    Models/
  database/
    migrations/
  routes/
    api.php
  Dockerfile
docker/
  nginx/
docker-compose.yml
```

Responsibilities:

- `Services/` contains business logic for external APIs and statistics.
- `Jobs/` contains background and scheduled tasks.
- `Models/` represent database tables using Eloquent ORM.
- `routes/api.php` defines all public API endpoints.

---

## 4. Search System Design

### 4.1 External API
The backend uses `https://swapi.dev/api` instead of swapi.tech due to stability and reliability.

### 4.2 Unified Search Endpoint
The API exposes:

```
GET /api/search?resource=people|films&q=<term>&page=<n>&limit=<n>
```

Design rationale:

- Both SWAPI resources (people and films) share similar structure.
- A unified endpoint simplifies the frontend integration.
- The `resource` parameter ensures flexibility and scalability.

### 4.3 Service Abstraction: SwapiSearchService
All calls to SWAPI are made through `SwapiSearchService`, which provides:

- Input validation
- URL construction
- Timing measurement
- Error handling
- Response normalization

This separation keeps the controller clean and promotes reusability.

#### Available Methods

- `search(resource, term, page)` - Search people or films by name/title with pagination.
- `getById(resource, id)` - Fetch a single resource by its ID.
- `getByUrl(url)` - Fetch a resource using its full SWAPI URL.
- `getManyByUrl(urls)` - Fetch multiple URLs concurrently using Laravel's `Http::pool()`. This method significantly improves performance when loading related resources (e.g., films for a person or characters for a movie).

### 4.4 Query Logging
Each request to `/api/search` results in a new entry in `search_queries` recording:

- Resource searched (people/films)
- Search term
- Pagination parameters
- Result count
- Response time
- Timestamp

This data is used later for analytics.

---

## 5. Statistics System

### 5.1 Purpose
The backend periodically calculates statistics from the historical search data.

### 5.2 Metrics Calculated
The statistics include:

- Total number of searches
- Top searched terms
- Average external API response time
- Searches grouped by resource
- Busiest hour of usage

These metrics are stored in `search_stats.payload` as a JSON snapshot.

### 5.3 StatsService
`StatsService` gathers data from `search_queries`, runs aggregation queries, and writes the results to `search_stats`.

### 5.4 Scheduled Job
A recurring job (`RecalculateStatsJob`) runs every five minutes:

```php
$schedule->job(new RecalculateStatsJob)->everyFiveMinutes();
```

In development, it can be executed manually using:

```
php artisan schedule:work
```

### 5.5 Stats Endpoint
```
GET /api/stats
```

If no statistics exist yet, they are calculated on demand.  
Otherwise, the latest snapshot is returned.

---

## 6. Endpoints

### 6.1 Search Endpoint
```
GET /api/search
```

Parameters:
- `resource`: people | films (also accepts `type` as alias)
- `q`: optional search term (also accepts `query` as alias)
- `page`: page number (default: 1)
- `limit`: number of items per page (default: 10, max: 50)

Response:
```json
{
  "data": [
    {
      "id": "1",
      "type": "people",
      "label": "Luke Skywalker",
      "subtitle": "19BBY"
    }
  ],
  "meta": {
    "page": 1,
    "perPage": 10,
    "total": 1,
    "totalPages": null,
    "resource": "people",
    "query": "luke",
    "responseTimeMs": 245.32
  }
}
```

### 6.2 Person Detail Endpoint
```
GET /api/people/{id}
```

Fetches a person by ID and loads their related films concurrently.

Response:
```json
{
  "id": "1",
  "name": "Luke Skywalker",
  "details": {
    "birthYear": "19BBY",
    "gender": "male",
    "eyeColor": "blue",
    "hairColor": "blond",
    "height": "172",
    "mass": "77"
  },
  "movies": [
    { "id": "1", "title": "A New Hope" },
    { "id": "2", "title": "The Empire Strikes Back" }
  ]
}
```

### 6.3 Film Detail Endpoint
```
GET /api/films/{id}
```

Fetches a film by ID and loads its characters concurrently.

Response:
```json
{
  "id": "1",
  "title": "A New Hope",
  "summary": "It is a period of civil war...",
  "characters": [
    { "id": "1", "name": "Luke Skywalker" },
    { "id": "2", "name": "C-3PO" }
  ]
}
```

### 6.4 Statistics Endpoint
```
GET /api/stats
```

Returns the latest precomputed statistics snapshot.

---

## 7. Testing Strategy

This project includes automated tests using PHPUnit, covering both feature and unit workflows.

### 7.1 Feature Tests
Feature tests validate the entire request lifecycle:

- SWAPI calls are mocked using Laravel’s HTTP fake functionality.
- `/api/search` responses are validated for structure and data.
- `/api/people/{id}` and `/api/films/{id}` responses are validated.
- Database entries in `search_queries` are asserted.

### 7.2 Unit Tests
Unit tests validate internal logic:

- `StatsService` is tested with controlled seeded data.
- Aggregations are validated for correctness.
- `search_stats` insertion is verified.

---

## 8. Error Handling

The backend converts external API errors into clean JSON responses.  
Main practices include:

- Validation of resource types.
- Automatic handling of upstream failures (returns 502).
- Structured output messages for frontend consumption.
- Logging of exceptional cases for debugging.

---

## 9. Summary of Key Design Choices

- Clear separation between controllers, services, jobs, and models.
- External API logic isolated for easier testing and future expansion.
- Historical data is stored for analytics and not recalculated on demand.
- Statistics are precomputed to reduce frontend latency.
- Containers enforce a reproducible and isolated development environment.

---

## 10. Running the Project

Build and start:

```
docker-compose up --build -d
```

Run migrations:

```
docker-compose exec backend php artisan migrate
```

Run scheduler (optional):

```
docker-compose exec backend php artisan schedule:work
```

Run automated tests:

```
docker-compose exec backend php artisan test
```

---

This backend is structured for clarity, testability, and future extensibility.  
All decisions were made considering maintainability and alignment with best engineering practices.
