<?php

namespace App\Http\Controllers\Santri;

use App\Http\Controllers\Controller;
use App\Models\TarbiyahGrade;
use App\Models\TarbiyahMonthlyExam;
use App\Models\TarbiyahMonthlyGrade;
use App\Models\TarbiyahSubject;
use App\Support\TarbiyahClass;
use Illuminate\Http\Request;

class TarbiyahController extends Controller
{
    public function index(Request $request)
    {
        $santri = auth()->user();
        $mode = $request->input('mode', 'monthly');
        $classLevel = $request->input('class_level', $santri->kelas);
        $month = $request->input('month', now()->format('Y-m'));

        if (! in_array($classLevel, TarbiyahClass::levels(), true)) {
            $classLevel = $santri->kelas;
        }

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
        $monthlyExams = TarbiyahMonthlyExam::query()
            ->whereYear('exam_date', substr($month, 0, 4))
            ->whereMonth('exam_date', substr($month, 5, 2))
            ->orderBy('exam_date')
            ->get();
        $monthlyGrades = TarbiyahMonthlyGrade::with('exam')
            ->where('santri_id', $santri->id)
            ->where('class_level', $classLevel)
            ->whereIn('monthly_exam_id', $monthlyExams->pluck('id'))
            ->orderBy('subject')
            ->get();

        return view('pages.santri.tarbiyah.index', [
            'mode' => $mode,
            'classLevels' => TarbiyahClass::levels(),
            'classLevel' => $classLevel,
            'month' => $month,
            'subjects' => $subjects,
            'grades' => $grades,
            'semesterAverages' => $grades->groupBy('semester')->map(fn ($items) => round($items->avg('score'), 1)),
            'monthlySubjects' => TarbiyahMonthlyGrade::SUBJECTS,
            'monthlyExams' => $monthlyExams,
            'monthlyGrades' => $monthlyGrades,
        ]);
    }
}
