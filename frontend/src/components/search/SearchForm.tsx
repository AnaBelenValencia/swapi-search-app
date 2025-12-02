import { type FormEvent, useMemo, useState } from "react";

export type UiResourceType = "people" | "movies";

interface SearchFormProps {
  initialQuery?: string;
  resourceType: UiResourceType;
  onResourceChange: (type: UiResourceType) => void;
  onSearch: (query: string) => void;
  isLoading?: boolean;
}

export function SearchForm({
  initialQuery = "",
  resourceType,
  onResourceChange,
  onSearch,
  isLoading = false,
}: SearchFormProps) {
  const [value, setValue] = useState(initialQuery);

  // Placeholder depends on people / movies selection
  const placeholder = useMemo(() => {
    if (resourceType === "people") {
      return "e.g. Chewbacca, Yoda, Boba Fett";
    }
    return "e.g. A New Hope, The Empire Strikes Back";
  }, [resourceType]);

  const handleSubmit = (event: FormEvent) => {
    event.preventDefault();
    const trimmed = value.trim();
    if (!trimmed) {
      return;
    }
    onSearch(trimmed);
  };

  const isSearchDisabled = isLoading || value.trim().length === 0;

  return (
    <form className="search-form" onSubmit={handleSubmit}>
      <fieldset className="search-fieldset">
        <legend className="search-legend">What are you searching for?</legend>

        <div className="search-toggle-group">
          <label className="search-toggle">
            <input
              type="radio"
              name="resourceType"
              value="people"
              checked={resourceType === "people"}
              onChange={() => onResourceChange("people")}
            />
            <span className="search-type">People</span>
          </label>

          <label className="search-toggle">
            <input
              type="radio"
              name="resourceType"
              value="movies"
              checked={resourceType === "movies"}
              onChange={() => onResourceChange("movies")}
            />
            <span className="search-type">Movies</span>
          </label>
        </div>
      </fieldset>

      <input
        className="search-input"
        placeholder={placeholder}
        value={value}
        onChange={(event) => setValue(event.target.value)}
      />

      <button
        className="search-button"
        type="submit"
        disabled={isSearchDisabled}
      >
        {isLoading ? "Searching..." : "SEARCH"}
      </button>
    </form>
  );
}
