import { useState } from "react";
import {
  searchResources,
  type ResourceType,
  type SearchResponse,
} from "../api/search";
import { SearchForm, type UiResourceType } from "../components/search/SearchForm";
import { ResultsList } from "../components/search/ResultsList";

export function SearchPage() {
  const [uiResourceType, setUiResourceType] = useState<UiResourceType>("people");
  const [query, setQuery] = useState("");
  const [page, setPage] = useState(1);
  const [results, setResults] = useState<SearchResponse | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const mapUiToApiResource = (uiType: UiResourceType): ResourceType => {
    if (uiType === "people") {
      return "people";
    }
    return "films";
  };
  
  const handleSearch = async (newQuery: string, pageToLoad = 1) => {
    setQuery(newQuery);
    setPage(pageToLoad);

    if(!newQuery) {
      setResults(null);
      setError(null);
      return;
    }

    const apiResource = mapUiToApiResource(uiResourceType);

    try {
      setIsLoading(true);
      setError(null);

      const response = await searchResources(apiResource, newQuery, pageToLoad, 10);
      setResults(response);
    } catch(exception) {
      console.error(exception);
      setError((exception as Error).message);
      setResults(null);
    } finally {
      setIsLoading(false);
    }
  };

  const hasResults = !!results && results.data.length > 0;

  return (
    <div className="home-layout">
      <section className="home-panel home-panel-left">
        <SearchForm
          initialQuery={query}
          resourceType={uiResourceType}
          onResourceChange={(type) => {
            setUiResourceType(type);
            setResults(null);
          }}
          onSearch={(value) => handleSearch(value, 1)}
          isLoading={isLoading}
        />
      </section>

      <section className="home-panel home-panel-right">
        <h2 className="results-title">Results</h2>

        {isLoading && <p className="search-message">Loading results...</p>}

        {error && (
          <p className="search-error">
            Something went wrong while searching: {error}
          </p>
        )}

        {!isLoading && !error && hasResults && (
          <ResultsList items={results!.data} />
        )}

        {!isLoading && !error && !hasResults && (
          <ResultsList items={[]} />
        )}
      </section>
    </div>
  );
}