# Achar Event Booking

Achar Event Booking is a split-stack project with:

- `frontend/`: Vue 3 + Vite SPA
- `backend/`: Laravel 11 API

## Project Setup

Use this flow to run the project locally for development.

## 1. Prerequisites

Install these first:

- PHP 8.2+
- Composer
- Node.js 20+
- npm
- MySQL or SQLite

Optional for extra features:

- Redis
- Cloudinary account
- Google OAuth credentials

## 2. Set Up the Backend

From the `backend/` folder:

```bash
composer install
npm ci
cp .env.example .env
php artisan key:generate
```

On Windows, use `copy .env.example .env` instead of `cp`.

Configure `backend/.env`:

- Keep `APP_URL=http://127.0.0.1:8000`
- Set `FRONTEND_URL=http://localhost:5173`
- Choose a database

For SQLite, keep `DB_CONNECTION=sqlite` and create `backend/database/database.sqlite`.

For MySQL, set:

- `DB_CONNECTION=mysql`
- `DB_HOST=127.0.0.1`
- `DB_PORT=3306`
- `DB_DATABASE=your_database_name`
- `DB_USERNAME=your_database_user`
- `DB_PASSWORD=your_database_password`

Leave `GOOGLE_*` and `CLOUDINARY_*` empty unless you want those features locally.

Then run:

```bash
php artisan migrate
php artisan storage:link
```

Notes:

- No default seeders are included right now.
- `npm ci` in `backend/` installs the Laravel Vite tooling. It is useful for the Laravel welcome page and asset builds, but the main app still runs through `frontend/`.

## 3. Set Up the Frontend

From the `frontend/` folder:

```bash
npm ci
cp .env.example .env.local
```

On Windows, use `copy .env.example .env.local` instead of `cp`.

Set `frontend/.env.local` like this:

```env
VITE_API_URL=http://127.0.0.1:8000
VITE_AUTH_PROXY_BASE=/backend-auth
```

This lets the Vite dev server proxy `/api/*` and `/backend-auth/*` to Laravel during local development.

## 4. Run the Project Locally

Start the backend:

```bash
cd backend
php artisan serve
```

Start the frontend in a second terminal:

```bash
cd frontend
npm run dev
```

Open:

- Frontend: `http://localhost:5173`
- Backend API health: `http://127.0.0.1:8000/api/health`

## 5. Local Setup Checklist

After startup, verify:

1. The frontend opens on `http://localhost:5173`.
2. `http://127.0.0.1:8000/api/health` returns `{"status":"ok"}`.
3. Registration and login work.
4. Bookings and event lists load from the backend.
5. Password reset works after mail settings are configured.

## Deployment Process

This project should be deployed as two parts:

1. A static frontend build from `frontend/`
2. A Laravel backend from `backend/`

Important: the frontend must be able to reach the backend through the same-origin paths `/api/*` and `/backend-auth/*`. Several frontend screens call `/api/...` directly, so deployment will break if those paths are not proxied or rewritten to the backend.

## Recommended Production Setup

- Frontend: Vercel, Netlify, Nginx, or any static hosting that supports SPA rewrites
- Backend: VPS, Render, Laravel Forge, or any PHP 8.2+ hosting
- Database: MySQL recommended
- Optional services: Redis, Cloudinary, Google OAuth

## 1. Prepare Production Services

Before deploying, prepare:

- A frontend domain, for example `https://app.example.com`
- A backend domain, for example `https://api.example.com`
- A production database
- PHP 8.2+
- Composer
- Node.js 20+

## 2. Deploy the Backend

From the `backend/` folder:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

Set these production values in `backend/.env`:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://api.example.com`
- `FRONTEND_URL=https://app.example.com`
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `MAIL_*` values if password reset emails should work
- `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI=https://api.example.com/auth/google/callback` if Google login is enabled
- `EVENT_IMAGE_DISK=cloudinary` plus `CLOUDINARY_*` values if event images should use Cloudinary

Notes:

- If you keep `EVENT_IMAGE_DISK=public`, make sure `php artisan storage:link` is run.
- Redis is optional unless you choose to use it for cache or queues.
- Keep real `.env` secrets out of git.

## 3. Expose Backend Routes

Your backend server must serve:

- `/api/*` for the Laravel API
- `/auth/*` for Google OAuth redirects and callbacks

Useful health checks after deploy:

- `https://api.example.com/api/health`
- `https://api.example.com/api/health/redis`

## 4. Deploy the Frontend

From the `frontend/` folder:

```bash
npm ci
npm run build
```

Set production frontend env values:

```env
VITE_API_URL=/api
VITE_AUTH_PROXY_BASE=/backend-auth
```

Deploy the generated `frontend/dist/` folder to your static host.

## 5. Add Frontend Rewrites / Proxy Rules

The frontend host must rewrite or proxy these paths to the backend:

- `/api/:path*` -> `https://api.example.com/api/:path*`
- `/backend-auth/:path*` -> `https://api.example.com/auth/:path*`
- `/:path*` -> `/index.html` for Vue Router SPA routing

If you deploy the frontend on Vercel, update `frontend/vercel.json` so the backend destination points to your real backend URL instead of a temporary IP.

## 6. Final Verification

After both parts are deployed:

1. Open the frontend home page.
2. Confirm the frontend can load data from `/api/health`.
3. Test register and login.
4. Test booking creation and vendor/admin pages.
5. Test password reset email flow.
6. Test Google sign-in if OAuth is enabled.
7. Test image upload if Cloudinary or local storage is enabled.

## Quick Summary

- Deploy Laravel from `backend/`
- Deploy the built Vue app from `frontend/dist/`
- Point `/api/*` and `/backend-auth/*` from the frontend host to the backend
- Set `APP_URL`, `FRONTEND_URL`, database, mail, and optional OAuth/media secrets correctly
