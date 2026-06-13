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

        $adminPermission = Permission::findOrCreate('admin.dashboard-content.manage', 'web');
        $financePermission = Permission::findOrCreate('petugas.finance.dashboard', 'web');

        Role::findOrCreate('admin', 'web')->givePermissionTo($adminPermission);

        User::query()
            ->where('role', 'petugas')
            ->get()
            ->filter(fn (User $user) => $user->hasAnyPermission([
                'petugas.transactions.manage',
                'petugas.history.view',
                'petugas.withdrawals.manage',
            ]))
            ->each(fn (User $user) => $user->givePermissionTo($financePermission));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::findByName('admin.dashboard-content.manage', 'web')->delete();
        Permission::findByName('petugas.finance.dashboard', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
