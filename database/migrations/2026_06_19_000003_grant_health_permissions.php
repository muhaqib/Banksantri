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

        $petugasPermission = Permission::findOrCreate('petugas.health.manage', 'web');
        $santriPermission = Permission::findOrCreate('santri.health.view', 'web');

        User::where('role', 'petugas')->each(fn (User $user) => $user->givePermissionTo($petugasPermission));
        Role::findOrCreate('santri', 'web')->givePermissionTo($santriPermission);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::whereIn('name', ['petugas.health.manage', 'santri.health.view'])->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
