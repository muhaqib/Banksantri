# Product Requirements Document Berdasarkan Implementasi

## 1. Informasi Dokumen

| Item | Nilai |
|---|---|
| Nama produk pada antarmuka | Mawa Smart / Mawasmart |
| Deskripsi produk pada manifest | Sistem Manajemen Keuangan Pondok Pesantren Mambaul Hikmah |
| Jenis dokumen | PRD as-built, disusun dari source code yang tersedia |
| Tanggal analisis | 20 Juli 2026 (Update fitur baru, Keamanan, & UI Dark Mode) |
| Cakupan | Aplikasi web, REST API, database, autentikasi, otorisasi, dashboard, laporan, PWA, WhatsApp Gateway (WAHA), Akademik Tarbiyah, Laundry, Kesehatan, Keamanan, dan proses pendukung |

Dokumen ini mendeskripsikan perilaku yang sudah diimplementasikan. Pernyataan mengenai fitur yang belum lengkap hanya dibuat ketika source code secara eksplisit menunjukkan placeholder, `TODO`, route yang tidak tersedia, atau dependensi data yang tidak memiliki migration di repository.

## 2. Ringkasan Produk

Mawa Smart adalah aplikasi pengelolaan keuangan, akademik, dan data operasional pesantren dengan tiga kelompok pengguna:

1. **Admin** mengelola data santri (termasuk import/export), petugas, kamar, keuangan (kas, top-up langsung/verifikasi, settlement), kelas akademik (formal & pondok), mata pelajaran tarbiyah, paket & rincian laundry, blog, manajemen konten dashboard (pengumuman & todo), WhatsApp Gateway (WAHA), permission petugas, dan profil.
2. **Petugas** memproses transaksi santri berbasis RFID dan PIN, mengelola transaksi laundry santri, memasukkan/mengimpor nilai Tarbiyah, mengelola rekam kesehatan, mencatat pelanggaran keamanan santri, mengelola prestasi hafalan, mengelola perizinan santri, mengajukan settlement, serta melihat riwayat & statistik kinerjanya.
3. **Santri** melihat saldo, ringkasan dan riwayat transaksi keuangan, mengajukan top-up mandiri, melihat prestasi hafalan, perizinan, kesehatan, keamanan, nilai Tarbiyah, serta mengelola profil melalui web maupun REST API.

Aplikasi juga menyediakan API publik untuk blog, slider, galeri, pendaftaran, dan kontak. API galeri, pendaftaran, dan kontak saat ini belum memiliki penyimpanan persisten.

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

## 4. Sasaran Produk yang Terlihat dari Implementasi

Implementasi saat ini mendukung sasaran operasional berikut:

- Mencatat saldo digital setiap santri dan memproses transaksi keluar (belanja/tarik) menggunakan scan RFID dan verifikasi PIN santri.
- Menyediakan dashboard informatif terpisah untuk Admin (keuangan global & kinerja petugas), Dashboard Keuangan Petugas (saldo & transaksi yang ditangani), serta Dashboard Umum Petugas (pengumuman, todo list, blog pondok).
- Mengintegrasikan pengiriman pesan WhatsApp berulang (*WA Recurring*) dan broadcast manual/excel menggunakan WAHA API.
- Mengelola data akademik (kelas formal & pondok, mata pelajaran, ujian bulanan) serta nilai ujian santri dengan kemampuan ekspor/impor Excel.
- Mengelola kuota dan transaksi laundry (cash, saldo tabungan, atau kuota bulanan) santri.
- Mencatat rekam medis/kesehatan (diagnosa, terapi) dan catatan kedisiplinan/keamanan (pelanggaran, poin) santri.
- Mengelola data perizinan keluar santri dan mencetak kartu izin resmi.
- Memisahkan kas utama dari saldo dan transaksi santri.

## 5. Aktor, Role, dan Otorisasi

### 5.1 Role

Kolom `users.role` hanya menerima:

| Role | Fungsi |
|---|---|
| `admin` | Pengelola utama sistem |
| `petugas` | Operator transaksi/unit, pengelola data pesantren, dan pembimbing |
| `santri` | Pemilik saldo, data akademik, dan rekam medis/disiplin |

Role disimpan dua kali secara konseptual:

- Kolom enum `users.role`, yang digunakan oleh banyak query dan redirect.
- Role Spatie pada tabel `roles` dan `model_has_roles`, yang digunakan middleware `role:*`.

Saat login web, sistem menyinkronkan role Spatie apabila user belum memiliki role yang sama dengan `users.role`.

### 5.2 Permission

