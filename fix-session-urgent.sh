#!/bin/bash
# ============================================
# URGENT FIX: Session Database Error
# Switch to FILE driver immediately
# ============================================

echo "🚨 URGENT: Fixing session database error..."
echo ""

# 1. Change session driver in .env
echo "📝 Updating .env..."
if [ -f .env ]; then
    sed -i 's/SESSION_DRIVER=.*/SESSION_DRIVER=file/' .env
    sed -i '/SESSION_SECURE_COOKIE/d' .env
    echo "SESSION_SECURE_COOKIE=true" >> .env
    echo "✅ .env updated"
else
    echo "❌ .env not found"
    exit 1
fi

# 2. Fix permissions
echo "🔐 Fixing permissions..."
chmod -R 775 storage/framework/sessions
chmod -R 775 storage/framework/cache
chmod -R 775 storage/framework/views
chmod -R 775 storage/logs
chmod -R 775 bootstrap/cache
echo "✅ Permissions set"

# 3. Clear caches
echo "🗑️  Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
echo "✅ Caches cleared"

echo ""
echo "✅ DONE! Login should work now."
echo "   Try logging in again."
