# 🚀 DEPLOY - Fix Password Bcrypt Error

## ✅ Yang Sudah Diperbaiki

**LoginController** sekarang punya fitur:
1. ✅ Auto-detect password format (Bcrypt, MD5, SHA1, SHA256, Plain text)
2. ✅ Auto-convert password ke Bcrypt setelah login berhasil
3. ✅ Fallback untuk password yang tidak pakai Bcrypt
4. ✅ Session file driver sudah bekerja

---

## 📤 Cara Deploy ke cPanel

### **Opsi 1: Upload File via cPanel File Manager** (TERCEPAT)

1. **Login ke cPanel** → File Manager
2. **Navigate ke:** `/home/mamk7444/public_html/smart.mambaulhikmah.com/app/Http/Controllers/Auth/`
3. **Upload file** `LoginController.php` (timpa yang lama)
4. **Test login** → Harusnya sudah bisa masuk dashboard ✅

---

### **Opsi 2: Via Git (jika pakai Git di cPanel)**

```bash
cd /home/mamk7444/public_html/smart.mambaulhikmah.com
git pull origin main
```

---

### **Opsi 3: Copy-Paste Manual**

1. **Buka** cPanel File Manager
2. **Edit file:** `app/Http/Controllers/Auth/LoginController.php`
3. **Replace semua isi file** dengan yang baru (dari repository)
4. **Save**
5. **Test login**

---

## 🔍 Bagaimana Cara Kerjanya?

### Flow Login Baru:

```
1. User input username + password
2. Cari user di database
3. Coba Auth::attempt (Bcrypt)
   ↓ Jika error "not Bcrypt":
4. Cek password manual:
   - Plain text match?
   - MD5 match?
   - SHA1 match?
   - SHA256 match?
5. Jika cocok → Auth::login() langsung
6. Auto-convert password ke Bcrypt
7. Redirect ke dashboard ✅
```

### Setelah Login Berhasil:
- Password user otomatis di-convert ke Bcrypt
- Login berikutnya akan pakai Bcrypt normal
- Tidak perlu query manual lagi

---

## 🎯 Hasil Akhir

Setelah deploy:
1. ✅ Login dengan password lama (apapun formatnya) → **BERHASIL**
2. ✅ Password otomatis di-convert ke Bcrypt
3. ✅ Login berikutnya pakai Bcrypt normal
4. ✅ Session tersimpan di file (bukan database)
5. ✅ Dashboard sesuai role (admin/petugas/santri)

---

## 🐛 Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Masih error Bcrypt | Pastikan file sudah ter-upload dengan benar |
| Error 500 | Cek `storage/logs/laravel.log` |
| Session tidak jalan | Pastikan `SESSION_DRIVER=file` di `.env` |
| Permissions error | Set folder `storage/` ke 775 |

---

## 📝 Catatan

- **User yang diregister via RegisterController** sudah pakai Bcrypt
- **User yang ada di database production** kemungkinan tidak pakai Bcrypt
- Fix ini menangani **semua format password** dan auto-convert
- Setelah semua user login minimal 1x, semua password akan jadi Bcrypt