| Permission | Kapabilitas |
|---|---|
| `admin.dashboard.view` | Melihat dashboard admin |
| `admin.finance.manage` | Mengelola kas, transaksi, top-up langsung/verifikasi, dan approval settlement |
| `admin.laundry.manage` | Mengelola paket/kuota subscription laundry dan pakaian laundry |
| `admin.santri.manage` | Mengelola data santri (termasuk import/export) |
| `admin.petugas.manage` | Mengelola data petugas |
| `admin.kamar.manage` | Mengelola kamar santri |
| `admin.tarbiyah.manage` | Mengelola kelas (pondok & formal) dan mata pelajaran Tarbiyah |
| `admin.prestasi.manage` | Mengelola prestasi santri |
| `admin.attendance.dashboard` | Melihat dashboard kehadiran santri |
| `admin.attendance.rfid` | Kelola RFID presensi santri |
| `admin.attendance.manual` | Kelola presensi manual santri |
| `admin.attendance.monthly` | Melihat rekap bulanan absensi santri |
| `admin.permissions.manage` | Mengelola dan membuat perizinan santri |
| `admin.blog.manage` | Mengelola blog & artikel |
| `admin.dashboard-content.manage` | Mengelola pengumuman dan agenda/todo petugas di dashboard |
| `admin.wa-schedules.manage` | Mengelola WhatsApp Gateway, broadcast, dan jadwal WhatsApp |
| `admin.access.manage` | Mengelola direct permission petugas |
| `admin.profile.manage` | Mengelola profil admin |
| `petugas.dashboard.view` | Melihat dashboard petugas (pengumuman, todo list, blog) |
| `petugas.transactions.manage` | Memproses transaksi keluar santri (RFID & PIN) |
| `petugas.laundry.manage` | Memproses transaksi laundry santri |
| `petugas.laundry.history` | Melihat dashboard & riwayat laundry |
| `petugas.finance.dashboard` | Melihat dashboard keuangan petugas |
| `petugas.history.view` | Melihat riwayat transaksi yang diproses petugas |
| `petugas.withdrawals.manage` | Mengajukan settlement/tarik tunai ke admin |
| `petugas.santri.manage` | Mengelola & melihat data santri tingkat petugas (Data Santri Lihat Data, Master Santri CRUD, Tambah Santri) |
| `petugas.prestasi.manage` | Mengelola prestasi santri dan kitab |
| `petugas.tarbiyah.manage` | Memasukkan, mengimpor, dan mempromosikan nilai Tarbiyah |
| `petugas.health.manage` | Mengelola data kesehatan santri |
| `petugas.security.manage` | Mengelola data pelanggaran keamanan santri |
| `petugas.attendance.dashboard` | Melihat dashboard kehadiran tingkat petugas |
| `petugas.attendance.rfid` | Kelola presensi RFID santri tingkat petugas |
| `petugas.attendance.manual` | Kelola presensi manual santri tingkat petugas |
| `petugas.attendance.monthly` | Melihat rekap bulanan absensi tingkat petugas |
| `petugas.permissions.manage` | Mengelola perizinan santri tingkat petugas |
| `petugas.blog.manage` | Mengelola blog & artikel tingkat petugas |
| `petugas.profile.manage` | Mengelola profil petugas |
| `santri.dashboard.view` | Melihat dashboard santri mobile |
| `santri.history.view` | Melihat riwayat transaksi sendiri |
| `santri.topup.manage` | Mengajukan top-up santri |
| `santri.prestasi.view` | Melihat prestasi hafalan sendiri |
| `santri.health.view` | Melihat riwayat kesehatan sendiri |
| `santri.security.view` | Melihat riwayat keamanan/pelanggaran sendiri |
| `santri.tarbiyah.view` | Melihat hasil nilai Tarbiyah sendiri |
| `santri.profile.manage` | Mengelola profil santri (ubah email, password, PIN) |

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

- **Bento Financial Widgets**:
  - Total top-up santri hari ini (dari transaksi `masuk` kategori `top_up`).
  - Total nominal transaksi keluar hari ini.
  - Saldo kas utama saat ini (total kas masuk dikurangi kas keluar).
  - Jumlah transaksi hari ini.
- **Top-Up Quick Actions**: Jumlah permintaan top-up tertunda (pending) dengan tautan menuju halaman verifikasi, serta daftar aktivitas top-up terbaru (maksimal 3) yang telah disetujui atau ditolak.
- **Weekly Trends Chart**: Visualisasi tren mingguan yang membandingkan total top-up santri (hijau) dan total transaksi keluar (merah) selama 7 hari terakhir dalam bentuk grafik batang HTML.
- **Petugas Performance**: Daftar kinerja petugas hari ini yang memuat nama, jumlah transaksi yang diproses (menggunakan relasi `processedTransactions`), dan total nominal transaksi, dilengkapi pagination.
- **Penarikan Tunai Terbaru**: Daftar permohonan penarikan tunai petugas yang berstatus `pending` (maksimal 5 data) lengkap dengan aksi langsung untuk "Setujui" atau "Tolak".

