# IT Security Nepal — website (Laravel)

Production implementation (Phase 2) of the approved Theme E design from `../itsn-prototype/`.

- **Stack:** Laravel 13, Blade, Tailwind CSS 4 (Vite), Alpine.js 3, Filament 5 admin, MySQL/MariaDB (SQLite locally).
- **Public site:** all live-site URLs are kept (trailing slashes, legacy slugs). 43 courses, 8 tracks, 6 blog posts imported.
- **Admin panel:** `/admin` for courses, tracks, batches, learning paths, training methods, blog, FAQs, instructors, testimonials, legal pages, redirects, site settings, and the **enquiry inbox**.

## Quick start (local)

```bash
composer install
npm install
cp .env.example .env          # then set ADMIN_EMAIL / ADMIN_PASSWORD for a local admin
php artisan key:generate
php artisan migrate --seed    # creates tables, imports content, creates the admin user
npm run build                 # or `npm run dev` for hot reload
php artisan serve             # http://localhost:8000  ·  admin: http://localhost:8000/admin
```

Tests: `php artisan test` (33 tests: public pages and SEO, redirects, enquiry validation/spam/rate-limit/email, admin screens).

## Where things live

| Area | Files |
|---|---|
| Routes | `routes/web.php` (one catch-all `/{slug}/` resolves track → course → post → training method → legal page) |
| Controllers | `app/Http/Controllers/SiteController.php`, `EnquiryController.php`, `SeoController.php` |
| Trailing slashes / redirects | `app/Http/Middleware/TrailingSlash.php`, redirect lookup in `bootstrap/app.php` |
| Models | `app/Models/*` (list content stored as JSON, edited with Filament repeaters) |
| Content import | `database/seeders/ContentImportSeeder.php` + `database/seeders/data/*.json` |
| Layout / partials / components | `resources/views/layouts`, `partials`, `components` |
| Pages | `resources/views/pages/*.blade.php` |
| Design tokens | `resources/css/app.css` (unchanged from the approved prototype) |
| Front-end behaviour | `resources/js/itsn.js` (Alpine components) |
| Admin | `app/Filament/**`, `app/Providers/Filament/AdminPanelProvider.php` |
| Page text and photos (every page) | `app/Support/PageContent.php` (defaults) + `content()` / `media()` helpers in `app/helpers.php`; edited in Admin → Content → Page content (saved as `page_*` settings) |
| Stock photos | `storage/app/public/stock/*.webp` (Unsplash, sources in `database/seeders/data/stock-photos.json`) |
| Settings (contact, offer bar, stats, audiences, analytics) | `settings` table, edited in Admin → Settings → Site settings |

See **`docs/DEPLOYMENT.md`** to go live and **`docs/ADMIN-GUIDE.md`** for staff.
