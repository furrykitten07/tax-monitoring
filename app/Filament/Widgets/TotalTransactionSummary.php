<?php

namespace App\Filament\Resources\AdminResource\Widgets;

use App\Models\TaxRecord;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TotalTransactionSummary extends BaseWidget
{
    protected function getStats(): array
    {
        $records = TaxRecord::all();

        $totalPrice = $records->sum('total_price');
        $totalDPP = $records->sum('dpp_amount');
        $totalPPN = $records->sum('ppn_amount');
        $totalPPH = $records->sum('pph_amount');
        $grandTotal = $records->sum('grand_total');

        return [
            Stat::make('TOTAL TRANSAKSI KESELURUHAN', '')
                ->description('Total Faktur: ' . $records->count())
                ->descriptionIcon('heroicon-m-document-chart-bar')
                ->color('primary'),
            Stat::make('Jumlah Harga', 'Rp ' . number_format($totalPrice, 0, ',', '.'))
                ->description('DPP Lain Lain: Rp ' . number_format($totalDPP, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary'),
            Stat::make('Total PPN', 'Rp ' . number_format($totalPPN, 0, ',', '.'))
                ->description('Total PPH: Rp ' . number_format($totalPPH, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),
            Stat::make('TOTAL', 'Rp ' . number_format($grandTotal, 0, ',', '.'))
                ->description('Nilai SP2D: Rp ' . number_format($records->sum('sp2d_value'), 0, ',', '.'))
                ->descriptionIcon('heroicon-m-calculator')
                ->color('primary'),
        ];
    }
}
