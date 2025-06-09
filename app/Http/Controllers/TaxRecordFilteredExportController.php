<?php

namespace App\Http\Controllers;

use App\Models\TaxRecord;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TaxRecordFilteredExportController extends Controller
{
    public function exportFiltered(Request $request)
    {
        $month = $request->get('month');
        $year = $request->get('year');
        
        // Ambil data berdasarkan filter
        $records = TaxRecord::whereMonth('date', $month)
                            ->whereYear('date', $year)
                            ->orderBy('date')
                            ->get();
                            
        // Pisahkan data instansi dan swasta
        $instansiRecords = $records->where('invoice_type', '020');
        $swastaRecords = $records->where('invoice_type', '040');
        
        // Nama bulan dalam bahasa Indonesia
        $bulanNames = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
            '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];
        
        $bulanName = $bulanNames[$month];
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Header template seperti gambar
        $row = 1;
        
        // MONITORING PROJECT
        $sheet->mergeCells('A' . $row . ':L' . $row);
        $sheet->setCellValue('A' . $row, 'MONITORING PROJECT');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;
        
        // PT. AUTOMATA INFO NUSANTARA
        $sheet->mergeCells('A' . $row . ':L' . $row);
        $sheet->setCellValue('A' . $row, 'PT. AUTOMATA INFO NUSANTARA');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;
        
        // TAHUN 2025 (dynamic)
        $sheet->mergeCells('A' . $row . ':L' . $row);
        $sheet->setCellValue('A' . $row, 'TAHUN ' . $year);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;
        
        // Garis pembatas
        $sheet->mergeCells('A' . $row . ':L' . $row);
        $sheet->setCellValue('A' . $row, '');
        $sheet->getStyle('A' . $row . ':L' . $row)->getBorders()->getBottom()
            ->setBorderStyle(Border::BORDER_THICK);
        $row++;
        
        // Spasi
        $row++;
        
        // Periode
        $sheet->mergeCells('A' . $row . ':L' . $row);
        $sheet->setCellValue('A' . $row, 'PERIODE: ' . strtoupper($bulanName) . ' ' . $year);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;
        
        // Spasi
        $row++;
        
        // SECTION TRANSAKSI INSTANSI
        $sheet->mergeCells('A' . $row . ':L' . $row);
        $sheet->setCellValue('A' . $row, 'TRANSAKSI INSTANSI (020)');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('A' . $row)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E3F2FD');
        $row++;
        
        // Header tabel
        $headerRow = $row;
        $headers = ['No', 'Tanggal', 'Customer', 'Project', 'Uraian', 'Qty', 'Harga Satuan', 'Jumlah Harga', 'DPP Asli', 'DPP Lain2', 'PPN', 'PPH', 'TOTAL', 'SP2D'];
        foreach ($headers as $index => $header) {
            $col = chr(65 + $index); // A, B, C, etc.
            $sheet->setCellValue($col . $headerRow, $header);
            $sheet->getStyle($col . $headerRow)->getFont()->setBold(true);
            $sheet->getStyle($col . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($col . $headerRow)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('BBDEFB');
        }
        $row++;
        
        // Data Instansi
        $no = 1;
        foreach ($instansiRecords as $record) {
            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $record->date->format('d/m/Y'));
            $sheet->setCellValue('C' . $row, $record->customer_name);
            $sheet->setCellValue('D' . $row, $record->project_name);
            $sheet->setCellValue('E' . $row, $record->description);
            $sheet->setCellValue('F' . $row, $record->quantity . ' ' . $record->unit_type);
            $sheet->setCellValue('G' . $row, 'Rp ' . number_format($record->price, 0, ',', '.'));
            $sheet->setCellValue('H' . $row, 'Rp ' . number_format($record->total_price, 0, ',', '.'));
            $sheet->setCellValue('I' . $row, 'Rp ' . number_format($record->dpp_amount, 0, ',', '.'));
            $sheet->setCellValue('J' . $row, 'Rp ' . number_format($record->dpp_amount_other, 0, ',', '.'));
            $sheet->setCellValue('K' . $row, 'Rp ' . number_format($record->ppn_amount, 0, ',', '.'));
            $sheet->setCellValue('L' . $row, 'Rp ' . number_format($record->pph_amount, 0, ',', '.'));
            $sheet->setCellValue('M' . $row, 'Rp ' . number_format($record->grand_total, 0, ',', '.'));
            $sheet->setCellValue('N' . $row, 'Rp ' . number_format($record->sp2d_value, 0, ',', '.'));
            $row++;
            $no++;
        }
        
        // Total Instansi
        if ($instansiRecords->count() > 0) {
            $sheet->mergeCells('A' . $row . ':G' . $row);
            $sheet->setCellValue('A' . $row, 'TOTAL INSTANSI');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $sheet->setCellValue('H' . $row, 'Rp ' . number_format($instansiRecords->sum('total_price'), 0, ',', '.'));
            $sheet->setCellValue('I' . $row, 'Rp ' . number_format($instansiRecords->sum('dpp_amount'), 0, ',', '.'));
            $sheet->setCellValue('J' . $row, 'Rp ' . number_format($instansiRecords->sum('dpp_amount_other'), 0, ',', '.'));
            $sheet->setCellValue('K' . $row, 'Rp ' . number_format($instansiRecords->sum('ppn_amount'), 0, ',', '.'));
            $sheet->setCellValue('L' . $row, 'Rp ' . number_format($instansiRecords->sum('pph_amount'), 0, ',', '.'));
            $sheet->setCellValue('M' . $row, 'Rp ' . number_format($instansiRecords->sum('grand_total'), 0, ',', '.'));
            $sheet->setCellValue('N' . $row, 'Rp ' . number_format($instansiRecords->sum('sp2d_value'), 0, ',', '.'));
            $sheet->getStyle('A' . $row . ':N' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row . ':N' . $row)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('C8E6C9');
            $row++;
        }
        
        // Spasi
        $row += 2;
        
        // SECTION TRANSAKSI SWASTA
        $sheet->mergeCells('A' . $row . ':L' . $row);
        $sheet->setCellValue('A' . $row, 'TRANSAKSI SWASTA (040)');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('A' . $row)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFF3E0');
        $row++;
        
        // Header tabel Swasta
        $headerRow = $row;
        foreach ($headers as $index => $header) {
            $col = chr(65 + $index);
            $sheet->setCellValue($col . $headerRow, $header);
            $sheet->getStyle($col . $headerRow)->getFont()->setBold(true);
            $sheet->getStyle($col . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($col . $headerRow)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('FFCC80');
        }
        $row++;
        
        // Data Swasta
        $no = 1;
        foreach ($swastaRecords as $record) {
            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $record->date->format('d/m/Y'));
            $sheet->setCellValue('C' . $row, $record->customer_name);
            $sheet->setCellValue('D' . $row, $record->project_name);
            $sheet->setCellValue('E' . $row, $record->description);
            $sheet->setCellValue('F' . $row, $record->quantity . ' ' . $record->unit_type);
            $sheet->setCellValue('G' . $row, 'Rp ' . number_format($record->price, 0, ',', '.'));
            $sheet->setCellValue('H' . $row, 'Rp ' . number_format($record->total_price, 0, ',', '.'));
            $sheet->setCellValue('I' . $row, 'Rp ' . number_format($record->dpp_amount, 0, ',', '.'));
            $sheet->setCellValue('J' . $row, 'Rp ' . number_format($record->dpp_amount_other, 0, ',', '.'));
            $sheet->setCellValue('K' . $row, 'Rp ' . number_format($record->ppn_amount, 0, ',', '.'));
            $sheet->setCellValue('L' . $row, 'Rp ' . number_format($record->pph_amount, 0, ',', '.'));
            $sheet->setCellValue('M' . $row, 'Rp ' . number_format($record->grand_total, 0, ',', '.'));
            $sheet->setCellValue('N' . $row, 'Rp ' . number_format($record->sp2d_value, 0, ',', '.'));
            $row++;
            $no++;
        }
        
        // Total Swasta
        if ($swastaRecords->count() > 0) {
            $sheet->mergeCells('A' . $row . ':G' . $row);
            $sheet->setCellValue('A' . $row, 'TOTAL SWASTA');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $sheet->setCellValue('H' . $row, 'Rp ' . number_format($swastaRecords->sum('total_price'), 0, ',', '.'));
            $sheet->setCellValue('I' . $row, 'Rp ' . number_format($swastaRecords->sum('dpp_amount'), 0, ',', '.'));
            $sheet->setCellValue('J' . $row, 'Rp ' . number_format($swastaRecords->sum('dpp_amount_other'), 0, ',', '.'));
            $sheet->setCellValue('K' . $row, 'Rp ' . number_format($swastaRecords->sum('ppn_amount'), 0, ',', '.'));
            $sheet->setCellValue('L' . $row, 'Rp ' . number_format($swastaRecords->sum('pph_amount'), 0, ',', '.'));
            $sheet->setCellValue('M' . $row, 'Rp ' . number_format($swastaRecords->sum('grand_total'), 0, ',', '.'));
            $sheet->setCellValue('N' . $row, 'Rp ' . number_format($swastaRecords->sum('sp2d_value'), 0, ',', '.'));
            $sheet->getStyle('A' . $row . ':N' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row . ':N' . $row)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('FFAB91');
            $row++;
        }
        
        // GRAND TOTAL
        $row += 2;
        $sheet->mergeCells('A' . $row . ':G' . $row);
        $sheet->setCellValue('A' . $row, 'GRAND TOTAL');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
        $sheet->setCellValue('H' . $row, 'Rp ' . number_format($records->sum('total_price'), 0, ',', '.'));
        $sheet->setCellValue('I' . $row, 'Rp ' . number_format($records->sum('dpp_amount'), 0, ',', '.'));
        $sheet->setCellValue('J' . $row, 'Rp ' . number_format($records->sum('dpp_amount_other'), 0, ',', '.'));
        $sheet->setCellValue('K' . $row, 'Rp ' . number_format($records->sum('ppn_amount'), 0, ',', '.'));
        $sheet->setCellValue('L' . $row, 'Rp ' . number_format($records->sum('pph_amount'), 0, ',', '.'));
        $sheet->setCellValue('M' . $row, 'Rp ' . number_format($records->sum('grand_total'), 0, ',', '.'));
        $sheet->setCellValue('N' . $row, 'Rp ' . number_format($records->sum('sp2d_value'), 0, ',', '.'));
        $sheet->getStyle('A' . $row . ':N' . $row)->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A' . $row . ':N' . $row)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E1F5FE');
        
        // Auto-size columns
        foreach (range('A', 'N') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Prepare response
        $filename = "Monitoring_Pajak_{$bulanName}_{$year}.xlsx";
        
        return new StreamedResponse(function() use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
