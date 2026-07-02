<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TarbiyahMonthlyExam;
use App\Models\TarbiyahSubject;
use App\Support\TarbiyahClass;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TarbiyahSubjectController extends Controller
{
    public function index(Request $request)
    {
        $classLevels = TarbiyahClass::levels();
        $classLevel = $request->input('class_level', $classLevels[0]);

        return view('pages.admin.tarbiyah.subjects', [
            'activeRole' => 'admin',
            'classLevels' => $classLevels,
            'classLevel' => $classLevel,
            'subjects' => TarbiyahSubject::where('class_level', $classLevel)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'monthlyExams' => TarbiyahMonthlyExam::query()
                ->latest('exam_date')
                ->latest()
                ->paginate(10, ['*'], 'exam_page')
                ->withQueryString(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_level' => ['required', Rule::in(TarbiyahClass::levels())],
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

    public function storeMonthlyExam(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'exam_date' => ['required', 'date'],
        ]);

        TarbiyahMonthlyExam::create([
            ...$validated,
            'created_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Ujian bulanan berhasil dibuat.');
    }

    public function updateMonthlyExam(Request $request, TarbiyahMonthlyExam $monthlyExam)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'exam_date' => ['required', 'date'],
        ]);

        $monthlyExam->update($validated);

        return back()->with('success', 'Ujian bulanan berhasil diperbarui.');
    }

    public function destroyMonthlyExam(TarbiyahMonthlyExam $monthlyExam)
    {
        $monthlyExam->delete();

        return back()->with('success', 'Ujian bulanan berhasil dihapus.');
    }
}
