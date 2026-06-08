# Product Requirements Document Berdasarkan Implementasi

## 1. Informasi Dokumen

| Item | Nilai |
|---|---|
| Nama produk pada antarmuka | Mawa Smart / Mawasmart |
| Deskripsi produk pada manifest | Sistem Manajemen Keuangan Pondok Pesantren Mambaul Hikmah |
| Jenis dokumen | PRD as-built, disusun dari source code yang tersedia |
| Tanggal analisis | 7 Juni 2026 |
| Cakupan | Aplikasi web, REST API, database, autentikasi, otorisasi, dashboard, laporan, PWA, dan proses pendukung |

Dokumen ini mendeskripsikan perilaku yang sudah diimplementasikan. Pernyataan mengenai fitur yang belum lengkap hanya dibuat ketika source code secara eksplisit menunjukkan placeholder, `TODO`, route yang tidak tersedia, atau dependensi data yang tidak memiliki migration di repository.

## 2. Ringkasan Produk

Mawa Smart adalah aplikasi pengelolaan keuangan dan data operasional pesantren dengan tiga kelompok pengguna:

1. **Admin** mengelola data santri, petugas, kamar, keuangan, top-up, settlement, prestasi hafalan kitab, blog, permission petugas, dan profil.
2. **Petugas** memproses transaksi santri berbasis RFID dan PIN, melihat riwayat/kinerja, mengajukan settlement, mengelola prestasi hafalan jika diberi permission, dan mengelola profil.
3. **Santri** melihat saldo, ringkasan dan riwayat transaksi, mengajukan top-up, melihat prestasi hafalan, dan mengelola profil melalui web maupun REST API.

Aplikasi juga menyediakan API publik untuk blog, slider, galeri, formulir pendaftaran, dan kontak. API galeri, pendaftaran, dan kontak saat ini belum memiliki penyimpanan persisten.

## 3. Sumber Analisis

Area source code yang dianalisis:

- `routes/web.php`, `routes/api.php`, dan `bootstrap/app.php`
- Seluruh file dalam `app/Models`
- Seluruh file dalam `app/Http/Controllers`
- `app/Support/PermissionRegistry.php`
- Seluruh migration dan seeder dalam `database`
- Seluruh view dalam `resources/views`
- Konfigurasi aplikasi dalam `config`
- Aset frontend dan PWA dalam `resources/js`, `resources/css`, `public/manifest.json`, dan `public/sw.js`
- Command dalam `app/Console/Commands`
- Test dalam `tests`

Hasil scan:

- Tidak terdapat file implementasi dalam `app/Services`, `app/Repositories`, atau `app/Livewire`.
- Logika bisnis utama ditempatkan langsung dalam controller dan model Eloquent.
- `php artisan route:list` menghasilkan 120 route: 97 route non-API dan 23 route API.

## 4. Sasaran Produk yang Terlihat dari Implementasi

Implementasi saat ini mendukung sasaran operasional berikut:

- Mencatat saldo digital setiap santri.
- Mencatat transaksi masuk dan keluar beserta saldo sebelum/sesudah.
- Memungkinkan petugas unit memproses pembayaran atau penarikan santri.
- Memungkinkan petugas mengumpulkan saldo hasil transaksi dan mengajukan settlement kepada admin.
- Memungkinkan santri mengajukan top-up dengan bukti pembayaran untuk diverifikasi admin.
- Memungkinkan admin melakukan top-up langsung.
- Memisahkan kas utama dari saldo dan transaksi santri.
- Mengelola data pengguna, kamar santri, prestasi hafalan kitab, dan publikasi blog.
- Menyediakan akses web berbasis role/permission serta API token untuk klien santri.

## 5. Aktor, Role, dan Otorisasi

### 5.1 Role

Kolom `users.role` hanya menerima:

| Role | Fungsi |
|---|---|
| `admin` | Pengelola utama sistem |
| `petugas` | Operator transaksi/unit dan pembimbing prestasi |
| `santri` | Pemilik saldo dan data prestasi |

Role disimpan dua kali secara konseptual:

- Kolom enum `users.role`, yang digunakan oleh banyak query dan redirect.
- Role Spatie pada tabel `roles` dan `model_has_roles`, yang digunakan middleware `role:*`.

Saat login web, sistem menyinkronkan role Spatie apabila user belum memiliki role yang sama dengan `users.role`.

### 5.2 Permission

