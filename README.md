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

## Cloud Deployment

For detailed deployment instructions to Render cloud platform, see [DEPLOYMENT.md](./DEPLOYMENT.md).

### Quick Deploy to Render

1. Create account at [render.com](https://render.com)
2. Connect your GitHub repository
3. The `render.yaml` and `Dockerfile` are pre-configured
4. Follow the detailed guide in DEPLOYMENT.md

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

