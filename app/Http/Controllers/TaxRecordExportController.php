<?php

namespace App\Http\Controllers;

use App\Models\TaxRecord;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TaxRecordExportController extends Controller
{
    public function export(Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');
        $bulanNama = $month ? date('F', mktime(0, 0, 0, $month, 10)) : '';
        $records = TaxRecord::query()
            ->when($month, fn($q) => $q->whereMonth('date', $month))
            ->when($year, fn($q) => $q->whereYear('date', $year))
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header Bulan
        $sheet->mergeCells('A1:P1');
        $sheet->setCellValue('A1', 'Bulan : ' . $bulanNama);
        $sheet->getStyle('A1')->getFont()->setBold(true);

        // Sub-header
        $sheet->setCellValue('A2', 'BULAN');
        $sheet->setCellValue('B2', 'CUSTOMER');
        $sheet->setCellValue('C2', 'URAIAN');
        $sheet->setCellValue('D2', 'Qty');
        $sheet->setCellValue('E2', 'Unit');
        $sheet->mergeCells('F2:G2');
        $sheet->setCellValue('F2', 'MODAL');
        $sheet->mergeCells('H2:I2');
        $sheet->setCellValue('H2', 'DPP Asli');
        $sheet->setCellValue('J2', 'DPP Lain Lain');
        $sheet->setCellValue('K2', 'PPN');
        $sheet->setCellValue('L2', 'PPH');
        $sheet->setCellValue('M2', 'TOTAL');
        $sheet->mergeCells('N2:P2');
        $sheet->setCellValue('N2', 'ADM TAGIHAN');
        $sheet->mergeCells('Q2:R2');
        $sheet->setCellValue('Q2', 'NILAI');
        $sheet->mergeCells('S2:S3');
        $sheet->setCellValue('S2', 'TANGGAL MASUK');

        // Sub-sub-header
        $sheet->setCellValue('F3', 'H_SATUAN');
        $sheet->setCellValue('G3', 'JML_HARGA');
        $sheet->setCellValue('H3', 'H_SATUAN');
        $sheet->setCellValue('I3', 'JML_HARGA');
        $sheet->setCellValue('N3', 'NO KW');
        $sheet->setCellValue('O3', 'TGI KW');
        $sheet->setCellValue('P3', 'NO FAKTUR PAJAK');
        $sheet->setCellValue('Q3', 'SP2D');
        $sheet->setCellValue('R3', ''); // Kosong untuk merge
        $sheet->setCellValue('S3', 'MASUK');

        // Merge cell untuk header utama
        $sheet->mergeCells('A2:A3');
        $sheet->mergeCells('B2:B3');
        $sheet->mergeCells('C2:C3');
        $sheet->mergeCells('D2:D3');
        $sheet->mergeCells('E2:E3');
        $sheet->mergeCells('J2:J3');
        $sheet->mergeCells('K2:K3');
        $sheet->mergeCells('L2:L3');
        $sheet->mergeCells('M2:M3');
        $sheet->mergeCells('S2:S3');
        $sheet->mergeCells('Q2:Q3');
        $sheet->mergeCells('R2:R3');

        $sheet->getStyle('A2:S3')->getFont()->setBold(true);
        $sheet->getStyle('A2:S3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2:S3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // Data
        $row = 4;
        foreach ($records as $record) {
            $sheet->setCellValue('A' . $row, $record->date->format('M'));
            $sheet->setCellValue('B' . $row, $record->customer_name);
            $sheet->setCellValue('C' . $row, $record->description);
            $sheet->setCellValue('D' . $row, $record->quantity);
            $sheet->setCellValue('E' . $row, $record->unit_type);
            $sheet->setCellValue('F' . $row, ''); // Modal H_SATUAN (kosong)
            $sheet->setCellValue('G' . $row, ''); // Modal JML_HARGA (kosong)
            $sheet->setCellValue('H' . $row, number_format($record->price, 2, ',', '.')); // DPP Asli H_SATUAN
            $sheet->setCellValue('I' . $row, number_format($record->dpp_amount, 2, ',', '.')); // DPP Asli JML_HARGA
            $sheet->setCellValue('J' . $row, number_format($record->dpp_amount_other, 2, ',', '.')); // DPP Lain-lain
            $sheet->setCellValue('K' . $row, number_format($record->ppn_amount, 2, ',', '.')); // PPN
            $sheet->setCellValue('L' . $row, number_format($record->pph_amount, 2, ',', '.')); // PPH
            $sheet->setCellValue('M' . $row, number_format($record->grand_total, 2, ',', '.')); // TOTAL
            $sheet->setCellValue('N' . $row, $record->no_kw);
            $sheet->setCellValue('O' . $row, $record->tanggal_kw ? $record->tanggal_kw->format('d-M-y') : '');
            $sheet->setCellValue('P' . $row, $record->invoice_number);
            $sheet->setCellValue('Q' . $row, number_format($record->sp2d_value, 2, ',', '.')); // SP2D
            $sheet->setCellValue('S' . $row, $record->tanggal_masuk ? $record->tanggal_masuk->format('d-M-y') : '');
            $row++;
        }

        // Baris total transaksi
        $sheet->setCellValue('A' . $row, 'TOTAL TRANSAKSI KESELURUHAN');
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->setCellValue('G' . $row, ''); // Modal JML_HARGA (kosong)
        $sheet->setCellValue('I' . $row, number_format($records->sum('dpp_amount'), 2, ',', '.')); // Total DPP Asli
        $sheet->setCellValue('J' . $row, number_format($records->sum('dpp_amount_other'), 2, ',', '.')); // Total DPP Lain-lain
        $sheet->setCellValue('K' . $row, number_format($records->sum('ppn_amount'), 2, ',', '.')); // Total PPN
        $sheet->setCellValue('L' . $row, number_format($records->sum('pph_amount'), 2, ',', '.')); // Total PPH
        $sheet->setCellValue('M' . $row, number_format($records->sum('grand_total'), 2, ',', '.')); // Total GRAND
        $sheet->setCellValue('Q' . $row, number_format($records->sum('sp2d_value'), 2, ',', '.')); // Total SP2D
        $sheet->getStyle('A' . $row . ':S' . $row)->getFont()->setBold(true);

        // Border
        $sheet->getStyle('A2:S' . ($row))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Auto width
        foreach (range('A', 'S') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'RekapPajak-'.$bulanNama.'-'.$year.'.xlsx';
        return new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment;filename="' . $fileName . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }
} 