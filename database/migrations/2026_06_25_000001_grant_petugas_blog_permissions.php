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

        $permission = Permission::findOrCreate('petugas.blog.manage', 'web');
        Role::findOrCreate('petugas', 'web')->givePermissionTo($permission);

        User::where('role', 'petugas')->each(fn (User $user) => $user->givePermissionTo($permission));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Permission::findByName('petugas.blog.manage', 'web')->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