| Permission | Kapabilitas |
|---|---|
| `admin.dashboard.view` | Melihat dashboard admin |
| `admin.finance.manage` | Mengelola kas, transaksi, top-up, dan settlement |
| `admin.santri.manage` | Mengelola data santri |
| `admin.petugas.manage` | Mengelola data petugas |
| `admin.kamar.manage` | Mengelola kamar santri |
| `admin.prestasi.manage` | Mengelola prestasi santri |
| `admin.blog.manage` | Mengelola blog |
| `admin.access.manage` | Mengelola permission petugas |
| `admin.profile.manage` | Mengelola profil admin |
| `petugas.dashboard.view` | Melihat dashboard petugas |
| `petugas.transactions.manage` | Memproses transaksi santri |
| `petugas.history.view` | Melihat riwayat transaksi yang diproses |
| `petugas.withdrawals.manage` | Mengajukan dan melihat tarik tunai/settlement |
| `petugas.prestasi.manage` | Mengelola prestasi santri dan kitab |
| `petugas.profile.manage` | Mengelola profil petugas |
| `santri.dashboard.view` | Melihat beranda santri |
| `santri.history.view` | Melihat riwayat transaksi sendiri |
| `santri.topup.manage` | Mengajukan dan melihat top-up sendiri |
| `santri.prestasi.view` | Melihat prestasi sendiri |
| `santri.profile.manage` | Mengelola profil santri |

Default permission:

- Role admin memperoleh semua permission non-`petugas.*`.
- Role santri memperoleh seluruh permission `santri.*` yang terdaftar.
- Role petugas tidak memperoleh role permission secara default, tetapi user petugas baru diberi direct permission dari `PermissionRegistry::petugasDefaults()`.
- Admin dapat mengganti seluruh direct permission seorang petugas melalui halaman manajemen akses.

### 5.3 Mekanisme Otorisasi

- Route web terproteksi oleh middleware session `auth`.
- Prefix admin, petugas, dan santri terproteksi middleware role Spatie.
- Setiap fitur web terproteksi middleware permission Spatie.
- Menu sidebar juga disaring dengan Blade `@can`.
- API santri terproteksi `auth:sanctum`.
- Detail transaksi, top-up, dan prestasi pada API memeriksa kepemilikan data sebelum mengembalikan respons.

## 6. Autentikasi

### 6.1 Login Web

1. Pengguna membuka halaman pemilihan role.
2. Pengguna memilih `admin`, `petugas`, atau `santri`.
3. Admin/petugas login menggunakan username atau email dan password.
4. Santri login hanya menggunakan NIS; form dan controller tidak meminta password atau PIN.
5. Setelah berhasil, session diregenerasi dan pengguna diarahkan ke dashboard sesuai role.
6. Logout menghapus session, menginvalidasi session lama, dan meregenerasi token CSRF.

### 6.2 Registrasi Web

- Route `/register` tersedia untuk guest.
- Registrasi meminta nama, email, username, password, dan konfirmasi password.
- Setiap registrasi web membuat user dengan role `admin`, menyinkronkan role Spatie `admin`, lalu langsung login.

### 6.3 Login API Santri

1. Santri mengirim NIS dan PIN enam karakter.
2. Sistem mencari user dengan role `santri`.
3. PIN mendukung nilai plaintext lama atau hash.
4. Jika PIN plaintext cocok, PIN langsung dikonversi menjadi hash.
5. Seluruh token lama user dicabut.
6. Sistem membuat Sanctum token `santri-mobile` dengan ability `santri`.

Catatan implementasi: repository tidak memiliki migration `personal_access_tokens`. Pemanggilan Sanctum token membutuhkan tabel tersebut tersedia dari sumber di luar migration repository atau proses login API akan gagal saat membuat/menarik token.

### 6.4 Pengelolaan Kredensial

- Password model memiliki cast `hashed`.
- Admin, petugas, dan santri dapat mengganti email dan password melalui endpoint profil masing-masing.
- Perubahan password memerlukan password saat ini dan konfirmasi password baru.
- Perubahan PIN via API santri memverifikasi PIN lama dan menyimpan PIN baru sebagai hash.
- Perubahan PIN via web santri hanya memvalidasi input lalu mengembalikan pesan sukses; verifikasi dan update ditandai `TODO` dan belum dijalankan.
- Tersedia command CLI untuk reset dan audit/rehash password.

## 7. Fitur dan Kebutuhan Fungsional

### 7.1 Dashboard Admin

Dashboard admin menampilkan:

- Total top-up hari ini dari transaksi `masuk` berkategori `top_up`.
- Total nominal transaksi keluar hari ini.
- Jumlah seluruh transaksi hari ini.
- Saldo kas utama, dihitung dari total kas masuk dikurangi kas keluar.
- Lima transaksi terbaru beserta santri dan petugas.
- Kinerja petugas hari ini: jumlah transaksi, total nominal, saldo digital, dan status aktif yang selalu diset `true`.
- Maksimal lima permintaan settlement pending.
- Jumlah settlement pending dan top-up pending.
- Sepuluh aktivitas top-up terbaru yang sudah disetujui atau ditolak.
- Tren top-up dan transaksi keluar selama tujuh hari terakhir, dinormalisasi menjadi persentase untuk visual chart.

### 7.2 Manajemen Santri

Admin dapat:

