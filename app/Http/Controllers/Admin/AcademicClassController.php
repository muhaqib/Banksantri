<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormalClass;
use App\Models\PondokClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AcademicClassController extends Controller
{
    public function pondokIndex()
    {
        return view('pages.admin.classes.pondok', [
            'activeRole' => 'admin',
            'classes' => PondokClass::query()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->withCount(['students'])
                ->get(),
        ]);
    }

    public function storePondok(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:pondok_classes,name'],
            'homeroom_teacher' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'uses_monthly_exam' => ['nullable', 'boolean'],
            'uses_semester_exam' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        PondokClass::create([
            'name' => $validated['name'],
            'homeroom_teacher' => $validated['homeroom_teacher'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 100,
            'uses_monthly_exam' => $request->boolean('uses_monthly_exam', true),
            'uses_semester_exam' => $request->boolean('uses_semester_exam', true),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Kelas pondok berhasil ditambahkan.');
    }

    public function updatePondok(Request $request, PondokClass $pondokClass)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('pondok_classes', 'name')->ignore($pondokClass)],
            'homeroom_teacher' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'uses_monthly_exam' => ['nullable', 'boolean'],
            'uses_semester_exam' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $oldName = $pondokClass->name;
        $pondokClass->update([
            'name' => $validated['name'],
            'homeroom_teacher' => $validated['homeroom_teacher'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 100,
            'uses_monthly_exam' => $request->boolean('uses_monthly_exam'),
            'uses_semester_exam' => $request->boolean('uses_semester_exam'),
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($oldName !== $pondokClass->name) {
            User::activeSantri()->where('kelas', $oldName)->update(['kelas' => $pondokClass->name]);
        }

        return back()->with('success', 'Kelas pondok berhasil diperbarui.');
    }

    public function destroyPondok(PondokClass $pondokClass)
    {
        if (User::activeSantri()->where('kelas', $pondokClass->name)->exists()) {
            return back()->with('error', 'Kelas pondok masih dipakai santri aktif.');
        }

        $pondokClass->delete();

        return back()->with('success', 'Kelas pondok berhasil dihapus.');
    }

    public function formalIndex()
    {
        $classes = FormalClass::query()
            ->with('nextClass')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $studentCounts = User::activeSantri()
            ->select('asal_sekolah', DB::raw('COUNT(*) as total'))
            ->whereNotNull('asal_sekolah')
            ->groupBy('asal_sekolah')
            ->pluck('total', 'asal_sekolah');

        return view('pages.admin.classes.formal', [
            'activeRole' => 'admin',
            'classes' => $classes,
            'studentCounts' => $studentCounts,
        ]);
    }

    public function storeFormal(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:formal_classes,name'],
            'next_class_id' => ['nullable', 'exists:formal_classes,id'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        FormalClass::create([
            'name' => $validated['name'],
            'next_class_id' => $validated['next_class_id'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 100,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Kelas formal berhasil ditambahkan.');
    }

    public function updateFormal(Request $request, FormalClass $formalClass)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('formal_classes', 'name')->ignore($formalClass)],
            'next_class_id' => ['nullable', 'exists:formal_classes,id', Rule::notIn([$formalClass->id])],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $oldName = $formalClass->name;
        $formalClass->update([
            'name' => $validated['name'],
            'next_class_id' => $validated['next_class_id'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 100,
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($oldName !== $formalClass->name) {
            User::activeSantri()->where('asal_sekolah', $oldName)->update(['asal_sekolah' => $formalClass->name]);
        }

        return back()->with('success', 'Kelas formal berhasil diperbarui.');
    }

    public function destroyFormal(FormalClass $formalClass)
    {
        if (User::activeSantri()->where('asal_sekolah', $formalClass->name)->exists()) {
            return back()->with('error', 'Kelas formal masih dipakai santri aktif.');
        }

        FormalClass::where('next_class_id', $formalClass->id)->update(['next_class_id' => null]);
        $formalClass->delete();

        return back()->with('success', 'Kelas formal berhasil dihapus.');
    }

    public function promoteFormal(FormalClass $formalClass)
    {
        if (! $formalClass->nextClass) {
            return back()->withErrors(['next_class_id' => 'Kelas tujuan berikutnya belum diatur.']);
        }

        $promoted = User::activeSantri()
            ->where('asal_sekolah', $formalClass->name)
            ->update(['asal_sekolah' => $formalClass->nextClass->name]);

        return back()->with('success', "{$promoted} santri berhasil dinaikkan dari {$formalClass->name} ke {$formalClass->nextClass->name}.");
    }

    public function promoteAllFormal()
    {
        $promoted = 0;

        DB::transaction(function () use (&$promoted): void {
            FormalClass::query()
                ->with('nextClass')
                ->whereNotNull('next_class_id')
                ->orderByDesc('sort_order')
                ->orderByDesc('id')
                ->get()
                ->each(function (FormalClass $class) use (&$promoted): void {
                    if (! $class->nextClass) {
                        return;
                    }

                    $promoted += User::activeSantri()
                        ->where('asal_sekolah', $class->name)
                        ->update(['asal_sekolah' => $class->nextClass->name]);
                });
        });

        return back()->with('success', "{$promoted} santri berhasil dinaikkan kelas formal.");
    }
}
