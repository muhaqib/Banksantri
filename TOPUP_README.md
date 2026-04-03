# Sistem Top Up Saldo Santri

## Deskripsi
Sistem top up saldo santri dengan alur verifikasi admin yang aman dan terstruktur.

## Fitur Utama

### 1. **Santri - Pengajuan Top Up**
- Form pengisian nominal top up (min: Rp 1.000, max: Rp 10.000.000)
- Upload bukti pembayaran (gambar)
- Quick nominal buttons (Rp 10K, 20K, 50K, 100K, 200K, 500K)
- Preview gambar sebelum submit
- Riwayat top up dengan status real-time
- Status top up:
  - **Pending**: Menunggu verifikasi admin
  - **Approved**: Terverifikasi, saldo sudah bertambah
  - **Rejected**: Ditolak oleh admin

### 2. **Admin - Verifikasi Top Up**
- Dashboard menampilkan jumlah top up pending
- Halaman verifikasi dengan 2 section:
  - **Menunggu Verifikasi**: List semua top up yang pending
  - **Riwayat Top Up**: Top up yang sudah diproses (approved/rejected)
- Detail top up:
  - Nama santri & NIS
  - Nominal top up
  - Tanggal pengajuan
  - Bukti pembayaran (bisa dilihat)
- Aksi:
  - **Verifikasi**: Saldo santri otomatis bertambah
  - **Tolak**: Bisa memberikan alasan penolakan

## Database

### Tabel: `top_up_requests`
```sql
- id (bigint, primary key)
- santri_id (bigint, foreign key to users)
- nominal (decimal 15,2)
- bukti_pembayaran (string - path file)
- status (enum: 'pending', 'approved', 'rejected')
- admin_note (text, nullable)
- admin_id (bigint, foreign key to users, nullable)
- verified_at (timestamp, nullable)
- created_at (timestamp)
- updated_at (timestamp)
```

## Routes

### Santri Routes
```
GET  /santri/topup              - Form top up
POST /santri/topup              - Submit top up request
GET  /santri/topup/status       - Get status via AJAX
```

### Admin Routes
```
GET  /admin/topup                      - List top up requests
GET  /admin/topup/{topUp}              - Show detail (JSON)
GET  /admin/topup/{topUp}/modal-data   - Get modal data (JSON)
POST /admin/topup/{topUp}/approve      - Approve top up
POST /admin/topup/{topUp}/reject       - Reject top up
```

## Alur Sistem

### 1. Santri Melakukan Top Up
1. Santri klik "Top Up" atau "Isi Saldo" di dashboard
2. Isi nominal atau pilih nominal cepat
3. Upload bukti pembayaran
4. Submit form
5. Status: **Pending** (saldo BELUM bertambah)

### 2. Admin Verifikasi
1. Admin melihat notifikasi di sidebar (badge count)
2. Klik "Verifikasi Top Up" di sidebar
3. Lihat daftar pending requests
4. Klik "Lihat Bukti" untuk melihat bukti pembayaran
5. Pilih aksi:
   - **Verifikasi**: Status → Approved, saldo santri bertambah otomatis
   - **Tolak**: Status → Rejected, bisa berikan alasan

### 3. Keamanan
- Transaction database transaction saat approve (rollback jika error)
- Validasi file upload (image only, max 2MB)
- Validasi nominal (min 1000, max 10.000.000)
- Hanya admin yang bisa verifikasi
- Saldo hanya bertambah setelah admin approve

## File yang Dibuat/Dimodifikasi

### Database
- ✅ `database/migrations/2026_04_03_001239_create_top_up_requests_table.php`

### Models
- ✅ `app/Models/TopUpRequest.php`
- ✅ `app/Models/User.php` (added relationships)

### Controllers
- ✅ `app/Http/Controllers/Santri/TopUpController.php`
- ✅ `app/Http/Controllers/Admin/TopUpController.php`
- ✅ `app/Http/Controllers/Admin/DashboardController.php` (added pending count)

### Views
- ✅ `resources/views/pages/santri/topup.blade.php`
- ✅ `resources/views/pages/santri/home.blade.php` (added link)
- ✅ `resources/views/pages/admin/topup/index.blade.php`
- ✅ `resources/views/components/sidebar.blade.php` (added menu)

### Routes
- ✅ `routes/web.php` (added top-up routes)

## Cara Menggunakan

### Untuk Santri:
1. Login sebagai santri
2. Klik "Top Up" di dashboard atau "Isi Saldo" di balance card
3. Masukkan nominal atau pilih nominal cepat
4. Upload bukti pembayaran
5. Klik "Ajukan Top Up"
6. Tunggu verifikasi admin

### Untuk Admin:
1. Login sebagai admin
2. Lihat notifikasi di sidebar (badge merah jika ada pending)
3. Klik "Verifikasi Top Up"
4. Lihat detail & bukti pembayaran
5. Klik "Verifikasi" atau "Tolak"

## Testing

### Test Flow:
1. Login sebagai santri → Buat top up
2. Login sebagai admin → Verifikasi
3. Login kembali sebagai santri → Cek saldo bertambah

## Keamanan
✅ CSRF Protection
✅ File validation (image, max 2MB)
✅ Nominal validation (1000 - 10.000.000)
✅ Database transactions untuk approve
✅ Role-based access (middleware)
✅ Foreign key constraints