- Melihat daftar santri dengan pagination 15 data.
- Mencari santri berdasarkan nama atau NIS.
- Mengambil autocomplete maksimal 10 santri.
- Menambah santri beserta identitas, akademik, wali, saldo awal, RFID, password, PIN, dan foto.
- Melihat detail santri melalui modal AJAX.
- Mengubah data santri, saldo, password, PIN, RFID, dan foto.
- Menghapus santri dan foto terkait.

Aturan penting:

- Email, NIS, dan RFID unik.
- PIN wajib enam karakter saat membuat santri.
- Password minimal enam karakter.
- Foto maksimal 2 MB.
- Foto santri di-resize maksimal lebar 800 px dan disimpan sebagai JPEG kualitas 70.
- Menghapus user santri menghapus transaksi, top-up, prestasi, dan kamar yang memiliki foreign key cascade.

### 7.3 Manajemen Petugas

Admin dapat:

- Melihat daftar petugas dengan pagination 15 data.
- Menambah petugas dengan nama, email, NIP, jabatan, password, foto, nomor HP, dan alamat.
- Melihat detail petugas melalui modal AJAX.
- Mengubah data petugas.
- Menghapus petugas dan foto terkait.

Petugas baru memperoleh direct permission default untuk dashboard, transaksi, riwayat, tarik tunai, prestasi, dan profil.

### 7.4 Manajemen Akses Petugas

Admin dapat melihat seluruh petugas dan direct permission mereka, lalu menyinkronkan permission petugas dengan pilihan permission `petugas.*` yang terdaftar. Permission di luar registry ditolak oleh validasi.

### 7.5 Kamar Santri

Admin dapat:

- Melihat delapan kamar tetap: `kamar_1` sampai `kamar_8`.
- Melihat jumlah penghuni setiap kamar.
- Melihat anggota kamar dengan pagination.
- Mengambil daftar santri yang belum memiliki kamar.
- Menambahkan santri ke kamar.
- Menghapus penempatan santri dari kamar.

Setiap user hanya dapat memiliki satu penempatan kamar karena constraint unik `kamar_santris.user_id`.

### 7.6 Transaksi oleh Petugas

Alur transaksi:

1. Petugas membuka terminal transaksi.
2. Sistem memetakan jabatan petugas ke kategori dan label transaksi:

| Jabatan | Kategori |
|---|---|
| Kepala Unit | `tarik uang` |
| Staff Pengurus | `tarik uang` |
| Petugas Laundry | `laundry` |
| Petugas Syirkah | `syirkah` |
| Koperasi Kitab | `beli kitab` |
| Petugas Mart | `mart` |
| Jabatan lain | `lainnya` |

3. Petugas memindai RFID santri.
4. Sistem menampilkan data santri, saldo, foto, dan tiga transaksi terbaru.
5. Petugas memasukkan nominal, kategori, keterangan opsional, dan PIN santri.
6. Sistem membandingkan PIN dengan nilai kolom `users.pin` secara langsung.
7. Sistem menolak transaksi jika saldo santri tidak cukup.
8. Dalam database transaction, sistem membuat transaksi keluar dan mengurangi saldo santri.
9. Untuk kategori `kantin`, `koperasi`, `laundry`, `fotokopi`, `lainnya`, `beli kitab`, dan `mart`, saldo petugas bertambah sebesar nominal transaksi.

Kategori `tarik uang` dan `syirkah` tidak menambah saldo petugas.

### 7.7 Riwayat dan Dashboard Petugas

Dashboard petugas menampilkan:

- Saldo digital petugas.
- Jumlah dan total nominal transaksi yang diproses hari ini.
- Success rate yang selalu bernilai 100%.
- Lima transaksi terbaru.
- Jumlah transaksi per hari selama tujuh hari terakhir.

Halaman riwayat petugas menampilkan seluruh transaksi yang diproses petugas, pagination 20, total transaksi masuk, dan total transaksi keluar.

### 7.8 Settlement/Tarik Tunai Petugas

Alur:

1. Petugas mengajukan tarik tunai minimal Rp10.000 dan catatan opsional.
2. Sistem menolak pengajuan jika nominal melebihi saldo digital petugas saat pengajuan.
3. Request dibuat berstatus `pending`; saldo belum dikurangi.
4. Admin melihat daftar pending dan riwayat keputusan.
5. Saat approve, sistem memastikan request masih pending dan saldo petugas masih cukup.
6. Dalam database transaction, saldo petugas dikurangi dan request ditandai `approved`, beserta admin dan waktu keputusan.
7. Saat reject, request ditandai `rejected`, beserta admin dan waktu keputusan; saldo tidak berubah.

Petugas dapat melihat request pending, 20 riwayat keputusan terakhir, dan total settlement approved pada bulan berjalan.

### 7.9 Top-Up Santri oleh Admin

Admin dapat melakukan top-up langsung:

