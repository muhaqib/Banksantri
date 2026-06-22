<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TarbiyahSubject;
use App\Support\TarbiyahClass;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TarbiyahSubjectController extends Controller
{
    public function index(Request $request)
    {
        $classLevel = $request->input('class_level', TarbiyahClass::LEVELS[0]);

        return view('pages.admin.tarbiyah.subjects', [
            'activeRole' => 'admin',
            'classLevels' => TarbiyahClass::LEVELS,
            'classLevel' => $classLevel,
            'subjects' => TarbiyahSubject::where('class_level', $classLevel)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_level' => ['required', Rule::in(TarbiyahClass::LEVELS)],
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        TarbiyahSubject::updateOrCreate(
            ['class_level' => $validated['class_level'], 'name' => $validated['name']],
            [
                'sort_order' => $validated['sort_order'] ?? 100,
                'is_active' => $request->boolean('is_active', true),
            ]
        );

        return back()->with('success', 'Mata pelajaran Tarbiyah berhasil disimpan.');
    }

    public function update(Request $request, TarbiyahSubject $subject)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $subject->update([
            'name' => $validated['name'],
            'sort_order' => $validated['sort_order'] ?? 100,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Mata pelajaran Tarbiyah berhasil diperbarui.');
    }

    public function destroy(TarbiyahSubject $subject)
    {
        $subject->delete();

        return back()->with('success', 'Mata pelajaran Tarbiyah berhasil dihapus.');
    }
}
