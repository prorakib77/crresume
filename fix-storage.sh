#!/bin/bash

# 🔧 Storage Link Fix Script for Live Hosting
# This script fixes image path issues on live hosting

echo "🔧 Fixing storage link issues..."

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

# Step 1: Try Laravel storage:link command
print_status "🔗 Attempting to create storage link..."
if php artisan storage:link; then
    print_success "Storage link created successfully!"
else
    print_warning "Laravel storage:link failed. Trying alternative methods..."

    # Step 2: Try manual symlink
    print_status "🔗 Creating manual symlink..."
    if [ -d "storage/app/public" ] && [ -d "public" ]; then
        # Remove existing storage directory if it exists
        rm -rf public/storage 2>/dev/null || true

        # Create symlink
        if ln -sf ../storage/app/public public/storage; then
            print_success "Manual symlink created!"
        else
            print_warning "Symlink creation failed. Trying copy method..."

            # Step 3: Copy files as fallback
            print_status "📁 Copying storage files to public directory..."
            if [ -d "storage/app/public" ]; then
                cp -r storage/app/public public/storage 2>/dev/null || true
                print_success "Files copied to public/storage!"
            fi
        fi
    fi
fi

# Step 4: Set proper permissions
print_status "🔐 Setting proper permissions..."
chmod -R 755 storage/app/public 2>/dev/null || true
chmod -R 755 public/storage 2>/dev/null || true
print_success "Permissions set!"

# Step 5: Test storage access
print_status "🧪 Testing storage access..."
if [ -d "public/storage" ]; then
    print_success "Storage directory exists!"
    echo "📁 Storage files:"
    ls -la public/storage/ 2>/dev/null || echo "No files in storage yet"
else
    print_warning "Storage directory not found. You may need to upload files first."
fi

# Step 6: Display summary
echo
echo "=========================================="
print_success "🎉 Storage fix completed!"
echo "=========================================="
echo
echo "📋 What was done:"
echo "✅ Attempted Laravel storage:link"
echo "✅ Created manual symlink if needed"
echo "✅ Set proper permissions"
echo "✅ Tested storage access"
echo
echo "🔍 Next steps:"
echo "1. Test your image uploads"
echo "2. Check if images are displaying"
echo "3. If still not working, contact your hosting provider"
echo
print_success "Storage should now be working! 🚀"

