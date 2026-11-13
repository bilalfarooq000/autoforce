# Deploying to A2 Hosting (cPanel)

## 1. Requirements on your A2 plan
- PHP 8.2+ (cPanel → **MultiPHP Manager** → select PHP 8.2/8.3 for the domain)
- PHP extensions: `gd` (QR codes) and `zip`, `pdo_mysql`, `fileinfo` (usually on by default on A2) — enable any missing ones in cPanel → **MultiPHP INI Editor**
- MySQL database (cPanel → **MySQL Databases**)
- SSH access is strongly preferred (A2 shared/reseller plans include SSH on request). Without SSH, Composer and `artisan` commands must be run locally and only the resulting files uploaded.

## 2. Prepare the build locally
```bash
composer install --no-dev --optimize-autoloader
npm run build
```

## 3. Upload
Upload everything **except** `node_modules` and `.git`. Two layout options:

- **Preferred:** put the whole `autoforce-portal` folder above `public_html` (e.g. `/home/USER/autoforce-portal`), then point the domain's document root to `autoforce-portal/public` (cPanel → **Domains** → Document Root). This keeps `.env`, `app/`, `storage/` outside the web-servable folder.
- **If you can't change document root:** upload `public/` contents into `public_html`, upload everything else one level above it, and edit `public_html/index.php` so `__DIR__.'/../vendor/autoload.php'` and `__DIR__.'/../bootstrap/app.php'` point to the real location.

## 4. Database
Create a MySQL database + user in cPanel, then set in `.env` on the server:
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cpaneluser_autoforce
DB_USERNAME=cpaneluser_autoforce
DB_PASSWORD=your-strong-password
```

## 5. One-time setup (via SSH)
```bash
php artisan key:generate
php artisan migrate --force
php artisan db:seed --class=RoleSeeder --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
If there's no SSH, run `php artisan storage:link` locally, then manually upload the resulting `public/storage` as a real copy (not a symlink — most shared hosts don't allow symlinks created this way through FTP) pointing at `storage/app/public`.

## 6. File upload limits
Inspection photos/videos are capped at 50MB per file in code (`StoreInspectionItemRequest` / `InspectionController`). Match this in cPanel → **MultiPHP INI Editor**: set `upload_max_filesize` and `post_max_size` to at least `60M`.

## 7. Cron (for future phases: service reminders, WhatsApp/SMS)
Not required for Phase 1. When added, use cPanel → **Cron Jobs**:
```
* * * * * php /home/USER/autoforce-portal/artisan schedule:run >> /dev/null 2>&1
```

## 8. Local → production parity
Locally this project uses SQLite for speed. The code is written against Eloquent/Laravel, not raw SQLite-specific SQL, so switching `DB_CONNECTION` to `mysql` in `.env` is the only change needed.