1. Mencari santri berdasarkan NIS atau nama.
2. Melihat saldo dan lima transaksi terakhir.
3. Memasukkan nominal minimal Rp10.000, sumber dana, dan keterangan.
4. Dalam database transaction, sistem membuat transaksi `masuk` berkategori `top_up` dan menambah saldo santri.

Top-up langsung tidak membuat `top_up_requests`.

### 7.10 Pengajuan dan Verifikasi Top-Up

Alur santri:

1. Santri mengirim nominal Rp1.000 sampai Rp10.000.000 dan gambar bukti pembayaran maksimal 2 MB.
2. Sistem menyimpan file ke disk `public` dalam direktori `bukti_pembayaran`.
3. Sistem membuat `top_up_requests` berstatus `pending`.
4. Santri dapat melihat riwayat/status top-up sendiri melalui web atau API.

Alur admin:

1. Admin melihat request pending dan aktivitas verifikasi terbaru.
2. Admin dapat melihat detail santri dan bukti pembayaran.
3. Saat approve, dalam database transaction sistem menambah saldo santri, membuat transaksi `masuk` kategori `top_up`, dan mencatat admin serta waktu verifikasi.
4. Saat reject, sistem menyimpan catatan admin opsional, admin pemroses, dan waktu verifikasi.
5. Request yang tidak lagi pending tidak dapat diproses ulang.

### 7.11 Kas Utama

Admin dapat:

- Melihat saldo kas dari total kas masuk dikurangi kas keluar.
- Melihat 20 transaksi kas terbaru.
- Mencatat kas masuk atau keluar minimal Rp1.000.

Kas masuk wajib memiliki sumber dana. Kas keluar wajib memiliki keperluan. Semua transaksi wajib memiliki keterangan. Sistem menolak kas keluar yang membuat saldo kas negatif.

Kas utama tidak otomatis terhubung dengan transaksi santri, top-up, atau settlement.

### 7.12 Prestasi Hafalan Kitab

Admin dan petugas yang memiliki permission dapat:

- Melihat daftar seluruh prestasi dengan relasi santri, kitab, dan pembimbing.
- Membuat kitab baru dengan nama unik, kategori, dan gambar opsional.
- Membuat, mengubah, melihat detail modal, dan menghapus prestasi.

Saat menyimpan prestasi:

- Santri wajib merupakan user dengan role `santri`.
- Kitab harus tersedia.
- Pembimbing selalu diambil dari user yang sedang login.
- Nama kitab, kategori, dan foto kitab disalin ke record prestasi.
- Progress harus 0 sampai 100.
- Status ditentukan server: 0 = `belum_dihafal`, 1-99 = `sedang_dihafal`, 100 = `telah_dihafalkan`.
- Predikat menentukan skor dan poin:

| Predikat | Skor | Poin |
|---|---:|---:|
| Mumtaz | 100 | 10 |
| Jayyid Jiddan | 90 | 9 |
| Jayyid | 75 | 7 |
| Maqbul | 60 | 6 |

Santri hanya dapat melihat daftar dan detail prestasi miliknya. Daftar web menghitung total poin. API mendukung filter status.

### 7.13 Blog

Admin dapat:

- Melihat daftar blog dengan pagination.
- Membuat, melihat, mengubah, dan menghapus blog.
- Mengunggah thumbnail.
- Mengubah status publish/draft.

Slug unik dibuat dari judul jika tidak diberikan. Ketika blog dipublikasikan pertama kali, model mengisi `published_at`. API publik hanya mengembalikan blog berstatus publish, mendukung filter kategori, pagination, dan mode limit.

### 7.14 Profil

- Admin dan petugas memiliki halaman pengaturan email dan password.
- Santri memiliki halaman profil mobile yang menampilkan identitas dan menyediakan aksi perubahan PIN.
- Endpoint web juga tersedia untuk perubahan email dan password santri.
- File view `pages/santri/profile-settings.blade.php` tersedia, tetapi controller web santri merender `pages.santri.profile`, bukan view tersebut.

### 7.15 Fitur Publik dan Placeholder

| Fitur | Status implementasi |
|---|---|
| Blog publik | Operasional, membaca data blog published |
| Slider publik | Mengembalikan dua item statis |
| Galeri publik | Selalu mengembalikan koleksi kosong |
| Pendaftaran publik API | Memvalidasi input dan mengembalikan sukses, tidak menyimpan data |
| Kontak publik API | Memvalidasi input dan mengembalikan sukses, tidak menyimpan atau mengirim email |
| Tombol Tanya Ustadz pada home santri | Hanya elemen UI tanpa route/aksi |
| Sedekah Jumat pada home santri | Hanya kartu informasi UI |

### 7.16 Absensi Kamar dan Perizinan Santri

Admin dan petugas yang memiliki permission absensi dapat:

