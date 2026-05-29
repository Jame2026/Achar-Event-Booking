# Admin Data Store Workflow (Vue 3)

This file explains how `frontend/src/features/useAdminDataStore.js` orchestrates the admin dashboard data flow. It is meant to help you describe the code to a teacher or teammate.

## Purpose
- Provide a single source of truth for admin-facing data: summary stats, bookings, events, users, and system health.
- Handle pagination, normalization, and fallback API paths so views stay simple.
- Expose computed error state and utility mutations (update/remove) for event rows.

## High-level flow
1. **Reactive state** – `state`, `loading`, `loaded`, and `errors` track data, fetch status, and error messages per section. `error` is a computed aggregate of any error string.
2. **Fetch helpers**
   - `apiGet` performs HTTP GETs.
   - `fetchPagedRows(endpoint, query)` auto-paginates up to 40 pages and flattens results.
   - Small helpers normalize roles, booking status, and merge user sets when different APIs are used.
3. **Section loaders**
   - `loadSummary` calls `admin/dashboard`. On failure, it resets to defaults; when `loadAll` later sees a summary error, it rebuilds summary from already-fetched users/bookings/events so the dashboard still shows numbers.
   - `loadBookings` tries `admin/bookings`, falls back to public `bookings`.
   - `loadEvents` pulls up to 200 events including inactive ones.
   - `loadUsers` prefers `admin/users`; if that fails and an `adminUserId` is provided, it combines vendors and customer directory data, assigning roles before merging duplicates by id/email.
   - `loadHealth` checks `health/redis`, then falls back to `health`.
4. **Bulk loader**
   - `loadAll({ force, adminUserId })` runs all section loaders in parallel, dedupes concurrent calls with `inFlight`, and throttles reloads to once per minute unless forced. After all loads, it synthesizes a summary if the API one failed and stamps `lastLoadedAt`.
5. **Mutations for UI**
   - `updateEvent(updated)` patches a single event in place (keeps vendor/bookings fields when missing).
   - `removeEvent(id)` deletes an event from the local list.

## Why it matters
- **Resilience:** Each loader has fallbacks (alt endpoints, recomputed summary) so the admin UI degrades gracefully instead of breaking.
- **Consistency:** Shared helpers normalize roles and booking status, preventing mismatched counts across widgets.
- **Performance:** Pagination batching and the one-minute throttle avoid hammering the API while keeping data fresh.
- **Simplicity for views:** Components can call `useAdminDataStore()` and rely on ready-made state/error flags instead of reimplementing fetch logic.

## Typical usage in a component
```js
import { onMounted } from "vue";
import { useAdminDataStore } from "@/features/useAdminDataStore";

const store = useAdminDataStore();

onMounted(() => {
  store.loadAll({ adminUserId: currentAdminId });
});

// template can bind to store.state.*, store.loading.*, store.error, and call updateEvent/removeEvent
```

## Talking points for a presentation
- The store centralizes admin data fetching and normalization.
- Parallel loaders plus safe fallbacks keep the dashboard usable even when some endpoints fail.
- Role/status normalization ensures counts (users, bookings, revenue, service fees) stay consistent across widgets.
- The computed summary rebuild is a safety net: the dashboard still shows numbers using already-fetched lists.
- Throttling prevents accidental API overload while keeping data reasonably fresh.
