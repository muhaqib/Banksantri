<?php

namespace App\Http\Controllers\Santri;

use App\Http\Controllers\Controller;
use App\Models\TarbiyahGrade;
use App\Models\TarbiyahSubject;

class TarbiyahController extends Controller
{
    public function index()
    {
        $santri = auth()->user();
        $classLevel = $santri->kelas;
        $subjects = TarbiyahSubject::where('class_level', $classLevel)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        $grades = TarbiyahGrade::with('subject')
            ->where('santri_id', $santri->id)
            ->where('class_level', $classLevel)
            ->latest('academic_year')
            ->orderBy('semester')
            ->get();

        return view('pages.santri.tarbiyah.index', [
            'classLevel' => $classLevel,
            'subjects' => $subjects,
            'grades' => $grades,
            'semesterAverages' => $grades->groupBy('semester')->map(fn ($items) => round($items->avg('score'), 1)),
        ]);
    }
}
