<?php

namespace App\Exports;

use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InvoiceExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Invoice::with(['items.actions'])->get();
    }

    public function headings(): array
    {
        return [
            'Nomor Faktur',
            'Tanggal',
            'Nama',
            'Alamat',
            'NPWP',
            'Nama Barang',
            'Nomor Inventaris',
            'Bagian',
            'Tindakan',
            'Qty',
            'Satuan',
            'Harga Satuan',
            'Jumlah Harga',
            'Total Harga',
            'PPN',
            'Total Keseluruhan',
        ];
    }

    public function map($invoice): array
    {
        $rows = [];
        foreach ($invoice->items as $item) {
            foreach ($item->actions as $action) {
                $rows[] = [
                    $invoice->nomor_faktur,
                    $invoice->tanggal,
                    $invoice->nama,
                    $invoice->alamat,
                    $invoice->npwp,
                    $item->nama_barang,
                    $item->nomor_inventaris,
                    $item->bagian,
                    $action->tindakan,
                    $action->qty,
                    $action->satuan,
                    $action->harga_satuan,
                    $action->jumlah_harga,
                    $invoice->total_harga,
                    $invoice->ppn,
                    $invoice->total_keseluruhan,
                ];
            }
        }
        return $rows;
    }
} 