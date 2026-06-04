# Installation Guide

This document provides instructions for installing Chronos Event Manager in two ways: using Docker and natively on Linux.

## System Requirements

### Hardware
- CPU: 2 cores minimum
- RAM: 4GB minimum, 8GB recommended
- Disk: 10GB free space

### Software Requirements

#### For Docker Installation
- Docker Engine 20.10 or higher
- Docker Compose 2.0 or higher

#### For Native Linux Installation
- Linux OS (Ubuntu 20.04+, Debian 11+, or equivalent)
- PHP 8.1 or higher
- Composer 2.0 or higher
- Node.js 18 or higher
- npm 9 or higher
- MySQL 8.0 or PostgreSQL 13+
- Nginx or Apache web server
- Redis (optional, for caching)

---

## Installation Method 1: Docker

### Step 1: Clone the Repository

```bash
git clone https://github.com/yourusername/chronos-event-manager.git
cd chronos-event-manager
```

### Step 2: Configure Environment Variables

Copy the example environment file and configure it:

```bash
cp .env.example .env
```

Edit the `.env` file with your database credentials and application settings.

### Step 3: Build and Start Containers

```bash
docker-compose build
docker-compose up -d
```

### Step 4: Install Dependencies

```bash
docker-compose exec app composer install
docker-compose exec app npm install
```

### Step 5: Run Migrations

```bash
docker-compose exec app php artisan migrate
```

### Step 6: Build Frontend Assets

```bash
docker-compose exec app npm run build
```

### Step 7: Access the Application

Open your browser and navigate to `http://localhost:8000`

---

## Installation Method 2: Native Linux

### Step 1: Clone the Repository

```bash
git clone https://github.com/yourusername/chronos-event-manager.git
cd chronos-event-manager
```

### Step 2: Install System Dependencies

#### Ubuntu/Debian

```bash
sudo apt update
sudo apt install php8.1 php8.1-fpm php8.1-mysql php8.1-xml php8.1-mbstring php8.1-curl php8.1-zip php8.1-bcmath
sudo apt install nginx mysql-server redis-server
sudo apt install nodejs npm
```

#### CentOS/RHEL

```bash
sudo yum install php php-fpm php-mysqlnd php-xml php-mbstring php-curl php-zip php-bcmath
sudo yum install nginx mariadb-server redis
sudo yum install nodejs npm
```

### Step 3: Install Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### Step 4: Configure Database

Create a MySQL database and user:

```sql
CREATE DATABASE chronos;
CREATE USER 'chronos_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON chronos.* TO 'chronos_user'@'localhost';
FLUSH PRIVILEGES;
```

### Step 5: Configure Environment Variables

```bash
cp .env.example .env
nano .env
```

Update the following values:
- `APP_URL=http://your-domain.com`
- `DB_DATABASE=chronos`
- `DB_USERNAME=chronos_user`
- `DB_PASSWORD=your_password`

### Step 6: Install PHP Dependencies

```bash
composer install --no-dev --optimize-autoloader
```

### Step 7: Install Node Dependencies

```bash
npm install
```

### Step 8: Generate Application Key

```bash
php artisan key:generate
```

### Step 9: Run Migrations

```bash
php artisan migrate
```

### Step 10: Build Frontend Assets

```bash
npm run build
```

### Step 11: Configure Web Server

#### Nginx Configuration

Create a new Nginx configuration file:

```bash
sudo nano /etc/nginx/sites-available/chronos
```

Add the following configuration:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/chronos-event-manager/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable the site:

```bash
sudo ln -s /etc/nginx/sites-available/chronos /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

#### Apache Configuration

If using Apache, ensure mod_rewrite is enabled:

```bash
sudo a2enmod rewrite
```

Create a virtual host configuration and point the DocumentRoot to the `public` directory.

### Step 12: Set Permissions

```bash
sudo chown -R www-data:www-data /var/www/chronos-event-manager
sudo chmod -R 755 /var/www/chronos-event-manager
sudo chmod -R 777 /var/www/chronos-event-manager/storage
sudo chmod -R 777 /var/www/chronos-event-manager/bootstrap/cache
```

### Step 13: Configure Queue Worker (Optional)

If using Redis for queues:

```bash
php artisan queue:work --daemon
```

Or set up as a systemd service.

---

## Verification

To verify the installation:

1. Open your browser and navigate to your application URL
2. You should see the Chronos Event Manager interface
3. Try creating an event to ensure the database connection is working

---

## Troubleshooting

### Docker Issues

**Container won't start:**
```bash
docker-compose logs app
```

**Database connection failed:**
- Ensure the database container is running
- Check database credentials in `.env` file

### Native Installation Issues

**Permission denied:**
```bash
sudo chown -R $USER:$USER /var/www/chronos-event-manager
```

**Composer install fails:**
```bash
composer install --no-interaction --prefer-dist
```

**NPM install fails:**
```bash
npm install --legacy-peer-deps
```

---

## Next Steps

After installation, refer to the [User Guide](USER_GUIDE.md) for information on using the application.
