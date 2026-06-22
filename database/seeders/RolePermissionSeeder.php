<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\PermissionRegistry;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (PermissionRegistry::all() as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach (PermissionRegistry::defaults() as $roleName => $permissions) {
            Role::findOrCreate($roleName, 'web')->syncPermissions($permissions);
        }

        User::query()
            ->whereIn('role', array_keys(PermissionRegistry::defaults()))
            ->each(function (User $user): void {
                $user->syncRoles([$user->role]);

                if ($user->role === 'petugas' && $user->permissions()->count() === 0) {
                    $user->syncPermissions(PermissionRegistry::petugasDefaults());
                } elseif ($user->role === 'petugas') {
                    $user->givePermissionTo([
                        'petugas.dashboard.view',
                        'petugas.prestasi.manage',
                        'petugas.tarbiyah.manage',
                        'petugas.health.manage',
                        'petugas.security.manage',
                    ]);
                    if ($user->hasAnyPermission([
                        'petugas.transactions.manage',
                        'petugas.history.view',
                        'petugas.withdrawals.manage',
                        'petugas.laundry.manage',
                        'petugas.laundry.history',
                    ])) {
                        $user->givePermissionTo('petugas.finance.dashboard');
                    }
                    if ($user->jabatan === 'Petugas Laundry' || $user->hasPermissionTo('petugas.transactions.manage')) {
                        $user->givePermissionTo([
                            'petugas.laundry.manage',
                            'petugas.laundry.history',
                        ]);
                    }
                    if ($user->hasPermissionTo('petugas.attendance.manage')) {
                        $user->givePermissionTo([
                            'petugas.attendance.dashboard',
                            'petugas.attendance.rfid',
                            'petugas.attendance.manual',
                            'petugas.attendance.monthly',
                            'petugas.permissions.manage',
                        ]);
                    }
                }
            });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
