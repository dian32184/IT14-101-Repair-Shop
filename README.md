# 101 Repair Shop System

A comprehensive appliance repair management system built with Laravel 12.

## Features

- Customer management
- Service report tracking
- Transaction and payment processing
- Inventory management
- Staff management
- Archive system
- Receipt generation
- Warranty tracking
- Miscellaneous cost tracking

## Tech Stack

- **Backend:** Laravel 12 (PHP 8.2)
- **Frontend:** Blade Templates with TailwindCSS
- **Database:** MySQL / PostgreSQL
- **Image Processing:** Intervention Image
- **Cloud Storage:** Cloudinary (optional)

## Installation

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js & NPM
- MySQL or PostgreSQL

### Local Setup

1. Clone the repository
```bash
git clone https://github.com/dian32184/IT14-101-Repair-Shop.git
cd IT14-101-Repair-Shop
```

2. Install dependencies
```bash
composer install
npm install
```

3. Configure environment
```bash
cp .env.example .env
php artisan key:generate
```

4. Configure database in `.env`
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=repair_system
DB_USERNAME=root
DB_PASSWORD=
```

5. Run migrations
```bash
php artisan migrate --seed
```

6. Build assets
```bash
npm run build
```

7. Start development server
```bash
php artisan serve
```

## Cloud Deployment Guide

This guide describes how to deploy the 101 Repair Shop System to Render cloud platform using Docker.

### Architecture

- **Frontend:** Blade Views (Laravel) with Vite
- **Backend:** Laravel 12 (PHP 8.2 + Apache on Docker)
- **Database:** PostgreSQL (Render managed)
- **Deployment:** Docker container with automatic migrations
- **Cloud Storage:** Cloudinary (for receipt PDFs)

### Step 1: Create Render Account

1. Go to [https://render.com](https://render.com)
2. Sign up with GitHub account
3. Authorize Render to access your GitHub repositories

### Step 2: Create PostgreSQL Database

1. In Render dashboard, click **New +**
2. Select **PostgreSQL**
3. Configure:
   - Database Name: `repair_shop_db`
   - User: `repair_shop_user`
   - Region: Choose closest to your users
   - Plan: Free (for testing) or paid for production
4. Click **Create Database**

**Important:** Copy the **Internal Database URL** from the database dashboard (format: `postgresql://username:password@host:port/database`)

### Step 3: Create Cloudinary Account (for Receipt PDFs)

1. Go to [https://cloudinary.com](https://cloudinary.com)
2. Sign up for a free account
3. Go to Dashboard → Settings → API Keys
4. Copy your Cloudinary URL (format: `cloudinary://API_KEY:API_SECRET@CLOUD_NAME`)

### Step 4: Create Web Service

1. In Render dashboard, click **New +**
2. Select **Web Service**
3. Connect your GitHub repository: `dian32184/IT14-101-Repair-Shop`
4. Configure:

**Build & Deploy Settings:**
- Name: `101-repair-shop`
- Region: Same as your database
- Branch: `main`
- Runtime: Docker
- Root Directory: Leave empty

**Environment Variables:**

Required:
```
APP_KEY=base64:your-generated-key-here
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app-name.onrender.com
DB_CONNECTION=pgsql
DB_URL=postgresql://username:password@host:port/database
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
CLOUDINARY_URL=cloudinary://API_KEY:API_SECRET@CLOUD_NAME
```

Admin Account (optional):
```
ADMIN_EMAIL=your-admin-email@example.com
ADMIN_PASSWORD=your-secure-password
ADMIN_NAME=Administrator
```

Sample Data (optional):
```
SEED_SAMPLE_DATA=true
```

**Notes:**
- Generate `APP_KEY` locally: `php artisan key:generate --show`
- Replace `DB_URL` with Internal Database URL from Step 2
- Replace `CLOUDINARY_URL` with Cloudinary URL from Step 3

### Step 5: Deploy

Click **Create Web Service**. Render will automatically:
- Build the Docker image
- Run migrations on startup
- Create admin user (if credentials provided)
- Seed sample data (if enabled)
- Deploy to production with HTTPS

### First-Time Setup

1. Access your app at the Render URL
2. Login with admin credentials (if set)
3. Verify dashboard loads correctly

### Updating the Application

Render automatically deploys when you push to `main`:
```bash
git add .
git commit -m "Update feature"
git push origin main
```

## Project Structure

```
├── app/
│   ├── Http/Controllers/    # Application controllers
│   ├── Models/              # Eloquent models
│   └── Notifications/       # Notification classes
├── database/
│   ├── migrations/         # Database migrations
│   └── seeders/            # Database seeders
├── resources/
│   └── views/              # Blade templates
├── routes/
│   ├── web.php             # Web routes
│   └── auth.php            # Authentication routes
├── public/                 # Public assets
└── storage/                # Application storage
```

## User Roles

- **Administrator:** Full access to all features
- **Secretary:** Customer and service report management
- **Technician:** Service report updates
- **Cashier:** Transaction and payment processing
- **Staff:** Limited access based on role

## Payment Methods Supported

- Cash
- GCash
- PayMaya
- Bank Transfer
- Check

## License

This project is open-sourced software licensed under the MIT license.

## Support

For deployment issues, refer to [DEPLOYMENT.md](./DEPLOYMENT.md)

