<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TarbiyahGrade;
use App\Models\TarbiyahMonthlyExam;
use App\Models\TarbiyahMonthlyGrade;
use App\Models\TarbiyahSubject;
use App\Support\TarbiyahClass;
use Illuminate\Http\Request;

class SantriTarbiyahController extends Controller
{
    /**
     * Get tarbiyah grades and monthly exam data for the authenticated santri.
     */
    public function index(Request $request)
    {
        $santri = $request->user();
        $mode = $request->input('mode', 'monthly');
        $classLevel = $request->input('class_level', $santri->kelas);
        $month = $request->input('month', now()->format('Y-m'));

        if (! in_array($classLevel, TarbiyahClass::LEVELS, true)) {
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

        return response()->json([
            'filters' => [
                'mode' => $mode,
                'class_level' => $classLevel,
                'month' => $month,
            ],
            'class_levels' => TarbiyahClass::LEVELS,
            'subjects' => $subjects->map(fn (TarbiyahSubject $subject) => $this->formatSubject($subject)),
            'grades' => $grades->map(fn (TarbiyahGrade $grade) => $this->formatGrade($grade)),
            'semester_averages' => $grades
                ->groupBy('semester')
                ->map(fn ($items) => round($items->avg('score'), 1))
                ->all(),
            'monthly_subjects' => TarbiyahMonthlyGrade::SUBJECTS,
            'monthly_exams' => $monthlyExams->map(fn (TarbiyahMonthlyExam $exam) => $this->formatMonthlyExam($exam)),
            'monthly_grades' => $monthlyGrades->map(fn (TarbiyahMonthlyGrade $grade) => $this->formatMonthlyGrade($grade)),
        ]);
    }

    private function formatSubject(TarbiyahSubject $subject): array
    {
        return [
            'id' => $subject->id,
            'class_level' => $subject->class_level,
            'name' => $subject->name,
            'sort_order' => $subject->sort_order,
            'is_active' => $subject->is_active,
        ];
    }

    private function formatGrade(TarbiyahGrade $grade): array
    {
        return [
            'id' => $grade->id,
            'subject_id' => $grade->subject_id,
            'subject' => $grade->subject?->name,
            'class_level' => $grade->class_level,
            'semester' => (int) $grade->semester,
            'academic_year' => $grade->academic_year,
            'score' => (float) $grade->score,
            'notes' => $grade->notes,
            'created_at' => $grade->created_at?->toISOString(),
            'updated_at' => $grade->updated_at?->toISOString(),
        ];
    }

    private function formatMonthlyExam(TarbiyahMonthlyExam $exam): array
    {
        return [
            'id' => $exam->id,
            'name' => $exam->name,
            'exam_date' => $exam->exam_date?->format('Y-m-d'),
        ];
    }

    private function formatMonthlyGrade(TarbiyahMonthlyGrade $grade): array
    {
        return [
            'id' => $grade->id,
            'monthly_exam_id' => $grade->monthly_exam_id,
            'exam_name' => $grade->exam?->name,
            'exam_date' => $grade->exam?->exam_date?->format('Y-m-d'),
            'class_level' => $grade->class_level,
            'subject' => $grade->subject,
            'score' => (float) $grade->score,
            'created_at' => $grade->created_at?->toISOString(),
            'updated_at' => $grade->updated_at?->toISOString(),
        ];
    }
}