### 7.2 Manajemen Santri

Admin dapat:

- Melihat daftar santri dengan pagination 15 data.
- Mencari santri berdasarkan nama atau NIS.
- Mengambil autocomplete maksimal 10 santri.
- Menambah santri beserta identitas, akademik, wali, saldo awal, RFID, password, PIN, dan foto.
- Melihat detail santri melalui modal AJAX.
- Mengubah data santri, saldo, password, PIN, RFID, dan foto.
- Menghapus santri dan foto terkait.
- Mengimpor data santri dari file Excel (XLSX) dan mengekspor data santri ke Excel menggunakan template yang disediakan.
- Mengubah status keaktifan santri (aktif / lulus).

Petugas yang memiliki permission `petugas.santri.manage` dapat mengelola santri melalui 3 opsi menu dropdown pada sidebar:

1. **Data Santri (Melihat Data)** (`petugas.santri.index`): Halaman khusus eksplorasi santri yang dikemas secara minimalis dan rapi. Menampilkan foto thumbnail (yang dapat diklik untuk membuka **Modal Pratinjau Foto Besar**), NIS, Kelas, Kamar, dan Status keaktifan tanpa menampilkan informasi saldo tabungan. Dilengkapi pencarian cepat (Nama/NIS) serta filter per kamar dan status.
2. **Master Santri (CRUD)** (`petugas.santri.master`): Halaman manajemen santri berfitur lengkap yang memungkinkan petugas melakukan Aksi Tambah, Edit Data, Hapus Data, serta Mengubah Status Keaktifan (Aktifkan / Jadikan Alumni).
3. **Tambah Santri** (`petugas.santri.create`): Form registrasi santri baru serta fasilitas impor data santri dari file Excel (XLSX).

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

Every user can only have one room placement due to unique constraint `kamar_santris.user_id`.

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

#### 7.7.1 Dashboard Umum Petugas
Menampilkan:
- Banner sambutan personal dengan kutipan khidmat.
- **Pengumuman**: Daftar maksimal 5 pengumuman aktif yang diterbitkan admin, lengkap dengan indikator prioritas (urgent, important, normal) dan detail isi.
- **To-Do List**: Daftar maksimal 8 tugas/agenda bersama yang ditugaskan kepada petugas bersangkutan yang belum selesai. Dilengkapi aksi klik centang untuk menandai tugas telah selesai (mengirimkan permintaan PATCH ke server).
- **Blog Pondok**: Daftar berita atau cerita pondok pesantren terbaru yang dipublikasikan.

#### 7.7.2 Dashboard Keuangan Petugas
Menampilkan:
- Saldo digital petugas.
- Penghasilan hari ini, jumlah transaksi hari ini, dan total nominal yang dikelola petugas hari ini.
- Success rate transaksi (selalu bernilai 100%).
- Grafik tren mingguan (jumlah transaksi per hari selama 7 hari terakhir).
- Daftar 5 transaksi terbaru yang diproses oleh petugas tersebut.
- Tombol aksi cepat untuk melakukan Tarik Tunai/Settlement.

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

Admin dan petugas yang memiliki permission dapat:

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

Admin dan petugas yang memiliki permission absensi/perizinan dapat:

- Melakukan absensi harian berdasarkan kamar dan tanggal.
- Memindai RFID untuk menandai santri sebagai `hadir`.
- Mengubah status secara manual menjadi `hadir`, `izin`, atau `ghoib`.
- Melihat dashboard bulanan dengan filter kamar, bulan, dan tahun.
- Membuat, mengubah, menghapus, serta mencetak kartu perizinan santri.
- Perizinan langsung aktif pada periode tanggal yang ditentukan. Jika santri melakukan scan selama masa izin, status hari tersebut menjadi `hadir`. Scheduler menjalankan command `attendance:finalize` setiap pukul `00:00` Asia/Jakarta untuk memproses hari sebelumnya: santri tanpa kehadiran dan tanpa izin aktif menjadi `ghoib`, sedangkan santri dengan izin aktif menjadi `izin`.
- Kartu izin cetak menampilkan nomor izin, nama, NIS, kamar, asal sekolah, nomor HP santri/wali, tanggal mulai, batas akhir, alasan, catatan, dan user pemberi izin.

### 7.17 Kelola Konten Dashboard (Dashboard Content Management)

