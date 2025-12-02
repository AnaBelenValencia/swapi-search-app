import { useEffect, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";

type CharacterSummary = {
  id: string;
  name: string;
};

type MovieDetailResponse = {
  id: string;
  title: string;
  summary: string;
  characters: CharacterSummary[];
};

const API_BASE_URL =
  import.meta.env.VITE_API_BASE_URL ?? "http://localhost:8000/api";

export const MovieDetailPage = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();

  const [movie, setMovie] = useState<MovieDetailResponse | null>(null);
  const [isLoading, setIsLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);

  // Fetch movie detail on mount and when id changes.
  useEffect(() => {
    if (!id) {
      setError("Missing movie id");
      setIsLoading(false);
      return;
    }

    const fetchMovie = async () => {
      try {
        setIsLoading(true);
        setError(null);

        const response = await fetch(`${API_BASE_URL}/films/${id}`);
        if (!response.ok) {
          throw new Error(`Failed to fetch movie with id ${id}`);
        }

        const data: MovieDetailResponse = await response.json();
        setMovie(data);
      } catch (err) {
        setError(
          err instanceof Error ? err.message : "Unexpected error fetching movie"
        );
      } finally {
        setIsLoading(false);
      }
    };

    fetchMovie();
  }, [id]);

  const handleBackClick = () => {
    // Navigate back to main search page.
    navigate("/");
  };

  const handleCharacterClick = (characterId: string) => {
    // Navigate to person detail page.
    navigate(`/people/${characterId}`);
  };

  if (isLoading) {
    return (
      <main className="page">
        <section className="card detail-card">
          <p className="muted-text">Loading movie details…</p>
        </section>
      </main>
    );
  }

  if (error || !movie) {
    return (
      <main className="page">
        <section className="card detail-card">
          <p className="error-text">
            {error ?? "Movie not found. Please go back to search."}
          </p>
          <button
            type="button"
            className="btn btn-primary detail-back-btn"
            onClick={handleBackClick}
          >
            BACK TO SEARCH
          </button>
        </section>
      </main>
    );
  }

  const { title, summary, characters } = movie;

  return (
    <main className="page">
      <section className="card detail-card">
        <h1 className="detail-title">{title}</h1>

        <div className="detail-columns">
          <div className="detail-column">
            <h2 className="detail-section-title">Opening Crawl</h2>
            <hr className="detail-divider" />
            <p className="detail-summary">{summary}</p>
          </div>

          <div className="detail-column">
            <h2 className="detail-section-title">Characters</h2>
            <hr className="detail-divider" />

            {characters.length === 0 ? (
              <p className="muted-text">No characters available.</p>
            ) : (
              <p className="detail-characters-text">
                {characters.map((character, index) => (
                  <span key={character.id}>
                    <button
                      type="button"
                      className="link-button"
                      onClick={() => handleCharacterClick(character.id)}
                    >
                      {character.name}
                    </button>
                    {index < characters.length - 1 && ", "}
                  </span>
                ))}
              </p>
            )}
          </div>
        </div>

        <div className="detail-actions">
          <button
            type="button"
            className="btn btn-primary detail-back-btn"
            onClick={handleBackClick}
          >
            BACK TO SEARCH
          </button>
        </div>
      </section>
    </main>
  );
};
