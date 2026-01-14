# Deployment Guide - UPTD SDN Pengasinan 1 Presensi System

## 📋 Informasi Aplikasi
- **Nama Aplikasi**: UPTD SDN Pengasinan 1 - Sistem Presensi
- **Domain**: https://presensi.aventra.my.id
- **Framework**: Laravel 11
- **Database**: MySQL
- **Timezone**: Asia/Jakarta (UTC+7)

## 🚀 Prasyarat Server
1. **Web Server**: Apache/Nginx dengan PHP 8.2+
2. **Database**: MySQL 8.0+
3. **PHP Extensions**: 
   - BCMath, Ctype, cURL, DOM, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML, GD/ImageMagick
4. **Composer**: Versi terbaru
5. **Node.js**: 18+ (untuk build assets)

## 📁 Struktur File Penting
```
.env.example          # Template konfigurasi production
config/app.php        # Konfigurasi aplikasi (timezone: Asia/Jakarta)
database/migrations/  # Skema database
public/              # Public assets
storage/             # Storage dengan permission 775
bootstrap/cache/     # Cache dengan permission 775
```

## 🔧 Langkah-langkah Deployment

### 1. Persiapan Server
```bash
# Update sistem
sudo apt update && sudo apt upgrade -y

# Install Apache2
sudo apt install apache2 -y

# Install PHP 8.2+ dengan modul Apache
sudo apt install php8.2 libapache2-mod-php8.2 php8.2-cli php8.2-fpm php8.2-mysql \
php8.2-mbstring php8.2-xml php8.2-curl php8.2-gd php8.2-zip \
php8.2-bcmath php8.2-dom php8.2-fileinfo -y

# Enable modul Apache yang diperlukan
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod ssl

# Install Composer
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer

# Install Node.js 18+
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install nodejs -y
```

### 2. Konfigurasi Database MySQL
```sql
-- Buat database
CREATE DATABASE adi_presensi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Buat user (sesuaikan dengan .env)
CREATE USER 'presensi_user'@'localhost' IDENTIFIED BY 'password_kuat';
GRANT ALL PRIVILEGES ON adi_presensi.* TO 'presensi_user'@'localhost';
FLUSH PRIVILEGES;
```

### 3. Clone Repository
```bash
cd /var/www
sudo git clone https://github.com/ChaisarAbi/presensi_adi.git presensi
cd presensi
```

### 4. Konfigurasi Environment
```bash
# Salin template .env
cp .env.example .env

# Edit .env dengan konfigurasi production
nano .env
```

**Konfigurasi .env untuk Production:**
```env
APP_NAME="UPTD SDN Pengasinan 1"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://presensi.aventra.my.id
APP_KEY=base64:... # Generate dengan php artisan key:generate

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=adi_presensi
DB_USERNAME=presensi_user
DB_PASSWORD=password_kuat

TIMEZONE=Asia/Jakarta
```

### 5. Install Dependencies
```bash
# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install Node.js dependencies
npm install
npm run build

# Set permissions
sudo chown -R www-data:www-data /var/www/presensi
sudo chmod -R 775 storage bootstrap/cache
```

### 6. Generate Key dan Migrasi Database
```bash
# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate --force

# (Opsional) Seed data awal
php artisan db:seed
```

### 7. Konfigurasi Web Server (Apache)

#### 7.1 Buat File Virtual Host
```bash
# Buat file virtual host
sudo nano /etc/apache2/sites-available/presensi.conf
```

#### 7.2 Isi File Virtual Host
```apache
<VirtualHost *:80>
    ServerName presensi.aventra.my.id
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/presensi/public

    <Directory /var/www/presensi/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
        
        # Laravel specific configurations
        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteRule ^ index.php [L]
        </IfModule>
    </Directory>

    # Error logging
    ErrorLog ${APACHE_LOG_DIR}/presensi_error.log
    CustomLog ${APACHE_LOG_DIR}/presensi_access.log combined
    
    # Security headers
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
</VirtualHost>
```

#### 7.3 Aktifkan Virtual Host
```bash
# Nonaktifkan default site
sudo a2dissite 000-default.conf

# Aktifkan site presensi
sudo a2ensite presensi.conf

# Test konfigurasi Apache
sudo apache2ctl configtest

# Restart Apache
sudo systemctl restart apache2

# Enable Apache untuk start otomatis
sudo systemctl enable apache2
```

#### 7.4 Cek Status Apache
```bash
sudo systemctl status apache2
```

### 8. Konfigurasi SSL (HTTPS)
```bash
# Install Certbot untuk Apache
sudo apt install certbot python3-certbot-apache -y

# Generate SSL certificate
sudo certbot --apache -d presensi.aventra.my.id
```

### 9. Konfigurasi Cron Job
```bash
# Edit crontab
sudo crontab -u www-data -e

# Tambahkan baris berikut
* * * * * cd /var/www/presensi && php artisan schedule:run >> /dev/null 2>&1
```

### 10. Konfigurasi Queue Worker (Opsional)
```bash
# Buat systemd service
sudo nano /etc/systemd/system/presensi-worker.service
```

**Isi file service:**
```ini
[Unit]
Description=Presensi Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
WorkingDirectory=/var/www/presensi
ExecStart=/usr/bin/php artisan queue:work --sleep=3 --tries=3
Restart=always

[Install]
WantedBy=multi-user.target
```

**Aktifkan service:**
```bash
sudo systemctl enable presensi-worker
sudo systemctl start presensi-worker
```

## 🔍 Testing Deployment
1. **Cek aplikasi**: https://presensi.aventra.my.id
2. **Cek database connection**: Login ke aplikasi
3. **Cek QR Code generator**: /qr/generate
4. **Cek absensi scanner**: /attendance/scanner
5. **Cek monitoring**: /monitoring

## 🛠️ Troubleshooting

### 1. Permission Issues
```bash
sudo chown -R www-data:www-data /var/www/presensi
sudo chmod -R 775 storage bootstrap/cache
```

### 2. Database Connection Error
- Pastikan MySQL service running: `sudo systemctl status mysql`
- Cek kredensial di .env
- Test koneksi: `php artisan db:show`

### 3. 500 Internal Server Error
```bash
# Enable debug sementara
APP_DEBUG=true di .env

# Cek error log
tail -f storage/logs/laravel.log

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### 4. Asset tidak muncul
```bash
# Rebuild assets
npm run build

# Clear cache
php artisan config:clear
```

## 📈 Monitoring Production
1. **Logs**: `tail -f storage/logs/laravel.log`
2. **Performance**: Install Laravel Telescope (opsional)
3. **Backup**: Setup backup database otomatis
4. **Updates**: Update dependencies secara berkala

## 🔄 Update Deployment
```bash
cd /var/www/presensi

# Pull perubahan terbaru
sudo git pull origin main

# Update dependencies
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Run migrations
php artisan migrate --force

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Restart queue worker
sudo systemctl restart presensi-worker
```

## 📞 Support
- **GitHub**: https://github.com/ChaisarAbi/presensi_adi
- **Issues**: GitHub Issues
- **Documentation**: Lihat README.md

---
**Terakhir diperbarui**: 14 Januari 2026
**Versi**: 1.0.0
**Status**: Production Ready