Admin dapat mempublikasikan pengumuman (*announcement*) dan agenda (*to-do list*) untuk petugas:
- **Pengumuman**: Memiliki field judul, ringkasan, isi detail, tingkat prioritas (Normal, Penting, Mendesak), dan tanggal pelaksanaan opsional.
- **To-Do List**: Memiliki tenggat waktu (*due date*). Penugasan dapat diberikan kepada seluruh petugas secara otomatis (*assign to all*) atau kepada petugas tertentu saja yang dipilih secara manual.
- Admin dapat memantau progres pengerjaan To-Do list secara mendetail, yang menampilkan persentase progres total, jumlah penugasan yang selesai, dan status pengerjaan setiap petugas lengkap dengan tanggal penyelesaian.

### 7.18 WhatsApp Gateway & Broadcast (WAHA)

Aplikasi memiliki integrasi WhatsApp menggunakan API WAHA (WhatsApp HTTP API):
- **Status Koneksi**: Menampilkan status koneksi ke server WAHA (Connected/Disconnected) beserta perangkat WhatsApp yang terhubung. Menyediakan tampilan QR Code dinamis apabila koneksi terputus untuk dipindai oleh admin.
- **Pesan Broadcast**:
  - Pengiriman pesan broadcast manual atau massal kepada wali santri aktif, petugas, atau seluruh user.
  - Dukungan pengiriman massal dengan mengunggah file Excel penerima.
  - Delay otomatis sebesar 10 detik antar pengiriman nomor untuk meminimalkan risiko blokir.
  - Penambahan teks kaki (*footer*) otomatis: "Pesan otomatis by: MawaSmart".
- **WA Recurring (Pesan Berulang)**:
  - Admin dapat menjadwalkan pesan WhatsApp otomatis berulang yang dikirimkan pada hari dan jam tertentu.
  - Ditujukan untuk guru/pengajar (*teacher_name*) dengan tipe penerima personal maupun grup WhatsApp.
- **Riwayat Pengiriman**: Menyimpan log status pengiriman (Berhasil/Gagal) beserta respons API WAHA untuk kebutuhan audit.

### 7.19 Manajemen Laundry

Fitur pengelolaan laundry santri yang terbagi antara admin dan petugas:
- **Admin**:
  - Mengelola paket berlangganan laundry bulanan (*laundry subscriptions*) yang mencakup biaya bulanan, kuota berat dalam kilogram (default 12 Kg), dan mencatat sisa kuota santri.
  - Mengelola master jenis pakaian laundry (*laundry clothes*) lengkap dengan ikon visual, keaktifan, dan urutan sorting.
- **Petugas**:
  - Terminal transaksi laundry dengan pemindaian RFID santri untuk mendeteksi keaktifan langganan bulanan secara langsung.
  - Metode pembayaran fleksibel: tunai (*cash*), menggunakan sisa kuota berat bulanan (*quota*), atau potong dari saldo tabungan santri (memerlukan verifikasi PIN 6 digit santri).
  - Menginput berat pakaian laundry (minimal 0,1 Kg), jumlah pakaian, mencatat detail jumlah per jenis pakaian (disimpan sebagai array JSON), dan catatan opsional.
  - Cetak struk transaksi laundry (*receipt*) yang memuat detail cucian.
  - Melihat riwayat transaksi laundry dan total berat yang dikelola.

### 7.20 Akademik Tarbiyah & Kelas

Sistem manajemen kelas dan penilaian akademik madrasah diniyah/pesantren:
- **Manajemen Kelas**:
  - Pembagian kelas menjadi dua kategori: **Kelas Pondok** (memiliki guru wali kelas, opsi menggunakan ujian bulanan, dan opsi menggunakan ujian semester) dan **Kelas Formal** (memiliki aturan kenaikan kelas ke kelas berikutnya secara berurutan).
  - Fitur kenaikan kelas massal (*promote all*) atau kenaikan per kelas formal untuk meluluskan/memindahkan santri ke jenjang berikutnya.
- **Kurikulum & Nilai**:
  - Pengelolaan master mata pelajaran Tarbiyah per tingkat kelas.
  - Input dan rekap Nilai Tarbiyah per semester/tahun akademik.
  - **Ujian Bulanan (*Monthly Exam*)**:
    - Petugas dapat membuat jadwal ujian bulanan berdasarkan tanggal.
    - Menginput nilai ujian bulanan santri untuk mata pelajaran inti (Nahwu, Shorof, Fiqih) dengan batas nilai desimal.
    - Dukungan ekspor template penilaian ke format Excel (XLSX) dan melakukan impor nilai massal dari file Excel.
- **Santri**: Dapat mengakses ringkasan nilai Tarbiyah miliknya melalui web atau mobile API.

### 7.21 Kesehatan Santri (Health Record)

