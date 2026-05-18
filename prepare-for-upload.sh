#!/bin/bash

# 🚀 Laravel Project Preparation Script for Zip Upload
# This script prepares your Laravel project for uploading to shared hosting

echo "🚀 Preparing Laravel project for zip upload..."

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

# Check if we're in a Laravel project
if [ ! -f "artisan" ]; then
    echo "❌ This doesn't appear to be a Laravel project directory."
    echo "Please run this script from your Laravel project root."
    exit 1
fi

# Step 1: Clean up unnecessary files
print_status "🧹 Cleaning up unnecessary files..."

# Remove node_modules if exists
if [ -d "node_modules" ]; then
    rm -rf node_modules
    print_success "Removed node_modules"
fi

# Remove .git if exists
if [ -d ".git" ]; then
    rm -rf .git
    print_success "Removed .git directory"
fi

# Clean storage logs
if [ -d "storage/logs" ]; then
    rm -rf storage/logs/*
    print_success "Cleaned storage logs"
fi

# Clean cache files
if [ -d "storage/framework/cache" ]; then
    rm -rf storage/framework/cache/*
    print_success "Cleaned cache files"
fi

if [ -d "storage/framework/sessions" ]; then
    rm -rf storage/framework/sessions/*
    print_success "Cleaned session files"
fi

if [ -d "storage/framework/views" ]; then
    rm -rf storage/framework/views/*
    print_success "Cleaned view cache"
fi

# Step 2: Create production environment file
print_status "📝 Creating production environment template..."

cat > .env.production << 'EOF'
APP_NAME="W-Automation"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_MAINTENANCE_DRIVER=file

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

BROADCAST_DRIVER=log
CACHE_STORE=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_email_password
MAIL_SCHEME=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
EOF

print_success "Created .env.production template"

# Step 3: Generate application key
print_status "🔑 Generating application key..."
APP_KEY=$(php artisan key:generate --show 2>/dev/null)

if [ ! -z "$APP_KEY" ]; then
    # Update the .env.production file with the actual key
    sed -i "s/base64:YOUR_APP_KEY_HERE/$APP_KEY/g" .env.production
    print_success "Application key generated and added to .env.production"
    echo "Your APP_KEY: $APP_KEY"
else
    print_warning "Could not generate application key. You'll need to do this manually."
fi

# Step 4: Create upload instructions
print_status "📋 Creating upload instructions..."

cat > UPLOAD_INSTRUCTIONS.txt << 'EOF'
📦 LARAVEL PROJECT UPLOAD INSTRUCTIONS

1. UPLOAD TO HOSTING:
   - Login to your cPanel
   - Go to File Manager
   - Navigate to public_html
   - Upload laravel-project.zip
   - Extract the files

2. SET UP FILE STRUCTURE:
   - Move all files from laravel-project/public/ to public_html/
   - Keep laravel-project/ folder in public_html/

3. UPDATE index.php:
   - Open public_html/index.php
   - Replace content with the code from index.php.production

4. UPDATE .htaccess:
   - Make sure public_html/.htaccess has Laravel rewrite rules

5. CONFIGURE DATABASE:
   - Create database in cPanel MySQL
   - Rename .env.production to .env
   - Update database credentials in .env

6. RUN COMMANDS:
   - Open Terminal in cPanel
   - cd public_html/laravel-project
   - composer install --optimize-autoloader --no-dev
   - php artisan key:generate --force
   - php artisan migrate --force
   - php artisan storage:link
   - php artisan optimize:clear
   - php artisan config:cache

7. SET PERMISSIONS:
   - Set storage/ folder to 755
   - Set bootstrap/cache/ folder to 755

8. TEST:
   - Visit your domain
   - Check if everything works
EOF

print_success "Created upload instructions"

# Step 5: Create production index.php
print_status "📄 Creating production index.php..."

cat > index.php.production << 'EOF'
<?php
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

require __DIR__.'/laravel-project/vendor/autoload.php';

$app = require_once __DIR__.'/laravel-project/bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
EOF

print_success "Created production index.php"

# Step 6: Create production .htaccess
print_status "📄 Creating production .htaccess..."

cat > .htaccess.production << 'EOF'
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
EOF

print_success "Created production .htaccess"

# Step 7: Create zip file
print_status "📦 Creating zip file..."

# Create zip excluding unnecessary files
zip -r laravel-project.zip . \
    -x "node_modules/*" \
    -x ".git/*" \
    -x "storage/logs/*" \
    -x "storage/framework/cache/*" \
    -x "storage/framework/sessions/*" \
    -x "storage/framework/views/*" \
    -x "*.log" \
    -x "prepare-for-upload.sh" \
    -x "deploy.sh" \
    -x "DEPLOYMENT_*.md" \
    -x "HOSTING_GUIDES.md" \
    -x "SIMPLE_UPLOAD_GUIDE.md"

if [ $? -eq 0 ]; then
    print_success "Created laravel-project.zip"
else
    print_warning "Failed to create zip file. You may need to create it manually."
fi

# Step 8: Display summary
echo
echo "=========================================="
print_success "🎉 Project preparation completed!"
echo "=========================================="
echo
echo "📁 Files created:"
echo "✅ laravel-project.zip (ready for upload)"
echo "✅ .env.production (environment template)"
echo "✅ index.php.production (Laravel entry point)"
echo "✅ .htaccess.production (rewrite rules)"
echo "✅ UPLOAD_INSTRUCTIONS.txt (step-by-step guide)"
echo
echo "📋 Next steps:"
echo "1. Upload laravel-project.zip to your hosting"
echo "2. Follow UPLOAD_INSTRUCTIONS.txt"
echo "3. Or read SIMPLE_UPLOAD_GUIDE.md for detailed steps"
echo
echo "🔑 Your APP_KEY: $APP_KEY"
echo "📝 Update .env.production with your database credentials"
echo
print_success "Ready for upload! 🚀"
