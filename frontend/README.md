# SWStarter Frontend

A clean and simple Star Wars search experience built with React and TypeScript.  
This frontend consumes a custom Laravel API that proxies and normalizes data from the public SWAPI service.

The goal of the project is to implement a minimal UI that allows users to search for people or movies, view paginated results, and explore detailed views for each entity following a provided design specification.

## Features

### 1. Search for People or Movies
The homepage provides a search form where the user can:

- Select between People and Movies  
- Enter a search term  
- Trigger the query with a disabled/enabled Search button  
- View results in a simple list format  

The search results come from the backend endpoint:

GET /api/search?resource=people|films&q=term&page=n&limit=n

Each result is normalized into the structure:

{
  id: string,
  type: "people" | "films",
  label: string,
  subtitle?: string
}

### 2. People Detail View

Users can click on any person from the results to access:

/people/:id

The frontend fetches:

GET /api/people/:id

The API returns:

{
  id: string,
  name: string,
  details: {
    birthYear,
    gender,
    eyeColor,
    hairColor,
    height,
    mass
  },
  movies: [
    { id: string, title: string }
  ]
}

The UI displays:

- Left column: Personal details  
- Right column: List of related movies, linking to their detail pages  

### 3. Movie Detail View

Movies use the endpoint:

GET /api/films/:id

Which returns:

{
  id,
  title,
  summary,
  characters: [
    { id, name }
  ]
}

The UI renders:

- Movie title  
- Opening Crawl text  
- A list of characters linking to their corresponding person views  
- A button to return to the search page  

### 4. Statistics Page

The application includes a statistics dashboard accessible via the header navigation or directly at `/stats`.

The frontend fetches:

GET /api/stats

The API returns:

{
  stats: {
    total_searches: number,
    top_queries: [{ term: string, hits: number }],
    avg_response_ms: number | null,
    by_resource: [{ resource: string, hits: number }],
    busiest_hour: { hour: number, hits: number } | null,
    generated_at: string
  },
  calculated_at: string
}

The UI displays:

- Total number of searches performed
- Average API response time in milliseconds
- Busiest hour of the day with search count
- Top 5 most searched terms with hit counts
- Breakdown of searches by resource type (people/films)
- Timestamp of when the statistics were generated

The page uses a card-based grid layout for key metrics and list sections for detailed breakdowns.

### 5. Not Found Page

Any unmatched route displays a simple 404 page handled by `NotFoundPage.tsx`.

### 6. Full Routing Integration

The frontend uses React Router and supports:

- `/` - Search page
- `/people/:id` - Person detail view
- `/movies/:id` - Movie detail view
- `/stats` - Statistics dashboard
- `*` - 404 Not Found

Navigation between resources is seamless. The header includes a centered logo linking to the search page and a navigation link to the stats page.

## Tech Stack

- React 18  
- TypeScript  
- Vite  
- React Router  
- Custom CSS (no UI frameworks)  
- Fetch API for backend integration  

This keeps the project lightweight, fast, and easy to understand.

## Project Structure

```
src/
 ├── api/
 │     ├── client.ts
 │     └── search.ts
 ├── components/
 │     ├── layout/
 │     │     └── AppLayout.tsx
 │     └── search/
 │           ├── ResultsList.tsx
 │           └── SearchForm.tsx
 ├── pages/
 │     ├── SearchPage.tsx
 │     ├── PersonDetailPage.tsx
 │     ├── MovieDetailPage.tsx
 │     ├── StatsPage.tsx
 │     └── NotFoundPage.tsx
 ├── router/
 │     └── index.tsx
 ├── App.tsx
 ├── main.tsx
 └── index.css
```

### api/client.ts
Base HTTP client with `request()` function for all API calls. Handles URL construction, headers, and error handling.

### api/search.ts
Exports `searchResources()` function and TypeScript interfaces for search items, metadata, and responses.

### components/layout/AppLayout.tsx
Wrapper component that renders the header and provides consistent layout across all pages.

### components/search/SearchForm.tsx
Search form with radio buttons for resource type selection, text input, and submit button with disabled state handling.

### components/search/ResultsList.tsx
Renders the list of search results with "SEE DETAILS" buttons that navigate to detail pages.

### pages/SearchPage.tsx
Handles form state, calls the search API, and renders results.

### pages/PersonDetailPage.tsx
Fetches person details and lists their related films.

### pages/MovieDetailPage.tsx
Fetches film details, displays the Opening Crawl, and lists characters.

### pages/StatsPage.tsx
Displays search statistics from the backend including total searches, average response time, busiest hour, top queries, and searches by resource. Uses a responsive grid layout with stat cards and list sections.

### pages/NotFoundPage.tsx
Simple 404 page displayed for unmatched routes.

### router/index.tsx
Defines all application routes using React Router, wraps pages with AppLayout.

### index.css
Contains handcrafted CSS matching the design:
- Header with centered logo and navigation
- Grid layout  
- Typography  
- Buttons  
- Dividers  
- Forms  
- Detail pages
- Stats page cards and lists  

## Environment Variables

Create a .env file in the frontend root:

VITE_API_BASE_URL=http://localhost:8000/api

If omitted, the application defaults to the above.

## Running the Project

### Install dependencies

npm install

### Start the development server

npm run dev

The app runs at:

http://localhost:5173

Backend should be running at:

http://localhost:8000

## API Requirements

This frontend expects the backend to provide the following endpoints:

### Search

GET /api/search?resource=people|films&q=term&page=n&limit=n

### Person Detail

GET /api/people/:id

### Movie Detail

GET /api/films/:id

Each endpoint must return cleaned, normalized values.

## Design Notes

The UI was built to match the provided design regarding:

- Spacing  
- Typography  
- Button styles  
- Disabled form states  
- Input placeholders  
- Layout grid  

Special attention was given to preserving whitespace in Opening Crawl text.

## Extending the Project

The structure allows for easy extension. You may add:

- Pagination UI  
- Error boundaries  
- Loading skeletons  
- Favorites/bookmarks  
- Unit tests with Vitest and React Testing Library  
- Additional resources like planets or starships  
