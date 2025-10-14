# Rocket LMS - Learning Management System

Complete Learning Management System (LMS) built with Laravel framework for creating and managing online courses, quizzes, certificates, and more.

## Overview

Rocket LMS is a comprehensive learning management system that enables you to create an online education platform. It includes features for course management, student enrollment, quiz creation, certificate generation, payment processing, and much more.

## Features

### Core Features

- **Course Management**: Create and manage unlimited courses
- **Quiz System**: Create quizzes with multiple question types
- **Certificate Generation**: Automatic certificate generation upon course completion
- **Payment Integration**: Multiple payment gateway support
- **User Roles**: Admin, Instructor, Student, Organization
- **Multi-language Support**: 94+ languages included
- **Responsive Design**: Works on desktop, tablet, and mobile
- **Video Hosting**: Support for multiple video platforms
- **Live Classes**: Integration with video conferencing tools
- **Assignment System**: Create and grade assignments
- **Discussion Forums**: Course-specific discussion boards
- **Subscription Plans**: Recurring subscription support
- **Affiliate System**: Built-in affiliate program
- **Reporting**: Comprehensive analytics and reports

### Additional Features

- **Blog System**: Built-in blogging platform
- **Meeting Management**: Schedule and manage meetings
- **Bundle Courses**: Create course bundles
- **Installment Payments**: Allow payment in installments
- **Gift System**: Gift courses to others
- **Waitlist**: Manage course waitlists
- **Cashback**: Cashback rewards system
- **Rewards Points**: Point-based reward system
- **Registration Bonus**: Welcome bonuses for new users
- **Offline Payments**: Support for offline payment methods

## Technology Stack

- **Framework**: Laravel 9.x
- **PHP**: 8.0+
- **Database**: MySQL 5.7+
- **Frontend**: Bootstrap, jQuery, Vue.js components
- **Package Manager**: Composer, NPM

## Requirements

### Server Requirements

- **PHP**: 8.0 or higher
- **MySQL**: 5.7 or higher
- **Apache/Nginx**: Web server
- **Composer**: Dependency manager
- **Node.js & NPM**: For asset compilation

### PHP Extensions

- OpenSSL
- PDO
- Mbstring
- Tokenizer
- XML
- Ctype
- JSON
- BCMath
- Fileinfo
- GD Library

## Installation

### 1. Clone Repository

```bash
git clone https://github.com/HealthFlowEgy/rocket-lms-plugins.git
cd rocket-lms-plugins/lms
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install NPM dependencies
npm install
```

### 3. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Configure Database

Edit `.env` file and update database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rocket_lms
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Import Database

Choose one of the provided SQL files:

**Option A: Empty Database** (for fresh installation)
```bash
mysql -u username -p database_name < databases/empty_db.sql
```

**Option B: Demo Database** (with sample data)
```bash
mysql -u username -p database_name < databases/demo_db.sql
```

### 6. Run Migrations (if using empty database)

```bash
php artisan migrate
```

### 7. Storage Link

```bash
php artisan storage:link
```

### 8. Set Permissions

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 9. Compile Assets

```bash
# For development
npm run dev

# For production
npm run build
```

### 10. Serve Application

```bash
# Development server
php artisan serve

# Production: Configure Apache/Nginx to point to public/ directory
```

## Configuration

### Application Settings

Update the following in `.env`:

```env
APP_NAME="Rocket LMS"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
```

### Mail Configuration

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Payment Gateways

Configure payment gateways in admin panel or `.env`:

```env
PAYPAL_MODE=sandbox
PAYPAL_CLIENT_ID=your_client_id
PAYPAL_SECRET=your_secret

STRIPE_KEY=your_stripe_key
STRIPE_SECRET=your_stripe_secret
```

### Firebase (for notifications)

Update `firebase-auth.json` with your Firebase credentials.

## Default Admin Credentials

**Demo Database**:
- Email: admin@admin.com
- Password: admin123

**Empty Database**:
- Create admin user via seeder or manually in database

## Directory Structure

```
lms/
├── app/                    # Application core
│   ├── Http/              # Controllers, Middleware
│   ├── Models/            # Eloquent models
│   └── ...
├── bootstrap/             # Framework bootstrap
├── config/                # Configuration files
├── database/              # Migrations, seeders
│   ├── migrations/
│   └── seeders/
├── databases/             # SQL database files
│   ├── demo_db.sql       # Demo database
│   └── empty_db.sql      # Empty database
├── lang/                  # Language files (94+ languages)
├── public/                # Public web root
│   ├── index.php         # Entry point
│   └── assets/           # CSS, JS, images
├── resources/             # Views, raw assets
│   ├── views/
│   └── lang/
├── routes/                # Route definitions
│   ├── web.php
│   └── api.php
├── storage/               # Logs, cache, uploads
├── tests/                 # Unit tests
├── vendor/                # Composer dependencies (not in repo)
├── .env                   # Environment configuration
├── artisan                # CLI tool
├── composer.json          # PHP dependencies
└── package.json           # NPM dependencies
```

## Admin Panel

Access admin panel at: `https://yourdomain.com/admin`

### Admin Features

- Dashboard with statistics
- User management
- Course management
- Financial management
- Settings configuration
- Plugin management
- Theme customization
- Email templates
- SEO settings
- And much more...

## API Documentation

API endpoints are available at `/api/*`. Check `routes/api.php` for available endpoints.

## Plugins

The system supports plugins for extending functionality. Check the `plugins/` directory in the parent repository for available plugins including:

- **HealthPay Payment Gateway**: Egyptian payment solution
- **Universal Plugins Bundle**: Additional features and integrations

## Mobile App

A Flutter mobile app is available in the `mobile-app/` directory of the parent repository.

## Troubleshooting

### Common Issues

#### 1. 500 Internal Server Error

**Solution**:
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
chmod -R 775 storage bootstrap/cache
```

#### 2. Database Connection Error

**Solution**:
- Verify database credentials in `.env`
- Ensure MySQL service is running
- Check database exists

#### 3. Asset Not Found

**Solution**:
```bash
npm run build
php artisan storage:link
```

#### 4. Permission Denied

**Solution**:
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## Updating

To update to a new version:

1. Backup database and files
2. Download update package
3. Replace files (keep `.env` and `storage/`)
4. Run migrations: `php artisan migrate`
5. Clear cache: `php artisan cache:clear`

## Security

- Keep `.env` file secure and never commit to version control
- Use strong database passwords
- Enable HTTPS in production
- Keep Laravel and dependencies updated
- Disable debug mode in production (`APP_DEBUG=false`)
- Use secure session and cookie settings

## Performance Optimization

### Production Optimization

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

### Queue Workers

For better performance, use queue workers:

```bash
php artisan queue:work
```

## Support

For support and documentation:
- Check `Important.txt` for license information
- Visit CodeCanyon support forum
- Check official documentation

## License

This software is licensed under the CodeCanyon Regular License. Please refer to `Important.txt` for license details.

## Changelog

See parent repository for version history and updates.

---

**Framework**: Laravel 9.x  
**Version**: Check composer.json  
**Last Updated**: October 14, 2025  
**Repository**: https://github.com/HealthFlowEgy/rocket-lms-plugins

