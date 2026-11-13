# Deploying to Railway (free trial)

This repo includes a `Dockerfile` — Railway auto-detects it and builds the whole app
(Apache + PHP 8.3 + the compiled frontend assets) from a single `git push`.

## 1. Sign up & create the project
1. Go to railway.com → **Login** → **Login with GitHub** (no separate password; uses your GitHub account).
2. **New Project** → **Deploy from GitHub repo** → pick this repo.
3. Railway finds the `Dockerfile` automatically and starts a build — it will fail on the
   first deploy because there's no database/env vars yet. That's expected; continue below.

## 2. Add a MySQL database
In the project → **+ New** → **Database** → **Add MySQL**. Railway provisions it and
exposes connection variables automatically (`MYSQLHOST`, `MYSQLPORT`, `MYSQLDATABASE`,
`MYSQLUSER`, `MYSQLPASSWORD`).

## 3. Add a persistent volume (for uploaded photos/videos)
On the **web service** (not the database) → **Settings** → **Volumes** → **New Volume**.
- Mount path: `/data`

Without this, every redeploy wipes uploaded inspection photos — the app's entrypoint
script automatically links `storage/app/public` to `/data/app/public` when it finds this
mount, so this one setting is what makes uploads survive deploys.

## 4. Set environment variables
On the **web service** → **Variables**, add these (use Railway's "reference a variable
from another service" picker for the `${{MySQL.*}}` ones, don't retype them by hand):

```
APP_NAME=AutoForce UK
APP_ENV=production
APP_DEBUG=false
APP_KEY=                      # generate locally: php artisan key:generate --show
APP_URL=https://${{RAILWAY_PUBLIC_DOMAIN}}

DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
FILESYSTEM_DISK=public

LOG_CHANNEL=stack
LOG_LEVEL=error

MAIL_MAILER=log
```

`MAIL_MAILER=log` keeps emails working exactly like local dev (logged, not sent) unless
you have real SMTP credentials to drop in later (`MAIL_MAILER=smtp` + host/port/user/pass).

## 5. Generate APP_KEY
Run once, locally, to get a key to paste into the `APP_KEY` variable above:
```bash
php artisan key:generate --show
```

## 6. Deploy & first migration
Saving the variables triggers a redeploy. The entrypoint script runs
`php artisan migrate --force` automatically on every boot, so the schema is created on
first deploy — no separate step needed. Seed the roles once, via Railway's web shell
(service → **⋮** → **Shell**) or the Railway CLI:
```bash
php artisan db:seed --class=RoleSeeder --force
php artisan db:seed --class=DemoDataSeeder --force   # optional: demo branches/vehicles
php artisan db:seed --class=InventorySeeder --force  # optional: demo inventory
```

## 7. Open it
Railway gives you a free `*.up.railway.app` subdomain under **Settings** → **Networking**
→ **Generate Domain** — that's your live URL for the trial month. A custom domain can be
attached later from the same screen if you have one.

## Notes
- The $5 trial credit (no card required) typically covers a small demo app like this for
  the full 30 days; watch **usage** in the Railway dashboard if traffic grows.
- `RUN_MIGRATIONS=true` is the default (runs `migrate --force` on every boot). Set it to
  `false` once the schema is stable if you want faster restarts.
