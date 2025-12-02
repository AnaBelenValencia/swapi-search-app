import { useNavigate} from "react-router-dom";
import type { SearchItem } from "../api/search";

interface ResultsListProps {
  items: SearchItem[];
}

export function ResultsList({ items }: ResultsListProps) {
  const navigate = useNavigate();

  if (!items.length) {
    return (
      <div className="results-empty">
        <p>There are zero matches.</p>
        <p>Use the form to search for People or Movies.</p>
      </div>
    );
  }

  const handleSeeDetails = (item: SearchItem) => {
    if (item.type === 'people') {
      navigate(`/people/${item.id}`);
    } else if (item.type === "films") {
      navigate(`/movies/${item.id}`);
    }
  };

  return (
    <ul className="results-list">
      {items.map((item) => (
        <li key={`${item.type}-${item.id}`} className="results-list-item">
          <span className="results-list-label">{item.label}</span>
          <button type="button" className="results-list-button" onClick={() => handleSeeDetails(item)}>SEE DETAILS</button>
        </li>
      ))}
    </ul>
  );
}