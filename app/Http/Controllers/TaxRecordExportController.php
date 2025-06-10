<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TaxRecordExport;

class TaxRecordExportController extends Controller
{
    public function export(Request $request)
    {
        $filters = $request->only(['month', 'year', 'invoice_type']);
        
        $filename = 'data-pajak';
        
        if (isset($filters['year'])) {
            $filename .= '-' . $filters['year'];
        }
        
        if (isset($filters['month'])) {
            $monthNames = [
                '01' => 'januari', '02' => 'februari', '03' => 'maret', '04' => 'april',
                '05' => 'mei', '06' => 'juni', '07' => 'juli', '08' => 'agustus',
                '09' => 'september', '10' => 'oktober', '11' => 'november', '12' => 'desember'
            ];
            $filename .= '-' . $monthNames[$filters['month']];
        }
        
        if (isset($filters['invoice_type'])) {
            $filename .= '-' . ($filters['invoice_type'] === '020' ? 'instansi' : 'swasta');
        }
        
        $filename .= '.xlsx';

        return Excel::download(new TaxRecordExport($filters), $filename);
    }
} 