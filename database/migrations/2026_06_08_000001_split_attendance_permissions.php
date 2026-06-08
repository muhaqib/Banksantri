<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    private array $permissions = [
        'admin' => [
            'admin.attendance.dashboard',
            'admin.attendance.rfid',
            'admin.attendance.manual',
            'admin.attendance.monthly',
            'admin.permissions.manage',
        ],
        'petugas' => [
            'petugas.attendance.dashboard',
            'petugas.attendance.rfid',
            'petugas.attendance.manual',
            'petugas.attendance.monthly',
            'petugas.permissions.manage',
        ],
    ];

    public function up(): void
    {
        foreach ($this->permissions as $permissions) {
            foreach ($permissions as $permission) {
                Permission::findOrCreate($permission, 'web');
            }
        }

        Role::findOrCreate('admin', 'web')->givePermissionTo($this->permissions['admin']);

        if (Permission::where('name', 'petugas.attendance.manage')->where('guard_name', 'web')->exists()) {
            User::permission('petugas.attendance.manage')
                ->where('role', 'petugas')
                ->each(fn (User $user) => $user->givePermissionTo($this->permissions['petugas']));
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        foreach ($this->permissions as $permissions) {
            foreach ($permissions as $permission) {
                Permission::where('name', $permission)->where('guard_name', 'web')->delete();
            }
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