Petugas dapat mendokumentasikan kondisi kesehatan santri:
- Mencatat rekam pemeriksaan kesehatan santri: tanggal periksa, keluhan utama, lokasi penanganan (poskestren/puskesmas/kamar), berat badan, tinggi badan, tekanan darah, suhu tubuh, terapi/tindakan yang diberikan, dan obat.
- Mengatur status kesehatan: Sehat, Sakit, Sembuh, Dirawat.
- Santri dapat memantau riwayat rekam medis dan status kesehatan pribadi secara real-time via API/aplikasi mobile.

### 7.22 Pelanggaran Keamanan (Security Violation)

Pencatatan kedisiplinan dan keamanan santri:
- Petugas dapat mencatat pelanggaran santri yang meliputi: jenis pelanggaran, waktu kejadian, besaran pengurangan poin kedisiplinan, tindakan/sanksi yang diberikan, dan keterangan.
- Santri dapat melihat daftar pelanggaran dan sisa poin kedisiplinan miliknya secara langsung melalui web/API.

## 8. Dashboard dan Laporan

| Permukaan | Data/Laporan |
|---|---|
| Dashboard admin | Ringkasan bento financial widget (top-up, transaksi keluar, saldo kas utama, jumlah transaksi), pending top-up count, riwayat verifikasi top-up terbaru, tren mingguan, kinerja petugas (total transaksi & nominal), pending settlement terbaru, status koneksi WAHA. |
| Riwayat transaksi admin | Seluruh transaksi santri, total masuk, total keluar, pagination, cetak struk transaksi. |
| Daftar santri transaksi admin | Daftar santri dan saldo, pencarian, cetak receipt transaksi. |
| Kas admin | Saldo kas utama dan 20 transaksi kas terbaru. |
| Settlement admin | Request pending dan 20 keputusan terakhir. |
| Konten Dashboard admin | Daftar pengumuman dan agenda/todo list, progres pengerjaan todo per petugas, edit/delete konten. |
| WA Schedules admin | Status koneksi WAHA (dengan QR Code scan), form broadcast pesan manual/excel, daftar recurring schedules, riwayat pesan WA (log sukses/gagal). |
| Laundry subscriptions admin | Daftar santri berlangganan laundry, tambah subscription, riwayat, dan konfigurasi master pakaian laundry. |
| Kelas Akademik admin | Daftar kelas formal & pondok, tambah/edit kelas, tombol naik kelas massal. |
| Dashboard petugas umum | Banner sambutan khidmat, daftar pengumuman aktif, daftar to-do list penugasan (dengan checkbox selesai), blog pondok. |
| Dashboard Keuangan petugas | Saldo digital, nominal penghasilan & transaksi hari ini, success rate (100%), transaksi terbaru, grafik tren mingguan transaksi. |
| Terminal transaksi petugas | Input transaksi RFID, detail data santri, input nominal, kategori, dan validasi PIN santri. |
| Terminal laundry petugas | Input transaksi laundry, scan RFID, pilih tipe bayar (tunai, saldo, kuota), detail jumlah pakaian per jenis, berat Kg, cetak struk laundry. |
| Nilai Tarbiyah petugas | Dashboard kelulusan ujian bulanan, filter kelas, input nilai ujian, tombol unduh template Excel dan unggah nilai Excel. |
| Kesehatan & Keamanan petugas | Form input diagnosa medis, form input pelanggaran poin kedisiplinan santri, dan daftar riwayat rekam medis/keamanan. |
| Absensi petugas | Presensi harian per kamar (RFID scan atau manual status), dashboard absensi bulanan. |
| Perizinan petugas | Daftar surat perizinan santri, cetak surat izin jalan santri. |
| Dashboard santri | Saldo, pemasukan dan pengeluaran bulan berjalan, 3 transaksi terakhir. |
| Riwayat santri | Filter jenis, periode, kategori, bulan/tahun; summary bulanan; chart harian pemasukan/pengeluaran. |
| Akademik & Rekam santri | Riwayat prestasi hafalan kitab, rekap perizinan aktif, riwayat kesehatan (diagnosa & status), poin & riwayat pelanggaran keamanan, nilai Tarbiyah. |

