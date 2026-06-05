# PRODUCT REQUIREMENTS DOCUMENT (PRD)

## Project

Refactor Sistem Manajemen Pesantren "MawaSmart"

## Latar Belakang

Saat ini aplikasi MawaSmart sudah memiliki fitur Bank Santri dan beberapa modul lainnya. Struktur menu dan manajemen hak akses masih belum optimal sehingga diperlukan:

1. Implementasi Role & Permission menggunakan Laravel Spatie.
2. Pengaturan hak akses terpusat oleh Super Admin.
3. Restrukturisasi menu sidebar/navbar agar lebih rapi.
4. Pemisahan modul berdasarkan bidang kerja pesantren.
5. sebelum login buatkan seperti select role ada 3 , santri, admin, petugas, baru nantinya akan mengarah ke login

# ACCEPTANCE CRITERIA

1. Laravel Spatie berhasil terpasang.
2. Role dan Permission dapat dikelola Super Admin.
3. Sidebar berubah menjadi dropdown.
4. Menu tampil sesuai permission.
5. Petugas hanya melihat menu yang diizinkan.
6. Semua route terlindungi middleware.
7. UI responsive desktop dan mobile.
8. Tidak ada hardcode role pada view.
9. Sistem mudah ditambah modul baru.
10. Seluruh fitur lama tetap berjalan normal setelah refactor.
