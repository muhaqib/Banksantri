# 🚀 QUICK DEPLOY - 5 Menit Fix Login cPanel

## 📋 YANG HARUS DILAKUKAN:

### ✅ 1. UPDATE .env (2 menit)

**Edit via cPanel File Manager:**
`/home/mamk7444/public_html/smart.mambaulhikmah.com/.env`

```env
SESSION_DRIVER=file
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
SESSION_DOMAIN=
APP_URL=https://smart.mambaulhikmah.com
APP_ENV=production
APP_DEBUG=false
```

---

### ✅ 2. HAPUS CACHE (30 detik)

**Hapus file ini** (jika ada):
```
bootstrap/cache/config.php
bootstrap/cache/routes-v7.php
```

---

### ✅ 3. SET PERMISSIONS (30 detik)

**Klik kanan folder → Change Permissions → 775:**
```
storage/framework/sessions/
storage/framework/cache/
storage/framework/views/
storage/logs/
bootstrap/cache/
```

---

### ✅ 4. UPLOAD FILE (30 detik)

**Upload file ini** (timpa yang lama):
```
app/Http/Controllers/Auth/LoginController.php
```

---

### ✅ 5. TEST SESSION (1 menit)

1. **Upload file:** `public/test-session.php`
2. **Buka:** `https://smart.mambaulhikmah.com/test-session.php`
3. **Refresh 2-3x** → Harus ada "Session Persisted!"
4. **Hapus file test-session.php** setelah selesai

---

### ✅ 6. TEST LOGIN (1 menit)

1. **Clear cookies browser** untuk domain `smart.mambaulhikmah.com`
2. **Buka:** `https://smart.mambaulhikmah.com/login`
3. **Login** dengan credentials
4. **Harusnya redirect ke dashboard** ✅
5. **Refresh dashboard** → Masih login ✅

---

## 🎯 HASIL AKHIR:

| Sebelum | Sesudah |
|---------|---------|
| ❌ Login redirect ke /login | ✅ Login ke dashboard |
| ❌ Session tidak tersimpan | ✅ Session di file |
| ❌ Error Bcrypt | ✅ Auto-convert password |
| ❌ Error database sessions | ✅ Tidak ada query sessions |
| ❌ Error 500 | ✅ Normal |

---

## 🐛 MASIH ERROR?

**Cek:** `storage/logs/laravel.log`

**Atau test manual:**
```
https://smart.mambaulhikmah.com/test-session.php
```

Semua informasi debug ada di sana!

---

## 📁 FILE YANG DI-UPLOAD:

```
✅ .env (update isi)
✅ app/Http/Controllers/Auth/LoginController.php (update)
✅ public/test-session.php (temporary, hapus setelah test)
```

**DONE!** 🎉
