<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\TarbiyahGrade;
use App\Models\TarbiyahSubject;
use App\Models\User;
use App\Support\TarbiyahClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class TarbiyahGradeController extends Controller
{
    public function index(Request $request)
    {
        $classLevel = $request->input('class_level', TarbiyahClass::LEVELS[0]);
        $semester = (int) $request->input('semester', 1);
        $academicYear = $request->input('academic_year', $this->defaultAcademicYear());

        $subjects = $this->subjects($classLevel);
        $santriList = User::activeSantri()
            ->where('kelas', $classLevel)
            ->with(['tarbiyahGrades' => fn ($query) => $query
                ->where('class_level', $classLevel)
                ->where('semester', $semester)
                ->where('academic_year', $academicYear)])
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('pages.petugas.tarbiyah.index', [
            'activeRole' => 'petugas',
            'classLevels' => TarbiyahClass::LEVELS,
            'classLevel' => $classLevel,
            'semester' => $semester,
            'academicYear' => $academicYear,
            'subjects' => $subjects,
            'santriList' => $santriList,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateGradeRequest($request);
        $subjects = $this->subjects($validated['class_level']);
        $santriIds = array_keys($validated['grades'] ?? []);

        DB::transaction(function () use ($validated, $subjects, $santriIds, $request): void {
            foreach ($santriIds as $santriId) {
                User::activeSantri()->where('kelas', $validated['class_level'])->findOrFail($santriId);

                foreach ($subjects as $subject) {
                    $score = $validated['grades'][$santriId][$subject->id] ?? null;
                    if ($score === null || $score === '') {
                        continue;
                    }

                    TarbiyahGrade::updateOrCreate(
                        [
                            'santri_id' => $santriId,
                            'subject_id' => $subject->id,
                            'class_level' => $validated['class_level'],
                            'semester' => $validated['semester'],
                            'academic_year' => $validated['academic_year'],
                        ],
                        [
                            'score' => $score,
                            'recorded_by' => $request->user()->id,
                        ]
                    );
                }
            }
        });

        return back()->with('success', 'Nilai Tarbiyah berhasil disimpan.');
    }

    public function import(Request $request)
    {
        $validated = $request->validate([
            'class_level' => ['required', Rule::in(TarbiyahClass::LEVELS)],
            'semester' => ['required', 'integer', 'in:1,2'],
            'academic_year' => ['required', 'string', 'max:20'],
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
        ]);

        $subjects = $this->subjects($validated['class_level']);
        $sheet = IOFactory::load($validated['excel_file']->getRealPath())->getActiveSheet();
        $headers = array_map(fn ($value) => trim((string) $value), $sheet->rangeToArray('A1:'.$sheet->getHighestColumn().'1')[0]);
        $normalizedHeaders = array_map(fn ($value) => mb_strtolower($value), $headers);
        $nisIndex = array_search('nis', $normalizedHeaders, true);

        if ($nisIndex === false) {
            return back()->withErrors(['excel_file' => 'Header NIS wajib ada di file Excel.']);
        }

        $subjectIndexes = [];
        foreach ($subjects as $subject) {
            $index = array_search(mb_strtolower($subject->name), $normalizedHeaders, true);
            if ($index !== false) {
                $subjectIndexes[$subject->id] = $index;
            }
        }

        if (! $subjectIndexes) {
            return back()->withErrors(['excel_file' => 'Tidak ada header mata pelajaran yang cocok dengan kelas ini.']);
        }

        $updated = 0;
        $errors = [];

        for ($rowNumber = 2; $rowNumber <= $sheet->getHighestDataRow(); $rowNumber++) {
            $values = $sheet->rangeToArray('A'.$rowNumber.':'.$sheet->getHighestColumn().$rowNumber)[0];
            $nis = trim((string) ($values[$nisIndex] ?? ''));
            if ($nis === '') {
                continue;
            }

            try {
                $santri = User::activeSantri()
                    ->where('nis', $nis)
                    ->where('kelas', $validated['class_level'])
                    ->firstOrFail();

                foreach ($subjects as $subject) {
                    if (! array_key_exists($subject->id, $subjectIndexes)) {
                        continue;
                    }

                    $score = $values[$subjectIndexes[$subject->id]] ?? null;
                    if ($score === null || $score === '') {
                        continue;
                    }

                    $score = (float) $score;
                    if ($score < 0 || $score > 100) {
                        throw new \InvalidArgumentException("Nilai {$subject->name} harus 0-100.");
                    }

                    TarbiyahGrade::updateOrCreate(
                        [
                            'santri_id' => $santri->id,
                            'subject_id' => $subject->id,
                            'class_level' => $validated['class_level'],
                            'semester' => $validated['semester'],
                            'academic_year' => $validated['academic_year'],
                        ],
                        ['score' => $score, 'recorded_by' => $request->user()->id]
                    );
                    $updated++;
                }
            } catch (Throwable $exception) {
                $errors[] = "Baris {$rowNumber}: {$exception->getMessage()}";
            }
        }

        return back()
            ->with('success', "{$updated} nilai berhasil diimport.")
            ->with('import_errors', $errors);
    }

    public function template(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'class_level' => ['required', Rule::in(TarbiyahClass::LEVELS)],
        ]);

        $headers = ['NIS', 'Nama Santri', ...$this->subjects($validated['class_level'])->pluck('name')->all()];
        $rows = User::activeSantri()
            ->where('kelas', $validated['class_level'])
            ->orderBy('name')
            ->get()
            ->map(fn (User $santri) => [$santri->nis, $santri->name])
            ->all();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($headers, null, 'A1');
        if ($rows) {
            $sheet->fromArray($rows, null, 'A2');
        }
        $lastColumn = $sheet->getHighestColumn();
        $sheet->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true);
        $sheet->getStyle("A1:{$lastColumn}1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD1FAE5');
        foreach (range('A', $lastColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($spreadsheet): void {
            (new Xlsx($spreadsheet))->save('php://output');
        }, 'template-nilai-tarbiyah-'.str_replace(' ', '-', $validated['class_level']).'.xlsx');
    }

    public function promote(Request $request)
    {
        $validated = $request->validate([
            'class_level' => ['required', Rule::in(TarbiyahClass::LEVELS)],
            'academic_year' => ['required', 'string', 'max:20'],
        ]);

        $nextClass = TarbiyahClass::next($validated['class_level']);
        if (! $nextClass) {
            return back()->withErrors(['class_level' => 'Kelas ini sudah berada di tingkat terakhir.']);
        }

        $subjects = $this->subjects($validated['class_level']);
        $requiredCount = $subjects->count() * 2;
        $promoted = 0;

        User::activeSantri()->where('kelas', $validated['class_level'])->get()->each(function (User $santri) use ($subjects, $requiredCount, $validated, $nextClass, &$promoted): void {
            $gradeCount = TarbiyahGrade::where('santri_id', $santri->id)
                ->where('class_level', $validated['class_level'])
                ->where('academic_year', $validated['academic_year'])
                ->whereIn('subject_id', $subjects->pluck('id'))
                ->whereIn('semester', [1, 2])
                ->get(['subject_id', 'semester'])
                ->unique(fn (TarbiyahGrade $grade) => $grade->subject_id.'-'.$grade->semester)
                ->count();

            if ($requiredCount > 0 && $gradeCount >= $requiredCount) {
                $santri->forceFill(['kelas' => $nextClass])->save();
                $promoted++;
            }
        });

        return back()->with('success', "{$promoted} santri berhasil dinaikkan ke {$nextClass}.");
    }

    private function validateGradeRequest(Request $request): array
    {
        return $request->validate([
            'class_level' => ['required', Rule::in(TarbiyahClass::LEVELS)],
            'semester' => ['required', 'integer', 'in:1,2'],
            'academic_year' => ['required', 'string', 'max:20'],
            'grades' => ['array'],
            'grades.*' => ['array'],
            'grades.*.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);
    }

    private function subjects(string $classLevel)
    {
        return TarbiyahSubject::where('class_level', $classLevel)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    private function defaultAcademicYear(): string
    {
        $year = now()->year;

        return now()->month >= 7 ? "{$year}/".($year + 1) : ($year - 1)."/{$year}";
    }
}
