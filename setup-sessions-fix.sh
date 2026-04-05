#!/bin/bash
# ============================================
# Laravel Session Fix for cPanel (HTTPS)
# Session Driver: file + Permission Setup
# ============================================

echo "🔧 Laravel Session Fix - cPanel Production Setup"
echo "================================================"
echo ""

# 1. Update .env file
echo "📝 Updating .env file..."
if [ -f .env ]; then
    # Replace SESSION_DRIVER if exists
    sed -i.bak 's/SESSION_DRIVER=.*/SESSION_DRIVER=file/' .env
    
    # Replace SESSION_SECURE_COOKIE if exists
    if grep -q "SESSION_SECURE_COOKIE=" .env; then
        sed -i.bak 's/SESSION_SECURE_COOKIE=.*/SESSION_SECURE_COOKIE=true/' .env
    else
        echo "SESSION_SECURE_COOKIE=true" >> .env
    fi
    
    # Replace SESSION_SAME_SITE if exists
    if grep -q "SESSION_SAME_SITE=" .env; then
        sed -i.bak 's/SESSION_SAME_SITE=.*/SESSION_SAME_SITE=lax/' .env
    else
        echo "SESSION_SAME_SITE=lax" >> .env
    fi
    
    # Set production environment
    sed -i.bak 's/APP_ENV=.*/APP_ENV=production/' .env
    sed -i.bak 's/APP_DEBUG=.*/APP_DEBUG=false/' .env
    
    echo "✅ .env file updated successfully"
    echo ""
    echo "📄 Current session settings in .env:"
    grep -E "SESSION_|APP_ENV|APP_DEBUG" .env
else
    echo "❌ .env file not found!"
    exit 1
fi

echo ""

# 2. Fix folder permissions
echo "🔐 Setting folder permissions..."

chmod -R 775 storage/
echo "✅ storage/ → 775"

chmod -R 775 storage/framework/
echo "✅ storage/framework/ → 775"

chmod -R 775 storage/framework/sessions
echo "✅ storage/framework/sessions → 775"

chmod -R 775 storage/framework/cache
echo "✅ storage/framework/cache → 775"

chmod -R 775 storage/framework/views
echo "✅ storage/framework/views → 775"

chmod -R 775 storage/logs
echo "✅ storage/logs → 775"

chmod -R 775 bootstrap/cache/
echo "✅ bootstrap/cache/ → 775"

echo ""

# 3. Clear Laravel caches
echo "🗑️  Clearing Laravel caches..."

php artisan config:clear 2>/dev/null && echo "✅ Config cache cleared" || echo "⚠️  Config clear skipped"
php artisan cache:clear 2>/dev/null && echo "✅ Application cache cleared" || echo "⚠️  Cache clear skipped"
php artisan view:clear 2>/dev/null && echo "✅ View cache cleared" || echo "⚠️  View clear skipped"
php artisan route:clear 2>/dev/null && echo "✅ Route cache cleared" || echo "⚠️  Route clear skipped"

echo ""

# 4. Verify session directory
echo "✅ Session Setup Verification:"
echo "-------------------------------"

if [ -d "storage/framework/sessions" ]; then
    echo "✅ Session directory exists"
    PERMS=$(stat -f "%Lp" storage/framework/sessions 2>/dev/null || stat -c "%a" storage/framework/sessions 2>/dev/null)
    echo "✅ Permissions: $PERMS"
    
    FILE_COUNT=$(find storage/framework/sessions -type f -name "*.php" | wc -l | tr -d ' ')
    echo "✅ Session files count: $FILE_COUNT (excluding .gitkeep)"
else
    echo "❌ Session directory missing!"
fi

echo ""
echo "================================================"
echo "🎉 Session fix complete!"
echo ""
echo "📌 Next steps on cPanel:"
echo "1. Upload this script to your Laravel root"
echo "2. Run: bash setup-sessions-fix.sh"
echo "3. Test login"
echo ""
echo "⚠️  If still not working, manually set permissions"
echo "    to 777 in cPanel File Manager (temporary fix)"
echo "================================================"
