import { useEffect, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";

type PersonDetails = {
  birthYear: string | null;
  gender: string | null;
  eyeColor: string | null;
  hairColor: string | null;
  height: string | null;
  mass: string | null;
};

type MovieSummary = {
  id: string;
  title: string;
};

type PersonDetailResponse = {
  id: string;
  name: string;
  details: PersonDetails;
  movies: MovieSummary[];
};

const API_BASE_URL =
  import.meta.env.VITE_API_BASE_URL ?? "http://localhost:8000/api";

export const PersonDetailPage = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();

  const [person, setPerson] = useState<PersonDetailResponse | null>(null);
  const [isLoading, setIsLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);

  // Fetch person detail on mount and when id changes.
  useEffect(() => {
    if (!id) {
      setError("Missing person id");
      setIsLoading(false);
      return;
    }

    const fetchPerson = async () => {
      try {
        setIsLoading(true);
        setError(null);

        const response = await fetch(`${API_BASE_URL}/people/${id}`);
        if (!response.ok) {
          throw new Error(`Failed to fetch person with id ${id}`);
        }

        const data: PersonDetailResponse = await response.json();
        setPerson(data);
      } catch (err) {
        setError(
          err instanceof Error ? err.message : "Unexpected error fetching person"
        );
      } finally {
        setIsLoading(false);
      }
    };

    fetchPerson();
  }, [id]);

  const handleBackClick = () => {
    // Navigate back to main search page.
    navigate("/");
  };

  const handleMovieClick = (movieId: string) => {
    // Navigate to movie detail page (to be implemented).
    navigate(`/movies/${movieId}`);
  };

  if (isLoading) {
    return (
      <main className="page">
        <div className="card detail-card">
          <p className="muted-text">Loading character details…</p>
        </div>
      </main>
    );
  }

  if (error || !person) {
    return (
      <main className="page">
        <div className="card detail-card">
          <p className="error-text">
            {error ?? "Character not found. Please go back to search."}
          </p>
          <button
            type="button"
            className="btn btn-primary detail-back-btn"
            onClick={handleBackClick}
          >
            BACK TO SEARCH
          </button>
        </div>
      </main>
    );
  }

  const { name, details, movies } = person;

  return (
    <main className="page">
      <section className="card detail-card">
        <h1 className="detail-title">{name}</h1>

        <div className="detail-columns">
          <div className="detail-column">
            <h2 className="detail-section-title">Details</h2>
            <hr className="detail-divider" />
            <ul className="detail-list">
              <li>Birth Year: {details.birthYear ?? "Unknown"}</li>
              <li>Gender: {details.gender ?? "Unknown"}</li>
              <li>Eye Color: {details.eyeColor ?? "Unknown"}</li>
              <li>Hair Color: {details.hairColor ?? "Unknown"}</li>
              <li>Height: {details.height ?? "Unknown"}</li>
              <li>Mass: {details.mass ?? "Unknown"}</li>
            </ul>
          </div>

          <div className="detail-column">
            <h2 className="detail-section-title">Movies</h2>
            <hr className="detail-divider" />
            {movies.length === 0 ? (
              <p className="muted-text">No movies available.</p>
            ) : (
              <ul className="detail-list detail-movies-list">
                {movies.map((movie) => (
                  <li key={movie.id}>
                    <button
                      type="button"
                      className="link-button"
                      onClick={() => handleMovieClick(movie.id)}
                    >
                      {movie.title}
                    </button>
                  </li>
                ))}
              </ul>
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