Terdapat dukungan ekspor/impor ke format Excel (XLSX) untuk data santri (admin) dan nilai ujian bulanan Tarbiyah (petugas). Cetak struk/tanda terima didukung dalam bentuk tampilan HTML siap cetak untuk transaksi keuangan, transaksi laundry, dan kartu perizinan santri.

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
| GET | `/santri/transactions` | Riwayat transaksi dengan filter/pagination dan summary (alias: `/santri/riwayat`) |
| GET | `/santri/transactions/chart-data` | Data pemasukan/pengeluaran harian per bulan (alias: `/santri/riwayat/chart-data`) |
| GET | `/santri/transactions/{transaction}` | Detail transaksi milik santri (alias: `/santri/riwayat/{transaction}`) |
| GET | `/santri/topups` | Daftar top-up milik santri |
| POST | `/santri/topups` | Membuat pengajuan top-up (middleware `santri.active`) |
| GET | `/santri/topups/pending-count` | Jumlah top-up pending |
| GET | `/santri/topups/{topUp}` | Detail top-up milik santri |
| GET | `/santri/profile` | Profil lengkap santri |
| POST | `/santri/profile/change-pin` | Mengganti PIN (middleware `santri.active`) |
| POST | `/santri/profile/email` | Mengganti email (middleware `santri.active`) |
| POST | `/santri/profile/password` | Mengganti password (middleware `santri.active`) |
| GET | `/santri/prestasi` | Daftar prestasi hafalan kitab santri dan total poin |
| GET | `/santri/prestasi/{prestasi}` | Detail prestasi hafalan kitab |
| GET | `/santri/permissions` | Daftar perizinan santri (alias: `/santri/perizinan`) |
| GET | `/santri/permissions/{permission}` | Detail perizinan santri |
| GET | `/santri/security` | Daftar riwayat pelanggaran keamanan santri (alias: `/santri/keamanan`) |
| GET | `/santri/security/{violation}` | Detail pelanggaran keamanan |
| GET | `/santri/tarbiyah` | Daftar nilai akademik Tarbiyah milik santri |
| GET | `/santri/health` | Daftar riwayat kesehatan santri (alias: `/santri/kesehatan`) |
| GET | `/santri/health/{health}` | Detail rekam medis kesehatan |

## 10. Model Data dan Relasi

### 10.1 Tabel Domain

#### `users`
Menyimpan data admin, petugas, dan santri.
Kolom domain: `name`, `email`, `foto`, `no_hp`, `alamat`, `tempat_lahir`, `tanggal_lahir`, `nama_wali`, `no_hp_wali`, `asal_sekolah`, `kelas`, `password`, `role`, `jabatan`, `nis`, `nip`, `pin`, `saldo`, `rfid_code`, `lulus_at`, `status_alumni`.

#### `transactions`
Ledger transaksi saldo keuangan santri.
Kolom: `santri_id`, `petugas_id`, `jenis`, `nominal`, `kategori`, `keterangan`, `saldo_sebelum`, `saldo_setelah`.

#### `kas_transactions`
Ledger kas utama pesantren terpisah.
Kolom: `jenis`, `nominal`, `sumber_dana`, `keperluan`, `keterangan`, `saldo_sebelum`, `saldo_setelah`, `created_by`.

#### `withdrawal_requests`
Permintaan settlement penarikan dana petugas.
Kolom: `petugas_id`, `nominal`, `catatan`, `status` (`pending`, `approved`, `rejected`), `approved_by`, `approved_at`.

#### `top_up_requests`
Pengajuan top-up mandiri santri.
Kolom: `santri_id`, `nominal`, `bukti_pembayaran`, `status` (`pending`, `approved`, `rejected`), `admin_note`, `admin_id`, `verified_at`.

#### `prestasi_santris`
Rekam prestasi hafalan kitab santri.
Kolom: `santri_id`, `kitab_id`, `pembimbing_id`, `nama_kitab`, `kategori`, `keterangan`, `status`, `progress`, `nilai`, `skor`, `tanggal_selesai`, `bulan_tahun_selesai`, `ustadz_pembimbing`, `foto_kitab`, `catatan_ustadz`, `poin`, `tags`.

#### `kitabs`
Master data kitab.

#### `blogs`
Konten publikasi berita pesantren.

#### `kamar_santris`
Penempatan kamar santri (`kamar_1` s.d `kamar_8`).

#### `attendances`
Rekam presensi santri per kamar.
Kolom: `santri_id`, `kamar`, `attendance_date`, `status` (`hadir`, `ghoib`, `izin`), `method` (`rfid`, `manual`, `permission`, `automatic`), `notes`, `recorded_by`, `recorded_at`.

#### `santri_permissions`
Surat perizinan keluar/pulang santri.
Kolom: `permission_number`, `santri_id`, `kamar`, `start_date`, `end_date`, `reason`, `notes`, `created_by`, `approved_by`.

#### `dashboard_contents`
Konten pengumuman dan todo untuk dashboard petugas.
Kolom: `created_by`, `type` (`announcement`, `todo`), `title`, `summary`, `thumbnail_url`, `content`, `priority` (`normal`, `important`, `urgent`), `event_date`, `due_date`, `assign_to_all`, `is_published`, `published_at`.

