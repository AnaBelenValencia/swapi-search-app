import { BrowserRouter, Routes, Route } from "react-router-dom";
import { SearchPage } from "../pages/SearchPage";
import { PersonDetailPage } from "../pages/PersonDetailPage";
import { MovieDetailPage } from "../pages/MovieDetailPage";
import { StatsPage } from "../pages/StatsPage";
import { NotFoundPage } from "../pages/NotFoundPage";
import { AppLayout } from "../components/layout/AppLayout";

export function AppRouter() {
  return (
    <BrowserRouter>
      <AppLayout>
        <Routes>
          <Route path="/" element={<SearchPage />} />
          <Route path="/people/:id" element={<PersonDetailPage />} />
          <Route path="/movies/:id" element={<MovieDetailPage />} />
          <Route path="/stats" element={<StatsPage />} />
          <Route path="*" element={<NotFoundPage />} />
        </Routes>
      </AppLayout>
    </BrowserRouter>
  );
}