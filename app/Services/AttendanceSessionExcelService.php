<?php

namespace App\Services;

use App\Models\AttendanceSession;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class AttendanceSessionExcelService
{
    public const HEADERS = [
        'No',
        'Foto',
        'UID RFID',
        'Nama Santri',
        'Asal',
        'Kamar',
        'Kelas',
        'Waktu Kedatangan',
        'Status Kehadiran',
    ];

    public function export(AttendanceSession $session): StreamedResponse
    {
        $session->load(['records.santri.kamarSantri']);
        
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        
        // Header Info
        $sheet->setCellValue('A1', 'Sesi Absensi: ' . $session->title);
        $sheet->setCellValue('A2', 'Tanggal Pelaksanaan: ' . $session->start_time->format('d M Y'));
        $sheet->setCellValue('A3', 'Waktu: ' . $session->start_time->format('H:i') . ' - ' . $session->end_time->format('H:i'));
        
        $sheet->getStyle('A1:A3')->getFont()->setBold(true);
        
        // Table Headers
        $sheet->fromArray(self::HEADERS, null, 'A5');
        $lastColumn = 'I';
        $sheet->getStyle("A5:{$lastColumn}5")->getFont()->setBold(true);
        $sheet->getStyle("A5:{$lastColumn}5")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD1FAE5');
            
        $rowNumber = 6;
        $no = 1;
        
        foreach ($session->records as $record) {
            $santri = $record->santri;
            
            $sheet->setCellValue('A' . $rowNumber, $no++);
            $sheet->setCellValue('C' . $rowNumber, $santri->rfid_code ?? '-');
            $sheet->setCellValue('D' . $rowNumber, $santri->name);
            $sheet->setCellValue('E' . $rowNumber, $santri->asal_sekolah ?? $santri->tempat_lahir ?? '-');
            $sheet->setCellValue('F' . $rowNumber, $santri->kamarSantri?->kamar ?? $santri->kamar_terakhir ?? '-');
            $sheet->setCellValue('G' . $rowNumber, $santri->kelas ?? '-');
            $sheet->setCellValue('H' . $rowNumber, $record->scanned_at->format('Y-m-d H:i:s'));
            $sheet->setCellValue('I' . $rowNumber, ucfirst($record->status));
            
            // Image
            if ($santri->foto && Storage::disk('public')->exists($santri->foto)) {
                $imagePath = Storage::disk('public')->path($santri->foto);
                if (file_exists($imagePath)) {
                    try {
                        $drawing = new Drawing();
                        $drawing->setName('Foto ' . $santri->name);
                        $drawing->setDescription('Foto Santri');
                        $drawing->setPath($imagePath);
                        $drawing->setCoordinates('B' . $rowNumber);
                        $drawing->setHeight(40);
                        $drawing->setOffsetX(4);
                        $drawing->setOffsetY(4);
                        $drawing->setWorksheet($sheet);

                        $sheet->getRowDimension($rowNumber)->setRowHeight(36);
                    } catch (Throwable $e) {
                        // ignore drawing error
                    }
                }
            }
            
            $rowNumber++;
        }
        
        foreach (range('A', $lastColumn) as $column) {
            if ($column === 'B') {
                $sheet->getColumnDimension($column)->setAutoSize(false);
                $sheet->getColumnDimension($column)->setWidth(18);
            } else {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
        }
        
        $filename = "hasil-absensi-{$session->id}-" . now()->format('Ymd-His') . '.xlsx';
        
        return response()->streamDownload(function () use ($spreadsheet): void {
            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }
}