#### `dashboard_content_assignments`
Penugasan agenda/todo list kepada petugas.
Kolom: `dashboard_content_id`, `user_id`, `is_completed`, `completed_at`.

#### `laundry_subscriptions`
Langganan kuota laundry bulanan santri.
Kolom: `santri_id`, `created_by`, `month`, `year`, `monthly_fee`, `quota_kg`, `used_kg`, `status`, `notes`.

#### `laundry_transactions`
Ledger transaksi laundry santri.
Kolom: `santri_id`, `petugas_id`, `laundry_subscription_id`, `transaction_id` (opsional jika bayar potong saldo), `payment_type` (`tunai`, `bulanan`), `payment_method` (`cash`, `saldo_tabungan`, `quota_bulanan`), `laundry_date`, `weight_kg`, `price_per_kg`, `total_price`, `total_clothes`, `clothes_detail` (JSON array pakaian), `notes`.

#### `laundry_clothes`
Master daftar jenis pakaian laundry (misal: Baju, Celana, Sarung, Jilbab).

#### `santri_health_records`
Rekam medis kesehatan santri.
Kolom: `santri_id`, `created_by`, `checkup_date`, `title`, `status` (`sehat`, `sakit`, `sembuh`, `dirawat`), `location`, `weight_kg`, `height_cm`, `blood_pressure`, `temperature_c`, `complaint`, `treatment`, `notes`.

#### `santri_violations`
Catatan pelanggaran kedisiplinan santri.
Kolom: `santri_id`, `created_by`, `jenis_pelanggaran`, `waktu`, `pengurangan_point`, `keterangan`.

#### `tarbiyah_subjects`
Master mata pelajaran Tarbiyah per tingkat kelas.

#### `tarbiyah_grades`
Rapor nilai semesteran Tarbiyah santri.
Kolom: `santri_id`, `subject_id`, `class_level`, `semester`, `academic_year`, `score`, `notes`, `recorded_by`.

#### `tarbiyah_monthly_exams`
Jadwal ujian bulanan Tarbiyah.

#### `tarbiyah_monthly_grades`
Nilai ujian bulanan Tarbiyah santri.
Kolom: `monthly_exam_id`, `santri_id`, `class_level`, `subject`, `score`, `recorded_by`.

#### `pondok_classes` & `formal_classes`
Master data kelas pondok (diniyah) dan kelas formal (sekolah umum).

#### `schedules`
Jadwal pengiriman pesan WhatsApp berulang (*WA Recurring*).
Kolom: `teacher_name`, `recipient_type` (`personal`, `group`), `target_id`, `day_of_week`, `send_time`, `message_content`, `is_active`.

#### `waha_message_logs`
Log pengiriman WhatsApp Gateway.
Kolom: `schedule_id`, `teacher_name`, `target_id`, `session`, `message_content`, `status` (`success`, `failed`), `http_status`, `response_body`, `error_message`, `sent_at`.

### 10.2 Tabel Role dan Permission
*(sama dengan sebelumnya)*

### 10.3 Tabel Infrastruktur
Tabel `personal_access_tokens` yang digunakan Sanctum telah tersedia melalui file migration di repository (`2026_06_29_000001_create_personal_access_tokens_table.php`).

## 11. Antarmuka Web
*(sama dengan sebelumnya)*

## 12. Aturan Bisnis Utama

- Saldo keuangan santri tidak boleh negatif.
- Sisa kuota berat laundry (*laundry subscription quota*) tidak boleh kurang dari berat laundry yang diserahkan jika menggunakan metode bayar kuota bulanan.
- Poin kedisiplinan santri dikurangi setiap terjadi pelanggaran keamanan.
- Absensi finalisasi otomatis dijalankan oleh scheduler setiap jam `00:00` untuk menetapkan santri yang tidak absen dan tidak berizin aktif menjadi `ghoib`.
- Kenaikan kelas formal santri memindahkan santri ke `next_class_id` secara terurut.

## 13. Teknologi

| Area | Implementasi |
|---|---|
| Backend | PHP `^8.3`, Laravel `^13.0` |
| API auth | Laravel Sanctum `^4.3` (dengan migrasi `personal_access_tokens` lengkap) |
| Spreadsheet | PhpSpreadsheet `^5.5` (untuk manajemen santri dan nilai Tarbiyah) |
| WhatsApp integration | WAHA (WhatsApp HTTP API) |
| Styling | Tailwind CSS `^4.2.2`, Material Design 3 theme (dukungan penuh Light & Dark Mode via `localStorage` dan CSS variables) |
| Database | SQLite (testing/local default) dan MySQL (aktif di env) |

## 14. Seeder dan Data Awal
*(sama dengan sebelumnya)*

