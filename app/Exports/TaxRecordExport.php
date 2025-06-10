<?php

namespace App\Exports;

use App\Models\TaxRecord;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class TaxRecordExport implements FromCollection, WithStyles, WithEvents
{
    protected $filters;
    protected $monthName;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
        
        if (isset($filters['month'])) {
            $monthNames = [
                '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
            ];
            $this->monthName = $monthNames[$filters['month']];
        } else {
            $this->monthName = 'Semua Bulan';
        }
    }

    public function collection()
    {
        // Return empty collection as we'll build the data manually in the events
        return collect([]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ],
            2 => [
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ],
            3 => [
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $currentRow = 1;
                
                // Get year for title
                $year = $this->filters['year'] ?? date('Y');
                
                // Add main header
                $sheet->mergeCells('A1:Q1');
                $sheet->setCellValue('A1', 'MONITORING PROJECT');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                $currentRow = 2;
                $sheet->mergeCells('A2:Q2');
                $sheet->setCellValue('A2', 'PT. AUTOMATA INFO NUSANTARA');
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                $currentRow = 3;
                $sheet->mergeCells('A3:Q3');
                $sheet->setCellValue('A3', 'TAHUN ' . $year);
                $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Add border line
                $currentRow = 4;
                $sheet->getStyle('A4:Q4')->applyFromArray([
                    'borders' => [
                        'top' => ['borderStyle' => Border::BORDER_THICK],
                        'bottom' => ['borderStyle' => Border::BORDER_THICK]
                    ]
                ]);
                
                // Add month filter info
                $currentRow = 6;
                $sheet->setCellValue('A6', 'Bulan : ' . $this->monthName);
                $sheet->getStyle('A6')->getFont()->setBold(true);
                
                // Get data
                $query = TaxRecord::query();
                
                if (isset($this->filters['month'])) {
                    $query->whereMonth('date', $this->filters['month']);
                }
                
                if (isset($this->filters['year'])) {
                    $query->whereYear('date', $this->filters['year']);
                }
                
                if (isset($this->filters['invoice_type'])) {
                    $query->where('invoice_type', $this->filters['invoice_type']);
                }
                
                $allRecords = $query->orderBy('date')->get();
                $instansiRecords = $allRecords->where('invoice_type', '020');
                $swastaRecords = $allRecords->where('invoice_type', '040');
                
                // Create table headers
                $currentRow = 8;
                $this->createTableHeaders($sheet, $currentRow);
                
                // Add data rows
                $currentRow = 10;
                foreach ($allRecords as $record) {
                    $this->addDataRow($sheet, $record, $currentRow);
                    $currentRow++;
                }
                
                // Add total rows
                $currentRow += 1;
                $this->addTotalRows($sheet, $allRecords, $instansiRecords, $swastaRecords, $currentRow);
                
                // Auto-size columns
                foreach (range('A', 'Q') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }
    
    private function createTableHeaders($sheet, $startRow)
    {
        // Main headers row 1 - updated structure with ADM TAGIHAN expanded
        $sheet->setCellValue('A' . $startRow, 'BULAN');
        $sheet->setCellValue('B' . $startRow, 'COSTUMER');
        $sheet->setCellValue('C' . $startRow, 'URAIAN');
        $sheet->setCellValue('D' . $startRow, 'Qty');
        $sheet->setCellValue('E' . $startRow, 'MODAL');
        $sheet->setCellValue('G' . $startRow, 'DPP Asli');
        $sheet->setCellValue('I' . $startRow, 'DPP Lain Lain');
        $sheet->setCellValue('J' . $startRow, 'ADM TAGIHAN'); // Now spans J to O (6 columns)
        $sheet->setCellValue('P' . $startRow, 'NILAI SP2D');
        $sheet->setCellValue('Q' . $startRow, 'TANGGAL MASUK');
        
        // Merge cells for main headers
        $sheet->mergeCells('A' . $startRow . ':A' . ($startRow + 1));  // BULAN (single column, merge vertically)
        $sheet->mergeCells('B' . $startRow . ':B' . ($startRow + 1));  // COSTUMER (single column, merge vertically)
        $sheet->mergeCells('C' . $startRow . ':C' . ($startRow + 1));  // URAIAN (single column, merge vertically)
        $sheet->mergeCells('D' . $startRow . ':D' . ($startRow + 1));  // Qty (single column, merge vertically)
        $sheet->mergeCells('E' . $startRow . ':F' . $startRow);        // MODAL (2 columns horizontally)
        $sheet->mergeCells('G' . $startRow . ':H' . $startRow);        // DPP Asli (2 columns horizontally)
        $sheet->mergeCells('I' . $startRow . ':I' . ($startRow + 1));  // DPP Lain Lain (single column, merge vertically)
        $sheet->mergeCells('J' . $startRow . ':O' . $startRow);        // ADM TAGIHAN (6 columns horizontally: J,K,L,M,N,O)
        $sheet->mergeCells('P' . $startRow . ':P' . ($startRow + 1));  // NILAI (single column, merge vertically)
        $sheet->mergeCells('Q' . $startRow . ':Q' . ($startRow + 1));  // TANGGAL (single column, merge vertically)
        
        // Sub headers row 2 - clear first, then set correct values
        $subRow = $startRow + 1;
        
        // Clear all cells in sub header row first
        for ($col = 'A'; $col <= 'Q'; $col++) {
            $sheet->setCellValue($col . $subRow, '');
        }
        
        // Set only the sub headers that need them
        $sheet->setCellValue('E' . $subRow, 'H_SATUAN');        // MODAL
        $sheet->setCellValue('F' . $subRow, 'JML_HARGA');       // MODAL
        $sheet->setCellValue('G' . $subRow, 'H_SATUAN');        // DPP Asli
        $sheet->setCellValue('H' . $subRow, 'JML_HARGA');       // DPP Asli
        $sheet->setCellValue('J' . $subRow, 'PPN');             // ADM TAGIHAN
        $sheet->setCellValue('K' . $subRow, 'PPH');             // ADM TAGIHAN
        $sheet->setCellValue('L' . $subRow, 'TOTAL');           // ADM TAGIHAN
        $sheet->setCellValue('M' . $subRow, 'NO KW');           // ADM TAGIHAN
        $sheet->setCellValue('N' . $subRow, 'TGL KW');          // ADM TAGIHAN (fixed from TGI to TGL)
        $sheet->setCellValue('O' . $subRow, 'NO FAKTUR PAJAK'); // ADM TAGIHAN
        
        // Style headers
        $sheet->getStyle('A' . $startRow . ':Q' . ($startRow + 1))->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'E6E6FA']
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
    }
    
    private function addDataRow($sheet, $record, $row)
    {
        $monthNames = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Ags',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];
        
        $sheet->setCellValue('A' . $row, $monthNames[Carbon::parse($record->date)->month]);
        $sheet->setCellValue('B' . $row, $record->customer_name);
        $sheet->setCellValue('C' . $row, $record->description);
        $sheet->setCellValue('D' . $row, $record->quantity . ' ' . $record->unit_type);
        $sheet->setCellValue('E' . $row, ''); // Modal H_SATUAN (kosong)
        $sheet->setCellValue('F' . $row, ''); // Modal JML_HARGA (kosong)
        
        // Format numbers properly as actual numbers, not strings
        $sheet->setCellValue('G' . $row, $record->price);        // DPP Asli H_SATUAN
        $sheet->setCellValue('H' . $row, $record->total_price);  // DPP Asli JML_HARGA
        $sheet->setCellValue('I' . $row, $record->dpp_amount_other); // DPP Lain Lain
        $sheet->setCellValue('J' . $row, $record->ppn_amount);   // PPN
        $sheet->setCellValue('K' . $row, $record->pph_amount);   // PPH
        $sheet->setCellValue('L' . $row, $record->grand_total);  // TOTAL
        
        // ADM TAGIHAN
        $sheet->setCellValue('M' . $row, $record->no_kw);        // NO KW
        $sheet->setCellValue('N' . $row, $record->tanggal_kw ? Carbon::parse($record->tanggal_kw)->format('d-M-y') : ''); // TGI KW
        $sheet->setCellValue('O' . $row, $record->invoice_number); // NO FAKTUR PAJAK
        
        // NILAI
        $sheet->setCellValue('P' . $row, $record->sp2d_value);   // SP2D
        
        // TANGGAL
        $sheet->setCellValue('Q' . $row, $record->tanggal_masuk ? Carbon::parse($record->tanggal_masuk)->format('d-M-y') : ''); // MASUK
        
        // Format number columns with Indonesian number format
        $numberColumns = ['G', 'H', 'I', 'J', 'K', 'L', 'P'];
        foreach ($numberColumns as $col) {
            $sheet->getStyle($col . $row)->getNumberFormat()->setFormatCode('#,##0');
        }
        
        // Add background color based on invoice type
        $bgColor = $record->invoice_type === '020' ? 'D6EAF8' : 'FED7AA'; // Light blue for instansi, light orange for swasta
        
        // Add borders and background
        $sheet->getStyle('A' . $row . ':Q' . $row)->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => $bgColor]
            ]
        ]);
    }
    
        private function addTotalRows($sheet, $allRecords, $instansiRecords, $swastaRecords, $startRow)
    {
        // TOTAL TRANSAKSI KESELURUHAN
        $sheet->mergeCells('A' . $startRow . ':F' . $startRow);
        $sheet->setCellValue('A' . $startRow, 'TOTAL TRANSAKSI KESELURUHAN');
        $sheet->setCellValue('G' . $startRow, ''); // Modal H_SATUAN kosong
        $sheet->setCellValue('H' . $startRow, $allRecords->sum('total_price'));
        $sheet->setCellValue('I' . $startRow, $allRecords->sum('dpp_amount_other'));
        $sheet->setCellValue('J' . $startRow, $allRecords->sum('ppn_amount'));
        $sheet->setCellValue('K' . $startRow, $allRecords->sum('pph_amount'));
        $sheet->setCellValue('L' . $startRow, $allRecords->sum('grand_total'));
        $sheet->setCellValue('P' . $startRow, $allRecords->sum('sp2d_value'));
        
        // Format number columns
        $numberColumns = ['H', 'I', 'J', 'K', 'L', 'P'];
        foreach ($numberColumns as $col) {
            $sheet->getStyle($col . $startRow)->getNumberFormat()->setFormatCode('#,##0');
        }
        
        $sheet->getStyle('A' . $startRow . ':Q' . $startRow)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'E8E8E8']
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ]);
        
        $startRow++;
        
        // Total Transaksi Swasta
        if ($swastaRecords->count() > 0) {
            $sheet->mergeCells('A' . $startRow . ':F' . $startRow);
            $sheet->setCellValue('A' . $startRow, 'Total Transaksi Swasta');
            $sheet->setCellValue('H' . $startRow, $swastaRecords->sum('total_price'));
            $sheet->setCellValue('I' . $startRow, $swastaRecords->sum('dpp_amount_other'));
            $sheet->setCellValue('J' . $startRow, $swastaRecords->sum('ppn_amount'));
            $sheet->setCellValue('K' . $startRow, $swastaRecords->sum('pph_amount'));
            $sheet->setCellValue('L' . $startRow, $swastaRecords->sum('grand_total'));
            $sheet->setCellValue('P' . $startRow, $swastaRecords->sum('sp2d_value'));
            
            // Format number columns
            foreach ($numberColumns as $col) {
                $sheet->getStyle($col . $startRow)->getNumberFormat()->setFormatCode('#,##0');
            }
            
            $sheet->getStyle('A' . $startRow . ':Q' . $startRow)->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN]
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => 'FED7AA'] // Orange background for swasta
                ]
            ]);
            
            $startRow++;
        }
        
        // Total Transaksi Instansi
        if ($instansiRecords->count() > 0) {
            $sheet->mergeCells('A' . $startRow . ':F' . $startRow);
            $sheet->setCellValue('A' . $startRow, 'Total Transaksi Instansi');
            $sheet->setCellValue('H' . $startRow, $instansiRecords->sum('total_price'));
            $sheet->setCellValue('I' . $startRow, $instansiRecords->sum('dpp_amount_other'));
            $sheet->setCellValue('J' . $startRow, $instansiRecords->sum('ppn_amount'));
            $sheet->setCellValue('K' . $startRow, $instansiRecords->sum('pph_amount'));
            $sheet->setCellValue('L' . $startRow, $instansiRecords->sum('grand_total'));
            $sheet->setCellValue('P' . $startRow, $instansiRecords->sum('sp2d_value'));
            
            // Format number columns
            foreach ($numberColumns as $col) {
                $sheet->getStyle($col . $startRow)->getNumberFormat()->setFormatCode('#,##0');
            }
            
            $sheet->getStyle('A' . $startRow . ':Q' . $startRow)->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN]
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => 'D6EAF8'] // Blue background for instansi
                ]
            ]);
        }
    }
} 