- Melakukan absensi harian berdasarkan kamar dan tanggal.
- Memindai RFID untuk menandai santri sebagai `hadir`.
- Mengubah status secara manual menjadi `hadir`, `izin`, atau `ghoib`.
- Melihat dashboard bulanan dengan filter kamar, bulan, dan tahun.
- Membuat, mengubah, menghapus, serta mencetak kartu perizinan tanpa proses approval.

Perizinan langsung aktif pada periode tanggal yang ditentukan. Jika santri melakukan scan selama masa izin, status hari tersebut menjadi `hadir`. Scheduler menjalankan command `attendance:finalize` setiap pukul `00:00` Asia/Jakarta untuk memproses hari sebelumnya: santri tanpa kehadiran dan tanpa izin aktif menjadi `ghoib`, sedangkan santri dengan izin aktif menjadi `izin`.

Kartu izin cetak menampilkan nomor izin, nama, NIS, kamar, asal sekolah, nomor HP santri/wali, tanggal mulai, batas akhir, alasan, catatan, dan user pemberi izin.

## 8. Dashboard dan Laporan

| Permukaan | Data/Laporan |
|---|---|
| Dashboard admin | Ringkasan top-up hari ini, transaksi keluar hari ini, jumlah transaksi, saldo kas, transaksi terbaru, kinerja petugas, pending settlement/top-up, aktivitas top-up, tren tujuh hari |
| Riwayat transaksi admin | Seluruh transaksi, total masuk, total keluar, pagination |
| Daftar santri transaksi admin | Daftar santri dan saldo, pencarian |
| Kas admin | Saldo kas dan 20 transaksi kas terakhir |
| Settlement admin | Request pending dan 20 keputusan terakhir |
| Dashboard petugas | Saldo digital, transaksi hari ini, nominal hari ini, success rate, transaksi terbaru, tren jumlah transaksi tujuh hari |
| Riwayat petugas | Transaksi yang diproses, total masuk dan keluar |
| Tarik tunai petugas | Pending request, riwayat keputusan, total approved bulan ini |
| Dashboard santri | Saldo, pemasukan dan pengeluaran bulan berjalan, tiga transaksi terakhir |
| Riwayat santri | Filter jenis, periode, kategori, bulan/tahun; summary bulanan; chart harian pemasukan/pengeluaran |
| Prestasi santri | Daftar prestasi dan total poin |

Tidak terdapat implementasi ekspor laporan ke PDF, Excel, CSV, atau proses cetak khusus. Ikon `print` yang tampil pada kartu transaksi merupakan ikon kategori, bukan aksi cetak.

## 9. REST API

Semua URI berikut memiliki prefix `/api`.

### 9.1 API Publik

| Method | Endpoint | Fungsi | Persisten |
|---|---|---|---|
| GET | `/blog` | Daftar blog published; query `limit`, `page`, `per_page`, `category` | Ya |
| GET | `/blog/{slug}` | Detail blog published | Ya |
| GET | `/gallery` | Daftar galeri kosong/placeholder | Tidak |
| GET | `/slider` | Dua slider statis | Tidak |
| POST | `/registration` | Validasi formulir pendaftaran dan respons sukses | Tidak |
| POST | `/contact` | Validasi formulir kontak dan respons sukses | Tidak |
| POST | `/santri/login` | Login NIS + PIN dan membuat token Sanctum | Token |

### 9.2 API Santri Terautentikasi

