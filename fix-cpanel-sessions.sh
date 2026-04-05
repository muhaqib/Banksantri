#!/bin/bash
# ============================================
# 🚀 CPANEL SESSION FIX - Automated Script
# Upload this to cPanel and run once
# ============================================

echo ""
echo "╔══════════════════════════════════════════╗"
echo "║  🚀 MawaSmart cPanel Session Fix         ║"
echo "╚══════════════════════════════════════════╝"
echo ""

# Check if .env exists
if [ ! -f .env ]; then
    echo "❌ ERROR: .env file not found!"
    echo "   Make sure you're in the Laravel root directory"
    exit 1
fi

echo "📍 Location: $(pwd)"
echo ""

# ============================================
# 1. UPDATE .ENV
# ============================================
echo "📝 Step 1: Updating .env file..."

# Backup .env
cp .env .env.backup
echo "✅ Backup created: .env.backup"

# Update SESSION_DRIVER
sed -i 's/SESSION_DRIVER=.*/SESSION_DRIVER=file/' .env

# Update APP_ENV
sed -i 's/APP_ENV=.*/APP_ENV=production/' .env

# Update APP_DEBUG
sed -i 's/APP_DEBUG=.*/APP_DEBUG=false/' .env

# Update APP_URL
sed -i 's|APP_URL=.*|APP_URL=https://smart.mambaulhikmah.com|' .env

# Add SESSION_SECURE_COOKIE if not exists
if ! grep -q "SESSION_SECURE_COOKIE=" .env; then
    echo "SESSION_SECURE_COOKIE=true" >> .env
    echo "✅ Added SESSION_SECURE_COOKIE=true"
else
    sed -i 's/SESSION_SECURE_COOKIE=.*/SESSION_SECURE_COOKIE=true/' .env
    echo "✅ Updated SESSION_SECURE_COOKIE=true"
fi

# Add SESSION_SAME_SITE if not exists
if ! grep -q "SESSION_SAME_SITE=" .env; then
    echo "SESSION_SAME_SITE=lax" >> .env
    echo "✅ Added SESSION_SAME_SITE=lax"
else
    sed -i 's/SESSION_SAME_SITE=.*/SESSION_SAME_SITE=lax/' .env
fi

echo "✅ .env file updated"
echo ""

# ============================================
# 2. CREATE STORAGE DIRECTORIES
# ============================================
echo "📁 Step 2: Creating storage directories..."

mkdir -p storage/framework/sessions
mkdir -p storage/framework/cache
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache

echo "✅ Directories created"
echo ""

# ============================================
# 3. FIX PERMISSIONS
# ============================================
echo "🔐 Step 3: Fixing permissions..."

chmod -R 775 storage/
echo "✅ storage/ → 775"

chmod -R 775 storage/framework/sessions
echo "✅ storage/framework/sessions → 775"

chmod -R 775 storage/framework/cache
echo "✅ storage/framework/cache → 775"

chmod -R 775 storage/framework/views
echo "✅ storage/framework/views → 775"

chmod -R 775 storage/logs
echo "✅ storage/logs → 775"

chmod -R 775 bootstrap/cache
echo "✅ bootstrap/cache → 775"

echo ""

# ============================================
# 4. CLEAR LARAVEL CACHES
# ============================================
echo "🗑️  Step 4: Clearing caches..."

if command -v php &> /dev/null; then
    php artisan config:clear 2>&1 && echo "✅ Config cleared" || echo "⚠️  Config clear failed"
    php artisan cache:clear 2>&1 && echo "✅ Cache cleared" || echo "⚠️  Cache clear failed"
    php artisan view:clear 2>&1 && echo "✅ View cache cleared" || echo "⚠️  View clear failed"
    php artisan route:clear 2>&1 && echo "✅ Route cache cleared" || echo "⚠️  Route clear failed"
    php artisan optimize:clear 2>&1 && echo "✅ All caches cleared" || echo "⚠️  Optimize clear failed"
else
    # Manual cache clear
    rm -f bootstrap/cache/config.php
    rm -f bootstrap/cache/routes-v7.php
    rm -f bootstrap/cache/events.php
    echo "✅ Cache files deleted manually"
fi

echo ""

# ============================================
# 5. VERIFY
# ============================================
echo "🔍 Verification:"
echo "─────────────────────────────────────────"

# Check SESSION_DRIVER
SESSION_DRIVER=$(grep "SESSION_DRIVER=" .env | cut -d'=' -f2)
if [ "$SESSION_DRIVER" = "file" ]; then
    echo "✅ SESSION_DRIVER=file"
else
    echo "❌ SESSION_DRIVER is '$SESSION_DRIVER' (should be 'file')"
fi

# Check SESSION_SECURE_COOKIE
SECURE_COOKIE=$(grep "SESSION_SECURE_COOKIE=" .env | cut -d'=' -f2)
if [ "$SECURE_COOKIE" = "true" ]; then
    echo "✅ SESSION_SECURE_COOKIE=true"
else
    echo "⚠️  SESSION_SECURE_COOKIE=$SECURE_COOKIE"
fi

# Check permissions
SESS_PERMS=$(stat -c "%a" storage/framework/sessions 2>/dev/null || stat -f "%Lp" storage/framework/sessions 2>/dev/null)
if [ "$SESS_PERMS" = "775" ] || [ "$SESS_PERMS" = "777" ]; then
    echo "✅ Session folder permissions: $SESS_PERMS"
else
    echo "⚠️  Session folder permissions: $SESS_PERMS (should be 775)"
fi

echo ""
echo "╔══════════════════════════════════════════╗"
echo "║  ✅ FIX COMPLETE!                        ║"
echo "╚══════════════════════════════════════════╝"
echo ""
echo "📋 Next steps:"
echo "1. Clear browser cookies for smart.mambaulhikmah.com"
echo "2. Go to https://smart.mambaulhikmah.com/login"
echo "3. Login with your credentials"
echo "4. You should be redirected to dashboard"
echo ""
echo "⚠️  If still having issues:"
echo "   - Check storage/logs/laravel.log"
echo "   - Check if files created in storage/framework/sessions/"
echo "   - Try setting permissions to 777 temporarily"
echo ""
