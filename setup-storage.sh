#!/bin/bash

# Laravel Storage Setup Script for cPanel Deployment
# This script creates all required storage directories with proper permissions

echo "🚀 Setting up Laravel storage directories..."

# Create storage directory structure
echo "📁 Creating storage directories..."

# Main storage directories
mkdir -p storage/app
mkdir -p storage/app/public
mkdir -p storage/framework
mkdir -p storage/framework/cache
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/testing
mkdir -p storage/framework/views
mkdir -p storage/logs

# Create .gitkeep files to ensure directories are tracked by git
find storage -type d -empty -not -path "*/vendor/*" -exec touch {}/.gitkeep \;

echo "✅ Storage directories created successfully!"
echo ""

# Set permissions
echo "🔐 Setting permissions..."

# Set directory permissions (755 for directories)
chmod -R 755 storage

# Set file permissions (644 for files)
find storage -type f -exec chmod 644 {} \;

# Make specific directories writable by web server
chmod -R 775 storage/framework/sessions
chmod -R 775 storage/framework/views
chmod -R 775 storage/framework/cache
chmod -R 775 storage/logs
chmod -R 775 storage/app/public

echo "✅ Permissions set successfully!"
echo ""

# Set proper ownership (optional - uncomment if needed)
# echo "👤 Setting ownership..."
# chown -R $USER:www-data storage
# echo "✅ Ownership set successfully!"
# echo ""

# Create bootstrap/cache directory
echo "📁 Creating bootstrap/cache directory..."
mkdir -p bootstrap/cache
chmod -R 775 bootstrap/cache
touch bootstrap/cache/.gitkeep
echo "✅ Bootstrap cache directory created!"
echo ""

echo "🎉 Laravel storage setup complete!"
echo ""
echo "📋 Next steps:"
echo "1. Upload to cPanel with all files"
echo "2. Run 'php artisan config:cache' after deployment"
echo "3. Run 'php artisan route:cache' after deployment"
echo "4. Test login functionality"
