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

        $adminPermission = Permission::findOrCreate('admin.attendance.manage', 'web');
        $petugasPermission = Permission::findOrCreate('petugas.attendance.manage', 'web');

        Role::findOrCreate('admin', 'web')->givePermissionTo($adminPermission);
        User::where('role', 'petugas')->each(fn (User $user) => $user->givePermissionTo($petugasPermission));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::whereIn('name', ['admin.attendance.manage', 'petugas.attendance.manage'])->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
