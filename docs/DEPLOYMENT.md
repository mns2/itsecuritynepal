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

Never commit `.env`. Enquiries are always saved in the database even if email sending fails (the failure is logged).

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
4. Create `.env` from `.env.example` with the values above.
5. In cPanel → Terminal (or SSH):
   ```bash
   php artisan key:generate
   php artisan migrate --force --seed
   php artisan storage:link
   php artisan optimize
   php artisan filament:optimize
   ```
6. In `public/.htaccess`, uncomment the **Force HTTPS** lines after the SSL certificate (AutoSSL) is active.
7. Queue: not required (emails are sent immediately). Cron: not required.

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
