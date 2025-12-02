export type HttpMethod = 'GET' | 'POST' | 'PUT' | 'DELETE';

const API_BASE_URL = (import.meta.env.VITE_API_BASE_URL ??
  "http://localhost:8000/api"
).replace(/\/+$/, "");

export async function request<T>(
  path: String,
  options: { method?: HttpMethod; body?: unknown } = {}
): Promise<T> {
  const normalizedPath = path.replace(/^\/+/, "");
  const url = `${API_BASE_URL}/${normalizedPath}`;
  
  const { method = 'GET', body } = options;

  const response = await fetch(url, {
    method,
    headers: {
      "content-type": "application/json",
      Accept: "application/json",
    },
    body: body ? JSON.stringify(body) : undefined,
  });

  if (!response.ok) {
    const errorText = await response.text().catch(() => "");
    throw new Error(`Request failed with status ${response.status}: ${errorText || response.statusText}`);
  }

  return response.json() as Promise<T>;
}