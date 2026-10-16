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
php artisan storage:link      # makes photos in storage/app/public visible at /storage (stock + uploaded photos)
npm run build                 # or `npm run dev` for hot reload
php artisan serve             # http://localhost:8000  ·  admin: http://localhost:8000/admin
```

Tests: `php artisan test` (54 tests, incl. roles, attendance, fees, campaigns, newsletter, SEO; public pages and SEO, redirects, enquiry validation/spam/rate-limit/email, page content editor, admin screens, dashboard).

## Server setup (cPanel) in short

Full guide: **`docs/DEPLOYMENT.md`**. The domain's document root must be the **`public/`** folder.

1. Build the release on your computer: `powershell -ExecutionPolicy Bypass -File scripts\make-release.ps1`, then upload and extract the zip.
2. Create `.env` from **`.env.production.example`** (not `.env.example`, which has local values such as `APP_URL=http://localhost:8000` and `APP_DEBUG=true`).
   Set `APP_URL` to the real address, e.g. `https://new.itsecuritynepal.com`, plus the database and email details.
3. In cPanel → Terminal, from the project folder:
   ```bash
   php artisan key:generate
   php artisan config:clear
   php artisan migrate --force --seed    # first install only (see "Admin users")
   php artisan storage:link              # required: photos are served from /storage
   php artisan optimize
   php artisan filament:optimize
   ```
4. **Every time you edit `.env` later, run `php artisan optimize` again**: the configuration is cached and changes are ignored until then.

If `storage:link` fails on shared hosting ("symlink() has been disabled"), ask the host to enable `symlink()`, or create the link in cPanel → Terminal:
`ln -s ../storage/app/public public/storage`.

## Admin users

The admin panel is at `/admin` (e.g. `https://new.itsecuritynepal.com/admin`). Run these commands from the project folder (where `artisan` is); on cPanel use **Terminal**.

**First admin: created automatically during setup**

1. Add to `.env`:
   ```
   ADMIN_NAME="Your Name"
   ADMIN_EMAIL=you@itsecuritynepal.com
   ADMIN_PASSWORD=choose-a-strong-password
   ```
2. Run (fresh database only):
   ```bash
   php artisan config:clear          # the seeder reads .env; a cached config hides these values
   php artisan migrate --force --seed
   ```
   It prints `Admin user ready: …`. If it prints `ADMIN_EMAIL / ADMIN_PASSWORD not set`, check `.env` and run `php artisan config:clear` again.
3. Remove the `ADMIN_PASSWORD` line from `.env`, then run `php artisan optimize`.

**Never run `--seed` again after launch:** it re-imports the original content and overwrites edits made in the admin panel.

**More admins, at any time**
```bash
php artisan make:filament-user
```
Asks for name, email and password; the password is not stored in any file.

**Forgotten password**
- Use *Forgot password?* on the login page (needs working email / SMTP in `.env`), or
- set a new one from the terminal:
  ```bash
  php artisan tinker --execute="App\Models\User::where('email', 'you@itsecuritynepal.com')->first()->update(['password' => 'new-strong-password']);"   # hashed automatically
  ```
  Then clear the terminal history if the host keeps it (`history -c`).

All admin users have full access. Remove someone by deleting their row in the `users` table (or ask the developer).

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
| Roles and permissions | `app/Support/Access.php` (role → areas), `App\Filament\Concerns\RestrictedToArea`, `User::canAccessPanel` |
| Instructor portal | `/trainer`: `app/Providers/Filament/TrainerPanelProvider.php`, `app/Filament/Trainer/**`, attendance page `app/Filament/Pages/TakeAttendance.php` |
| Students, fees, payments | `Student` (fee fields), `Payment`, `app/Filament/Resources/Students/Schemas/FeeFields.php`, Fee collection `app/Filament/Resources/Payments` |
| Pop-ups, campaigns, newsletter | `partials/popups.blade.php`, `sitePopup` in `resources/js/itsn.js`, `App\Support\Popups`, `Campaign`, `Subscriber`, `EmailCampaign`, `newsletter:send` command (scheduler) |
| SEO | `SeoMeta` + `HasSeo` trait, `App\Filament\Schemas\SeoFields`, layout head in `layouts/app.blade.php`, sitemap in `SeoController` |
| Google reviews | `App\Support\GoogleReviews`, `<x-google-reviews>` |
| Settings (contact, offer bar, stats, audiences, analytics) | `settings` table, edited in Admin → Settings → Site settings |

See **`docs/DEPLOYMENT.md`** to go live and **`docs/ADMIN-GUIDE.md`** for staff.
