<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceExportController extends Controller
{
    public function export($id)
    {
        $invoice = Invoice::with(['items.actions'])->findOrFail($id);

        // Perhitungan ulang agar pasti benar
        $subtotal = $invoice->items->sum(function($item) {
            return $item->actions->sum('jumlah_harga');
        });
        $ppn = 0;
        $grandTotal = $subtotal;
        if ($invoice->tax_type === 'tax') {
            $ppn = $subtotal * ($invoice->ppn_rate / 100);
            $grandTotal = $subtotal + $ppn;
        }

        // Terbilang
        $terbilang = $this->terbilang((int) $grandTotal) . ' rupiah';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Keterangan tambahan di atas judul besar
        $rowExcel = 1;
        $tanggalSurat = $invoice->tanggal_surat ? \Carbon\Carbon::parse($invoice->tanggal_surat)->translatedFormat('d F Y') : '';
        $sheet->mergeCells('A' . $rowExcel . ':H' . $rowExcel);
        $sheet->setCellValue('A' . $rowExcel, trim(($invoice->tempat ? $invoice->tempat . ', ' : '') . $tanggalSurat));
        $sheet->getStyle('A' . $rowExcel)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $rowExcel++;
        $sheet->mergeCells('A' . $rowExcel . ':H' . $rowExcel);
        $sheet->setCellValue('A' . $rowExcel, ''); // spasi
        $sheet->getStyle('A' . $rowExcel)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $rowExcel++;
        // Baris Kepada Yth selalu ada
        $sheet->mergeCells('A' . $rowExcel . ':H' . $rowExcel);
        $sheet->setCellValue('A' . $rowExcel, 'Kepada Yth,');
        $sheet->getStyle('A' . $rowExcel)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $rowExcel++;
        // Isi field kepada, auto wrap jika terlalu panjang
        if ($invoice->kepada) {
            $kepadaLines = preg_split('/\r\n|\r|\n/', $invoice->kepada);
            foreach ($kepadaLines as $line) {
                if (mb_strlen($line) > 40) {
                    // Bagi per kata, max 40 char per baris
                    $words = explode(' ', $line);
                    $current = '';
                    foreach ($words as $word) {
                        if (mb_strlen($current . ' ' . $word) > 40) {
                            $sheet->mergeCells('A' . $rowExcel . ':H' . $rowExcel);
                            $sheet->setCellValue('A' . $rowExcel, trim($current));
                            $sheet->getStyle('A' . $rowExcel)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                            $rowExcel++;
                            $current = $word;
                        } else {
                            $current .= ($current ? ' ' : '') . $word;
                        }
                    }
                    if ($current) {
                        $sheet->mergeCells('A' . $rowExcel . ':H' . $rowExcel);
                        $sheet->setCellValue('A' . $rowExcel, trim($current));
                        $sheet->getStyle('A' . $rowExcel)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                        $rowExcel++;
                    }
                } else {
                    $sheet->mergeCells('A' . $rowExcel . ':H' . $rowExcel);
                    $sheet->setCellValue('A' . $rowExcel, $line);
                    $sheet->getStyle('A' . $rowExcel)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $rowExcel++;
                }
            }
        }
        $sheet->mergeCells('A' . $rowExcel . ':H' . $rowExcel);
        $sheet->setCellValue('A' . $rowExcel, ''); // spasi
        $sheet->getStyle('A' . $rowExcel)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $rowExcel++;
        $sheet->mergeCells('A' . $rowExcel . ':H' . $rowExcel);
        $sheet->setCellValue('A' . $rowExcel, 'Di');
        $sheet->getStyle('A' . $rowExcel)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $rowExcel++;
        $sheet->mergeCells('A' . $rowExcel . ':H' . $rowExcel);
        $sheet->setCellValue('A' . $rowExcel, $invoice->di_lokasi);
        $sheet->getStyle('A' . $rowExcel)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $rowExcel++;

        // Judul besar
        $sheet->mergeCells('A' . $rowExcel . ':H' . $rowExcel);
        $sheet->setCellValue('A' . $rowExcel, 'FAKTUR BARANG');
        $sheet->getStyle('A' . $rowExcel)->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A' . $rowExcel)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $rowExcel++;
        // Nomor faktur di bawah judul
        $sheet->mergeCells('A' . $rowExcel . ':H' . $rowExcel);
        $sheet->setCellValue('A' . $rowExcel, 'Nomor : ' . $invoice->invoice_number);
        $sheet->getStyle('A' . $rowExcel)->getFont()->setBold(false)->setSize(12);
        $sheet->getStyle('A' . $rowExcel)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $rowExcel++;
        // Header tabel
        $headerRow = $rowExcel;
        $sheet->setCellValue('A' . $headerRow, 'No');
        $sheet->setCellValue('B' . $headerRow, 'Nama barang');
        $sheet->setCellValue('C' . $headerRow, 'No Inventaris / Bagian');
        $sheet->setCellValue('D' . $headerRow, 'Tindakan');
        $sheet->setCellValue('E' . $headerRow, 'Qty');
        $sheet->setCellValue('F' . $headerRow, 'Satuan');
        $sheet->setCellValue('G' . $headerRow, 'Harga_Satuan');
        $sheet->setCellValue('H' . $headerRow, 'Jumlah_Harga');
        $sheet->getStyle('A' . $headerRow . ':H' . $headerRow)->getFont()->setBold(true);
        $sheet->getStyle('A' . $headerRow . ':H' . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Data barang dan tindakan
        $row = $headerRow + 1;
        $no = 1;
        
        // Loop melalui items dan actions
        foreach ($invoice->items as $itemIndex => $item) {
            foreach ($item->actions as $actionIndex => $action) {
                // Tampilkan nama barang dan info hanya di baris pertama per item
                $isFirstAction = $actionIndex === 0;
                
                $sheet->setCellValue('A' . $row, $isFirstAction ? $no : '');
                $sheet->setCellValue('B' . $row, $isFirstAction ? $item->nama_barang : '');
                $sheet->setCellValue('C' . $row, $isFirstAction ? ($item->nomor_inventaris . ' / ' . $item->bagian) : '');
                $sheet->setCellValue('D' . $row, $action->tindakan);
                $sheet->setCellValue('E' . $row, $action->qty);
                $sheet->setCellValue('F' . $row, $action->satuan);
                $sheet->setCellValue('G' . $row, 'Rp ' . number_format($action->harga_satuan, 0, ',', '.'));
                $sheet->setCellValue('H' . $row, 'Rp ' . number_format($action->jumlah_harga, 0, ',', '.'));
                $row++;
            }
            
            // Tambahkan 1 baris kosong setelah setiap item selesai
            $sheet->setCellValue('A' . $row, '');
            $sheet->setCellValue('B' . $row, '');
            $sheet->setCellValue('C' . $row, '');
            $sheet->setCellValue('D' . $row, '');
            $sheet->setCellValue('E' . $row, '');
            $sheet->setCellValue('F' . $row, '');
            $sheet->setCellValue('G' . $row, '');
            $sheet->setCellValue('H' . $row, '');
            $row++;
            
            $no++;
        }

        // Baris total
        $sheet->mergeCells('A' . $row . ':F' . $row);
        if ($invoice->tax_type === 'tax') {
            $sheet->setCellValue('G' . $row, 'SUB - TOTAL');
            $sheet->setCellValue('H' . $row, 'Rp ' . number_format($subtotal, 0, ',', '.'));
            $sheet->getStyle('G' . $row . ':H' . $row)->getFont()->setBold(true);
            $row++;
            $sheet->mergeCells('A' . $row . ':F' . $row);
            $sheet->setCellValue('G' . $row, 'PPN');
            $sheet->setCellValue('H' . $row, 'Rp ' . number_format($ppn, 0, ',', '.'));
            $sheet->getStyle('G' . $row . ':H' . $row)->getFont()->setBold(true);
            $row++;
            $sheet->mergeCells('A' . $row . ':F' . $row);
            $sheet->setCellValue('G' . $row, 'GRAND TOTAL');
            $sheet->setCellValue('H' . $row, 'Rp ' . number_format($grandTotal, 0, ',', '.'));
            $sheet->getStyle('G' . $row . ':H' . $row)->getFont()->setBold(true);
            $row++;
        } else {
            $sheet->setCellValue('G' . $row, 'TOTAL');
            $sheet->setCellValue('H' . $row, 'Rp ' . number_format($subtotal, 0, ',', '.'));
            $sheet->getStyle('G' . $row . ':H' . $row)->getFont()->setBold(true);
            $row++;
        }

        // Simpan baris terakhir tabel untuk border
        $lastTableRow = $row - 1;

        // Terbilang
        $sheet->mergeCells('A' . $row . ':H' . $row);
        $sheet->setCellValue('A' . $row, 'Terbilang : ' . ucfirst($terbilang));
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Keterangan bawah khusus non pajak
        if ($invoice->tax_type === 'non_tax') {
            $row++;
            $sheet->mergeCells('A' . $row . ':H' . $row);
            $sheet->setCellValue('A' . $row, 'Keterangan: * Harga diatas Sudah Termasuk Pajak');
            $sheet->getStyle('A' . $row)->getFont()->setItalic(true);
        }

        // Blok tanda tangan
        $row += 2; // Spasi setelah terbilang/keterangan

        // Baris "Hormat Kami"
        $sheet->mergeCells('F' . $row . ':H' . $row);
        $sheet->setCellValue('F' . $row, 'Hormat Kami');
        $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row++;
        // Baris nama perusahaan (bold)
        $sheet->mergeCells('F' . $row . ':H' . $row);
        $sheet->setCellValue('F' . $row, 'PT. AUTOMATA INFO NUSANTARA');
        $sheet->getStyle('F' . $row)->getFont()->setBold(true);
        $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row += 3; // Spasi untuk tanda tangan

        // Baris nama direktur (bold)
        $sheet->mergeCells('F' . $row . ':H' . $row);
        $sheet->setCellValue('F' . $row, 'RAKIM');
        $sheet->getStyle('F' . $row)->getFont()->setBold(true);
        $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row++;
        // Baris jabatan
        $sheet->mergeCells('F' . $row . ':H' . $row);
        $sheet->setCellValue('F' . $row, 'Direktur');
        $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Table border styling HANYA untuk area tabel
        $sheet->getStyle('A' . $headerRow . ':H' . $lastTableRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Auto column width
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Download response
        $writer = new Xlsx($spreadsheet);
        $jenis = $invoice->tax_type === 'tax' ? 'Pajak' : 'NonPajak';
        $fileName = 'Faktur-' . $jenis . '-' . $invoice->invoice_number . '.xlsx';
        return new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment;filename="' . $fileName . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    // Fungsi sederhana terbilang (Indonesia)
    private function terbilang($number)
    {
        $words = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];
        if ($number < 12) {
            return $words[$number];
        } elseif ($number < 20) {
            return $this->terbilang($number - 10) . " belas";
        } elseif ($number < 100) {
            return $this->terbilang($number / 10) . " puluh " . $this->terbilang($number % 10);
        } elseif ($number < 200) {
            return "seratus " . $this->terbilang($number - 100);
        } elseif ($number < 1000) {
            return $this->terbilang($number / 100) . " ratus " . $this->terbilang($number % 100);
        } elseif ($number < 2000) {
            return "seribu " . $this->terbilang($number - 1000);
        } elseif ($number < 1000000) {
            return $this->terbilang($number / 1000) . " ribu " . $this->terbilang($number % 1000);
        } elseif ($number < 1000000000) {
            return $this->terbilang($number / 1000000) . " juta " . $this->terbilang($number % 1000000);
        } else {
            return "(terlalu besar)";
        }
    }
}