Semua endpoint menggunakan middleware `auth:sanctum`.

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/santri/me` | Profil ringkas user token |
| POST | `/santri/logout` | Menghapus current access token |
| GET | `/santri/dashboard` | Saldo, summary bulan berjalan, lima transaksi terakhir |
| GET | `/santri/transactions` | Riwayat dengan filter/pagination dan summary |
| GET | `/santri/transactions/chart-data` | Data pemasukan/pengeluaran harian per bulan |
| GET | `/santri/transactions/{transaction}` | Detail transaksi milik santri |
| GET | `/santri/topups` | Daftar top-up milik santri |
| POST | `/santri/topups` | Membuat pengajuan top-up |
| GET | `/santri/topups/pending-count` | Jumlah top-up pending |
| GET | `/santri/topups/{topUp}` | Detail top-up milik santri |
| GET | `/santri/profile` | Profil lengkap santri |
| POST | `/santri/profile/change-pin` | Mengganti PIN |
| POST | `/santri/profile/email` | Mengganti email |
| POST | `/santri/profile/password` | Mengganti password |
| GET | `/santri/prestasi` | Daftar prestasi sendiri dan total poin; filter status |
| GET | `/santri/prestasi/{prestasi}` | Detail prestasi sendiri |

## 10. Model Data dan Relasi

### 10.1 Tabel Domain

#### `users`

Menyimpan seluruh admin, petugas, dan santri.

Kolom domain: `name`, `email`, `foto`, `no_hp`, `alamat`, `tempat_lahir`, `tanggal_lahir`, `nama_wali`, `no_hp_wali`, `asal_sekolah`, `kelas`, `password`, `role`, `jabatan`, `nis`, `nip`, `pin`, `saldo`, dan `rfid_code`.

Constraint: email unik, NIS unik nullable, NIP unik nullable, RFID unik nullable.

Relasi:

- Has many `transactions` sebagai santri.
- Has many `transactions` sebagai petugas pemroses.
- Has many `withdrawal_requests` sebagai petugas.
- Has many `top_up_requests` sebagai santri.
- Has many `top_up_requests` sebagai admin verifikator.
- Has many `prestasi_santris` sebagai santri.
- Has many `prestasi_santris` sebagai pembimbing.
- Has one `kamar_santris`.

#### `transactions`

Ledger transaksi saldo santri.

Kolom: `santri_id`, `petugas_id`, `jenis`, `nominal`, `kategori`, `keterangan`, `saldo_sebelum`, `saldo_setelah`, timestamps.

- `jenis`: `masuk` atau `keluar`.
- `kategori` awalnya enum, kemudian diubah menjadi string.
- `santri_id` dan `petugas_id` mengarah ke `users`, cascade delete.

#### `kas_transactions`

Ledger kas utama terpisah.

Kolom: `jenis`, `nominal`, `sumber_dana`, `keperluan`, `keterangan`, `saldo_sebelum`, `saldo_setelah`, `created_by`, timestamps.

`created_by` mengarah ke `users`, cascade delete.

#### `withdrawal_requests`

Permintaan settlement petugas.

Kolom: `petugas_id`, `nominal`, `catatan`, `status`, `approved_by`, `approved_at`, timestamps.

- Status: `pending`, `approved`, `rejected`.
- `petugas_id` cascade delete.
- `approved_by` nullable dan menjadi null saat approver dihapus.

#### `top_up_requests`

Pengajuan top-up santri.

Kolom: `santri_id`, `nominal`, `bukti_pembayaran`, `status`, `admin_note`, `admin_id`, `verified_at`, timestamps.

- Status: `pending`, `approved`, `rejected`.
- `santri_id` cascade delete.
- `admin_id` nullable dan menjadi null saat admin dihapus.

#### `prestasi_santris`

Rekam prestasi/hafalan kitab santri.

Kolom: `santri_id`, `kitab_id`, `pembimbing_id`, `nama_kitab`, `kategori`, `keterangan`, `status`, `progress`, `nilai`, `skor`, `tanggal_selesai`, `bulan_tahun_selesai`, `ustadz_pembimbing`, `foto_kitab`, `catatan_ustadz`, `poin`, `tags`, timestamps.

- `santri_id` cascade delete.
- `kitab_id` dan `pembimbing_id` nullable serta menjadi null saat parent dihapus.
- Menyimpan snapshot nama/kategori/foto kitab dan nama pembimbing selain foreign key.

#### `kitabs`

Master kitab.

Kolom: `nama` unik, `kategori`, `gambar`, `created_by`, timestamps.

`created_by` nullable dan menjadi null saat user pembuat dihapus.

#### `blogs`

Konten publikasi.

Kolom: `title`, `slug` unik, `excerpt`, `content`, `thumbnail`, `category`, `author`, `is_published`, `published_at`, timestamps.

#### `kamar_santris`

Penempatan kamar santri.

Kolom: `user_id`, `kamar`, timestamps.

- `user_id` unik dan cascade delete.
- `kamar`: enum `kamar_1` sampai `kamar_8`.

### 10.2 Tabel Role dan Permission

| Tabel | Fungsi |
|---|---|
| `permissions` | Master permission Spatie |
| `roles` | Master role Spatie |
| `model_has_permissions` | Direct permission user/model |
| `model_has_roles` | Role user/model |
| `role_has_permissions` | Permission bawaan role |

Konfigurasi Spatie tidak menggunakan teams.

### 10.3 Tabel Infrastruktur

| Tabel | Fungsi |
|---|---|
| `password_reset_tokens` | Token reset password, tetapi tidak ada flow reset web |
| `sessions` | Session database |
| `cache`, `cache_locks` | Cache database |
| `jobs`, `job_batches`, `failed_jobs` | Queue dan pencatatan job gagal |

Tabel `personal_access_tokens` yang dibutuhkan Sanctum tidak didefinisikan oleh migration dalam repository.

### 10.4 Diagram Relasi Ringkas

```text
users (santri) 1 --- * transactions * --- 1 users (petugas/admin pemroses)
users (santri) 1 --- * top_up_requests * --- 0..1 users (admin verifikator)
users (petugas) 1 --- * withdrawal_requests * --- 0..1 users (admin approver)
users (santri) 1 --- * prestasi_santris * --- 0..1 users (pembimbing)
kitabs 1 --- * prestasi_santris
users 1 --- 0..1 kamar_santris
users 1 --- * kas_transactions
users 1 --- * kitabs
```

## 11. Antarmuka Web

### 11.1 Pola UI

- Admin dan petugas menggunakan layout sidebar.
- Santri menggunakan layout mobile dengan bottom navigation.
- Guest menggunakan layout login/registrasi.
- UI menggunakan Blade, Tailwind CSS, Alpine.js, Material Symbols, dan sebagian JavaScript inline.
- Chart riwayat santri menggunakan Chart.js dari CDN.
- Data modal dan pencarian tertentu menggunakan `fetch`.

### 11.2 PWA

- Manifest mendefinisikan nama Mawasmart, mode standalone, orientasi portrait, bahasa `id-ID`, serta kategori finance/education/productivity.
- Layout aplikasi mendaftarkan service worker `/sw.js`.
- Service worker menerapkan cache-first untuk resource yang telah dicache dan network fallback.
- Daftar cache statis mengacu ke `/css/app.css` dan `/js/app.js`, sedangkan build aplikasi menggunakan aset Vite; keberadaan URL statis tersebut tidak dijamin oleh konfigurasi build yang terlihat.

## 12. Aturan Bisnis Utama

- Saldo user tidak boleh negatif pada flow transaksi santri, settlement, dan kas keluar.
- Semua mutasi saldo utama mencatat saldo sebelum dan saldo sesudah pada ledger terkait.
- Flow yang mengubah lebih dari satu record keuangan menggunakan database transaction pada transaksi petugas, top-up langsung, approval top-up, approval settlement, dan kas.
- Pengajuan top-up tidak langsung mengubah saldo.
- Pengajuan settlement tidak langsung mengubah saldo petugas.
- Penghapusan user dapat menghapus histori keuangan terkait karena foreign key cascade pada transaksi dan beberapa tabel domain.
- Saldo petugas hanya bertambah untuk kategori transaksi tertentu.
- Kas utama dihitung dari ledger kas dan tidak direkonsiliasi otomatis dengan saldo user atau transaksi.
- Status, skor, poin, dan pembimbing prestasi ditentukan server, bukan dipercaya dari input klien.

## 13. Teknologi

| Area | Implementasi |
|---|---|
| Backend | PHP `^8.3`, Laravel `^13.0` |
| ORM | Eloquent |
| Web auth | Laravel session auth |
| API auth | Laravel Sanctum `^4.3` |
| Role/permission | Spatie Laravel Permission `^8.0` |
| Spreadsheet import | PhpSpreadsheet `^5.5` |
| Frontend rendering | Laravel Blade |
| Styling | Tailwind CSS `^4.2.2`, Material Design 3 theme |
| Interaksi frontend | Alpine.js `^3.15.11`, Axios `^1.17.0`, inline Fetch API |
| Chart | Chart.js 4.4.0 via CDN pada riwayat santri; chart admin/petugas berupa visual HTML |
| Build | Vite `^8.0.0`, Laravel Vite Plugin `^3.0.0` |
| PWA | Web app manifest dan custom service worker |
| Database | Default config SQLite; environment aktif yang terbaca menggunakan MySQL |
| File storage | Local/public Laravel filesystem; upload domain menggunakan disk `public` |
| Session/cache/queue | Mendukung database; environment aktif menggunakan session dan queue database |
| Testing | PHPUnit `^12.5.12`, SQLite in-memory |
| Zona waktu | `Asia/Jakarta` |

## 14. Seeder dan Data Awal

- `RolePermissionSeeder` membuat seluruh role dan permission, menyinkronkan default role permission, serta direct permission petugas.
- `UserSeeder` membuat akun contoh admin, petugas, santri, dan admin testing dengan kredensial hard-coded.
- `SantriSeeder` membaca file `202604072216.xlsx`, membuat email dari NIS, menggunakan password default `santri123`, dan mengimpor identitas dasar santri.
- `BlogSeeder` menyediakan empat artikel contoh, tetapi tidak dipanggil oleh `DatabaseSeeder`.
- `DatabaseSeeder` memanggil `RolePermissionSeeder`, `UserSeeder`, `SantriSeeder`, lalu `RolePermissionSeeder` kembali.

## 15. Validasi dan Batasan File

| Objek | Aturan upload |
|---|---|
| Foto santri | Gambar, maksimum 2 MB, di-resize dan disimpan JPEG |
| Foto petugas | Gambar, maksimum 2 MB |
| Bukti top-up | Gambar wajib, maksimum 2 MB |
| Thumbnail blog | Gambar opsional, maksimum 2 MB |
| Gambar kitab | Gambar opsional, maksimum 2 MB |

Semua file domain disimpan pada disk `public`; akses URL mengandalkan storage link atau mekanisme serving disk.

## 16. Kondisi Implementasi dan Gap yang Terverifikasi

Bagian ini bukan roadmap asumtif; seluruh item berasal dari kondisi source code.

### 16.1 Keamanan dan Integritas

- Login web santri hanya memerlukan NIS tanpa password atau PIN.
- Registrasi admin tersedia secara publik untuk guest.
- Transaksi petugas membandingkan PIN secara langsung (`$santri->pin !== $request->pin`), sedangkan pembuatan santri dan login API menyimpan/migrasikan PIN menjadi hash. Akibatnya PIN hash tidak akan lolos pada flow transaksi petugas.
- Perubahan PIN santri via web belum memverifikasi atau menyimpan PIN.
- Sanctum dipakai, tetapi migration `personal_access_tokens` tidak tersedia di repository.
- API Sanctum hanya memeriksa token valid; route tidak memeriksa ability `santri` atau memastikan role user adalah santri setelah token diterbitkan.
- Penghapusan santri/petugas menggunakan direct database delete. Foreign key cascade dapat menghapus histori transaksi, sehingga audit trail keuangan tidak immutable.
- Seeder menyimpan kredensial contoh/default dalam source code.

### 16.2 Konsistensi Data dan Perilaku

- Dashboard admin menghitung kinerja petugas melalui relasi `transactions`, tetapi model `User` mendefinisikan transaksi yang diproses sebagai `processedTransactions`; pemanggilan `withCount(['transactions'...])` menghitung transaksi user sebagai santri, bukan sebagai petugas.
- Success rate petugas selalu 100%.
- Dashboard santri web memfilter bulan tanpa memfilter tahun, sedangkan API dashboard memfilter bulan dan tahun.
- Kamar menerima setiap `users.id` yang valid pada proses simpan; validasi tidak memastikan user memiliki role santri.
- `top_up_requests.nominal` bertipe decimal, sedangkan saldo dan ledger transaksi menggunakan integer/unsigned big integer.
- Approval top-up dan settlement memeriksa status sebelum database transaction/locking tanpa row lock; source code tidak menunjukkan proteksi eksplisit terhadap race condition.
- Kas utama tidak otomatis menerima dampak settlement atau top-up.
- Kategori `syirkah` tidak menambah saldo petugas, berbeda dengan beberapa kategori unit lain.

### 16.3 UI, Route, dan Placeholder

- `resources/views/components/pin-modal.blade.php` memiliki default route `verify-pin`, tetapi route tersebut tidak tersedia.
- `resources/views/pages/admin/petugas.blade.php` mengacu ke route `admin.petugas.detail`, tetapi route tersebut tidak tersedia; view ini tidak dirender oleh controller petugas admin yang aktif.
- Tombol Tanya Ustadz dan kartu Sedekah Jumat tidak memiliki proses backend.
- API galeri, registration, dan contact merupakan placeholder non-persisten.
- Slider API menggunakan data statis.
- Tidak terdapat fitur export/cetak laporan.
- Tidak terdapat implementasi service, repository, atau Livewire meskipun direktori tersebut termasuk cakupan analisis.

## 17. Test Coverage yang Tersedia

Test yang tersedia memverifikasi:

- Halaman pemilihan role dan link registrasi.
- Login santri dengan NIS tanpa password.
- Penolakan email sebagai identitas login santri.
- Login admin tetap membutuhkan password.
- Status, skor, poin, dan pembimbing prestasi ditentukan server.
- Petugas berpermission dapat membuka form prestasi dan menambah kitab.
- Smoke test halaman `/`.

Tidak ditemukan test khusus untuk transaksi saldo, top-up, settlement, kas, permission matrix lengkap, API Sanctum, kepemilikan data API, blog, kamar, upload file, atau race condition.

## 18. Traceability Source Code

| Area PRD | Sumber utama |
|---|---|
| Route dan akses | `routes/web.php`, `routes/api.php`, `bootstrap/app.php` |
| Role dan permission | `app/Support/PermissionRegistry.php`, `database/seeders/RolePermissionSeeder.php` |
| Autentikasi | `app/Http/Controllers/Auth/*`, `app/Http/Controllers/Api/SantriAuthController.php`, `config/auth.php` |
| Transaksi dan saldo | `app/Http/Controllers/Petugas/TransaksiController.php`, `app/Http/Controllers/Admin/TransactionController.php`, `app/Models/Transaction.php` |
| Top-up | Controller TopUp pada namespace Admin, Santri, dan Api |
| Settlement | Controller Settlement dan TarikTunai |
| Kas | `app/Http/Controllers/Admin/KasController.php` |
| Prestasi dan kitab | `PrestasiSantriController`, `KitabController`, model terkait |
| Dashboard/laporan | Controller Dashboard dan Riwayat tiap role |
| Data model | Seluruh file `database/migrations/*` dan `app/Models/*` |
| UI dan PWA | `resources/views/*`, `resources/css/app.css`, `public/manifest.json`, `public/sw.js` |
| Teknologi | `composer.json`, `package.json`, `vite.config.js`, dan `config/*` |
| Test | `tests/Feature/*`, `tests/Unit/*` |
