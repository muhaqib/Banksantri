<?php

namespace App\Support;

class PermissionRegistry
{
    public static function grouped(): array
    {
        return [
            'Dashboard' => [
                'admin.dashboard.view' => 'Lihat dashboard admin',
                'petugas.dashboard.view' => 'Lihat dashboard petugas',
                'santri.dashboard.view' => 'Lihat beranda santri',
            ],
            'Bank Santri' => [
                'admin.finance.manage' => 'Kelola kas, transaksi, top up, dan penarikan tunai',
                'petugas.transactions.manage' => 'Kelola transaksi petugas',
                'petugas.finance.dashboard' => 'Lihat dashboard keuangan petugas',
                'petugas.history.view' => 'Lihat riwayat transaksi petugas',
                'petugas.withdrawals.manage' => 'Kelola tarik tunai',
                'santri.history.view' => 'Lihat riwayat santri',
                'santri.topup.manage' => 'Ajukan top up santri',
            ],
            'Laundry' => [
                'admin.laundry.manage' => 'Kelola paket dan rincian laundry',
                'petugas.laundry.manage' => 'Kelola transaksi laundry',
                'petugas.laundry.history' => 'Lihat dashboard dan riwayat laundry',
            ],
            'Data Pesantren' => [
                'admin.santri.manage' => 'Kelola data santri',
                'admin.petugas.manage' => 'Kelola data petugas',
                'admin.kamar.manage' => 'Kelola data kamar',
                'petugas.health.manage' => 'Kelola kesehatan santri',
                'petugas.security.manage' => 'Kelola pelanggaran santri',
                'admin.prestasi.manage' => 'Kelola prestasi santri',
                'petugas.prestasi.manage' => 'Kelola prestasi santri',
                'admin.attendance.dashboard' => 'Lihat dashboard kehadiran',
                'admin.attendance.rfid' => 'Kelola RFID presensi',
                'admin.attendance.manual' => 'Kelola presensi manual',
                'admin.attendance.monthly' => 'Lihat rekap bulanan absensi',
                'admin.permissions.manage' => 'Kelola perizinan santri',
                'petugas.attendance.dashboard' => 'Lihat dashboard kehadiran',
                'petugas.attendance.rfid' => 'Kelola RFID presensi',
                'petugas.attendance.manual' => 'Kelola presensi manual',
                'petugas.attendance.monthly' => 'Lihat rekap bulanan absensi',
                'petugas.permissions.manage' => 'Kelola perizinan santri',
                'santri.prestasi.view' => 'Lihat prestasi santri',
                'santri.health.view' => 'Lihat kesehatan santri',
                'santri.security.view' => 'Lihat keamanan santri',
            ],
            'Publikasi' => [
                'admin.blog.manage' => 'Kelola blog dan artikel',
                'admin.dashboard-content.manage' => 'Kelola pengumuman, berita pondok, dan to do dashboard',
            ],
            'Akses & Profil' => [
                'admin.access.manage' => 'Kelola role dan permission',
                'admin.profile.manage' => 'Kelola profil admin',
                'petugas.profile.manage' => 'Kelola profil petugas',
                'santri.profile.manage' => 'Kelola profil santri',
            ],
        ];
    }

    public static function petugasGrouped(): array
    {
        return collect(self::grouped())
            ->map(fn (array $permissions) => collect($permissions)
                ->filter(fn (string $label, string $permission) => str_starts_with($permission, 'petugas.'))
                ->all())
            ->filter()
            ->all();
    }

    public static function all(): array
    {
        return collect(self::grouped())
            ->flatMap(fn (array $permissions) => array_keys($permissions))
            ->values()
            ->all();
    }

    public static function defaults(): array
    {
        return [
            'admin' => collect(self::all())
                ->reject(fn (string $permission) => str_starts_with($permission, 'petugas.'))
                ->values()
                ->all(),
            'petugas' => [],
            'santri' => [
                'santri.dashboard.view',
                'santri.history.view',
                'santri.profile.manage',
                'santri.topup.manage',
                'santri.prestasi.view',
                'santri.health.view',
                'santri.security.view',
            ],
        ];
    }

    public static function petugasDefaults(): array
    {
        return [
            'petugas.dashboard.view',
            'petugas.transactions.manage',
            'petugas.laundry.manage',
            'petugas.laundry.history',
            'petugas.finance.dashboard',
            'petugas.history.view',
            'petugas.withdrawals.manage',
            'petugas.prestasi.manage',
            'petugas.health.manage',
            'petugas.security.manage',
            'petugas.attendance.dashboard',
            'petugas.attendance.rfid',
            'petugas.attendance.manual',
            'petugas.attendance.monthly',
            'petugas.permissions.manage',
            'petugas.profile.manage',
        ];
    }
}
