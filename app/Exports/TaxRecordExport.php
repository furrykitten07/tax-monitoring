<?php

namespace App\Exports;

use App\Models\TaxRecord;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TaxRecordExport implements FromCollection, WithHeadings, WithMapping
{
    protected $month;
    protected $year;

    public function __construct($month = null, $year = null)
    {
        $this->month = $month;
        $this->year = $year;
    }

    public function collection()
    {
        $query = TaxRecord::query();
        if ($this->month) {
            $query->whereMonth('date', $this->month);
        }
        if ($this->year) {
            $query->whereYear('date', $this->year);
        }
        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Nama Customer',
            'Nama Project',
            'Uraian',
            'Qty',
            'Satuan',
            'Harga Satuan',
            'Total Harga',
            'PPN',
            'Jumlah PPN',
            'PPH',
            'Jumlah PPH',
            'DPP',
            'TOTAL',
            'Nilai SP2D',
            'Nomor Faktur',
            'Tipe Faktur',
        ];
    }

    public function map($record): array
    {
        return [
            $record->date->format('d-m-Y'),
            $record->customer_name,
            $record->project_name,
            $record->description,
            $record->quantity,
            $record->unit_type,
            $record->price,
            $record->total_price,
            $record->ppn_rate.'%',
            $record->ppn_amount,
            $record->pph_rate.'%',
            $record->pph_amount,
            $record->dpp_amount,
            $record->grand_total,
            $record->sp2d_value,
            $record->invoice_number,
            $record->invoice_type == '020' ? 'Instansi' : 'Swasta',
        ];
    }
} 