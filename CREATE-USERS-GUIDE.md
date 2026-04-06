# 🚨 SOLUSI: "Username, email, NIS, atau password salah"

## MASALAH:
Login selalu muncul error: **"Username, email, NIS, atau password salah"**

## PENYEBAB:
1. ❌ User tidak ada di database production
2. ❌ Password di database bukan Bcrypt
3. ❌ Email/username yang dimasukkan tidak cocok

---

## ✅ SOLUSI TERCEPAT: BUAT USER BARU

### CARA 1: Via Script (PALING MUDAH - 2 MENIT)

**1. Upload file:**
```
public/create-users.php
```
Ke: `/home/mamk7444/public_html/smart.mambaulhikmah.com/public/`

**2. Buka di browser:**
```
https://smart.mambaulhikmah.com/create-users.php
```

**3. Klik "Create Users Now"**

**4. User yang tercipta:**

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@tabungan.id | admin123 |
| Admin | admin@gmail.com | admin123 |
| Petugas | petugas@tabungan.id | petugas123 |
| Santri | santri@tabungan.id | santri123 |

**5. Test login:**
```
URL: https://smart.mambaulhikmah.com/login
Email: admin@tabungan.id
Password: admin123
Role: Admin
```

**6. HAPUS file create-users.php setelah selesai!**

---

### CARA 2: Via phpMyAdmin (Manual)

**1. Buka cPanel → phpMyAdmin**

**2. Pilih database: `mamk7444_mawa`**

**3. Jalankan SQL ini:**

```sql
-- Create Admin User
INSERT INTO users (name, email, password, role, email_verified_at, created_at, updated_at)
VALUES (
    'admin',
    'admin@tabungan.id',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin',
    NOW(),
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE 
    password = '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    role = 'admin',
    name = 'admin';
```

**Password untuk admin:** `admin123`

---

### CARA 3: Via Seeder (jika ada SSH access)

```bash
cd /home/mamk7444/public_html/smart.mambaulhikmah.com
php artisan db:seed --class=UserSeeder
```

---

## 🔍 CARA CEK USER DI DATABASE

### Via phpMyAdmin:

```sql
-- Lihat semua user
SELECT id, name, email, role, 
       SUBSTRING(password, 1, 30) as password_hash,
       CASE 
           WHEN password LIKE '$2y$%' THEN '✅ Bcrypt'
           ELSE '❌ Not Bcrypt'
       END as hash_type
FROM users;
```

**Hasil yang diharapkan:**

| id | name | email | role | hash_type |
|----|------|-------|------|-----------|
| 1 | admin | admin@tabungan.id | admin | ✅ Bcrypt |

---

## 🎯 TEST LOGIN SETELAH USER DIBUAT

### Test di Browser:

**1. Clear cookies browser**
   - F12 → Application → Cookies → Delete all for smart.mambaulhikmah.com

**2. Test di Incognito**
   - Ctrl+Shift+N
   
**3. Login:**
   ```
   URL: https://smart.mambaulhikmah.com/login
   
   Role: Admin
   Username: admin@tabungan.id
   Password: admin123
   ```

**4. Expected result:**
   - ✅ Redirect ke `/admin/dashboard`
   - ✅ Dashboard tampil
   - ✅ Refresh tetap login

---

## 🐛 JIKA MASIH ERROR "USERNAME SALAH"

### Debug Step:

**1. Cek apakah user ada:**
```sql
SELECT * FROM users WHERE email = 'admin@tabungan.id';
```

**Kalau kosong** → User tidak ada, buat ulang dengan script

**2. Cek password hash:**
```sql
SELECT password FROM users WHERE email = 'admin@tabungan.id';
```

**Harusnya mulai dengan:** `$2y$12$`

**Kalau tidak** → Password bukan Bcrypt, run script create-users.php

**3. Cek role:**
```sql
SELECT role FROM users WHERE email = 'admin@tabungan.id';
```

**Harusnya:** `admin` (bukan `Admin` atau `ADMIN`)

---

## 📋 CHECKLIST

```
[ ] 1. Upload create-users.php ke public/
[ ] 2. Buka https://smart.mambaulhikmah.com/create-users.php
[ ] 3. Klik "Create Users Now"
[ ] 4. Lihat tabel user yang tercipta (semua ✅ Bcrypt)
[ ] 5. Clear browser cookies
[ ] 6. Test login: admin@tabungan.id / admin123 / Admin
[ ] 7. Redirect ke dashboard ✅
[ ] 8. HAPUS create-users.php dari server
```

---

## 🗑️ SETELAH SELESAI

**WAJIB HAPUS FILE INI:**
```
public/create-users.php
```

File ini bisa disalahgunakan untuk membuat user baru!

---

## 💡 TIPS

1. **Simpan credentials** user yang sudah dibuat
2. **Ganti password** default setelah login pertama
3. **Jangan share** password admin
4. **Backup database** sebelum modify user

---

**Upload `create-users.php` sekarang dan buat user!** 🚀
