<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $adminPermission = Permission::findOrCreate('admin.tarbiyah.manage', 'web');
        $petugasPermission = Permission::findOrCreate('petugas.tarbiyah.manage', 'web');
        $santriPermission = Permission::findOrCreate('santri.tarbiyah.view', 'web');

        Role::findOrCreate('admin', 'web')->givePermissionTo($adminPermission);
        Role::findOrCreate('santri', 'web')->givePermissionTo($santriPermission);
        User::where('role', 'petugas')->each(fn (User $user) => $user->givePermissionTo($petugasPermission));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::whereIn('name', ['admin.tarbiyah.manage', 'petugas.tarbiyah.manage', 'santri.tarbiyah.view'])->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
