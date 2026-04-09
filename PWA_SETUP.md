# PWA (Progressive Web App) Setup Guide

## Overview
This Laravel application has been configured as a Progressive Web App (PWA) with the Pondok Pesantren Mambaul Hikmah logo.

## What's Been Set Up

### 1. **Manifest File** (`public/manifest.json`)
- App name: Tabungan AI
- Theme color: #065f46 (green)
- Display mode: standalone
- Multiple icon sizes for various devices

### 2. **Service Worker** (`public/sw.js`)
- Caches essential assets for offline functionality
- Provides faster load times for cached resources
- Automatically updates cache when needed

### 3. **App Icons** (`public/images/icons/`)
Generated icons in 8 different sizes:
- 72x72px, 96x96px, 128x128px, 144x144px
- 152x152px, 192x192px, 384x384px, 512x512px

### 4. **Meta Tags** (Added to `resources/views/layouts/app.blade.php`)
- Theme color for browser UI
- Apple mobile web app capabilities
- Manifest link
- Apple touch icons
- Service worker registration (in production only)

## How to Use

### For Users
1. Open the application in a modern browser (Chrome, Edge, Safari)
2. Look for the "Install" or "Add to Home Screen" prompt
3. Click install to add the app to your device
4. The app will now work like a native application

### For Developers

#### Re-generate Icons
If you need to update the logo, run:
```bash
php generate-icons.php
```

This will regenerate all icon sizes from `public/images/logo.png`

#### Customize Manifest
Edit `public/manifest.json` to change:
- App name and description
- Theme colors
- Start URL
- Display orientation

#### Update Service Worker
Edit `public/sw.js` to modify caching behavior:
- Add more files to cache
- Change cache version
- Modify caching strategies

## Testing PWA

### 1. Local Development
```bash
# Build assets
npm run build

# Start server
php artisan serve
```

Visit `http://localhost:8000`

### 2. Chrome DevTools
1. Open DevTools (F12)
2. Go to "Application" tab
3. Check "Manifest" - should show app info
4. Check "Service Workers" - should show registered SW
5. Click "Lighthouse" tab → Generate report for PWA audit

### 3. Installation Test
- Desktop: Look for install icon in address bar
- Mobile: "Add to Home Screen" in browser menu

## Production Deployment

The service worker only registers in production environment (`APP_ENV=production`). Make sure:

1. Your `.env` has `APP_ENV=production`
2. All assets are built: `npm run build`
3. Routes are cached: `php artisan route:cache`
4. HTTPS is enabled (required for PWA)

## Troubleshooting

### Icons not showing
- Verify icons exist in `public/images/icons/`
- Check file permissions (should be readable)
- Clear browser cache

### Service Worker not registering
- Must be on HTTPS or localhost
- Check browser console for errors
- Verify `APP_ENV=production` in `.env`

### Install prompt not appearing
- PWA requires HTTPS (except localhost)
- App must have valid manifest
- Service worker must be registered
- User must have visited the site at least twice with 5+ minutes between visits

## Files Modified/Created

### Created
- `public/manifest.json` - PWA manifest
- `public/sw.js` - Service worker
- `public/images/icons/` - App icons (8 sizes)
- `generate-icons.php` - Icon generation script

### Modified
- `resources/views/layouts/app.blade.php` - Added PWA meta tags and service worker registration

## Browser Support

| Browser | Support |
|---------|---------|
| Chrome/Edge | ✅ Full |
| Firefox | ✅ Full |
| Safari (iOS) | ✅ Partial |
| Safari (macOS) | ✅ Partial |
| Samsung Internet | ✅ Full |

## Additional Resources

- [MDN PWA Guide](https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps)
- [Web.dev PWA Checklist](https://web.dev/pwa-checklist/)
- [Manifest Generator](https://tomitm.github.io/appmanifest/)
