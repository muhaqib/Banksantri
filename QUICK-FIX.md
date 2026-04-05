# 🚀 Quick Fix: Login cPanel tidak bisa

## ❌ Masalah
Error: `SQLSTATE[01004]: String data, right truncated` pada tabel `sessions`

## ✅ Solusi Cepat (5 menit)

### Cara 1: Via cPanel File Manager (PALING MUDAH)

1. **Login cPanel** → File Manager
2. **Buka:** `/home/mamk7444/public_html/smart.mambaulhikmah.com/`
3. **Edit file `.env`**, ubah bagian ini:
   ```
   SESSION_DRIVER=file
   SESSION_SECURE_COOKIE=true
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://smart.mambaulhikmah.com
   ```
4. **Hapus file ini** (jika ada):
   - `bootstrap/cache/config.php`

5. **Set permissions 775** untuk folder:
   - `storage/framework/sessions`
   - `storage/framework/cache`
   - `storage/framework/views`
   - `storage/logs`

6. **Clear cookies browser** untuk `smart.mambaulhikmah.com`

7. **Test login** → Harusnya sudah bisa masuk dashboard ✅

---

### Cara 2: Via cPanel Terminal (jika ada akses SSH)

Copy paste perintah ini ke terminal cPanel:

```bash
cd /home/mamk7444/public_html/smart.mambaulhikmah.com

# 1. Update .env
sed -i 's/SESSION_DRIVER=.*/SESSION_DRIVER=file/' .env
sed -i 's/APP_ENV=.*/APP_ENV=production/' .env
sed -i 's/APP_DEBUG=.*/APP_DEBUG=false/' .env
sed -i 's|APP_URL=.*|APP_URL=https://smart.mambaulhikmah.com|' .env
grep -q "SESSION_SECURE_COOKIE" .env || echo "SESSION_SECURE_COOKIE=true" >> .env

# 2. Create directories
mkdir -p storage/framework/sessions
mkdir -p storage/framework/cache
mkdir -p storage/framework/views
mkdir -p storage/logs

# 3. Fix permissions
chmod -R 775 storage/framework/sessions
chmod -R 775 storage/framework/cache
chmod -R 775 storage/framework/views
chmod -R 775 storage/logs
chmod -R 775 bootstrap/cache

# 4. Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# 5. Done
echo "✅ Session fix complete!"
```

---

### Cara 3: Upload & Run Script

1. **Upload file** `fix-cpanel-sessions.sh` ke root Laravel via cPanel File Manager
2. **Jalankan via cPanel Terminal:**
   ```bash
   bash fix-cpanel-sessions.sh
   ```

---

## 🔍 Verifikasi

Setelah fix, cek hal-hal ini:

### 1. Session files tercipta
```bash
ls -la storage/framework/sessions/
```
Harusnya ada file dengan nama panjang seperti:
`cdZo2Cw3VJm9Sj5pX6gIM0K84LLF1STiHGP5B99S`

### 2. Tidak ada error di log
```bash
tail -50 storage/logs/laravel.log
```

### 3. Session driver aktif
```bash
grep "SESSION_DRIVER=" .env
```
Output: `SESSION_DRIVER=file`

---

## 🐛 Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Folder permissions error | Set ke 777 sementara |
| Session files tidak tercipta | Cek ownership folder (harusnya user cPanel) |
| Masih redirect loop | Clear browser cookies, cek APP_URL |
| Error 500 | Cek `storage/logs/laravel.log` |
| Cookies tidak tersimpan | Pastikan `SESSION_SECURE_COOKIE=true` untuk HTTPS |

---

## 📞 Info Server

- **Domain:** smart.mambaulhikmah.com
- **Path:** `/home/mamk7444/public_html/smart.mambaulhikmah.com/`
- **Database:** mamk7444_mawa
- **PHP:** 8.5.4
- **Laravel:** 13.3.0
