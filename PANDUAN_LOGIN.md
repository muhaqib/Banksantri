# 🕌 Bank Pesantren - Panduan Login

## ✅ Database Sudah Di-seed

Database telah berhasil di-seed dengan data user berikut:

### 👤 Akun Admin
- **Email**: `admin@pesantren.id`
- **Password**: `password`
- **Role**: Admin

### 👨‍💼 Akun Petugas
1. **Petugas 1**
   - **Email**: `petugas1@pesantren.id`
   - **Password**: `password`
   - **Role**: Petugas

2. **Petugas 2**
   - **Email**: `petugas2@pesantren.id`
   - **Password**: `password`
   - **Role**: Petugas

### 🎓 Akun Santri
1. **Ahmad Fauzi**
   - **Email**: `ahmad@pesantren.id`
   - **NIS**: `12345`
   - **Password**: `password`
   - **PIN**: `123456`
   - **Saldo**: Rp 150.000
   - **RFID**: `RFID001`

2. **Budi Santoso**
   - **Email**: `budi@pesantren.id`
   - **NIS**: `12346`
   - **Password**: `password`
   - **PIN**: `123456`
   - **Saldo**: Rp 200.000
   - **RFID**: `RFID002`

3. **Candra Wijaya**
   - **Email**: `candra@pesantren.id`
   - **NIS**: `12347`
   - **Password**: `password`
   - **PIN**: `123456`
   - **Saldo**: Rp 75.000
   - **RFID**: `RFID003`

---

## 🚀 Cara Login

### 1. Akses Aplikasi
Buka browser dan akses: **http://127.0.0.1:8002/login**

### 2. Pilih Role
Di halaman login, pilih role Anda:
- Klik **Admin** untuk login sebagai admin
- Klik **Petugas** untuk login sebagai petugas
- Klik **Santri** untuk login sebagai santri

### 3. Masukkan Kredensial
- **Untuk Admin/Petugas**: Masukkan email dan password
- **Untuk Santri**: Masukkan NIS (contoh: `12345`) atau email atau nama, lalu password

### 4. Klik "Masuk Ke Akun"

---

## 📱 Fitur Tiap Role

### Admin
- Dashboard dengan analitik keuangan
- Kelola Kas Masuk/Keluar
- Monitoring Kinerja Petugas
- Approval Settlement Penarikan Tunai

### Petugas
- Dashboard transaksi
- Proses transaksi dengan RFID + PIN
- Riwayat transaksi
- Tarik Tunai (request ke Admin)

### Santri (Mobile UI)
- Cek saldo
- Notifikasi saldo menipis (≤ Rp 10.000)
- Riwayat transaksi (color-coded)
- Ganti PIN
- UI mobile-friendly seperti byond by BSI

---

## 🔧 Troubleshooting

### Login Gagal?
1. Pastikan server berjalan: `php artisan serve`
2. Clear cache: `php artisan cache:clear && php artisan config:clear`
3. Cek koneksi database di `.env`

### Database Kosong?
Jalankan seed ulang:
```bash
php artisan migrate:fresh --seed
```

### Tampilan Rusak?
Rebuild assets:
```bash
npm run build
```

---

## 📞 Butuh Bantuan?

Jika ada masalah, pastikan:
- ✅ MySQL sudah berjalan
- ✅ Database `laravel` sudah dibuat
- ✅ File `.env` sudah dikonfigurasi dengan benar
- ✅ `APP_KEY` sudah di-generate (`php artisan key:generate`)

---

**Server URL**: http://127.0.0.1:8002/login
