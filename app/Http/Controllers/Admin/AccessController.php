<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\PermissionRegistry;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;

class AccessController extends Controller
{
    public function index()
    {
        $petugasList = User::query()
            ->where('role', 'petugas')
            ->with('permissions')
            ->orderBy('name')
            ->get();

        return view('pages.admin.access.index', [
            'activeRole' => 'admin',
            'groups' => PermissionRegistry::petugasGrouped(),
            'petugasList' => $petugasList,
        ]);
    }

    public function update(Request $request, User $petugas)
    {
        if ($petugas->role !== 'petugas') {
            abort(404);
        }

        $availablePermissions = collect(PermissionRegistry::petugasGrouped())
            ->flatMap(fn (array $permissions) => array_keys($permissions))
            ->values()
            ->all();

        $validated = $request->validate([
            'permissions' => ['array'],
            'permissions.*' => ['string', 'in:'.implode(',', $availablePermissions)],
        ]);

        $permissions = array_values(array_unique([
            ...($validated['permissions'] ?? []),
            'petugas.dashboard.view',
        ]));
        $petugas->syncPermissions($permissions);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('admin.access.index')
            ->with('success', 'Permission petugas '.$petugas->name.' berhasil diperbarui.');
    }
}
