#!/bin/bash

# Laravel Project Deployment Script
# This script automates the deployment process for this Laravel application.

set -e

echo "Starting Laravel deployment..."

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

if [ ! -f "artisan" ]; then
    print_error "This does not appear to be a Laravel project directory."
    exit 1
fi

if ! command -v composer >/dev/null 2>&1; then
    print_error "Composer is not installed."
    exit 1
fi

if ! command -v npm >/dev/null 2>&1; then
    print_warning "NPM is not installed. Skipping asset compilation."
    SKIP_ASSETS=true
fi

if [ ! -f ".env" ]; then
    if [ -f "env.production.template" ]; then
        cp env.production.template .env
        print_warning "Created .env from env.production.template. Update it with real production values."
    elif [ -f ".env.example" ]; then
        cp .env.example .env
        print_warning "Created .env from .env.example. Update it with real production values."
    else
        print_error "No .env, env.production.template, or .env.example file found."
        exit 1
    fi
fi

if grep -q '^CACHE_DRIVER=' .env && ! grep -q '^CACHE_STORE=' .env; then
    CACHE_DRIVER_VALUE=$(grep '^CACHE_DRIVER=' .env | tail -n 1 | cut -d= -f2-)
    printf '\nCACHE_STORE=%s\n' "$CACHE_DRIVER_VALUE" >> .env
    print_warning "Added CACHE_STORE to .env from the legacy CACHE_DRIVER value."
fi

mkdir -p storage/app/private storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views bootstrap/cache

print_status "Installing PHP dependencies..."
composer install --optimize-autoloader --no-dev --no-interaction
print_success "PHP dependencies installed successfully."

if [ "$SKIP_ASSETS" != true ]; then
    print_status "Installing and building frontend assets..."
    npm install --silent
    npm run build
    print_success "Assets built successfully."
else
    print_warning "Skipping asset compilation."
fi

APP_KEY_VALUE=$(grep '^APP_KEY=' .env | tail -n 1 | cut -d= -f2- | tr -d '\r')
if [ -z "$APP_KEY_VALUE" ] || [ "$APP_KEY_VALUE" = "base64:YOUR_APP_KEY_HERE" ]; then
    print_status "Generating APP_KEY..."
    php artisan key:generate --force
    print_success "APP_KEY generated."
fi

print_status "Setting directory permissions..."
chmod -R 755 storage bootstrap/cache
print_success "Permissions updated."

print_status "Clearing stale Laravel caches..."
php artisan optimize:clear
print_success "Laravel caches cleared."

print_status "Creating storage link..."
if php artisan storage:link; then
    print_success "Storage link created."
else
    print_warning "storage:link failed. Trying a manual symlink..."
    if [ -d "storage/app/public" ] && [ -d "public" ]; then
        ln -sf ../storage/app/public public/storage 2>/dev/null || true
        print_success "Manual storage link created."
    else
        print_error "Could not create a storage link."
    fi
fi

RUN_MIGRATIONS=${RUN_MIGRATIONS:-true}
if [ "$RUN_MIGRATIONS" = "true" ]; then
    print_status "Running database migrations..."
    php artisan migrate --force
    print_success "Database migrations completed."
else
    print_warning "Skipping database migrations because RUN_MIGRATIONS=false."
fi

RUN_SEEDERS=${RUN_SEEDERS:-false}
if [ "$RUN_SEEDERS" = "true" ]; then
    print_status "Running database seeders..."
    php artisan db:seed --force
    print_success "Database seeders completed."
fi

print_status "Caching production artifacts..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
print_success "Laravel production caches rebuilt."

echo
echo "=========================================="
print_success "Deployment completed successfully."
echo "=========================================="
echo
echo "Summary:"
echo "- Dependencies installed"
if [ "$SKIP_ASSETS" != true ]; then
    echo "- Assets built"
fi
echo "- APP_KEY verified"
echo "- Storage link created"
echo "- Migrations applied: $RUN_MIGRATIONS"
echo "- Seeders applied: $RUN_SEEDERS"
echo "- Laravel caches rebuilt"
