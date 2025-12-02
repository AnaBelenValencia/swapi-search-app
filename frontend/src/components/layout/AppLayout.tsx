import type { PropsWithChildren } from "react";
import { Link } from "react-router-dom";

export function AppLayout({ children }: PropsWithChildren) {
  return (
    <div className="app-root">
      <header className="app-header">
        <div className="header-content">
          <Link to="/" className="header-logo">
            <h1>SWStarter</h1>
          </Link>
          <nav className="header-nav">
            <Link to="/stats" className="header-nav-link">Stats</Link>
          </nav>
        </div>
      </header>
      <main className="app-main">
        {children}
      </main>
    </div>
  );
}