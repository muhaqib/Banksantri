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
                    $user->givePermissionTo('petugas.prestasi.manage');
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