## 15. Validasi dan Batasan File
*(sama dengan sebelumnya)*

## 16. Kondisi Implementasi dan Gap yang Terverifikasi

### 16.1 Keamanan dan Integritas

- Login web santri hanya memerlukan NIS tanpa password atau PIN.
- Registrasi admin tersedia secara publik untuk guest.
- **[TERSELESAIKAN]** Verifikasi PIN santri pada transaksi petugas kini menggunakan metode `verifyPin()` yang mendukung validasi bcrypt hash secara aman serta mengomparasikan dan mengotomatiskan migrasi PIN plaintext lama menjadi hash saat pertama kali digunakan.
- Perubahan PIN santri via web belum memverifikasi atau menyimpan PIN.
- API Sanctum hanya memeriksa token valid; route tidak memeriksa ability `santri` atau memastikan role user adalah santri setelah token diterbitkan.
- **[TERSELESAIKAN]** Transaksi keuangan petugas menggunakan database transaction (`DB::transaction`) dengan `lockForUpdate()` pada model `User` (santri & petugas) untuk menjamin atomic concurrency dan mencegah race condition.
- **[TERSELESAIKAN]** Sanctum kini didukung migrasi `personal_access_tokens` yang lengkap di repository.

### 16.2 Konsistensi Data dan Perilaku

- **[TERSELESAIKAN]** Dashboard admin kini menghitung kinerja petugas dengan relasi `processedTransactions` (bukan `transactions` sebagai santri) sehingga data transaksi petugas terhitung akurat.
- Success rate petugas selalu 100%.
- Dashboard santri web memfilter bulan tanpa memfilter tahun, sedangkan API dashboard memfilter bulan dan tahun.
- Kamar menerima setiap `users.id` yang valid pada proses simpan; validasi tidak memastikan user memiliki role santri.
- `top_up_requests.nominal` bertipe decimal, sedangkan saldo dan ledger transaksi menggunakan integer/unsigned big integer.
- Approval top-up dan settlement memeriksa status sebelum database transaction/locking tanpa row lock.
- Kas utama tidak otomatis menerima dampak settlement atau top-up.

### 16.3 UI, Route, dan Placeholder

- `resources/views/components/pin-modal.blade.php` memiliki default route `verify-pin`, tetapi route tersebut tidak tersedia.
- **[TERSELESAIKAN SEBAGIAN]** Fitur ekspor/impor Excel (XLSX) telah ditambahkan untuk Manajemen Santri dan Manajemen Nilai Tarbiyah bulanan. Namun, untuk laporan kas utama, transaksi saldo, dan settlement belum mendukung ekspor.
- Tombol Tanya Ustadz dan kartu Sedekah Jumat tidak memiliki proses backend.
- API galeri, registration, dan contact merupakan placeholder non-persisten.
- Slider API menggunakan data statis.

## 17. Test Coverage yang Tersedia
*(sama dengan sebelumnya)*

## 18. Traceability Source Code

| Area PRD | Sumber utama |
|---|---|
| Route dan akses | `routes/web.php`, `routes/api.php`, `bootstrap/app.php` |
| Role dan permission | `app/Support/PermissionRegistry.php`, `database/seeders/RolePermissionSeeder.php` |
| Autentikasi | `app/Http/Controllers/Auth/*`, `app/Http/Controllers/Api/SantriAuthController.php` |
| Transaksi dan saldo | `app/Http/Controllers/Petugas/TransaksiController.php`, `app/Http/Controllers/Admin/TransactionController.php`, `app/Models/Transaction.php` |
| Top-up & Settlement | Controller TopUp dan Settlement / TarikTunai |
| Kas Utama | `app/Http/Controllers/Admin/KasController.php` |
| Konten Dashboard | `app/Http/Controllers/Admin/DashboardContentController.php`, `app/Models/DashboardContent.php` |
| WhatsApp Gateway | `app/Http/Controllers/Admin/WahaScheduleController.php`, `app/Models/Schedule.php` |
| Laundry | `app/Http/Controllers/Petugas/LaundryController.php`, `app/Http/Controllers/Admin/LaundrySubscriptionController.php`, model terkait |
| Akademik Tarbiyah | `app/Http/Controllers/Petugas/TarbiyahGradeController.php`, `app/Http/Controllers/Admin/TarbiyahSubjectController.php`, `AcademicClassController.php` |
| Kesehatan & Keamanan | `HealthRecordController`, `SecurityViolationController`, model terkait |
| Absensi & Perizinan | `AttendanceController`, `SantriPermissionController` |
| UI dan PWA | `resources/views/*`, `resources/css/app.css`, `public/manifest.json`, `public/sw.js` |
