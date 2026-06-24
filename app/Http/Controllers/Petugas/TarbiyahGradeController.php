<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\PrestasiSantri;
use App\Models\TarbiyahGrade;
use App\Models\TarbiyahMonthlyExam;
use App\Models\TarbiyahMonthlyGrade;
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
        $mode = $request->input('mode', 'semester');
        $classLevel = $request->input('class_level', TarbiyahClass::LEVELS[0]);
        $semester = (int) $request->input('semester', 1);
        $academicYear = $request->input('academic_year', $this->defaultAcademicYear());
        $monthlyExams = TarbiyahMonthlyExam::query()
            ->latest('exam_date')
            ->latest()
            ->get();
        $monthlyExam = $monthlyExams->firstWhere('id', (int) $request->input('monthly_exam_id')) ?? $monthlyExams->first();

        $subjects = $this->subjects($classLevel);
        $santriList = User::activeSantri()
            ->where('kelas', $classLevel)
            ->with([
                'tarbiyahGrades' => fn ($query) => $query
                    ->where('class_level', $classLevel)
                    ->where('semester', $semester)
                    ->where('academic_year', $academicYear),
                'tarbiyahMonthlyGrades' => fn ($query) => $query
                    ->when($monthlyExam, fn ($query) => $query->where('monthly_exam_id', $monthlyExam->id))
                    ->where('class_level', $classLevel),
            ])
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('pages.petugas.tarbiyah.index', [
            'activeRole' => 'petugas',
            'mode' => $mode,
            'classLevels' => TarbiyahClass::LEVELS,
            'classLevel' => $classLevel,
            'semester' => $semester,
            'academicYear' => $academicYear,
            'subjects' => $subjects,
            'monthlySubjects' => TarbiyahMonthlyGrade::SUBJECTS,
            'monthlyExams' => $monthlyExams,
            'monthlyExam' => $monthlyExam,
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

    public function storeMonthly(Request $request)
    {
        $validated = $this->validateMonthlyGradeRequest($request);
        $santriIds = array_keys($validated['grades'] ?? []);

        DB::transaction(function () use ($validated, $santriIds, $request): void {
            foreach ($santriIds as $santriId) {
                User::activeSantri()->where('kelas', $validated['class_level'])->findOrFail($santriId);

                foreach (TarbiyahMonthlyGrade::SUBJECTS as $subject) {
                    $score = $validated['grades'][$santriId][$subject] ?? null;
                    if ($score === null || $score === '') {
                        continue;
                    }

                    TarbiyahMonthlyGrade::updateOrCreate(
                        [
                            'monthly_exam_id' => $validated['monthly_exam_id'],
                            'santri_id' => $santriId,
                            'subject' => $subject,
                        ],
                        [
                            'class_level' => $validated['class_level'],
                            'score' => $score,
                            'recorded_by' => $request->user()->id,
                        ]
                    );
                }

                $this->syncMonthlyPrestasi((int) $santriId, (int) $validated['monthly_exam_id'], $validated['class_level'], $request->user()->id);
            }
        });

        return back()->with('success', 'Nilai ujian bulanan berhasil disimpan.');
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

    public function importMonthly(Request $request)
    {
        $validated = $request->validate([
            'class_level' => ['required', Rule::in(TarbiyahClass::LEVELS)],
            'monthly_exam_id' => ['required', 'exists:tarbiyah_monthly_exams,id'],
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
        ]);

        $sheet = IOFactory::load($validated['excel_file']->getRealPath())->getActiveSheet();
        $headers = array_map(fn ($value) => trim((string) $value), $sheet->rangeToArray('A1:'.$sheet->getHighestColumn().'1')[0]);
        $normalizedHeaders = array_map(fn ($value) => mb_strtolower($value), $headers);
        $nisIndex = array_search('nis', $normalizedHeaders, true);

        if ($nisIndex === false) {
            return back()->withErrors(['excel_file' => 'Header NIS wajib ada di file Excel.']);
        }

        $subjectIndexes = [];
        foreach (TarbiyahMonthlyGrade::SUBJECTS as $subject) {
            $index = array_search(mb_strtolower($subject), $normalizedHeaders, true);
            if ($index !== false) {
                $subjectIndexes[$subject] = $index;
            }
        }

        if (count($subjectIndexes) < count(TarbiyahMonthlyGrade::SUBJECTS)) {
            return back()->withErrors(['excel_file' => 'Header wajib: NIS, Nama Santri, Nahwu, Shorof, Fiqih.']);
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
                DB::transaction(function () use ($validated, $values, $subjectIndexes, $request, &$updated, $nis): void {
                    $santri = User::activeSantri()
                        ->where('nis', $nis)
                        ->where('kelas', $validated['class_level'])
                        ->firstOrFail();

                    foreach (TarbiyahMonthlyGrade::SUBJECTS as $subject) {
                        $score = $values[$subjectIndexes[$subject]] ?? null;
                        if ($score === null || $score === '') {
                            continue;
                        }

                        $score = (float) $score;
                        if ($score < 0 || $score > 100) {
                            throw new \InvalidArgumentException("Nilai {$subject} harus 0-100.");
                        }

                        TarbiyahMonthlyGrade::updateOrCreate(
                            [
                                'monthly_exam_id' => $validated['monthly_exam_id'],
                                'santri_id' => $santri->id,
                                'subject' => $subject,
                            ],
                            [
                                'class_level' => $validated['class_level'],
                                'score' => $score,
                                'recorded_by' => $request->user()->id,
                            ]
                        );
                        $updated++;
                    }

                    $this->syncMonthlyPrestasi($santri->id, (int) $validated['monthly_exam_id'], $validated['class_level'], $request->user()->id);
                });
            } catch (Throwable $exception) {
                $errors[] = "Baris {$rowNumber}: {$exception->getMessage()}";
            }
        }

        return back()
            ->with('success', "{$updated} nilai ujian bulanan berhasil diimport.")
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

    public function exportMonthly(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'class_level' => ['required', Rule::in(TarbiyahClass::LEVELS)],
            'monthly_exam_id' => ['nullable', 'exists:tarbiyah_monthly_exams,id'],
        ]);

        $exam = filled($validated['monthly_exam_id'] ?? null)
            ? TarbiyahMonthlyExam::find((int) $validated['monthly_exam_id'])
            : null;
        $headers = ['NIS', 'Nama Santri', ...TarbiyahMonthlyGrade::SUBJECTS, 'Total', 'Poin Otomatis'];
        $rows = User::activeSantri()
            ->where('kelas', $validated['class_level'])
            ->with(['tarbiyahMonthlyGrades' => fn ($query) => $query
                ->when($exam, fn ($query) => $query->where('monthly_exam_id', $exam->id))
                ->where('class_level', $validated['class_level'])])
            ->orderBy('name')
            ->get()
            ->map(function (User $santri): array {
                $grades = $santri->tarbiyahMonthlyGrades->keyBy('subject');
                $scores = collect(TarbiyahMonthlyGrade::SUBJECTS)->map(fn (string $subject) => $grades[$subject]->score ?? null);
                $total = $scores->filter(fn ($score) => $score !== null)->count() === count(TarbiyahMonthlyGrade::SUBJECTS)
                    ? (float) $scores->sum()
                    : null;

                return [
                    $santri->nis,
                    $santri->name,
                    ...$scores->all(),
                    $total,
                    $total === null ? null : $this->monthlyPoint($total),
                ];
            })
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

        $filenameExam = $exam ? str($exam->name)->slug('-')->toString() : 'template';

        return response()->streamDownload(function () use ($spreadsheet): void {
            (new Xlsx($spreadsheet))->save('php://output');
        }, 'nilai-bulanan-'.$filenameExam.'-'.str_replace(' ', '-', $validated['class_level']).'.xlsx');
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

    private function validateMonthlyGradeRequest(Request $request): array
    {
        return $request->validate([
            'class_level' => ['required', Rule::in(TarbiyahClass::LEVELS)],
            'monthly_exam_id' => ['required', 'exists:tarbiyah_monthly_exams,id'],
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

    private function syncMonthlyPrestasi(int $santriId, int $monthlyExamId, string $classLevel, ?int $recordedBy): void
    {
        $exam = TarbiyahMonthlyExam::findOrFail($monthlyExamId);
        $grades = TarbiyahMonthlyGrade::query()
            ->where('monthly_exam_id', $monthlyExamId)
            ->where('santri_id', $santriId)
            ->where('class_level', $classLevel)
            ->whereIn('subject', TarbiyahMonthlyGrade::SUBJECTS)
            ->get()
            ->keyBy('subject');

        if ($grades->count() < count(TarbiyahMonthlyGrade::SUBJECTS)) {
            return;
        }

        $total = (float) collect(TarbiyahMonthlyGrade::SUBJECTS)->sum(fn (string $subject) => (float) $grades[$subject]->score);
        $point = $this->monthlyPoint($total);

        PrestasiSantri::updateOrCreate(
            [
                'santri_id' => $santriId,
                'tarbiyah_monthly_exam_id' => $monthlyExamId,
            ],
            [
                'pembimbing_id' => $recordedBy,
                'nama_kitab' => 'Ujian Bulanan: '.$exam->name,
                'kategori' => 'tarbiyah',
                'keterangan' => 'Poin otomatis dari total nilai ujian bulanan '.$exam->name.' kelas '.$classLevel.'.',
                'status' => 'telah_dihafalkan',
                'progress' => 100,
                'nilai' => 'Total '.$total,
                'skor' => (int) round($total / count(TarbiyahMonthlyGrade::SUBJECTS)),
                'tanggal_selesai' => $exam->exam_date,
                'bulan_tahun_selesai' => $exam->exam_date?->translatedFormat('F Y'),
                'ustadz_pembimbing' => auth()->user()?->name,
                'catatan_ustadz' => 'Nahwu: '.$grades['Nahwu']->score.', Shorof: '.$grades['Shorof']->score.', Fiqih: '.$grades['Fiqih']->score.'.',
                'poin' => $point,
                'tags' => 'ujian bulanan,tarbiyah',
            ]
        );
    }

    private function monthlyPoint(float $total): int
    {
        if ($total >= 300) {
            return 10;
        }

        if ($total > 180) {
            return 5;
        }

        if ($total >= 90) {
            return 3;
        }

        return -3;
    }
}
