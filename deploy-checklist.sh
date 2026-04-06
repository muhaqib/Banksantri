#!/bin/bash

# cPanel Deployment Checklist Script
# Run this before uploading to cPanel

echo "🚀 Laravel cPanel Deployment Checklist"
echo "======================================"
echo ""

# Check if .env exists
if [ ! -f .env ]; then
    echo "⚠️  WARNING: .env file does not exist!"
    echo "   Run: cp .env.example .env"
    echo "   Then configure your database and session settings"
    echo ""
else
    echo "✅ .env file exists"
    
    # Check SESSION_SECURE_COOKIE
    SECURE_COOKIE=$(grep "SESSION_SECURE_COOKIE=" .env | cut -d'=' -f2)
    if [ "$SECURE_COOKIE" = "true" ]; then
        echo "⚠️  WARNING: SESSION_SECURE_COOKIE=true"
        echo "   Set to false if deploying to HTTP (non-HTTPS) cPanel"
    else
        echo "✅ SESSION_SECURE_COOKIE is set correctly"
    fi
fi

echo ""

# Check storage permissions
if [ -w "storage" ]; then
    echo "✅ storage/ directory is writable"
else
    echo "❌ storage/ directory is NOT writable"
    echo "   Run: chmod -R 775 storage/"
fi

if [ -w "bootstrap/cache" ]; then
    echo "✅ bootstrap/cache/ directory is writable"
else
    echo "❌ bootstrap/cache/ directory is NOT writable"
    echo "   Run: chmod -R 775 bootstrap/cache/"
fi

echo ""

# Check if vendor exists
if [ ! -d "vendor" ]; then
    echo "⚠️  WARNING: vendor/ directory does not exist"
    echo "   Run: composer install"
else
    echo "✅ vendor/ directory exists"
fi

echo ""

# Check APP_KEY
if [ -f .env ]; then
    APP_KEY=$(grep "APP_KEY=" .env | cut -d'=' -f2)
    if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
        echo "⚠️  WARNING: APP_KEY is not set"
        echo "   Run: php artisan key:generate"
    else
        echo "✅ APP_KEY is set"
    fi
fi

echo ""
echo "📋 Pre-deployment checks complete!"
echo ""
echo "📦 Next steps for cPanel:"
echo "   1. Update .env with your cPanel database credentials"
echo "   2. Zip all files: zip -r project.zip . -x 'node_modules/*' -x '.git/*'"
echo "   3. Upload to cPanel File Manager"
echo "   4. Extract and run: php artisan migrate --force"
echo "   5. Read CPANEL_DEPLOYMENT.md for detailed instructions"
