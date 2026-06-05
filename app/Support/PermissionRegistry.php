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
                'admin.finance.manage' => 'Kelola kas, transaksi, top up, dan settlement',
                'petugas.transactions.manage' => 'Kelola transaksi petugas',
                'petugas.history.view' => 'Lihat riwayat transaksi petugas',
                'petugas.withdrawals.manage' => 'Kelola tarik tunai',
                'santri.history.view' => 'Lihat riwayat santri',
                'santri.topup.manage' => 'Ajukan top up santri',
            ],
            'Data Pesantren' => [
                'admin.santri.manage' => 'Kelola data santri',
                'admin.petugas.manage' => 'Kelola data petugas',
                'admin.kamar.manage' => 'Kelola data kamar',
                'admin.prestasi.manage' => 'Kelola prestasi santri',
                'santri.prestasi.view' => 'Lihat prestasi santri',
            ],
            'Publikasi' => [
                'admin.blog.manage' => 'Kelola blog dan artikel',
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
            ],
        ];
    }

    public static function petugasDefaults(): array
    {
        return [
            'petugas.dashboard.view',
            'petugas.transactions.manage',
            'petugas.history.view',
            'petugas.withdrawals.manage',
            'petugas.profile.manage',
        ];
    }
}
