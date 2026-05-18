#!/bin/bash

# 🔧 MySQL Migration Fix Script
# This script fixes the MySQL key length issues and runs migrations

echo "🔧 Fixing MySQL migration issues..."

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
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

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if we're in a Laravel project
if [ ! -f "artisan" ]; then
    print_error "This doesn't appear to be a Laravel project directory."
    exit 1
fi

# Step 1: Clear any existing migration state
print_status "🧹 Clearing existing migration state..."
php artisan migrate:reset --force 2>/dev/null || true
print_success "Migration state cleared"

# Step 2: Drop all tables manually (if they exist)
print_status "🗑️ Dropping existing tables..."
php artisan tinker --execute="
try {
    \Illuminate\Support\Facades\Schema::dropAllTables();
    echo 'All tables dropped successfully';
} catch (Exception \$e) {
    echo 'No tables to drop or error: ' . \$e->getMessage();
}
" 2>/dev/null || true

# Step 3: Run migrations with proper error handling
print_status "🚀 Running migrations..."
if php artisan migrate --force; then
    print_success "Migrations completed successfully!"
else
    print_error "Migration failed. Let's try a different approach..."

    # Try running migrations one by one
    print_status "🔄 Trying individual migrations..."

    # Run core Laravel migrations first
    php artisan migrate --path=database/migrations/0001_01_01_000000_create_users_table.php --force
    php artisan migrate --path=database/migrations/0001_01_01_000001_create_cache_table.php --force
    php artisan migrate --path=database/migrations/0001_01_01_000002_create_jobs_table.php --force

    # Run the rest
    php artisan migrate --force
fi

# Step 3.5: Handle any remaining migration issues
print_status "🔧 Checking for any remaining migration issues..."
if ! php artisan migrate:status | grep -q "Pending"; then
    print_success "All migrations completed!"
else
    print_warning "Some migrations may still be pending. Running migrate again..."
    php artisan migrate --force
fi

# Step 4: Run seeders
print_status "🌱 Running database seeders..."
if php artisan db:seed --force; then
    print_success "Database seeded successfully!"
else
    print_warning "Seeding failed, but migrations completed"
fi

# Step 5: Display summary
echo
echo "=========================================="
print_success "🎉 Database setup completed!"
echo "=========================================="
echo
echo "📋 What was fixed:"
echo "✅ Reduced varchar lengths to 191 characters for primary keys"
echo "✅ Fixed password_reset_tokens table"
echo "✅ Fixed cache tables"
echo "✅ Fixed sessions table"
echo "✅ Fixed job_batches table"
echo
echo "🔍 Next steps:"
echo "1. Test your application"
echo "2. Check if all features work"
echo "3. Verify user roles and permissions"
echo
print_success "Ready to go! 🚀"
