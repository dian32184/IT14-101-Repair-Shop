# Cloud Deployment Guide

This document describes the deployment architecture and step-by-step process for deploying the 101 Repair Shop System to Render cloud platform.

## Architecture Overview

* **Frontend:** Blade Views (Laravel)
* **Backend:** Laravel 12 (deployed on Render)
* **Database:** PostgreSQL (Render managed)
* **Process Manager:** Render's built-in process manager
* **Reverse Proxy:** Render's built-in load balancer
* **File Storage:** Render Disk (persistent storage)
* **Environment:** Production on Render

---

# System Architecture

```
User
  ↓
Render Web Service (Laravel Application)
  ↓
Render PostgreSQL Database
  ↓
Render Disk (File Storage)
```

---

# 1. Preparing the Application for Deployment

## Step 1: Ensure Project is on GitHub

```bash
git add .
git commit -m "Ready for deployment"
git push origin main
```

## Step 2: Verify Dependencies

Ensure `composer.json` and `package.json` are up to date:

```bash
composer install
npm install
npm run build
```

## Step 3: Set Production Environment Variables

Create `.env.production` or configure in Render dashboard:

```
APP_NAME=101-Repair-Shop
APP_ENV=production
APP_KEY=your-generated-app-key
APP_DEBUG=false
APP_URL=https://your-app-name.onrender.com

APP_LOCALE=en
APP_FALLBACK_LOCALE=en

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=pgsql
DB_HOST=your-render-db-host
DB_PORT=5432
DB_DATABASE=your-db-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password

SESSION_DRIVER=database
CACHE_DRIVER=database
QUEUE_CONNECTION=database

FILESYSTEM_DISK=public
```

---

# 2. Deploying to Render

## Step 1: Create Render Account

1. Go to [https://render.com](https://render.com)
2. Sign up with GitHub account
3. Authorize Render to access your GitHub repositories

## Step 2: Create PostgreSQL Database

1. In Render dashboard, click **New +**
2. Select **PostgreSQL**
3. Configure:
   * Database Name: `repair_shop_db`
   * User: `repair_shop_user`
   * Region: Choose closest to your users
   * Plan: Free (for testing) or paid for production
4. Click **Create Database**

**Save the connection details** (Internal Database URL, username, password) for later use.

## Step 3: Create Web Service for Laravel

1. In Render dashboard, click **New +**
2. Select **Web Service**
3. Connect your GitHub repository: `dian32184/IT14-101-Repair-Shop`
4. Configure:

### Build & Deploy Settings

* **Name:** `101-repair-shop`
* **Region:** Same as your database
* **Branch:** `main`
* **Runtime:** Docker (recommended) or Native
* **Root Directory:** Leave empty (project root)

### Environment Variables

Add these in Render dashboard:

```
APP_ENV=production
APP_DEBUG=false
APP_KEY=generate-with-php-artisan-key-generate
DB_CONNECTION=pgsql
DB_HOST=your-internal-db-host
DB_PORT=5432
DB_DATABASE=repair_shop_db
DB_USERNAME=repair_shop_user
DB_PASSWORD=your-db-password
CACHE_DRIVER=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database
```

### Advanced Settings

* **Build Command:** `composer install --no-dev --optimize-autoloader && npm install && npm run build`
* **Start Command:** `php artisan serve --host=0.0.0.0 --port=$PORT`

## Step 4: Create Persistent Disk (Optional)

For file uploads and storage:

1. Go to your web service settings
2. Click **Advanced**
3. Click **Add Disk**
4. Configure:
   * Name: `storage`
   * Mount Path: `/var/www/html/storage`
   * Size: 1GB (or as needed)

## Step 5: Deploy

Click **Create Web Service**.

Render will automatically:
* Clone your repository
* Install dependencies
* Build assets
* Run migrations (if configured)
* Deploy to production
* Provide a public URL

---

# 3. Running Database Migrations

## Option 1: Automatic via Build Command

Add to your `composer.json` scripts:

```json
"post-autoload-dump": [
    "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
    "@php artisan package:discover --ansi",
    "@php artisan migrate --force"
]
```

## Option 2: Manual via Render Shell

1. Go to your web service in Render
2. Click **Shell**
3. Run:

```bash
php artisan migrate --force
php artisan db:seed --force
```

---

# 4. Configuring File Storage

## Step 1: Link Storage Directory

In your `Dockerfile` or build command:

```bash
php artisan storage:link
```

## Step 2: Set File Permissions

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## Step 3: Configure Filesystem

In `.env`:

```
FILESYSTEM_DISK=public
```

---

# 5. SSL Certificate

Render automatically provides SSL certificates for all services. Your application will be accessible via HTTPS.

---

# 6. Monitoring and Logs

## View Logs

1. Go to your web service in Render
2. Click **Logs**
3. View real-time application logs

## Health Checks

Render automatically monitors your service. Configure health check in `render.yaml`:

```yaml
healthCheckPath: /up
```

---

# 7. Updating the Application

## Automatic Deployments

Render automatically deploys when you push to the `main` branch:

```bash
git add .
git commit -m "Update feature"
git push origin main
```

## Manual Deploy

1. Go to your web service in Render
2. Click **Manual Deploy**
3. Select branch and click **Deploy**

---

# 8. Troubleshooting

## Common Issues

### Database Connection Failed

* Verify DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
* Ensure database is in the same region as web service
* Check Render dashboard for database status

### 502 Bad Gateway

* Check if application is running (view logs)
* Verify start command is correct
* Ensure port matches `$PORT` environment variable

### File Uploads Not Working

* Verify storage disk is mounted
* Check file permissions
* Ensure `php artisan storage:link` was run

### Build Failures

* Check build logs for specific errors
* Ensure all dependencies are in composer.json
* Verify Node.js version compatibility

---

# 9. Security Best Practices

1. **Never commit `.env` file** to GitHub
2. **Use strong passwords** for database
3. **Enable HTTPS** (automatic on Render)
4. **Keep dependencies updated** regularly
5. **Monitor logs** for suspicious activity
6. **Use Render's private services** for sensitive components

---

# 10. Scaling

## Horizontal Scaling

1. Go to web service settings
2. Click **Advanced**
3. Adjust **Instances** based on traffic

## Vertical Scaling

Upgrade your service plan in Render dashboard for more CPU/RAM.

---

# Contact & Support

* **Render Documentation:** [https://render.com/docs](https://render.com/docs)
* **Laravel Documentation:** [https://laravel.com/docs](https://laravel.com/docs)
* **Project Repository:** [https://github.com/dian32184/IT14-101-Repair-Shop](https://github.com/dian32184/IT14-101-Repair-Shop)
