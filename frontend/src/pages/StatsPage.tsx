import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";

type TopQuery = {
  term: string;
  hits: number;
};

type ResourceStats = {
  resource: string;
  hits: number;
};

type BusiestHour = {
  hour: number;
  hits: number;
};

type StatsPayload = {
  total_searches: number;
  top_queries: TopQuery[];
  avg_response_ms: number | null;
  by_resource: ResourceStats[];
  busiest_hour: BusiestHour | null;
  generated_at: string;
};

type StatsResponse = {
  stats: StatsPayload;
  calculated_at: string | null;
};

const API_BASE_URL =
  import.meta.env.VITE_API_BASE_URL ?? "http://localhost:8000/api";

export function StatsPage() {
  const navigate = useNavigate();

  const [stats, setStats] = useState<StatsPayload | null>(null);
  const [isLoading, setIsLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchStats = async () => {
      try {
        setIsLoading(true);
        setError(null);

        const response = await fetch(`${API_BASE_URL}/stats`);
        if (!response.ok) {
          throw new Error(`Failed to fetch stats: ${response.status}`);
        }

        const data: StatsResponse = await response.json();
        setStats(data.stats);
      } catch (err) {
        setError((err as Error).message);
      } finally {
        setIsLoading(false);
      }
    };

    fetchStats();
  }, []);

  const formatHour = (hour: number): string => {
    const suffix = hour >= 12 ? "PM" : "AM";
    const displayHour = hour % 12 || 12;
    return `${displayHour}:00 ${suffix}`;
  };

  if (isLoading) {
    return (
      <div className="page">
        <div className="card detail-card">
          <p className="search-message">Loading statistics...</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="page">
        <div className="card detail-card">
          <p className="error-text">{error}</p>
          <div className="detail-actions">
            <button
              type="button"
              className="btn btn-primary"
              onClick={() => navigate("/")}
            >
              Back to Search
            </button>
          </div>
        </div>
      </div>
    );
  }

  if (!stats) {
    return (
      <div className="page">
        <div className="card detail-card">
          <p>No statistics available yet.</p>
          <div className="detail-actions">
            <button
              type="button"
              className="btn btn-primary"
              onClick={() => navigate("/")}
            >
              Back to Search
            </button>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="page">
      <div className="card detail-card">
        <h1 className="detail-title">Search Statistics</h1>

        <div className="stats-grid">
          <div className="stats-card">
            <h3 className="stats-card-title">Total Searches</h3>
            <p className="stats-card-value">{stats.total_searches}</p>
          </div>

          <div className="stats-card">
            <h3 className="stats-card-title">Avg Response Time</h3>
            <p className="stats-card-value">
              {stats.avg_response_ms !== null
                ? `${stats.avg_response_ms} ms`
                : "N/A"}
            </p>
          </div>

          <div className="stats-card">
            <h3 className="stats-card-title">Busiest Hour</h3>
            <p className="stats-card-value">
              {stats.busiest_hour
                ? `${formatHour(stats.busiest_hour.hour)} (${stats.busiest_hour.hits} searches)`
                : "N/A"}
            </p>
          </div>
        </div>

        <div className="stats-section">
          <h2 className="stats-section-title">Top Queries</h2>
          {stats.top_queries.length > 0 ? (
            <ul className="stats-list">
              {stats.top_queries.map((query, index) => (
                <li key={index} className="stats-list-item">
                  <span className="stats-list-label">{query.term}</span>
                  <span className="stats-list-value">{query.hits} hits</span>
                </li>
              ))}
            </ul>
          ) : (
            <p className="muted-text">No queries recorded yet.</p>
          )}
        </div>

        <div className="stats-section">
          <h2 className="stats-section-title">Searches by Resource</h2>
          {stats.by_resource.length > 0 ? (
            <ul className="stats-list">
              {stats.by_resource.map((item, index) => (
                <li key={index} className="stats-list-item">
                  <span className="stats-list-label">{item.resource}</span>
                  <span className="stats-list-value">{item.hits} searches</span>
                </li>
              ))}
            </ul>
          ) : (
            <p className="muted-text">No data available.</p>
          )}
        </div>

        <p className="muted-text stats-generated">
          Generated at: {new Date(stats.generated_at).toLocaleString()}
        </p>

        <div className="detail-actions">
          <button
            type="button"
            className="btn btn-primary"
            onClick={() => navigate("/")}
          >
            Back to Search
          </button>
        </div>
      </div>
    </div>
  );
}
