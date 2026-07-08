// Client API typé de la brique Calendrier. Endpoints sous /melis/react-api/calendar-events.
// Réponse : { success, data, error }.

const XHR_HEADER = { 'X-Requested-With': 'XMLHttpRequest' }

async function apiFetch<T>(url: string, opts: RequestInit = {}): Promise<T> {
  const res = await fetch(url, {
    credentials: 'include',
    ...opts,
    headers: { ...XHR_HEADER, ...(opts.headers || {}) },
  })
  const txt = await res.text()
  let json: { success?: boolean; data?: T; error?: string } | null = null
  try { json = txt ? JSON.parse(txt) : null } catch { /* non-JSON */ }
  if (!res.ok || !json || json.success === false) {
    throw new Error((json && json.error) || `HTTP ${res.status}`)
  }
  return json.data as T
}

export interface CalEvent { id: number; title: string; start: string; end: string } // dates YYYY-MM-DD
export interface CalStats { total: number; ongoing: number; upcoming: number }

export function fetchEvents(range?: { from?: string; to?: string }): Promise<CalEvent[]> {
  const qs = new URLSearchParams()
  if (range?.from) qs.set('from', range.from)
  if (range?.to) qs.set('to', range.to)
  const q = qs.toString()
  return apiFetch<{ items: CalEvent[] }>(`/melis/react-api/calendar-events${q ? '?' + q : ''}`).then((d) => d.items)
}

export const fetchCalStats = () => apiFetch<CalStats>('/melis/react-api/calendar-events/stats')

export const saveEvent = (payload: { id?: number | null; title: string; start: string; end: string }) =>
  apiFetch<{ id: number }>('/melis/react-api/calendar-events/save', {
    method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload),
  })

export const deleteEvent = (id: number) =>
  apiFetch<null>(`/melis/react-api/calendar-events/delete/${id}`, { method: 'DELETE' })
