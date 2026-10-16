# Deployment guide

The site runs on any host with **PHP 8.3+**, **MySQL 8 / MariaDB 10.6+**, Composer, and (for building assets) Node 20+.
Hosting and email details were not yet confirmed when this was written; both common setups are covered.

---

## 1. Environment variables (`.env`)

| Variable | Production value |
|---|---|
| `APP_ENV` | `production` (also switches on indexing and the real robots.txt) |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://itsecuritynepal.com` (no trailing slash; canonical URLs and the sitemap use it) |
| `APP_KEY` | generate once with `php artisan key:generate` |
| `DB_CONNECTION` / `DB_HOST` / `DB_PORT` / `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` | `mysql` + your database credentials |
| `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_SCHEME` | SMTP account that sends enquiry notifications (e.g. the hosting mailbox, Google Workspace, Brevo) |
| `MAIL_FROM_ADDRESS` / `MAIL_FROM_NAME` | e.g. `website@itsecuritynepal.com` / `IT Security Nepal website` |
| `ENQUIRY_NOTIFY_EMAIL` | inbox that receives new enquiries, e.g. `info@itsecuritynepal.com` |
| `ADMIN_NAME` / `ADMIN_EMAIL` / `ADMIN_PASSWORD` | first admin account, used once by `db:seed` (then remove the password from `.env`) |
| `SESSION_SECURE_COOKIE` | `true` once HTTPS is on |
| `GA4_MEASUREMENT_ID` | optional; can also be set in Admin → Site settings → Analytics |

Never commit `.env`. **After every change to `.env` on the server, run `php artisan optimize`** (config is cached). Enquiries are always saved in the database even if email sending fails (the failure is logged).

## 2. Build on your computer (the server never compiles anything)

The server needs **only PHP 8.3+**. Node/npm are never needed there, and with option A Composer isn't either.

**A. Upload a zip (FTP / cPanel File Manager)**
```powershell
powershell -ExecutionPolicy Bypass -File scripts\make-release.ps1
```
This compiles CSS/JS, installs production PHP packages in a clean copy and writes `release/itsn-laravel-<date>.zip`
(app + `vendor/` + `public/build` + admin assets + stock photos). It never includes `.env`, `node_modules`, `public/hot`,
the local database, logs or tests. Upload and extract the zip, keeping the server's existing `.env` and `storage/app/public` (uploaded photos).

**B. Git repository**
Compiled files are committed (`public/build`, `public/css|js|fonts/filament` are no longer git-ignored).
Before every commit that changes `resources/css` or `resources/js`, run `npm run build` locally and commit `public/build` too.
On the server: `git pull` then `composer install --no-dev --optimize-autoloader` (or use option A if the host has no Composer).

Never upload `public/hot`: it exists only while `npm run dev` runs and makes the site load assets from your computer.

## 3a. Shared hosting (cPanel)

1. Create a MySQL database + user in cPanel.
2. Upload the project **outside** `public_html`, e.g. `/home/USER/itsn-laravel/`.
3. Point the domain's document root to `/home/USER/itsn-laravel/public` (cPanel → Domains). If the root cannot be changed, copy the contents of `public/` into `public_html` and edit the two paths in `public_html/index.php` to `../itsn-laravel/vendor/autoload.php` and `../itsn-laravel/bootstrap/app.php`.
4. Create `.env` from **`.env.production.example`** (never from `.env.example`: it has local values `APP_URL=http://localhost:8000`, `APP_DEBUG=true`). Set `APP_URL` to the real address (staging: `https://new.itsecuritynepal.com`).
5. In cPanel → Terminal (or SSH):
   ```bash
   php artisan key:generate
   php artisan config:clear
   php artisan migrate --force --seed
   php artisan storage:link
   php artisan optimize
   php artisan filament:optimize
   ```
6. Security files (already in the release):
   - `public/.htaccess`: forces HTTPS and `www` → `itsecuritynepal.com`, blocks hidden/config files and PHP in upload folders, sends security headers (HSTS, CSP, X-Frame-Options, Referrer-Policy, Permissions-Policy), caching and compression.
     **Make sure SSL (cPanel → SSL/TLS Status → AutoSSL) is active first.** On a staging domain without SSL, comment out the *Force HTTPS* rule and the HSTS header.
     If you later add a third-party script, embed or font (e.g. a Google Map), add its domain to the `Content-Security-Policy` line or the browser will block it.
   - `storage/app/public/.htaccess`: uploaded files can never run as scripts (served at `/storage/`).
   - `.htaccess` in the project root: a safety net that keeps `.env`, `vendor/` etc. private if the whole project is ever placed inside `public_html`.
   - `public/.user.ini`: PHP limits for 4 MB photo uploads, errors hidden from visitors. If your host ignores it, set the same values in cPanel → MultiPHP INI Editor.
   - After going live, check the headers at https://securityheaders.com (expected grade A).
7. **Cron: optional.** Email campaigns are sent from the admin's *Email campaigns* page while it is open, so nothing else is required.
   If you want them to keep sending with the page closed (and scheduled emails to go out on time unattended), add in cPanel → Cron Jobs, every minute:
   `* * * * * cd /home/USER/itsn-laravel && php artisan schedule:run >> /dev/null 2>&1`
   The page then shows "Automatic sending on". Queue worker: not needed. Sending speed is set in Admin → Site settings → Pop-ups → Newsletter.
