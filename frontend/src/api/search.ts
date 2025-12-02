import { request } from "./client";

export type ResourceType = "people" | "films";

export interface SearchItem {
  id: string;
  type: ResourceType;
  label: string;
  subtitle?: string;
}

export interface SearchMeta {
  page: number;
  perPage: number;
  total: number;
  totalPages: number;
  resource: ResourceType;
  query: string | null;
  hasNext: boolean;
  hasPrevious: boolean;
  responseTimeMs: number;
}

export interface SearchResponse {
  data: SearchItem[];
  meta: SearchMeta;
}

export async function searchResources(
  resource: ResourceType,
  query: string,
  page: number = 1,
  limit: number = 10
): Promise<SearchResponse> {
  const params = new URLSearchParams();
  
  params.set("resource", resource);
  if (query) {
    params.set("query", query);
  }
  params.set("page", String(page));
  params.set("limit", String(limit));

  return request<SearchResponse>(`search?${params.toString()}`);
}
