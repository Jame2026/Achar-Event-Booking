# Achar Event Booking

Modern event & vendor booking platform built with Laravel 11 (API/backend) and Vue 3 + Vite (frontend). It ships an admin console for bookings, vendors, revenue, and dashboard views, along with customer/vendor APIs.

## Stack
- Laravel 11, PHP 8.2
- Vue 3, Vite 7, Vue Router 4
- Redis (Predis) & queues for background work
- Cloudinary SDK for media uploads
- SQLite by default (use MySQL/Postgres in production)

## Prerequisites
- PHP 8.2+ with `composer`
- Node 20+ (matching `package.json` engines) with `npm`
- SQLite (built in) or another database driver enabled

## Backend setup (Laravel API)
1. `cd backend`
2. Install deps: `composer install`
3. Copy env: `cp .env.example .env` (or `copy` on Windows)
4. Generate key: `php artisan key:generate`
5. Create database (SQLite default): `touch database/database.sqlite`
6. Migrate tables: `php artisan migrate`
7. (Optional) Link storage for local files: `php artisan storage:link`
8. Run the API: `php artisan serve`
   - Queues for notifications: `php artisan queue:listen --tries=1`

## Frontend setup (Vue app)
1. `cd frontend`
2. Install deps: `npm install`
3. Start dev server: `npm run dev` (Vite; uses port 5173 by default)
4. Build for production: `npm run build`

## One-command full dev loop
From `backend/`, run:
```
composer run dev
```
This uses `concurrently` to start Laravel, queue worker, log viewer (`pail`), and Vite together. Requires `npx` available (npm 7+).

## Key locations
- API controllers: `backend/app/Http/Controllers/Api`
- Core models: `backend/app/Models`
- Admin Vue pages: `frontend/src/components/pages`
- Shared booking mappers/helpers: `frontend/src/features`
- Public/About page: `frontend/src/components/AboutPage.vue`
- Legacy shell & routing host: `frontend/src/LegacyAppShell.vue`

## Testing & quality
- Backend: `phpunit` (from `backend/`: `vendor/bin/phpunit` after install)
- Frontend: not yet configured; add Vitest/Jest as needed.

## Deployment notes
- Configure your production database and queue drivers in `backend/.env`.
- Set `CLOUDINARY_*` keys if using Cloudinary image uploads; otherwise disable those features.
- Build the frontend (`npm run build`) and serve `frontend/dist` via your web server or Laravel/Vite SSR setup.

## Troubleshooting
- If you see `Unexpected token '<<'` in PHP, ensure merge conflicts are resolved (see `VendorController` fix).
- Ensure Node and PHP versions match the listed engine requirements to avoid install errors.

## Where to add/edit pages
- Frontend pages live in `frontend/src/components/pages` (admin) and `frontend/src/components/AboutPage.vue` (public). Mount or route them through `frontend/src/LegacyAppShell.vue` or your Vue Router config.
- Backend is API-only; add new endpoints in `backend/app/Http/Controllers/Api`, register routes in `backend/routes/api.php`, and consume them from the Vue app via the API client in `frontend/src/features/apiClient.ts`.
## Database 

- We using SQL that open with Largon 