8. Instructor portal: `/trainer` (no extra setup). Optional: `GOOGLE_PLACES_API_KEY` for Google reviews (restrict the key to *Places API (New)* and your server in Google Cloud).
9. After updating an existing site: `php artisan migrate --force`, then `php artisan db:seed --class=CareerPathwaysSeeder --force` once (fills empty career-pathway texts only; safe on live data).
8. **Every page shows "403 Forbidden" but images/CSS load?** File permissions are too open (777/666), and LiteSpeed refuses to run world-writable PHP. Older release zips (made with `tar` before 2026-10-16) caused this; the current `make-release.ps1` does not. Fix in cPanel → Terminal:
   ```bash
   cd ~/new.itsecuritynepal.com   # the project folder
   find . -type d -exec chmod 755 {} +
   find . -type f -exec chmod 644 {} +
   ```
   Also check: hidden files are visible in File Manager (Settings → Show Hidden Files) and `public/.htaccess` exists; cPanel → Errors shows the exact reason if it persists.
9. **"Composer detected issues… PHP >= 8.3", or every `.php` file returns 403/404 while `.txt` files load?** The PHP version/handler is wrong. On the current host (CloudLinux + LiteSpeed) the working setup is: **no** `AddHandler` block in `public/.htaccess` (the MultiPHP `ea-php84` handler gave 403, `alt-php84` gave 404), and PHP 8.4 set in cPanel → **Select PHP Version** with the extensions `bcmath ctype curl fileinfo gd intl mbstring openssl pdo_mysql tokenizer xml zip`. If MultiPHP Manager writes a handler block back into `public/.htaccess`, remove it. Test with a one-line `test.php` (`<?php echo PHP_VERSION;`), then delete it. In Terminal, check `php -v` shows 8.3+ before running artisan.
10. **500 error right after admin login:** run `php artisan migrate --force && php artisan optimize:clear && php artisan optimize`; if it persists, read `storage/logs/laravel.log`.

## 3b. VPS (Ubuntu + Nginx + PHP-FPM)

```nginx
server {
    listen 443 ssl http2;
    server_name itsecuritynepal.com www.itsecuritynepal.com;
    root /var/www/itsn-laravel/public;
    index index.php;
    # ssl_certificate … (certbot)

    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }
    location ~* \.(css|js|woff2|png|jpg|webp|svg)$ { expires 30d; add_header Cache-Control "public"; }
    location ~ /\.(?!well-known) { deny all; }
}
server { listen 80; server_name itsecuritynepal.com www.itsecuritynepal.com; return 301 https://itsecuritynepal.com$request_uri; }
```

Then run the same artisan commands as in 3a step 5, and give `storage/` and `bootstrap/cache/` write access to the web user.
Trailing slashes are handled by the app (`TrailingSlash` middleware), so no extra Nginx rewrite is needed.

## 4. Updating the site later

```bash
git pull   # or upload + extract a new release zip (section 2A)
composer install --no-dev --optimize-autoloader   # git only; the zip already contains vendor/
php artisan migrate --force
php artisan optimize && php artisan filament:optimize
```

Content changes (courses, batches, blog, page text, photos, settings) are made in `/admin` and need no deployment.
Uploaded photos live in `storage/app/public` (served through `public/storage`; run `php artisan storage:link` once per server). Back that folder up together with the database.
**Do not re-run `db:seed` on production after launch:** it re-imports the original content and would overwrite edits made in the admin panel.

## 5. Launch checklist

**Before switching DNS**
- [ ] Confirm business facts in Admin → Site settings: main phone, **WhatsApp number**, hours (incl. Saturday), address.
- [ ] Key facts: verify the student count (Home 1,580+ vs About 750+), then switch on **Verified**. Unverified facts stay hidden.
- [ ] Batches: replace sample rows (marked "Sample data") with real dates or unpublish them.
- [ ] Course levels, certifications and durations reviewed (Admin → Courses).
- [ ] Enter the Privacy Policy and Terms text (Admin → Legal pages). They are `noindex` until text exists.
- [ ] Photos: the site ships with free Unsplash stock photos (`storage/app/public/stock/`, licence and sources in `database/seeders/data/stock-photos.json`; free for commercial use, no attribution required). Replace with real photos where possible: Admin → Page content (homepage, About, Group, Corporate), Tracks, Courses, Blog posts.
- [ ] Copy `storage/app/public/stock/` to the server and run `php artisan storage:link`.
- [ ] Add instructor profiles (Admin → Instructors) and genuine testimonials with consent, or leave them empty (the site shows honest fallbacks).
- [ ] Send a test enquiry and confirm the email arrives at `ENQUIRY_NOTIFY_EMAIL`.
- [ ] `APP_ENV=production`, `APP_DEBUG=false`, HTTPS forced, `SESSION_SECURE_COOKIE=true`.
- [ ] Back up the current WordPress site (files and database) and keep it for at least 3 months.

**At launch**
- [ ] Point DNS to the new server; check `https://itsecuritynepal.com/robots.txt` shows `Sitemap:` and not `Disallow: /`.
- [ ] Spot-check old URLs: `/redhat/`, `/red-hat-system-administration/`, `/rhcsa-certification-guide/`, `/devops-essential/` (→ `/devops-mastery/`).
- [ ] Google Search Console: verify the domain, submit `https://itsecuritynepal.com/sitemap.xml`, request indexing for the homepage.
- [ ] Test rich results for a course page and the FAQ page (Rich Results Test).
- [ ] Run PageSpeed Insights on the homepage and a course page (mobile); targets LCP ≤ 2.5 s, INP ≤ 200 ms, CLS ≤ 0.1.

**First weeks**
- [ ] Watch Search Console → Pages → "Not found (404)"; add any missing old URLs in Admin → Redirects.
- [ ] Check Admin → Enquiries daily; mark leads as contacted / enrolled.
